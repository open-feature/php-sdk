<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\OpenFeatureAPI;
use OpenFeature\Test\APITestHelper;
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
    public function testFactoryCreatesDistinctInstances(): void
    {
        $api1 = OpenFeatureAPIFactory::createAPI();
        $api2 = OpenFeatureAPIFactory::createAPI();

        $this->assertInstanceOf(API::class, $api1);
        $this->assertInstanceOf(OpenFeatureAPI::class, $api1);
        $this->assertNotSame($api1, $api2);
    }

    /**
     * Requirement 1.8.1
     *
     * Isolated instances do not share state with the global singleton and
     * mutating an isolated instance does not affect the singleton's state.
     */
    public function testIsolatedInstanceDoesNotInterfereWithSingleton(): void
    {
        $singleton = APITestHelper::new();
        $isolated = OpenFeatureAPIFactory::createAPI();

        $this->assertNotSame($singleton, $isolated);

        // Mutate the isolated instance
        $isolated->setProvider(new TestProvider());
        $isolated->addHooks(new TestHook());
        $isolated->setEvaluationContext(new EvaluationContext('isolated-key'));

        // Singleton state remains unchanged
        $this->assertInstanceOf(NoOpProvider::class, $singleton->getProvider());
        $this->assertEmpty($singleton->getHooks());
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
        $api1 = OpenFeatureAPIFactory::createAPI();
        $api2 = OpenFeatureAPIFactory::createAPI();

        $api1->setProvider(new TestProvider());

        $this->assertInstanceOf(TestProvider::class, $api1->getProvider());
        $this->assertInstanceOf(NoOpProvider::class, $api2->getProvider());
    }

    /**
     * Requirement 1.8.1
     *
     * Hooks are isolated between instances.
     */
    public function testHookIsolation(): void
    {
        $api1 = OpenFeatureAPIFactory::createAPI();
        $api2 = OpenFeatureAPIFactory::createAPI();

        $hook = new TestHook();
        $api1->addHooks($hook);

        $this->assertCount(1, $api1->getHooks());
        $this->assertEmpty($api2->getHooks());
    }

    /**
     * Requirement 1.8.1
     *
     * Evaluation context is isolated between instances.
     */
    public function testEvaluationContextIsolation(): void
    {
        $api1 = OpenFeatureAPIFactory::createAPI();
        $api2 = OpenFeatureAPIFactory::createAPI();

        $api1->setEvaluationContext(new EvaluationContext('key-1'));
        $api2->setEvaluationContext(new EvaluationContext('key-2'));

        $ctx1 = $api1->getEvaluationContext();
        $ctx2 = $api2->getEvaluationContext();

        $this->assertNotNull($ctx1);
        $this->assertNotNull($ctx2);
        $this->assertEquals('key-1', $ctx1->getTargetingKey());
        $this->assertEquals('key-2', $ctx2->getTargetingKey());
    }

    /**
     * Requirement 1.8.2
     *
     * A client obtained from an isolated instance uses that instance's provider.
     */
    public function testClientUsesItsOwnInstanceProvider(): void
    {
        $api1 = OpenFeatureAPIFactory::createAPI();
        $api2 = OpenFeatureAPIFactory::createAPI();

        $api1->setProvider(new TestProvider());

        $client1 = $api1->getClient('test', '1.0');
        $client2 = $api2->getClient('test', '1.0');

        $this->assertFalse($client1->getBooleanValue('flag-key', false));
        $this->assertFalse($client2->getBooleanValue('flag-key', false));
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
