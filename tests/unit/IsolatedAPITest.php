<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\OpenFeatureAPI;
use OpenFeature\Test\TestCase;
use OpenFeature\Test\TestHook;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\provider\NoOpProvider;
use OpenFeature\interfaces\flags\API;
use OpenFeature\isolated\OpenFeatureAPIFactory;

class IsolatedAPITest extends TestCase
{
    /**
     * Requirement 1.8.1
     *
     * The API MUST expose a factory function which creates and returns a new,
     * independent instance of the API.
     */
    public function testFactoryCreatesNewAPIInstance(): void
    {
        $api = OpenFeatureAPIFactory::createAPI();

        $this->assertInstanceOf(API::class, $api);
        $this->assertInstanceOf(OpenFeatureAPI::class, $api);
    }

    /**
     * Requirement 1.8.1
     *
     * Each instance returned by this factory function maintains its own state.
     * Instances created by the factory function do not share state with the
     * "default" global singleton or with each other.
     */
    public function testFactoryCreatesDistinctInstances(): void
    {
        $api1 = OpenFeatureAPIFactory::createAPI();
        $api2 = OpenFeatureAPIFactory::createAPI();

        $this->assertNotSame($api1, $api2);
    }

    /**
     * Requirement 1.8.1
     *
     * Instances do not share state with the "default" global singleton.
     */
    public function testIsolatedInstanceIsNotTheSingleton(): void
    {
        $singleton = OpenFeatureAPI::getInstance();
        $isolated = OpenFeatureAPIFactory::createAPI();

        $this->assertNotSame($singleton, $isolated);
    }

    /**
     * Requirement 1.8.2
     *
     * Instances returned by the factory function MUST conform to the same API
     * contract as the global singleton, including flag evaluation, provider
     * management, context, hooks, events, and shutdown functionality.
     */
    public function testIsolatedInstanceConformsToAPIContract(): void
    {
        $api = OpenFeatureAPIFactory::createAPI();

        // Provider management
        $provider = new TestProvider();
        $api->setProvider($provider);
        $this->assertSame($provider, $api->getProvider());
        $this->assertEquals($provider->getMetadata(), $api->getProviderMetadata());

        // Hooks
        $hook = new TestHook();
        $api->addHooks($hook);
        $this->assertEquals([$hook], $api->getHooks());

        // Evaluation context
        $context = new EvaluationContext('targeting-key');
        $api->setEvaluationContext($context);
        $this->assertSame($context, $api->getEvaluationContext());

        // Client creation
        $client = $api->getClient('test-domain', '1.0.0');
        $this->assertEquals('test-domain', $client->getMetadata()->getName());
    }

    /**
     * Requirement 1.8.1
     *
     * Providers are isolated between instances.
     */
    public function testProviderIsolation(): void
    {
        $singleton = OpenFeatureAPI::getInstance();
        $singleton->setProvider(new NoOpProvider());

        $isolated = OpenFeatureAPIFactory::createAPI();
        $isolated->setProvider(new TestProvider());

        $this->assertInstanceOf(NoOpProvider::class, $singleton->getProvider());
        $this->assertInstanceOf(TestProvider::class, $isolated->getProvider());
    }

    /**
     * Requirement 1.8.1
     *
     * Hooks are isolated between instances.
     */
    public function testHookIsolation(): void
    {
        $singleton = OpenFeatureAPI::getInstance();
        $singleton->clearHooks();

        $isolated = OpenFeatureAPIFactory::createAPI();
        $hook = new TestHook();
        $isolated->addHooks($hook);

        $this->assertEmpty($singleton->getHooks());
        $this->assertCount(1, $isolated->getHooks());
    }

    /**
     * Requirement 1.8.1
     *
     * Evaluation context is isolated between instances.
     */
    public function testEvaluationContextIsolation(): void
    {
        $singleton = OpenFeatureAPI::getInstance();
        $singletonContext = new EvaluationContext('singleton-key');
        $singleton->setEvaluationContext($singletonContext);

        $isolated = OpenFeatureAPIFactory::createAPI();
        $isolatedContext = new EvaluationContext('isolated-key');
        $isolated->setEvaluationContext($isolatedContext);

        $actualSingletonContext = $singleton->getEvaluationContext();
        $actualIsolatedContext = $isolated->getEvaluationContext();

        $this->assertNotNull($actualSingletonContext);
        $this->assertNotNull($actualIsolatedContext);
        $this->assertEquals('singleton-key', $actualSingletonContext->getTargetingKey());
        $this->assertEquals('isolated-key', $actualIsolatedContext->getTargetingKey());
    }

    /**
     * Requirement 1.8.2
     *
     * An isolated API instance is functionally equivalent to the global
     * singleton. A client obtained from an isolated instance behaves
     * identically to a client from the global singleton.
     */
    public function testClientFromIsolatedInstanceUsesIsolatedProvider(): void
    {
        $isolated = OpenFeatureAPIFactory::createAPI();
        $provider = new TestProvider();
        $isolated->setProvider($provider);

        $client = $isolated->getClient('test', '1.0');
        $result = $client->getBooleanValue('flag-key', false);

        // TestProvider returns the default value
        $this->assertFalse($result);
    }

    /**
     * Requirement 1.8.1
     *
     * clearHooks on one instance does not affect another.
     */
    public function testClearHooksDoesNotAffectOtherInstances(): void
    {
        $api1 = OpenFeatureAPIFactory::createAPI();
        $api2 = OpenFeatureAPIFactory::createAPI();

        $hook = new TestHook();
        $api1->addHooks($hook);
        $api2->addHooks($hook);

        $api1->clearHooks();

        $this->assertEmpty($api1->getHooks());
        $this->assertCount(1, $api2->getHooks());
    }
}
