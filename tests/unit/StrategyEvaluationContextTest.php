<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use InvalidArgumentException;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\multiprovider\strategy\StrategyEvaluationContext;
use PHPUnit\Framework\TestCase;

class StrategyEvaluationContextTest extends TestCase
{
    public function testValidBooleanFlagType(): void
    {
        $context = new StrategyEvaluationContext('flag-key', 'boolean', true, new EvaluationContext());
        $this->assertEquals('flag-key', $context->getFlagKey());
        $this->assertEquals('boolean', $context->getFlagType());
        $this->assertTrue($context->getDefaultValue());
        $this->assertInstanceOf(EvaluationContext::class, $context->getEvaluationContext());
    }

    public function testValidStringFlagType(): void
    {
        $context = new StrategyEvaluationContext('flag-key', 'string', 'default', new EvaluationContext());
        $this->assertEquals('string', $context->getFlagType());
        $this->assertEquals('default', $context->getDefaultValue());
    }

    public function testValidIntegerFlagType(): void
    {
        $context = new StrategyEvaluationContext('flag-key', 'integer', 42, new EvaluationContext());
        $this->assertEquals('integer', $context->getFlagType());
        $this->assertEquals(42, $context->getDefaultValue());
    }

    public function testValidFloatFlagType(): void
    {
        $context = new StrategyEvaluationContext('flag-key', 'float', 3.14, new EvaluationContext());
        $this->assertEquals('float', $context->getFlagType());
        $this->assertEquals(3.14, $context->getDefaultValue());
    }

    public function testValidObjectFlagType(): void
    {
        $context = new StrategyEvaluationContext('flag-key', 'object', ['key' => 'value'], new EvaluationContext());
        $this->assertEquals('object', $context->getFlagType());
        $this->assertEquals(['key' => 'value'], $context->getDefaultValue());
    }

    public function testInvalidBooleanDefaultValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StrategyEvaluationContext('flag-key', 'boolean', 'not-a-bool', new EvaluationContext());
    }

    public function testInvalidStringDefaultValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StrategyEvaluationContext('flag-key', 'string', 123, new EvaluationContext());
    }

    public function testInvalidIntegerDefaultValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StrategyEvaluationContext('flag-key', 'integer', 'not-an-int', new EvaluationContext());
    }

    public function testInvalidFloatDefaultValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StrategyEvaluationContext('flag-key', 'float', 'not-a-float', new EvaluationContext());
    }

    public function testInvalidObjectDefaultValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StrategyEvaluationContext('flag-key', 'object', 'not-an-array', new EvaluationContext());
    }

    public function testUnknownFlagTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StrategyEvaluationContext('flag-key', 'unknown-type', 'value', new EvaluationContext());
    }
}
