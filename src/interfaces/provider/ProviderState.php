<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

enum ProviderState: string
{
    case NOT_READY = 'NOT_READY';
    case READY = 'READY';
    case ERROR = 'ERROR';
}
