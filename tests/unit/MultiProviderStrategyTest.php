<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use DateTime;
use Exception;
use Mockery;
use OpenFeature\Test\TestCase;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\multiprovider\ProviderResolutionResult;
use OpenFeature\implementation\multiprovider\strategy\FirstMatchStrategy;
use OpenFeature\implementation\multiprovider\strategy\FirstSuccessfulStrategy;
use OpenFeature\implementation\multiprovider\strategy\StrategyEvaluationContext;
use OpenFeature\implementation\multiprovider\strategy\StrategyPerProviderContext;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\implementation\provider\ResolutionError;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;
use OpenFeature\interfaces\provider\ThrowableWithResolutionError;

class MultiProviderStrategyTest extends TestCase
{
    private Provider $mockProvider1;
    private Provider $mockProvider2;
    private Provider $mockProvider3;
    private StrategyEvaluationContext $baseContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockProvider1 = Mockery::mock(Provider::class);
        $this->mockProvider2 = Mockery::mock(Provider::class);
        $this->mockProvider3 = Mockery::mock(Provider::class);

        // Setup basic metadata for providers
        $this->mockProvider1->shouldReceive('getMetadata->getName')->andReturn('Provider1');
        $this->mockProvider2->shouldReceive('getMetadata->getName')->andReturn('Provider2');
        $this->mockProvider3->shouldReceive('getMetadata->getName')->andReturn('Provider3');

