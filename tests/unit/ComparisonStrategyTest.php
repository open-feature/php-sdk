<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Exception;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use OpenFeature\Test\TestCase;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\multiprovider\MultiProvider;
use OpenFeature\implementation\multiprovider\strategy\ComparisonStrategy;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;

use function count;

class ComparisonStrategyTest extends TestCase
{
    /** @var Provider&MockInterface */
    private Provider $providerA;
    /** @var Provider&MockInterface */
    private Provider $providerB;
    /** @var Provider&MockInterface */
    private Provider $providerC;

    protected function setUp(): void
    {
        parent::setUp();
        $this->providerA = Mockery::mock(Provider::class);
        $this->providerB = Mockery::mock(Provider::class);
        $this->providerC = Mockery::mock(Provider::class);

        $this->providerA->shouldReceive('getMetadata->getName')->andReturn('ProviderA');
        $this->providerB->shouldReceive('getMetadata->getName')->andReturn('ProviderB');
        $this->providerC->shouldReceive('getMetadata->getName')->andReturn('ProviderC');
    }

    private function details(bool $value): ResolutionDetails
    {
        return (new ResolutionDetailsBuilder())->withValue($value)->build();
    }

    public function testAllProvidersAgreeReturnsFirstValue(): void
    {
        $strategy = new ComparisonStrategy($this->providerB); // fallback is required
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerC->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new MultiProvider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
                ['name' => 'c', 'provider' => $this->providerC],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        $this->assertTrue($res->getValue());
    }

    public function testMismatchUsesFallbackProvider(): void
    {
        $strategy = new ComparisonStrategy($this->providerB);
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));
        $this->providerC->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new MultiProvider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
                ['name' => 'c', 'provider' => $this->providerC],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        // When values mismatch, returns fallback provider's value (false from providerB)
        $this->assertFalse($res->getValue());
    }

    public function testOnMismatchCallbackInvoked(): void
    {
        $invoked = false;
        $capturedCount = 0;
        $callback = function (array $resolutions) use (&$invoked, &$capturedCount): void {
            $invoked = true;
            $capturedCount = count($resolutions);
        };

        $strategy = new ComparisonStrategy($this->providerB, $callback);
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));
        $this->providerC->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new MultiProvider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
                ['name' => 'c', 'provider' => $this->providerC],
            ],
            $strategy,
        );

        $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        $this->assertTrue($invoked);
        $this->assertEquals(3, $capturedCount);
    }

    public function testAnyProviderErrorReturnsAllErrors(): void
    {
        // Per js-sdk: fail-fast on ANY error - returns all errors immediately
        $strategy = new ComparisonStrategy($this->providerB);
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andThrow(new Exception('err'));
        $this->providerC->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new MultiProvider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
                ['name' => 'c', 'provider' => $this->providerC],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        // Should return error immediately when any provider fails
        $this->assertNotNull($res->getError());
        $this->assertFalse($res->getValue()); // returns default
    }

    public function testAllProvidersErrorReturnsAggregatedErrors(): void
    {
        $strategy = new ComparisonStrategy($this->providerB);
        $this->providerA->shouldReceive('resolveBooleanValue')->andThrow(new Exception('a'));
        $this->providerB->shouldReceive('resolveBooleanValue')->andThrow(new Exception('b'));

        $mp = new MultiProvider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        // Should return aggregated errors
        $this->assertNotNull($res->getError());
        $this->assertFalse($res->getValue()); // returns default value
    }

    public function testFallbackProviderNotInResultsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fallback provider not found in resolution results');

        $providerD = Mockery::mock(Provider::class);
        $providerD->shouldReceive('getMetadata->getName')->andReturn('ProviderD');

        // Use providerD as fallback but don't include it in the provider list
        $strategy = new ComparisonStrategy($providerD);
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new MultiProvider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
            ],
            $strategy,
        );

        // Should throw when fallback provider not found
        $mp->resolveBooleanValue('flag', true, new EvaluationContext());
    }

    public function testOnMismatchCallbackErrorsAreIgnored(): void
    {
        $callback = function (array $resolutions): void {
            throw new Exception('Callback error');
        };

        $strategy = new ComparisonStrategy($this->providerB, $callback);
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));

        $mp = new MultiProvider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
            ],
            $strategy,
        );

        // Should not throw despite callback error
        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        $this->assertFalse($res->getValue());
    }
}
