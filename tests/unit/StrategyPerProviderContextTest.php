<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Mockery;
use Mockery\MockInterface;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\multiprovider\strategy\StrategyEvaluationContext;
use OpenFeature\implementation\multiprovider\strategy\StrategyPerProviderContext;
use OpenFeature\interfaces\provider\Provider;
use PHPUnit\Framework\TestCase;

class StrategyPerProviderContextTest extends TestCase
{
    /** @var Provider&MockInterface */
    private Provider $mockProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockProvider = Mockery::mock(Provider::class);
        $this->mockProvider->shouldReceive('getMetadata->getName')->andReturn('TestProvider');
    }

    public function testProviderContextGetters(): void
    {
        $baseContext = new StrategyEvaluationContext(
            'flag-key',
            'string',
            'default-value',
            new EvaluationContext(),
        );
        $providerName = 'TestProviderName';
        $context = new StrategyPerProviderContext($baseContext, $providerName, $this->mockProvider);

        $this->assertEquals('flag-key', $context->getFlagKey());
        $this->assertEquals('string', $context->getFlagType());
        $this->assertEquals('default-value', $context->getDefaultValue());
        $this->assertInstanceOf(EvaluationContext::class, $context->getEvaluationContext());
        $this->assertEquals($providerName, $context->getProviderName());
        $this->assertSame($this->mockProvider, $context->getProvider());
    }

    public function testProviderContextWithDifferentProviderName(): void
    {
        $baseContext = new StrategyEvaluationContext(
            'flag-key',
            'boolean',
            true,
            new EvaluationContext(),
        );
        $providerName = 'AnotherProvider';
        $context = new StrategyPerProviderContext($baseContext, $providerName, $this->mockProvider);

        $this->assertEquals($providerName, $context->getProviderName());
    }
}
