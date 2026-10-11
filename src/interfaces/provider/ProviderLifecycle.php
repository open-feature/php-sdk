<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use OpenFeature\interfaces\flags\EvaluationContext;

/**
 * Optional contract for providers which require initialization or cleanup.
 */
interface ProviderLifecycle
{
    public function initialize(EvaluationContext $context, ?string $domain = null): void;

    public function shutdown(): void;
}
