<?php

declare(strict_types=1);

namespace OpenFeature\Test;

use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\provider\ProviderLifecycle;
use RuntimeException;

class LegacyLifecycleTestProvider extends TestProvider implements ProviderLifecycle
{
    public int $initializeCalls = 0;
    public int $shutdownCalls = 0;
    public ?EvaluationContext $initialContext = null;
    public ?string $initialDomain = null;
    public bool $failInitialization = false;

    public function initialize(EvaluationContext $context, ?string $domain = null): void
    {
        ++$this->initializeCalls;
        $this->initialContext = $context;
        $this->initialDomain = $domain;

        if ($this->failInitialization) {
            throw new RuntimeException('legacy initialization failed');
        }
    }

    public function shutdown(): void
    {
        ++$this->shutdownCalls;
    }
}
