<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use LogicException;
use Mockery;
use Mockery\MockInterface;
use OpenFeature\OpenFeatureAPI;
use OpenFeature\Test\LegacyLifecycleTestProvider;
use OpenFeature\Test\LifecycleTestProvider;
use OpenFeature\Test\TestCase;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\events\ProviderEventDetails;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\provider\NoOpProvider;
use OpenFeature\implementation\provider\ResolutionError;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderStatus;
use OpenFeature\interfaces\provider\ErrorCode;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function str_contains;

class ProviderLifecycleTest extends TestCase
{
    public function testExistingProviderRemainsCompatibleAndBecomesReady(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new TestProvider();

        $api->setProvider($provider);

        $this->assertSame($provider, $api->getProvider());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testInitializationReceivesContextAndDefaultDomainAndRunsOnce(): void
    {
        $api = new OpenFeatureAPI();
        $context = new EvaluationContext('targeting-key');
        $provider = new LifecycleTestProvider();
        $api->setEvaluationContext($context);

        $api->setProviderAndWait($provider);
        $api->setProviderAndWait($provider);

        $this->assertSame(1, $provider->initializeCalls);
        $this->assertSame($context, $provider->initialContext);
        $this->assertNull($provider->initialDomain);
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testLegacyLifecycleProviderGetsSyntheticStatusTransitions(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LegacyLifecycleTestProvider();

        $api->setProviderAndWait($provider);

        $this->assertSame(1, $provider->initializeCalls);
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testLegacyLifecycleProviderRegistrationLogsDeprecationWarning(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LegacyLifecycleTestProvider();
        /** @var LoggerInterface&MockInterface $logger */
        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->with(
                Mockery::on(static fn (string $message): bool => str_contains($message, 'deprecated legacy lifecycle compatibility path')),
                ['providerName' => 'TestProvider'],
            );
        $api->setLogger($logger);

        $api->setProviderAndWait($provider);
    }

    public function testEventAwareProviderRegistrationDoesNotLogDeprecationWarning(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        /** @var LoggerInterface&MockInterface $logger */
        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldNotReceive('warning');
        $api->setLogger($logger);

        $api->setProviderAndWait($provider);
    }

    public function testInitializationFailureIsPropagatedAfterStatusIsUpdated(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LegacyLifecycleTestProvider();
        $provider->failInitialization = true;

        try {
            $api->setProviderAndWait($provider);
            $this->fail('Expected provider initialization to fail.');
        } catch (RuntimeException $error) {
            $this->assertSame('legacy initialization failed', $error->getMessage());
        }

        $this->assertSame($provider, $api->getProvider());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::ERROR()));
    }

    public function testEventAwareProviderMustEmitAnInitializationEvent(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $provider->emitInitializationEvent = false;

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must emit PROVIDER_READY or PROVIDER_ERROR');

        $api->setProviderAndWait($provider);
    }

    public function testEventAwareProviderMustEmitErrorBeforeThrowingDuringInitialization(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $provider->failInitialization = true;
        $provider->emitInitializationEvent = false;
        $errorHandlerCalls = 0;
        $api->addHandler(ProviderEvent::ERROR(), static function () use (&$errorHandlerCalls): void {
            ++$errorHandlerCalls;
        });

        try {
            $api->setProviderAndWait($provider);
            $this->fail('Expected registration to reject an event-aware provider that did not emit PROVIDER_ERROR.');
        } catch (LogicException $error) {
            $this->assertSame(
                'An event-aware provider must emit PROVIDER_ERROR before initialization terminates abnormally.',
                $error->getMessage(),
            );
            $this->assertInstanceOf(RuntimeException::class, $error->getPrevious());
            $this->assertSame('initialization failed', $error->getPrevious()->getMessage());
        }

        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::NOT_READY()));
        $this->assertSame(0, $errorHandlerCalls);
    }

    public function testInitializationFailurePreservesProviderErrorDetails(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $provider->failInitialization = true;
        $provider->fatalInitializationError = true;
        $provider->returnAfterInitializationError = true;

        try {
            $api->setProviderAndWait($provider);
            $this->fail('Expected provider initialization to fail.');
        } catch (ResolutionError $error) {
            $this->assertSame('initialization failed', $error->getMessage());
            $this->assertTrue($error->getResolutionErrorCode()->equals(ErrorCode::PROVIDER_FATAL()));
        }
    }

    public function testFailedInitializationCanBeRetriedWithSameProvider(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $provider->failInitialization = true;

        try {
            $api->setProviderAndWait($provider);
            $this->fail('Expected provider initialization to fail.');
        } catch (RuntimeException) {
            // The same provider is retried below.
        }

        $provider->failInitialization = false;
        $api->setProviderAndWait($provider);

        $this->assertSame(2, $provider->initializeCalls);
        $this->assertSame(0, $provider->shutdownCalls);
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testEventAwareProviderCanRecoverAfterInitializationFailure(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $provider->failInitialization = true;

        try {
            $api->setProviderAndWait($provider);
            $this->fail('Expected provider initialization to fail.');
        } catch (RuntimeException $error) {
            $this->assertSame('initialization failed', $error->getMessage());
        }

        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::ERROR()));

        $provider->emit(ProviderEvent::READY());

        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testProviderReplacementShutsDownOldProvider(): void
    {
        $api = new OpenFeatureAPI();
        $oldProvider = new LifecycleTestProvider();
        $newProvider = new LifecycleTestProvider();
        $api->setProviderAndWait($oldProvider);

        $api->setProviderAndWait($newProvider);

        $this->assertSame(1, $oldProvider->shutdownCalls);
        $this->assertSame(0, $newProvider->shutdownCalls);
        $this->assertSame($newProvider, $api->getProvider());
    }

    public function testShutdownIsIdempotentAndResetsApiState(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $api->setEvaluationContext(new EvaluationContext('targeting-key'));
        $api->setProviderAndWait($provider);

        $api->shutdown();
        $api->shutdown();

        $this->assertSame(1, $provider->shutdownCalls);
        $this->assertInstanceOf(NoOpProvider::class, $api->getProvider());
        $this->assertNull($api->getEvaluationContext());
        $this->assertSame([], $api->getHooks());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::NOT_READY()));
    }

    public function testStatusBecomesNotReadyAfterProviderShutdownTerminates(): void
    {
        $api = new OpenFeatureAPI();
        $client = $api->getClient();
        $provider = new class ($api) extends LifecycleTestProvider {
            private OpenFeatureAPI $api;
            public ?ProviderStatus $statusDuringShutdown = null;

            public function __construct(OpenFeatureAPI $api)
            {
                $this->api = $api;
            }

            public function shutdown(): void
            {
                $this->statusDuringShutdown = $this->api->getProviderStatus();
                parent::shutdown();
            }
        };
        $api->setProviderAndWait($provider);

        $api->shutdown();

        $this->assertNotNull($provider->statusDuringShutdown);
        $this->assertTrue($provider->statusDuringShutdown->equals(ProviderStatus::READY()));
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::NOT_READY()));
        $this->assertTrue($client->getProviderStatus()->equals(ProviderStatus::NOT_READY()));
    }

    public function testShutdownRemainsSafeWhenProviderShutdownFails(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $provider->failShutdown = true;
        $api->setProviderAndWait($provider);

        $api->shutdown();
        $api->shutdown();

        $this->assertSame(1, $provider->shutdownCalls);
        $this->assertInstanceOf(NoOpProvider::class, $api->getProvider());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::NOT_READY()));
    }

    public function testShutdownFailureUsesConfiguredLoggerBeforeReset(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $provider->failShutdown = true;
        /** @var LoggerInterface&MockInterface $logger */
        $logger = $this->mockery(LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->with(
                'OpenFeature provider shutdown failed.',
                Mockery::on(function (array $context): bool {
                    $this->assertArrayHasKey('exception', $context);
                    $this->assertInstanceOf(RuntimeException::class, $context['exception']);
                    $this->assertSame('shutdown failed', $context['exception']->getMessage());

                    return true;
                }),
            );
        $api->setLogger($logger);
        $api->setProviderAndWait($provider);

        $api->shutdown();

        $this->assertSame(1, $provider->shutdownCalls);
    }

    public function testProviderEventsDriveStatusAndAllowRecovery(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);

        $provider->emit(ProviderEvent::STALE());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::STALE()));

        $provider->emit(
            ProviderEvent::ERROR(),
            new ProviderEventDetails('failed', [], [], ErrorCode::GENERAL()),
        );
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::ERROR()));

        $provider->emit(ProviderEvent::READY());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));

        $provider->emit(ProviderEvent::CONFIGURATION_CHANGED());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testFatalProviderErrorSetsFatalStatus(): void
    {
        $api = new OpenFeatureAPI();
        $provider = new LifecycleTestProvider();
        $api->setProviderAndWait($provider);

        $provider->emit(
            ProviderEvent::ERROR(),
            new ProviderEventDetails('fatal', [], [], ErrorCode::PROVIDER_FATAL()),
        );

        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::FATAL()));
    }

    public function testReplacedProviderCanNoLongerChangeStatus(): void
    {
        $api = new OpenFeatureAPI();
        $oldProvider = new LifecycleTestProvider();
        $api->setProviderAndWait($oldProvider);
        $api->setProvider(new TestProvider());

        $oldProvider->emit(ProviderEvent::STALE());

        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }
}
