<?php

declare(strict_types=1);

namespace OpenFeature\implementation\errors;

use Exception;
use Throwable;

use function sprintf;

class FlagValueTypeError extends Exception
{
    private const ERROR_MESSAGE_TEMPLATE = "Flag value type '%s' is not valid";

    public function __construct(string $flagValueType, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf(self::ERROR_MESSAGE_TEMPLATE, $flagValueType);

        parent::__construct($message, $code, $previous);
    }
}
