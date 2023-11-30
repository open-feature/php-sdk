<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

enum Reason: string
{
    case Error = 'Error';

    /**
     * @deprecated prefer enum value over const
     */
    public const ERROR = 'Error';
}
