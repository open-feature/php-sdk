<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use ArrayObject;
use OpenFeature\OpenFeatureAPI;
use OpenFeature\Test\LegacyLifecycleTestProvider;
use OpenFeature\Test\LifecycleTestProvider;
use OpenFeature\Test\ProviderEventCounter;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\events\ProviderEventDetails;
use OpenFeature\implementation\flags\NoOpClient;
use OpenFeature\interfaces\events\EventDetails;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderStatus;
use OpenFeature\interfaces\provider\ErrorCode;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function array_keys;

class ProviderEventsTest extends TestCase
{
    public function testClientHandlerAddedDuringDispatchRunsOnce(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);
        $clientHandlerCalls = 0;

        $api->addHandler(
            ProviderEvent::STALE(),
            static function () use ($client, &$clientHandlerCalls): void {
                $client->addHandler(
                    ProviderEvent::STALE(),
                    static function () use (&$clientHandlerCalls): void {
                        ++$clientHandlerCalls;
                    },
                );
            },
        );

        $provider->emit(ProviderEvent::STALE());

        $this->assertSame(1, $clientHandlerCalls);
    }

    public function testStatusIsUpdatedBeforeApiAndClientHandlersRun(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new LifecycleTestProvider();
        /** @var ArrayObject<int, string> $apiStatuses */
        $apiStatuses = new ArrayObject();
        /** @var ArrayObject<int, string> $clientStatuses */
        $clientStatuses = new ArrayObject();
        $api->addHandler(
            ProviderEvent::READY(),
            static function () use ($api, $apiStatuses): void {
                $apiStatuses->append($api->getProviderStatus()->getValue());
            },
        );
        $client->addHandler(
            ProviderEvent::READY(),
            static function () use ($client, $clientStatuses): void {
                $clientStatuses->append($client->getProviderStatus()->getValue());
            },
        );
        $apiStatuses->exchangeArray([]);
        $clientStatuses->exchangeArray([]);

        $api->setProviderAndWait($provider);

        $this->assertSame(['READY'], $apiStatuses->getArrayCopy());
        $this->assertSame(['READY'], $clientStatuses->getArrayCopy());

        $api->addHandler(
            ProviderEvent::STALE(),
            static function () use ($api, $apiStatuses): void {
                $apiStatuses->append($api->getProviderStatus()->getValue());
            },
        );
        $client->addHandler(
            ProviderEvent::STALE(),
            static function () use ($client, $clientStatuses): void {
                $clientStatuses->append($client->getProviderStatus()->getValue());
            },
        );

        $provider->emit(ProviderEvent::STALE());

        $this->assertSame(['READY', 'STALE'], $apiStatuses->getArrayCopy());
        $this->assertSame(['READY', 'STALE'], $clientStatuses->getArrayCopy());
    }

    public function testApiAndClientReceiveEveryProviderEventWithDetails(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new LifecycleTestProvider();
        /** @var ArrayObject<string, EventDetails> $apiEvents */
        $apiEvents = new ArrayObject();
        /** @var ArrayObject<string, EventDetails> $clientEvents */
        $clientEvents = new ArrayObject();

        $events = [
            ProviderEvent::READY(),
            ProviderEvent::ERROR(),
            ProviderEvent::STALE(),
            ProviderEvent::CONFIGURATION_CHANGED(),
        ];

        foreach ($events as $event) {
            $eventName = $event->getValue();
            $api->addHandler(
                $event,
                static function (EventDetails $details) use ($apiEvents, $eventName): void {
                    $apiEvents[$eventName] = $details;
                },
            );
            $client->addHandler(
                $event,
                static function (EventDetails $details) use ($clientEvents, $eventName): void {
                    $clientEvents[$eventName] = $details;
                },
            );
        }
        $apiEvents->exchangeArray([]);
        $clientEvents->exchangeArray([]);

        $api->setProviderAndWait($provider);
        $provider->emit(ProviderEvent::STALE(), new ProviderEventDetails('stale'));
        $provider->emit(
            ProviderEvent::CONFIGURATION_CHANGED(),
            new ProviderEventDetails('changed', ['first-flag'], ['source' => 'test']),
        );
        $provider->emit(
            ProviderEvent::ERROR(),
            new ProviderEventDetails('failed', [], [], ErrorCode::GENERAL()),
        );

        $expectedEvents = [
            'PROVIDER_READY',
            'PROVIDER_STALE',
            'PROVIDER_CONFIGURATION_CHANGED',
            'PROVIDER_ERROR',
        ];
        $this->assertSame($expectedEvents, array_keys($apiEvents->getArrayCopy()));
        $this->assertSame($expectedEvents, array_keys($clientEvents->getArrayCopy()));
        $apiConfigurationChanged = $apiEvents['PROVIDER_CONFIGURATION_CHANGED'];
        $clientConfigurationChanged = $clientEvents['PROVIDER_CONFIGURATION_CHANGED'];
        $clientError = $clientEvents['PROVIDER_ERROR'];
        $this->assertInstanceOf(EventDetails::class, $apiConfigurationChanged);
        $this->assertInstanceOf(EventDetails::class, $clientConfigurationChanged);
        $this->assertInstanceOf(EventDetails::class, $clientError);
        $this->assertSame('TestProvider', $apiConfigurationChanged->getProviderName());
        $this->assertSame(['first-flag'], $apiConfigurationChanged->getFlagsChanged());
        $this->assertSame(['source' => 'test'], $clientConfigurationChanged->getEventMetadata());
        $this->assertSame('failed', $clientError->getMessage());
    }

    public function testHandlersCanBeRemoved(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);
        $apiCalls = 0;
        $clientCalls = 0;
        $apiHandler = static function () use (&$apiCalls): void {
            ++$apiCalls;
        };
        $clientHandler = static function () use (&$clientCalls): void {
            ++$clientCalls;
        };
        $api->addHandler(ProviderEvent::STALE(), $apiHandler);
        $client->addHandler(ProviderEvent::STALE(), $clientHandler);
        $api->removeHandler(ProviderEvent::STALE(), $apiHandler);
        $client->removeHandler(ProviderEvent::STALE(), $clientHandler);

        $provider->emit(ProviderEvent::STALE());

        $this->assertSame(0, $apiCalls);
        $this->assertSame(0, $clientCalls);
    }

    public function testFailingHandlersDoNotPreventOtherHandlers(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);
        $apiCalls = 0;
        $clientCalls = 0;
        $api->addHandler(ProviderEvent::STALE(), static function (): void {
            throw new RuntimeException('API handler failed');
        });
        $api->addHandler(ProviderEvent::STALE(), static function () use (&$apiCalls): void {
            ++$apiCalls;
        });
        $client->addHandler(ProviderEvent::STALE(), static function (): void {
            throw new RuntimeException('client handler failed');
        });
        $client->addHandler(ProviderEvent::STALE(), static function () use (&$clientCalls): void {
            ++$clientCalls;
        });

        $provider->emit(ProviderEvent::STALE());

        $this->assertSame(1, $apiCalls);
        $this->assertSame(1, $clientCalls);
    }

    public function testHandlersSurviveReplacementWithoutDuplicateReadyEvents(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $apiCalls = new ProviderEventCounter();
        $clientCalls = new ProviderEventCounter();
        $api->addHandler(ProviderEvent::READY(), static function () use ($apiCalls): void {
            $apiCalls->increment();
        });
        $client->addHandler(ProviderEvent::READY(), static function () use ($clientCalls): void {
            $clientCalls->increment();
        });
        $apiCalls->reset();
        $clientCalls->reset();

        $api->setProviderAndWait(new LifecycleTestProvider());
        $api->setProviderAndWait(new LifecycleTestProvider());

        $this->assertSame(2, $apiCalls->getValue());
        $this->assertSame(2, $clientCalls->getValue());
    }

    public function testLegacyProvidersReceiveSyntheticInitializationEvents(): void
    {
        $api = new OpenFeatureAPI();
        $readyCalls = new ProviderEventCounter();
        $errorCalls = new ProviderEventCounter();
        $api->addHandler(ProviderEvent::READY(), static function () use ($readyCalls): void {
            $readyCalls->increment();
        });
        $api->addHandler(ProviderEvent::ERROR(), static function () use ($errorCalls): void {
            $errorCalls->increment();
        });
        $readyCalls->reset();

        $api->setProviderAndWait(new LegacyLifecycleTestProvider());
        $failingProvider = new LegacyLifecycleTestProvider();
        $failingProvider->failInitialization = true;

        try {
            $api->setProviderAndWait($failingProvider);
            $this->fail('Expected provider initialization to fail.');
        } catch (RuntimeException) {
            // The synthetic error event is asserted below.
        }

        $this->assertSame(1, $readyCalls->getValue());
        $this->assertSame(1, $errorCalls->getValue());
    }

    public function testProviderWithoutLifecycleReceivesSyntheticReadyEvent(): void
    {
        $api = new OpenFeatureAPI();
        $readyCalls = new ProviderEventCounter();
        $api->addHandler(ProviderEvent::READY(), static function () use ($readyCalls): void {
            $readyCalls->increment();
        });
        $readyCalls->reset();

        $api->setProvider(new TestProvider());

        $this->assertSame(1, $readyCalls->getValue());
    }

    public function testInitializationErrorCanRecoverWithReadyEvent(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new LifecycleTestProvider();
        $provider->failInitialization = true;
        /** @var ArrayObject<int, string> $apiEvents */
        $apiEvents = new ArrayObject();
        /** @var ArrayObject<int, string> $clientEvents */
        $clientEvents = new ArrayObject();

        foreach ([ProviderEvent::ERROR(), ProviderEvent::READY()] as $event) {
            $eventName = $event->getValue();
            $api->addHandler($event, static function () use ($apiEvents, $eventName): void {
                $apiEvents->append($eventName);
            });
            $client->addHandler($event, static function () use ($clientEvents, $eventName): void {
                $clientEvents->append($eventName);
            });
        }
        $apiEvents->exchangeArray([]);
        $clientEvents->exchangeArray([]);

        try {
            $api->setProviderAndWait($provider);
            $this->fail('Expected provider initialization to fail.');
        } catch (RuntimeException) {
            // Recovery is triggered below.
        }

        $provider->emit(ProviderEvent::READY());

        $this->assertSame(['PROVIDER_ERROR', 'PROVIDER_READY'], $apiEvents->getArrayCopy());
        $this->assertSame(['PROVIDER_ERROR', 'PROVIDER_READY'], $clientEvents->getArrayCopy());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testLateHandlersReceiveTheLatestMatchingEventDetails(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);
        $provider->emit(
            ProviderEvent::ERROR(),
            new ProviderEventDetails('late failure', ['flag'], ['source' => 'test'], ErrorCode::GENERAL()),
        );
        $apiDetails = new class {
            public ?EventDetails $value = null;
        };
        $clientDetails = new class {
            public ?EventDetails $value = null;
        };

        $api->addHandler(
            ProviderEvent::ERROR(),
            static function (EventDetails $details) use ($apiDetails): void {
                $apiDetails->value = $details;
            },
        );
        $client->addHandler(
            ProviderEvent::ERROR(),
            static function (EventDetails $details) use ($clientDetails): void {
                $clientDetails->value = $details;
            },
        );

        $this->assertInstanceOf(EventDetails::class, $apiDetails->value);
        $this->assertInstanceOf(EventDetails::class, $clientDetails->value);
        $this->assertSame('late failure', $apiDetails->value->getMessage());
        $this->assertSame(['flag'], $clientDetails->value->getFlagsChanged());
    }

    public function testConfigurationChangedDoesNotChangeProviderStatus(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);

        $provider->emit(ProviderEvent::CONFIGURATION_CHANGED());

        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testExistingClientCanRegisterHandlersAfterApiShutdown(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $removedHandlerCalls = new ProviderEventCounter();
        $newHandlerCalls = new ProviderEventCounter();
        $client->addHandler(ProviderEvent::STALE(), static function () use ($removedHandlerCalls): void {
            $removedHandlerCalls->increment();
        });

        $api->shutdown();

        $client->addHandler(ProviderEvent::STALE(), static function () use ($newHandlerCalls): void {
            $newHandlerCalls->increment();
        });
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);
        $provider->emit(ProviderEvent::STALE());

        $this->assertSame(0, $removedHandlerCalls->getValue());
        $this->assertSame(1, $newHandlerCalls->getValue());
    }

    public function testNoOpClientIsReadyAndImmediatelyRunsReadyHandlers(): void
    {
        $client = new NoOpClient();
        $calls = new ProviderEventCounter();

        $client->addHandler(ProviderEvent::READY(), static function () use ($calls): void {
            $calls->increment();
        });

        $this->assertTrue($client->getProviderStatus()->equals(ProviderStatus::READY()));
        $this->assertSame(1, $calls->getValue());
    }
}
