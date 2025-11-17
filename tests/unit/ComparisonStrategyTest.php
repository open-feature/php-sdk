<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Exception;
use Mockery;
use Mockery\MockInterface;
use OpenFeature\Test\TestCase;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\multiprovider\Multiprovider;
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
        $strategy = new ComparisonStrategy();
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerC->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new Multiprovider(
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
        $strategy = new ComparisonStrategy('b');
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));
        $this->providerC->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new Multiprovider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
                ['name' => 'c', 'provider' => $this->providerC],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        $this->assertFalse($res->getValue());
    }

    public function testMismatchWithoutFallbackReturnsFirstSuccessful(): void
    {
        $strategy = new ComparisonStrategy(); // no fallback
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));

        $mp = new Multiprovider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        $this->assertTrue($res->getValue());
    }

    public function testOnMismatchCallbackInvoked(): void
    {
        $invoked = false;
        $capturedCount = 0;
        $callback = function (array $resolutions) use (&$invoked, &$capturedCount): void {
            $invoked = true;
            $capturedCount = count($resolutions);
        };

        $strategy = new ComparisonStrategy(null, $callback);
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));
        $this->providerC->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new Multiprovider(
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

    public function testSingleSuccessfulResult(): void
    {
        $strategy = new ComparisonStrategy();
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));
        $this->providerB->shouldReceive('resolveBooleanValue')->andThrow(new Exception('err'));
        $this->providerC->shouldReceive('resolveBooleanValue')->andThrow(new Exception('err2'));

        $mp = new Multiprovider(
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

    public function testNoSuccessfulResultsReturnsError(): void
    {
        $strategy = new ComparisonStrategy();
        $this->providerA->shouldReceive('resolveBooleanValue')->andThrow(new Exception('a'));
        $this->providerB->shouldReceive('resolveBooleanValue')->andThrow(new Exception('b'));

        $mp = new Multiprovider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', false, new EvaluationContext());
        $this->assertNotNull($res->getError());
    }

    public function testMismatchFallbackNotFoundReturnsFirst(): void
    {
        $strategy = new ComparisonStrategy('non-existent');
        $this->providerA->shouldReceive('resolveBooleanValue')->andReturn($this->details(false));
        $this->providerB->shouldReceive('resolveBooleanValue')->andReturn($this->details(true));

        $mp = new Multiprovider(
            [
                ['name' => 'a', 'provider' => $this->providerA],
                ['name' => 'b', 'provider' => $this->providerB],
            ],
            $strategy,
        );

        $res = $mp->resolveBooleanValue('flag', true, new EvaluationContext());
        $this->assertFalse($res->getValue());
    }
}
