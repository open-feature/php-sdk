<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

/**
 * Run mode for multi-provider evaluation strategies.
 */
enum RunMode: string
{
    case SEQUENTIAL = 'sequential';
    case EVALUATE_ALL = 'evaluate_all';
}
