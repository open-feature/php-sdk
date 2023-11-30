<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\Test\TestCase;
use OpenFeature\implementation\hooks\HookContextBuilder;
use OpenFeature\implementation\hooks\ImmutableHookContext;
use OpenFeature\implementation\hooks\MutableHookContext;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\HookContext;

class HookContextBuilderTest extends TestCase
{
    public function testAsMutable(): void
    {
        $expectedValue = new MutableHookContext(['flagKey' => 'test-key']);

        $actualValue = (new HookContextBuilder())->withFlagKey('test-key')->asMutable()->build();

        $this->assertInstanceOf(HookContext::class, $actualValue);
        $this->assertEqualsCanonicalizing($expectedValue, $actualValue);
    }

    public function testAsImmutable(): void
    {
        $expectedValue = new ImmutableHookContext(['flagKey' => 'test-key', 'type' => FlagValueType::Boolean]);

        $actualValue = (new HookContextBuilder())->withFlagKey('test-key')->withType(FlagValueType::Boolean)->asImmutable()->build();

        $this->assertInstanceOf(HookContext::class, $actualValue);
        $this->assertEqualsCanonicalizing($expectedValue, $actualValue);
    }
}
