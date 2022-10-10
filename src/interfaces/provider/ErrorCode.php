<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use MyCLabs\Enum\Enum;

/**
 * Error code
 *
 * @see https://github.com/open-feature/spec/blob/main/specification/types.md#error-code
 *
 * @method static ErrorCode PROVIDER_NOT_READY()
 * @method static ErrorCode FLAG_NOT_FOUND()
 * @method static ErrorCode PARSE_ERROR()
 * @method static ErrorCode TYPE_MISMATCH()
 * @method static ErrorCode TARGETING_KEY_MISSING()
 * @method static ErrorCode INVALID_CONTEXT()
 * @method static ErrorCode GENERAL()
 *
 * @extends Enum<string>
 *
 * @psalm-immutable
 */
final class ErrorCode extends Enum
{
    private const PROVIDER_NOT_READY = 'PROVIDER_NOT_READY';
    private const FLAG_NOT_FOUND = 'FLAG_NOT_FOUND';
    private const PARSE_ERROR = 'PARSE_ERROR';
    private const TYPE_MISMATCH = 'TYPE_MISMATCH';
    private const TARGETING_KEY_MISSING = 'TARGETING_KEY_MISSING';
    private const INVALID_CONTEXT = 'INVALID_CONTEXT';
    private const GENERAL = 'GENERAL';
}
