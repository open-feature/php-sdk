<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\Test\TestCase;
use OpenFeature\implementation\hooks\BooleanHook;
use OpenFeature\implementation\hooks\FloatHook;
use OpenFeature\implementation\hooks\IntegerHook;
use OpenFeature\implementation\hooks\ObjectHook;
use OpenFeature\implementation\hooks\StringHook;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\hooks\HookHints;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Throwable;

class BaseHooksTest extends TestCase
{
  /**
   * @dataProvider dataBooleanHook
   */
    public function testBooleanHook(FlagValueType $flagValueType, bool $supportsFlagValueType): void
    {
        $testBooleanHook = new class extends BooleanHook {
            public function before(HookContext $context, HookHints $hints): ?EvaluationContext
            {
                return null;
            }

            public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
            {
              // no-op
            }

            public function error(HookContext $context, Throwable $error, HookHints $hints): void
            {
              // no-op
            }

            public function finally(HookContext $context, HookHints $hints): void
            {
              // no-op
            }
        };

        $this->assertEquals($supportsFlagValueType, $testBooleanHook->supportsFlagValueType($flagValueType));
    }

  /**
   * @return Array<Array<mixed>>
   */
    public function dataBooleanHook(): array
    {
        return [
            [FlagValueType::Boolean, true],
            [FlagValueType::Float, false],
            [FlagValueType::Integer, false],
            [FlagValueType::Object, false],
            [FlagValueType::String, false],
        ];
    }

  /**
   * @dataProvider dataFloatHook
   */
    public function testFloatHook(FlagValueType $flagValueType, bool $supportsFlagValueType): void
    {
        $testFloatHook = new class extends FloatHook {
            public function before(HookContext $context, HookHints $hints): ?EvaluationContext
            {
                return null;
            }

            public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
            {
              // no-op
            }

            public function error(HookContext $context, Throwable $error, HookHints $hints): void
            {
              // no-op
            }

            public function finally(HookContext $context, HookHints $hints): void
            {
              // no-op
            }
        };

        $this->assertEquals($supportsFlagValueType, $testFloatHook->supportsFlagValueType($flagValueType));
    }

  /**
   * @return Array<Array<mixed>>
   */
    public function dataFloatHook(): array
    {
        return [
            [FlagValueType::Boolean, false],
            [FlagValueType::Float, true],
            [FlagValueType::Integer, false],
            [FlagValueType::Object, false],
            [FlagValueType::String, false],
        ];
    }

  /**
   * @dataProvider dataIntegerHook
   */
    public function testIntegerHook(FlagValueType $flagValueType, bool $supportsFlagValueType): void
    {
        $testIntegerHook = new class extends IntegerHook {
            public function before(HookContext $context, HookHints $hints): ?EvaluationContext
            {
                return null;
            }

            public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
            {
              // no-op
            }

            public function error(HookContext $context, Throwable $error, HookHints $hints): void
            {
              // no-op
            }

            public function finally(HookContext $context, HookHints $hints): void
            {
              // no-op
            }
        };

        $this->assertEquals($supportsFlagValueType, $testIntegerHook->supportsFlagValueType($flagValueType));
    }

  /**
   * @return Array<Array<mixed>>
   */
    public function dataIntegerHook(): array
    {
        return [
            [FlagValueType::Boolean, false],
            [FlagValueType::Float, false],
            [FlagValueType::Integer, true],
            [FlagValueType::Object, false],
            [FlagValueType::String, false],
        ];
    }

  /**
   * @dataProvider dataObjectHook
   */
    public function testObjectHook(FlagValueType $flagValueType, bool $supportsFlagValueType): void
    {
        $testObjectHook = new class extends ObjectHook {
            public function before(HookContext $context, HookHints $hints): ?EvaluationContext
            {
                return null;
            }

            public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
            {
              // no-op
            }

            public function error(HookContext $context, Throwable $error, HookHints $hints): void
            {
              // no-op
            }

            public function finally(HookContext $context, HookHints $hints): void
            {
              // no-op
            }
        };

        $this->assertEquals($supportsFlagValueType, $testObjectHook->supportsFlagValueType($flagValueType));
    }

  /**
   * @return Array<Array<mixed>>
   */
    public function dataObjectHook(): array
    {
        return [
            [FlagValueType::Boolean, false],
            [FlagValueType::Float, false],
            [FlagValueType::Integer, false],
            [FlagValueType::Object, true],
            [FlagValueType::String, false],
        ];
    }

  /**
   * @dataProvider dataStringHook
   */
    public function testStringHook(FlagValueType $flagValueType, bool $supportsFlagValueType): void
    {
        $testStringHook = new class extends StringHook {
            public function before(HookContext $context, HookHints $hints): ?EvaluationContext
            {
                return null;
            }

            public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
            {
              // no-op
            }

            public function error(HookContext $context, Throwable $error, HookHints $hints): void
            {
              // no-op
            }

            public function finally(HookContext $context, HookHints $hints): void
            {
              // no-op
            }
        };

        $this->assertEquals($supportsFlagValueType, $testStringHook->supportsFlagValueType($flagValueType));
    }

  /**
   * @return Array<Array<mixed>>
   */
    public function dataStringHook(): array
    {
        return [
            [FlagValueType::Boolean, false],
            [FlagValueType::Float, false],
            [FlagValueType::Integer, false],
            [FlagValueType::Object, false],
            [FlagValueType::String, true],
        ];
    }
}
