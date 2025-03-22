<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\Test\TestCase;
use OpenFeature\implementation\common\StringHelper;

class StringHelperTest extends TestCase
{
    /**
     * @dataProvider capitalizeData
     */
    public function testCapitalize(string $input, string $expectedValue): void
    {
        $actualValue = StringHelper::capitalize($input);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * @return array<array<string>>
     */
    public function capitalizeData(): array
    {
        return [
            ['', ''],
            ['a', 'A'],
            ['A', 'A'],
            ['ab', 'Ab'],
            ['Ab', 'Ab'],
            ['abc', 'Abc'],
            ['Abc', 'Abc'],
        ];
    }

    /**
     * @dataProvider decapitalizeData
     */
    public function testDecapitalize(string $input, string $expectedValue): void
    {
        $actualValue = StringHelper::decapitalize($input);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * @return array<array<string>>
     */
    public function decapitalizeData(): array
    {
        return [
            ['', ''],
            ['a', 'a'],
            ['A', 'a'],
            ['ab', 'ab'],
            ['Ab', 'ab'],
            ['abc', 'abc'],
            ['Abc', 'abc'],
        ];
    }
}
