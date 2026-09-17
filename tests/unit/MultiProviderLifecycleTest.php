<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\OpenFeatureAPI;
use OpenFeature\Test\LifecycleTestProvider;
use OpenFeature\Test\ProviderEventCounter;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\multiprovider\MultiProvider;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderStatus;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MultiProviderLifecycleTest extends TestCase
{
    public function testInitializesAndShutsDownDistinctLifecycleProvidersOnce(): void
    {
        $api = new OpenFeatureAPI();
        $context = new EvaluationContext('targeting-key');
        $child = new LifecycleTestProvider();
        $multiProvider = new MultiProvider([
            ['name' => 'first', 'provider' => $child],
            ['name' => 'second', 'provider' => $child],
            ['name' => 'legacy', 'provider' => new TestProvider()],
        ]);
        $api->setEvaluationContext($context);

        $api->setProviderAndWait($multiProvider);
        $api->shutdown();
        $api->shutdown();

        $this->assertSame(1, $child->initializeCalls);
        $this->assertSame(1, $child->shutdownCalls);
        $this->assertSame($context, $child->initialContext);
        $this->assertNull($child->initialDomain);
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testEmitsOneReadyEventAfterChildrenInitialize(): void
    {
        $api = new OpenFeatureAPI();
        $readyCalls = new ProviderEventCounter();
        $api->addHandler(ProviderEvent::READY(), static function () use ($readyCalls): void {
            $readyCalls->increment();
        });
        $readyCalls->reset();
        $multiProvider = new MultiProvider([
            ['provider' => new LifecycleTestProvider()],
        ]);

        $api->setProviderAndWait($multiProvider);

        $this->assertSame(1, $readyCalls->getValue());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }

    public function testCleansUpInitializedChildrenAndEmitsErrorAfterFailure(): void
    {
        $api = new OpenFeatureAPI();
        $errorCalls = new ProviderEventCounter();
        $readyChild = new LifecycleTestProvider();
        $failingChild = new LifecycleTestProvider();
        $failingChild->failInitialization = true;
        $api->addHandler(ProviderEvent::ERROR(), static function () use ($errorCalls): void {
            $errorCalls->increment();
        });
        $multiProvider = new MultiProvider([
            ['name' => 'ready', 'provider' => $readyChild],
            ['name' => 'failing', 'provider' => $failingChild],
        ]);

        try {
            $api->setProviderAndWait($multiProvider);
            $this->fail('Expected child initialization to fail.');
        } catch (RuntimeException $error) {
            $this->assertSame('initialization failed', $error->getMessage());
        }

        $this->assertSame(1, $readyChild->shutdownCalls);
        $this->assertSame(1, $failingChild->shutdownCalls);
        $this->assertSame(1, $errorCalls->getValue());
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::ERROR()));
    }

    public function testContinuesShuttingDownChildrenAfterOneFails(): void
    {
        $api = new OpenFeatureAPI();
        $failingChild = new LifecycleTestProvider();
        $failingChild->failShutdown = true;
        $secondChild = new LifecycleTestProvider();
        $multiProvider = new MultiProvider([
            ['name' => 'failing', 'provider' => $failingChild],
            ['name' => 'second', 'provider' => $secondChild],
        ]);
        $api->setProviderAndWait($multiProvider);

        $api->shutdown();

        $this->assertSame(1, $failingChild->shutdownCalls);
        $this->assertSame(1, $secondChild->shutdownCalls);
        $this->assertTrue($api->getProviderStatus()->equals(ProviderStatus::READY()));
    }
}
