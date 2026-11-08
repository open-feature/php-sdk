<?php

declare(strict_types=1);

namespace OpenFeature\implementation\errors;

use Exception;
use OpenFeature\interfaces\flags\FlagValueType;
use Throwable;

use function sprintf;

class InvalidResolutionValueError extends Exception
{
    private const ERROR_MESSAGE_TEMPLATE = "The resolution value type does not match the expected type '%s'";

    public function __construct(FlagValueType $flagValueType, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf(self::ERROR_MESSAGE_TEMPLATE, $flagValueType->value);

        parent::__construct($message, $code, $previous);
    }
}
