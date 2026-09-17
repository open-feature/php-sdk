<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\OpenFeatureAPI;
use OpenFeature\implementation\events\EventDetails;
use OpenFeature\implementation\events\ProviderEventDetails;
use OpenFeature\implementation\events\ProviderEventEmitterTrait;
use OpenFeature\implementation\multiprovider\MultiProvider;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderStatus;
use OpenFeature\interfaces\flags\API;
use OpenFeature\interfaces\flags\Client;
use OpenFeature\interfaces\flags\EventAwareClient;
use OpenFeature\interfaces\flags\ProviderLifecycleAPI;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\ProviderEventEmitter;
use OpenFeature\interfaces\provider\ProviderLifecycle;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function method_exists;

class ProviderLifecycleContractsTest extends TestCase
{
    public function testLifecycleAndEventCapabilitiesDoNotChangeBaseApiAndClientContracts(): void
    {
        $this->assertFalse(method_exists(API::class, 'setProviderAndWait'));
        $this->assertFalse(method_exists(API::class, 'shutdown'));
        $this->assertFalse(method_exists(API::class, 'getProviderStatus'));
        $this->assertFalse(method_exists(API::class, 'addHandler'));
        $this->assertFalse(method_exists(API::class, 'removeHandler'));
        $this->assertFalse(method_exists(Client::class, 'getProviderStatus'));
        $this->assertFalse(method_exists(Client::class, 'addHandler'));
        $this->assertFalse(method_exists(Client::class, 'removeHandler'));

        $api = OpenFeatureAPI::getInstance();
        $this->assertInstanceOf(ProviderLifecycleAPI::class, $api);
        $this->assertInstanceOf(EventAwareClient::class, $api->getClient());
    }

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

    public function testMultiProviderDoesNotClaimPartialLifecycleOrEventSupport(): void
    {
        $multiProvider = new MultiProvider();

        $this->assertNotInstanceOf(ProviderLifecycle::class, $multiProvider);
        $this->assertNotInstanceOf(ProviderEventEmitter::class, $multiProvider);
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
