<?php

declare(strict_types=1);

namespace OpenFeature\Test;

use OpenFeature\implementation\events\ProviderEventDetails;
use OpenFeature\implementation\events\ProviderEventEmitterTrait;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderEventDetails as ProviderEventDetailsInterface;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\ProviderEventAware;
use RuntimeException;

class LifecycleTestProvider extends TestProvider implements ProviderEventAware
{
    use ProviderEventEmitterTrait;

    public int $initializeCalls = 0;
    public int $shutdownCalls = 0;
    public ?EvaluationContext $initialContext = null;
    public ?string $initialDomain = null;
    public bool $failInitialization = false;
    public bool $failShutdown = false;
    public bool $emitInitializationEvent = true;
    public bool $fatalInitializationError = false;
    public bool $returnAfterInitializationError = false;

    public function initialize(EvaluationContext $context, ?string $domain = null): void
    {
        ++$this->initializeCalls;
        $this->initialContext = $context;
        $this->initialDomain = $domain;

        if ($this->failInitialization) {
            if ($this->emitInitializationEvent) {
                $this->emit(
                    ProviderEvent::ERROR(),
                    new ProviderEventDetails(
                        'initialization failed',
                        [],
                        [],
                        $this->fatalInitializationError ? ErrorCode::PROVIDER_FATAL() : ErrorCode::GENERAL(),
                    ),
                );
            }

            if ($this->returnAfterInitializationError) {
                return;
            }

            throw new RuntimeException('initialization failed');
        }

        if ($this->emitInitializationEvent) {
            $this->emit(ProviderEvent::READY(), new ProviderEventDetails());
        }
    }

    public function shutdown(): void
    {
        ++$this->shutdownCalls;

        if ($this->failShutdown) {
            throw new RuntimeException('shutdown failed');
        }
    }

    public function emit(ProviderEvent $event, ?ProviderEventDetailsInterface $details = null): void
    {
        $this->emitProviderEvent($event, $details ?? new ProviderEventDetails());
    }
}
