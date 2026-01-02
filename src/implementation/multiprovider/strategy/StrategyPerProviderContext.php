<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use OpenFeature\interfaces\provider\Provider;

/**
 * Context information for evaluating a specific provider in a multi-provider setup.
 * Extends StrategyEvaluationContext with provider-specific information.
 */
class StrategyPerProviderContext extends StrategyEvaluationContext
{
    public function __construct(
        StrategyEvaluationContext $baseContext,
        private string $providerName,
        private Provider $provider,
    ) {
        parent::__construct(
            $baseContext->getFlagKey(),
            $baseContext->getFlagType(),
            $baseContext->getDefaultValue(),
            $baseContext->getEvaluationContext(),
        );
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }
}
