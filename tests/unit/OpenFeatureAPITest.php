<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Mockery\MockInterface;
use OpenFeature\OpenFeatureAPI;
use OpenFeature\Test\APITestHelper;
use OpenFeature\Test\TestCase;
use OpenFeature\Test\TestHook;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\provider\NoOpProvider;
use OpenFeature\interfaces\flags\API;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\provider\Provider;
use Throwable;

use function get_class;

class OpenFeatureAPITest extends TestCase
{
    /**
     * Requirement 1.1.1
     *
     * The API, and any state it maintains SHOULD exist as a global singleton, even in cases wherein multiple versions of the API are present at runtime.
     *
     * It's important that multiple instances of the API not be active, so that state stored therein, such as the registered provider, static global
     * evaluation context, and globally configured hooks allow the API to behave predictably. This can be difficult in some runtimes or languages, but
     * implementors should make their best effort to ensure that only a single instance of the API is used.
     */
    public function testApiHasGlobalInstance(): void
    {
        $api = OpenFeatureAPI::getInstance();

        $this->assertInstanceOf(API::class, $api);
        $this->assertInstanceOf(OpenFeatureAPI::class, $api);
        $this->assertTrue($api === OpenFeatureAPI::getInstance());

        $differentApi = APITestHelper::new();
        /** @var Hook $mockHook */
        $mockHook = $this->mockery(Hook::class);
        $differentApi->addHooks($mockHook);

        $this->assertTrue($api === $differentApi);
    }

    /**
     * Requirement 1.1.1
     *
     * The API, and any state it maintains SHOULD exist as a global singleton, even in cases wherein multiple versions of the API are present at runtime.
     *
     * It's important that multiple instances of the API not be active, so that state stored therein, such as the registered provider, static global
     * evaluation context, and globally configured hooks allow the API to behave predictably. This can be difficult in some runtimes or languages, but
     * implementors should make their best effort to ensure that only a single instance of the API is used.
     */
    public function testApiCannotBeCreated(): void
    {
        $api = APITestHelper::new();

        $this->assertInstanceOf(API::class, $api);
    }

    /**
     * Requirement 1.1.2
     *
     * The API MUST provide a function to set the global provider singleton, which accepts an API-conformant provider implementation.
     */
    public function testApiCanHaveProviderSet(): void
    {
        $api = APITestHelper::new();
        $provider = new class extends NoOpProvider {
        };

        $this->assertInstanceOf(Provider::class, $provider);

        $api->setProvider($provider);

        $actualProvider = $api->getProvider();

        $this->assertInstanceOf(Provider::class, $actualProvider);
        $this->assertInstanceOf(get_class($provider), $actualProvider);
    }

    /**
     * Requirement 1.1.3
     *
     * The API MUST provide a function to add hooks which accepts one or more API-conformant hooks, and appends them to the collection of any previously added hooks. When new hooks are added, previously added hooks are not removed.
     *
     * Requirement 4.4.1
     *
     * The API, Client, Provider, and invocation MUST have a method for registering hooks.
     */
    public function testApiCanHaveHooksAdded(): void
    {
        $api = APITestHelper::new();

        /** @var Hook|MockInterface $firstHook */
        $firstHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Hook|MockInterface $secondHook */
        $secondHook = $this->mockery(TestHook::class)->makePartial();

        // Validate you can add a hook
        $api->addHooks($firstHook);
        $this->assertEquals([$firstHook], $api->getHooks());

        // Validate you can add another hook, retaining existing hooks
        $api->addHooks($secondHook);
        $this->assertEquals([$firstHook, $secondHook], $api->getHooks());
    }

    /**
     * Requirement 1.1.4
     *
     * The API MUST provide a function for retrieving the metadata field of the configured provider.
     */
    public function testApiCanProvideProviderMetadata(): void
    {
        $api = APITestHelper::new();
        $provider = new TestProvider();
        $api->setProvider($provider);

        $expectedMetadata = $provider->getMetadata();
        $actualMetadata = $api->getProviderMetadata();

        $this->assertEquals($expectedMetadata, $actualMetadata);
    }

    /**
     * Requirement 1.1.5
     *
     * The API MUST provide a function for creating a client which accepts the following options:
     *
     *   name (optional): A logical string identifier for the client.
     */
    public function testApiCanCreateClient(): void
    {
        $name = 'test-name';
        $version = 'test-version';

        $api = APITestHelper::new();

        $client = $api->getClient($name, $version);

        $this->assertEquals($name, $client->getMetadata()->getName());
        $this->assertInstanceOf(NoOpProvider::class, $api->getProvider());
    }

    /**
     * Requirement 1.1.6
     *
     * The client creation function MUST NOT throw, or otherwise abnormally terminate.
     *
     * Clients may be created in critical code paths, and even per-request in server-side HTTP contexts. Therefore, in keeping with the principle that OpenFeature should never cause abnormal execution of the first party application, this function should never throw. Abnormal execution in initialization should instead occur during provider registration.
     */
    public function testApiCreationMustNotThrow(): void
    {
        try {
            $constructedApi = APITestHelper::new();
            $globalApi = OpenFeatureAPI::getInstance();

            $this->assertInstanceOf(API::class, $constructedApi);
            $this->assertInstanceOf(API::class, $globalApi);
        } catch (Throwable $err) {
            $this->assertEquals(true, false, 'Must not throw');
        }
    }

    /**
     * Requirement 3.2.1
     *
     * The API, Client and invocation MUST have a method for supplying evaluation context.
     */
    public function testApiCanSetEvaluationContext(): void
    {
        $api = APITestHelper::new();
        $expectedEvaluationContext = EvaluationContext::createNull();
        $api->setEvaluationContext($expectedEvaluationContext);

        $actualEvaluationContext = $api->getEvaluationContext();

        $this->assertEquals($expectedEvaluationContext, $actualEvaluationContext);
    }

    //// Additional tests for various functionality, not necessary tied to a specific requirement

    public function testApiWillDefaultToNoOpProvider(): void
    {
        $api = APITestHelper::new();

        $actualProvider = $api->getProvider();

        $this->assertInstanceOf(NoOpProvider::class, $actualProvider);
    }

    public function testApiWillCreateClientWithoutProvider(): void
    {
        $name = 'test-name';
        $version = 'test-version';

        $api = APITestHelper::new();

        $client = $api->getClient($name, $version);

        $this->assertEquals($name, $client->getMetadata()->getName());
        $this->assertInstanceOf(NoOpProvider::class, $api->getProvider());
    }

    public function testApiWillCreateClientWithProvider(): void
    {
        $name = 'test-name';
        $version = 'test-version';

        $api = APITestHelper::new();
        $api->setProvider(new TestProvider());

        $client = $api->getClient($name, $version);

        $this->assertEquals($name, $client->getMetadata()->getName());
        $this->assertInstanceOf(TestProvider::class, $api->getProvider());
    }
}
