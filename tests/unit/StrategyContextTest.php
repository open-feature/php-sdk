<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\multiprovider\strategy\StrategyContext;
use PHPUnit\Framework\TestCase;

class StrategyContextTest extends TestCase
{
    public function testValidBooleanFlagType(): void
    {
        $context = new StrategyContext('flag-key', 'boolean', true, new EvaluationContext());
        $this->assertEquals('flag-key', $context->getFlagKey());
        $this->assertEquals('boolean', $context->getFlagType());
        $this->assertTrue($context->getDefaultValue());
        $this->assertInstanceOf(EvaluationContext::class, $context->getEvaluationContext());
    }

    public function testValidStringFlagType(): void
    {
        $context = new StrategyContext('flag-key', 'string', 'default', new EvaluationContext());
        $this->assertEquals('string', $context->getFlagType());
        $this->assertEquals('default', $context->getDefaultValue());
    }

    public function testValidIntegerFlagType(): void
    {
        $context = new StrategyContext('flag-key', 'integer', 42, new EvaluationContext());
        $this->assertEquals('integer', $context->getFlagType());
        $this->assertEquals(42, $context->getDefaultValue());
    }

    public function testValidFloatFlagType(): void
    {
        $context = new StrategyContext('flag-key', 'float', 3.14, new EvaluationContext());
        $this->assertEquals('float', $context->getFlagType());
        $this->assertEquals(3.14, $context->getDefaultValue());
    }

    public function testValidObjectFlagType(): void
    {
        $context = new StrategyContext('flag-key', 'object', ['key' => 'value'], new EvaluationContext());
        $this->assertEquals('object', $context->getFlagType());
        $this->assertEquals(['key' => 'value'], $context->getDefaultValue());
    }
}
