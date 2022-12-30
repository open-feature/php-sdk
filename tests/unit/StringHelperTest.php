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
     * @return Array<Array<string>>
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
}
