<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

/**
 * Run mode for multi-provider evaluation strategies.
 */
final class RunMode
{
    public const SEQUENTIAL = 'sequential';
    public const EVALUATE_ALL = 'evaluate_all';

    private function __construct()
    {
    }
}
