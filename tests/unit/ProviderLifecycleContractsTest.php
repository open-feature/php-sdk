<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\implementation\events\EventDetails;
use OpenFeature\implementation\events\ProviderEventDetails;
use OpenFeature\implementation\events\ProviderEventEmitterTrait;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderStatus;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\ProviderEventEmitter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ProviderLifecycleContractsTest extends TestCase
{
    public function testProviderEventsAndStatusesExposeSpecificationValues(): void
    {
        $this->assertSame('PROVIDER_READY', ProviderEvent::READY()->getValue());
        $this->assertSame('PROVIDER_ERROR', ProviderEvent::ERROR()->getValue());
        $this->assertSame('PROVIDER_STALE', ProviderEvent::STALE()->getValue());
        $this->assertSame('PROVIDER_CONFIGURATION_CHANGED', ProviderEvent::CONFIGURATION_CHANGED()->getValue());
        $this->assertSame('NOT_READY', ProviderStatus::NOT_READY()->getValue());
        $this->assertSame('READY', ProviderStatus::READY()->getValue());
        $this->assertSame('STALE', ProviderStatus::STALE()->getValue());
        $this->assertSame('ERROR', ProviderStatus::ERROR()->getValue());
        $this->assertSame('FATAL', ProviderStatus::FATAL()->getValue());
        $this->assertSame('PROVIDER_FATAL', ErrorCode::PROVIDER_FATAL()->getValue());
    }

    public function testEventDetailsIncludeProviderAndProviderSuppliedData(): void
    {
        $providerDetails = new ProviderEventDetails(
            'configuration changed',
            ['first-flag', 'second-flag'],
            ['source' => 'test'],
            ErrorCode::GENERAL(),
        );

        $details = new EventDetails('test-provider', $providerDetails);

        $this->assertSame('test-provider', $details->getProviderName());
        $this->assertSame('configuration changed', $details->getMessage());
        $this->assertSame(['first-flag', 'second-flag'], $details->getFlagsChanged());
        $this->assertSame(['source' => 'test'], $details->getEventMetadata());
        $this->assertTrue(ErrorCode::GENERAL()->equals($details->getErrorCode()));
    }

    public function testEmitterSupportsRemovalAndIsolatesFailingHandlers(): void
    {
        $emitter = new class implements ProviderEventEmitter {
            use ProviderEventEmitterTrait;

            public function emit(ProviderEvent $event, ProviderEventDetails $details): void
            {
                $this->emitProviderEvent($event, $details);
            }
        };
        $received = [];
        $removedHandler = static function () use (&$received): void {
            $received[] = 'removed';
        };
        $emitter->addProviderEventHandler($removedHandler);
        $emitter->removeProviderEventHandler($removedHandler);
        $emitter->addProviderEventHandler(static function (): void {
            throw new RuntimeException('handler failed');
        });
        $emitter->addProviderEventHandler(
            static function (ProviderEvent $event) use (&$received): void {
                $received[] = $event->getValue();
            },
        );

        $emitter->emit(ProviderEvent::READY(), new ProviderEventDetails());

        $this->assertSame(['PROVIDER_READY'], $received);
    }
}
