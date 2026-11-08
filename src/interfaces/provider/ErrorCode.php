<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

/**
 * Error code
 *
 * @see https://github.com/open-feature/spec/blob/main/specification/types.md#error-code
 */
enum ErrorCode: string
{
    case PROVIDER_NOT_READY = 'PROVIDER_NOT_READY';
    case FLAG_NOT_FOUND = 'FLAG_NOT_FOUND';
    case PARSE_ERROR = 'PARSE_ERROR';
    case TYPE_MISMATCH = 'TYPE_MISMATCH';
    case TARGETING_KEY_MISSING = 'TARGETING_KEY_MISSING';
    case INVALID_CONTEXT = 'INVALID_CONTEXT';
    case GENERAL = 'GENERAL';
}