        // Create base evaluation context for tests
        $this->baseContext = new StrategyEvaluationContext(
            'test-flag',
            'boolean',
            false,
            new EvaluationContext(),
        );
    }

    public function testFirstMatchStrategyRunMode(): void
    {
        $strategy = new FirstMatchStrategy();
        $this->assertEquals('sequential', $strategy->runMode);
    }

    public function testFirstSuccessfulStrategyRunMode(): void
    {
        $strategy = new FirstSuccessfulStrategy();
        $this->assertEquals('sequential', $strategy->runMode);
    }

    public function testFirstMatchStrategyShouldEvaluateThisProvider(): void
    {
        $strategy = new FirstMatchStrategy();
        $context = new StrategyPerProviderContext($this->baseContext, 'test1', $this->mockProvider1);

        $this->assertTrue($strategy->shouldEvaluateThisProvider($context));
    }

    public function testFirstSuccessfulStrategyShouldEvaluateThisProvider(): void
    {
        $strategy = new FirstSuccessfulStrategy();
        $context = new StrategyPerProviderContext($this->baseContext, 'test1', $this->mockProvider1);

        $this->assertTrue($strategy->shouldEvaluateThisProvider($context));
    }

    public function testFirstMatchStrategyWithSuccessfulResult(): void
    {
        $strategy = new FirstMatchStrategy();
        $context = new StrategyPerProviderContext($this->baseContext, 'test1', $this->mockProvider1);

        $details = $this->createResolutionDetails(true);
        $result = new ProviderResolutionResult('test1', $this->mockProvider1, $details, null);

        $this->assertFalse($strategy->shouldEvaluateNextProvider($context, $result));
    }

    public function testFirstMatchStrategyWithFlagNotFoundError(): void
    {
        $strategy = new FirstMatchStrategy();
        $context = new StrategyPerProviderContext($this->baseContext, 'test1', $this->mockProvider1);

        $error = new class extends Exception implements ThrowableWithResolutionError {
            public function getResolutionError(): \OpenFeature\interfaces\provider\ResolutionError
            {
                return new ResolutionError(ErrorCode::FLAG_NOT_FOUND(), 'Flag not found');
            }
        };

        $result = new ProviderResolutionResult('test1', $this->mockProvider1, null, $error);

        $this->assertTrue($strategy->shouldEvaluateNextProvider($context, $result));
    }

    public function testFirstMatchStrategyWithGeneralError(): void
    {
        $strategy = new FirstMatchStrategy();
        $context = new StrategyPerProviderContext($this->baseContext, 'test1', $this->mockProvider1);

        $error = new class extends Exception implements ThrowableWithResolutionError {
            public function getResolutionError(): \OpenFeature\interfaces\provider\ResolutionError
            {
                return new ResolutionError(ErrorCode::GENERAL(), 'General error');
            }
        };

        $result = new ProviderResolutionResult('test1', $this->mockProvider1, null, $error);

        $this->assertFalse($strategy->shouldEvaluateNextProvider($context, $result));
    }

    public function testFirstSuccessfulStrategyWithSuccessfulResult(): void
    {
        $strategy = new FirstSuccessfulStrategy();
        $context = new StrategyPerProviderContext($this->baseContext, 'test1', $this->mockProvider1);

        $details = $this->createResolutionDetails(true);
        $result = new ProviderResolutionResult('test1', $this->mockProvider1, $details, null);

        $this->assertFalse($strategy->shouldEvaluateNextProvider($context, $result));
    }

    public function testFirstSuccessfulStrategyWithError(): void
    {
        $strategy = new FirstSuccessfulStrategy();
        $context = new StrategyPerProviderContext($this->baseContext, 'test1', $this->mockProvider1);

        $error = new Exception('Test error');
        $result = new ProviderResolutionResult('test1', $this->mockProvider1, null, $error);

        $this->assertTrue($strategy->shouldEvaluateNextProvider($context, $result));
    }

    public function testFirstMatchStrategyDetermineFinalResultSuccess(): void
    {
        $strategy = new FirstMatchStrategy();

        $details1 = $this->createResolutionDetails(true);
        $result1 = new ProviderResolutionResult('test1', $this->mockProvider1, $details1, null);

        $finalResult = $strategy->determineFinalResult($this->baseContext, [$result1]);

        $this->assertTrue($finalResult->isSuccessful());
        $this->assertEquals('test1', $finalResult->getProviderName());
        $this->assertSame($details1, $finalResult->getDetails());
    }

    public function testFirstMatchStrategyDetermineFinalResultAllFlagNotFound(): void
    {
        $strategy = new FirstMatchStrategy();

        $error = new class extends Exception implements ThrowableWithResolutionError {
            public function getResolutionError(): \OpenFeature\interfaces\provider\ResolutionError
            {
                return new ResolutionError(ErrorCode::FLAG_NOT_FOUND(), 'Flag not found');
            }
        };

        $result1 = new ProviderResolutionResult('test1', $this->mockProvider1, null, $error);
        $result2 = new ProviderResolutionResult('test2', $this->mockProvider2, null, $error);

        $finalResult = $strategy->determineFinalResult($this->baseContext, [$result1, $result2]);

        $this->assertFalse($finalResult->isSuccessful());
        $this->assertNotNull($finalResult->getErrors());
    }

    public function testFirstSuccessfulStrategyDetermineFinalResultSuccess(): void
    {
        $strategy = new FirstSuccessfulStrategy();

        $error = new Exception('Test error');
        $result1 = new ProviderResolutionResult('test1', $this->mockProvider1, null, $error);

        $details2 = $this->createResolutionDetails(true);
        $result2 = new ProviderResolutionResult('test2', $this->mockProvider2, $details2, null);

        $finalResult = $strategy->determineFinalResult($this->baseContext, [$result1, $result2]);

        $this->assertTrue($finalResult->isSuccessful());
        $this->assertEquals('test2', $finalResult->getProviderName());
        $this->assertSame($details2, $finalResult->getDetails());
    }

    public function testFirstSuccessfulStrategyDetermineFinalResultAllErrors(): void
    {
        $strategy = new FirstSuccessfulStrategy();

        $error1 = new Exception('Error 1');
        $error2 = new Exception('Error 2');

        $result1 = new ProviderResolutionResult('test1', $this->mockProvider1, null, $error1);
        $result2 = new ProviderResolutionResult('test2', $this->mockProvider2, null, $error2);

        $finalResult = $strategy->determineFinalResult($this->baseContext, [$result1, $result2]);
        /** @var ThrowableWithResolutionError[] $error */
        $error = $finalResult->getErrors();
        $this->assertFalse($finalResult->isSuccessful());
        $this->assertCount(2, $error);
    }

    /**
     * @param bool|string|int|float|DateTime|array<mixed>|null $value
     */
    private function createResolutionDetails(bool | string | int | float | DateTime | array | null $value): ResolutionDetails
    {
        return (new ResolutionDetailsBuilder())
            ->withValue($value)
            ->build();
    }
}
