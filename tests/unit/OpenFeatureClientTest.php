<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Exception;
use Mockery;
use Mockery\MockInterface;
use OpenFeature\OpenFeatureClient;
use OpenFeature\Test\APITestHelper;
use OpenFeature\Test\TestCase;
use OpenFeature\Test\TestHook;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\flags\Attributes;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\flags\EvaluationOptions;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\implementation\provider\ResolutionDetailsFactory;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\Provider;
use Psr\Log\LoggerInterface;

class OpenFeatureClientTest extends TestCase
{
    /**
     * -----------------
     * Requirement 1.2.1
     * -----------------
     * The client MUST provide a method to add hooks which accepts one or more API-conformant hooks, and appends them to the collection of any previously added hooks. When new hooks are added, previously added hooks are not removed.
     *
     * -----------------
      * Requirement 4.4.1
     * -----------------
     * The API, Client, Provider, and invocation MUST have a method for registering hooks.
     */
    public function testClientCanAddHooks(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        /** @var Hook|MockInterface $firstHook */
        $firstHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Hook|MockInterface $secondHook */
        $secondHook = $this->mockery(TestHook::class)->makePartial();

        // Validate you can add a hook
        $client->addHooks($firstHook);
        $this->assertEquals([$firstHook], $client->getHooks());

        // Validate you can add another hook, retaining existing hooks
        $client->addHooks($secondHook);
        $this->assertEquals([$firstHook, $secondHook], $client->getHooks());
    }

    /**
     * -----------------
     * Requirement 1.2.2
     * -----------------
     * The client interface MUST define a metadata member or accessor, containing an immutable name field or accessor of type string, which corresponds to the name value supplied during client creation.
     */
    public function testClientHasMetadataAccessor(): void
    {
        $api = APITestHelper::new();
        $clientName = 'test-name';
        $client = new OpenFeatureClient($api, $clientName, 'test-version');

        $metadata = $client->getMetadata();

        $this->assertEquals($clientName, $metadata->getName());
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure, with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns the flag value.
     */
    public function testClientHasMethodForTypedFlagEvaluationValueForBoolean(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = true;

        $actualValue = $client->getBooleanValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure, with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns the flag value.
     *
     * Conditional Requirement 1.3.2.1
     *
     * The client SHOULD provide functions for floating-point numbers and integers, consistent with language idioms.
     */
    public function testClientHasMethodForTypedFlagEvaluationValueForFloat(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = 3.14;

        $actualValue = $client->getFloatValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure, with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns the flag value.
     *
     * Conditional Requirement 1.3.2.1
     *
     * The client SHOULD provide functions for floating-point numbers and integers, consistent with language idioms.
     */
    public function testClientHasMethodForTypedFlagEvaluationValueForInteger(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = 42;

        $actualValue = $client->getIntegerValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure, with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns the flag value.
     */
    public function testClientHasMethodForTypedFlagEvaluationValueForString(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = 'STRING VALUE';

        $actualValue = $client->getStringValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure, with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns the flag value.
     */
    public function testClientHasMethodForTypedFlagEvaluationValueForStructure(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = [];

        $actualValue = $client->getObjectValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.3
     * -----------------
     * The client SHOULD guarantee the returned value of any typed flag evaluation method is of the expected type. If the value returned by the underlying provider implementation does not match the expected type, it's to be considered abnormal execution, and the supplied default value should be returned.
     */
    public function testClientValidatesReturnTypeForRetrievalOfBoolean(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $invalidValue = 'not a bool';
        $expectedValue = true;

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturns(ResolutionDetailsFactory::fromSuccess($invalidValue));

        $api->setProvider($mockProvider);

        $actualValue = $client->getBooleanValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.3
     * -----------------
     * The client SHOULD guarantee the returned value of any typed flag evaluation method is of the expected type. If the value returned by the underlying provider implementation does not match the expected type, it's to be considered abnormal execution, and the supplied default value should be returned.
     */
    public function testClientValidatesReturnTypeForRetrievalOfFloat(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $invalidValue = 'not a float';
        $expectedValue = 3.14;

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveFloatValue')->andReturns(ResolutionDetailsFactory::fromSuccess($invalidValue));

        $api->setProvider($mockProvider);

        $actualValue = $client->getFloatValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.3
     * -----------------
     * The client SHOULD guarantee the returned value of any typed flag evaluation method is of the expected type. If the value returned by the underlying provider implementation does not match the expected type, it's to be considered abnormal execution, and the supplied default value should be returned.
     */
    public function testClientValidatesReturnTypeForRetrievalOfInteger(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $invalidValue = 'not an integer';
        $expectedValue = 42;

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveIntegerValue')->andReturns(ResolutionDetailsFactory::fromSuccess($invalidValue));

        $api->setProvider($mockProvider);

        $actualValue = $client->getIntegerValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.3
     * -----------------
     * The client SHOULD guarantee the returned value of any typed flag evaluation method is of the expected type. If the value returned by the underlying provider implementation does not match the expected type, it's to be considered abnormal execution, and the supplied default value should be returned.
     */
    public function testClientValidatesReturnTypeForRetrievalOfString(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $invalidValue = 42;
        $expectedValue = 'a string';

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveStringValue')->andReturns(ResolutionDetailsFactory::fromSuccess($invalidValue));

        $api->setProvider($mockProvider);

        $actualValue = $client->getStringValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.3.3
     * -----------------
     * The client SHOULD guarantee the returned value of any typed flag evaluation method is of the expected type. If the value returned by the underlying provider implementation does not match the expected type, it's to be considered abnormal execution, and the supplied default value should be returned.
     */
    public function testClientValidatesReturnTypeForRetrievalOfStructure(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $invalidValue = 42;
        $expectedValue = ['key' => 'value'];

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveStructureValue')->andReturns(ResolutionDetailsFactory::fromSuccess($invalidValue));

        $api->setProvider($mockProvider);

        $actualValue = $client->getObjectValue('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns an evaluation details structure.
     */
    public function testClientHasMethodForTypedFlagEvaluationDetailsForBoolean(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = true;

        $actualDetails = $client->getBooleanDetails('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualDetails->getValue());
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns an evaluation details structure.
     */
    public function testClientHasMethodForTypedFlagEvaluationDetailsForFloat(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = 3.14;

        $actualDetails = $client->getFloatDetails('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualDetails->getValue());
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns an evaluation details structure.
     */
    public function testClientHasMethodForTypedFlagEvaluationDetailsForInteger(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = 42;

        $actualDetails = $client->getIntegerDetails('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualDetails->getValue());
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns an evaluation details structure.
     */
    public function testClientHasMethodForTypedFlagEvaluationDetailsForString(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = 'STRING VALUE';

        $actualDetails = $client->getStringDetails('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualDetails->getValue());
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns an evaluation details structure.
     */
    public function testClientHasMethodForTypedFlagEvaluationDetailsForStructure(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = [];

        $actualDetails = $client->getObjectDetails('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualDetails->getValue());
    }

    /**
     * -----------------
     * Requirement 1.4.2
     * -----------------
     * The evaluation details structure's value field MUST contain the evaluated flag value.
     */
    public function testClientEvaluationDetailsHasFlagValue(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = ['key' => 'value'];

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveStructureValue')->andReturns(ResolutionDetailsFactory::fromSuccess($expectedValue));

        $api->setProvider($mockProvider);

        $actualDetails = $client->getObjectDetails('flagKey', $expectedValue, null, null);

        $this->assertEquals($expectedValue, $actualDetails->getValue());
    }

    /**
     * -----------------
     * Requirement 1.4.4
     * -----------------
     * The evaluation details structure's flag key field MUST contain the flag key argument passed to the detailed flag evaluation method.
     */

    /**
     * -----------------
     * Requirement 1.4.2
     * -----------------
     * The evaluation details structure's value field MUST contain the evaluated flag value.
     */
    public function testClientEvaluationDetailsHasFlagKey(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = 'flagKey';
        $actualDetails = $client->getBooleanDetails($expectedValue, false, null, null);

        $this->assertEquals($expectedValue, $actualDetails->getFlagKey());
    }

    /**
     * -----------------
     * Requirement 1.4.5
     * -----------------
     * In cases of normal execution, the evaluation details structure's variant field MUST contain the value of the variant field in the flag resolution structure returned by the configured provider, if the field is set.
     */
    public function testClientEvaluationDetailsHasVariantField(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedVariant = 'VARIANT VALUE';

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturns((new ResolutionDetailsBuilder())->withValue(true)->withVariant($expectedVariant)->build());

        $api->setProvider($mockProvider);

        $actualDetails = $client->getBooleanDetails('flagKey', false, null, null);

        $this->assertEquals($expectedVariant, $actualDetails->getVariant());
    }

    /**
     * -----------------
     * Requirement 1.4.6
     * -----------------
     * In cases of normal execution, the evaluation details structure's reason field MUST contain the value of the reason field in the flag resolution structure returned by the configured provider, if the field is set.
     */
    public function testClientEvaluationDetailsHasReasonField(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedReason = 'REASON VALUE';

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturns((new ResolutionDetailsBuilder())->withValue(true)->withReason($expectedReason)->build());

        $api->setProvider($mockProvider);

        $actualDetails = $client->getBooleanDetails('flagKey', false, null, null);

        $this->assertEquals($expectedReason, $actualDetails->getReason());
    }

    /**
     * -----------------
     * Requirement 1.4.7
     * -----------------
     * In cases of abnormal execution, the evaluation details structure's error code field MUST contain a string identifying an error occurred during flag evaluation and the nature of the error.
     */
    public function testClientEvaluationDetailsAbnormalExecutionHasErrorCodeField(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedErrorCode = ErrorCode::FLAG_NOT_FOUND();

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturns((new ResolutionDetailsBuilder())->withValue(true)->withErrorCode($expectedErrorCode)->build());

        $api->setProvider($mockProvider);

        $actualDetails = $client->getBooleanDetails('flagKey', false, null, null);

        $this->assertEquals($expectedErrorCode, $actualDetails->getErrorCode());
    }

    /**
     * -----------------
     * Requirement 1.4.8
     * -----------------
     * In cases of abnormal execution (network failure, unhandled error, etc) the reason field in the evaluation details SHOULD indicate an error.
     */
    public function testClientEvaluationDetailsAbnormalExecutionHasReasonField(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedErrorCode = ErrorCode::FLAG_NOT_FOUND();
        $expectedReason = 'Failed to reach target server';

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveBooleanValue')
            ->andReturns((new ResolutionDetailsBuilder())->withValue(true)->withErrorCode($expectedErrorCode)->withReason($expectedReason)->build());

        $api->setProvider($mockProvider);

        $actualDetails = $client->getBooleanDetails('flagKey', false, null, null);

        $this->assertEquals($expectedReason, $actualDetails->getReason());
    }

    /**
     * -----------------
     * Requirement 1.4.9
     * -----------------
     * Methods, functions, or operations on the client MUST NOT throw exceptions, or otherwise abnormally terminate. Flag evaluation calls must always return the default value in the event of abnormal execution. Exceptions include functions or methods for the purposes for configuration or setup.
     */
    public function testClientFunctionsDoNotThrow(): void
    {
        // TODO: Determine a real way to magically fuzz this :)
        $this->assertTrue(true);
    }

    /**
     * -----------------
     * Requirement 1.4.10
     * -----------------
     * In the case of abnormal execution, the client SHOULD log an informative error message.
     *
     * Implementations may define a standard logging interface that can be supplied as an optional argument to the client creation function, which may wrap standard logging functionality of the implementation language.
     */
    public function testClientShouldLogInformativeErrorDuringAbnormalExecution(): void
    {
        $api = APITestHelper::new();

        /** @var LoggerInterface|MockInterface $mockLogger */
        $mockLogger = $this->mockery(LoggerInterface::class);
        $mockLogger->shouldReceive('error')->once();
        $api->setLogger($mockLogger);

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveBooleanValue')
            ->andThrows(new Exception('NETWORK_ERROR'));
        $api->setProvider($mockProvider);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');
        $client->setLogger($mockLogger);

        $value = $client->getBooleanValue('flagKey', false, null, null);

        $this->assertEquals($value, false);
    }

    /**
     * -----------------
     * Requirement 1.5.1
     * -----------------
     * The evaluation options structure's hooks field denotes an ordered collection of hooks that the client MUST execute for the respective flag evaluation, in addition to those already configured.
     */
    public function testEvaluationOptionsHooksAreCalled(): void
    {
        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $expectedValue = true;

        $mockProvider = $this->getDefaultMockProvider();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturn(ResolutionDetailsFactory::fromSuccess($expectedValue));
        $api->setProvider($mockProvider);

        // create ALL mocked hooks
        $apiHook = $this->getMockedHook();
        $clientHook = $this->getMockedHook();
        $invocationHook = $this->getMockedHook();

        // ensure the call order of the 'before' hooks
        $apiHook->expects('before')->andReturnUsing(function () use ($clientHook, $invocationHook) {
            $clientHook->shouldNotHaveReceived('before');
            $invocationHook->shouldNotHaveReceived('before');
        });
        $clientHook->expects('before')->andReturnUsing(function () use ($apiHook, $invocationHook) {
            $apiHook->shouldHaveReceived('before');
            $invocationHook->shouldNotHaveReceived('before');
        });
        $invocationHook->expects('before')->andReturnUsing(function () use ($apiHook, $clientHook) {
            $apiHook->shouldHaveReceived('before');
            $clientHook->shouldHaveReceived('before');
        });

        // ensure the call order of the 'after' hooks is in reverse
        $apiHook->expects('after')->andReturnUsing(function () use ($clientHook, $invocationHook) {
            $clientHook->shouldHaveReceived('after');
            $invocationHook->shouldHaveReceived('after');
        });
        $clientHook->expects('after')->andReturnUsing(function () use ($apiHook, $invocationHook) {
            $apiHook->shouldNotHaveReceived('after');
            $invocationHook->shouldHaveReceived('after');
        });
        $invocationHook->expects('after')->andReturnUsing(function () use ($apiHook, $clientHook) {
            $apiHook->shouldNotHaveReceived('after');
            $clientHook->shouldNotHaveReceived('after');
        });

        // ensure the call order of the 'error' hooks does not occur
        $apiHook->shouldNotReceive('error');
        $clientHook->shouldNotReceive('error');
        $invocationHook->shouldNotReceive('error');

        // ensure the call order of the 'finally' hooks is in reverse
        $apiHook->expects('finally')->andReturnUsing(function () use ($clientHook, $invocationHook) {
            $clientHook->shouldHaveReceived('finally');
            $invocationHook->shouldHaveReceived('finally');
        });
        $clientHook->expects('finally')->andReturnUsing(function () use ($apiHook, $invocationHook) {
            $apiHook->shouldNotHaveReceived('finally');
            $invocationHook->shouldHaveReceived('finally');
        });
        $invocationHook->expects('finally')->andReturnUsing(function () use ($apiHook, $clientHook) {
            $apiHook->shouldNotHaveReceived('finally');
            $clientHook->shouldNotHaveReceived('finally');
        });

        // add hooks
        $apiHook->allows(Mockery::any());
        $client->addHooks($clientHook);
        $api->addHooks($apiHook);

        $actualValue = $client->getBooleanValue('key', false, null, new EvaluationOptions([$invocationHook]));

        $this->assertEquals($actualValue, $expectedValue);
    }

    /**
     * -----------------
     * Requirement 3.2.1
     * -----------------
     * The API, Client and invocation MUST have a method for supplying evaluation context.
     */
    public function testMustHaveMethodForSupplyingEvaluationContexts(): void
    {
        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->setEvaluationContext(new EvaluationContext(null, new Attributes(['api' => 'api'])));

        $client = new OpenFeatureClient($api, 'name', 'version');
        $client->setEvaluationContext(new EvaluationContext(null, new Attributes(['client' => 'client'])));

        $testRunner = $this;

        $mockHook->shouldReceive('before')->andReturnUsing(function (HookContext $ctx, $hints) use ($testRunner) {
            $attributes = $ctx->getEvaluationContext()->getAttributes();

            $testRunner->assertEquals('api', $attributes->get('api'));
            $testRunner->assertEquals('client', $attributes->get('client'));
            $testRunner->assertEquals('invocation', $attributes->get('invocation'));
        });

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(null, new Attributes(['invocation' => 'invocation'])), new EvaluationOptions([$mockHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 3.2.2
     * -----------------
     * Evaluation context MUST be merged in the order: API (global; lowest precedence) -> client -> invocation -> before hooks (highest precedence), with duplicate values being overwritten.
     */
    public function testMustMergeEvaluationContextsInExpectedOrder(): void
    {
        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();

        /** @var Mockery\MockInterface|EvaluationContext $mockApiEvaluationContext */
        $mockApiEvaluationContext = $this->mockery(EvaluationContext::class)->makePartial();
        /** @var Mockery\MockInterface|EvaluationContext $mockClientEvaluationContext */
        $mockClientEvaluationContext = $this->mockery(EvaluationContext::class)->makePartial();
        /** @var Mockery\MockInterface|EvaluationContext $mockInvocationEvaluationContext */
        $mockInvocationEvaluationContext = $this->mockery(EvaluationContext::class)->makePartial();
        /** @var Mockery\MockInterface|EvaluationContext $mockBeforeHookEvaluationContext */
        $mockBeforeHookEvaluationContext = $this->mockery(EvaluationContext::class)->makePartial();

        $mockApiEvaluationContext->shouldReceive('getAttributes')->andReturnUsing(function () use ($mockClientEvaluationContext, $mockInvocationEvaluationContext, $mockBeforeHookEvaluationContext) {
            $mockClientEvaluationContext->shouldNotHaveReceived('getAttributes');
            $mockInvocationEvaluationContext->shouldNotHaveReceived('getAttributes');
            $mockBeforeHookEvaluationContext->shouldNotHaveReceived('getAttributes');

            return new Attributes(['api' => 'api']);
        });
        $mockClientEvaluationContext->shouldReceive('getAttributes')->andReturnUsing(function () use ($mockApiEvaluationContext, $mockInvocationEvaluationContext, $mockBeforeHookEvaluationContext) {
            $mockApiEvaluationContext->shouldHaveReceived('getAttributes');
            $mockInvocationEvaluationContext->shouldNotHaveReceived('getAttributes');
            $mockBeforeHookEvaluationContext->shouldNotHaveReceived('getAttributes');

            return new Attributes(['client' => 'client']);
        });
        $mockInvocationEvaluationContext->shouldReceive('getAttributes')->andReturnUsing(function () use ($mockApiEvaluationContext, $mockClientEvaluationContext, $mockBeforeHookEvaluationContext) {
            $mockApiEvaluationContext->shouldHaveReceived('getAttributes');
            $mockClientEvaluationContext->shouldHaveReceived('getAttributes');
            $mockBeforeHookEvaluationContext->shouldNotHaveReceived('getAttributes');

            return new Attributes(['invocation' => 'invocation']);
        });
        $mockBeforeHookEvaluationContext->shouldReceive('getAttributes')->andReturnUsing(function () use ($mockApiEvaluationContext, $mockClientEvaluationContext, $mockInvocationEvaluationContext) {
            $mockApiEvaluationContext->shouldHaveReceived('getAttributes');
            $mockClientEvaluationContext->shouldHaveReceived('getAttributes');
            $mockInvocationEvaluationContext->shouldHaveReceived('getAttributes');

            return new Attributes(['invocation' => 'invocation']);
        });

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->setEvaluationContext(new EvaluationContext(null, new Attributes(['api' => 'api'])));

        $client = new OpenFeatureClient($api, 'name', 'version');
        $client->setEvaluationContext(new EvaluationContext(null, new Attributes(['client' => 'client'])));

        $testRunner = $this;

        $mockHook->shouldReceive('before')->andReturnUsing(function (HookContext $ctx, $hints) use ($testRunner, $mockBeforeHookEvaluationContext) {
            $attributes = $ctx->getEvaluationContext()->getAttributes();

            $testRunner->assertEquals('api', $attributes->get('api'));
            $testRunner->assertEquals('client', $attributes->get('client'));
            $testRunner->assertEquals('invocation', $attributes->get('invocation'));

            return $mockBeforeHookEvaluationContext;
        });

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(null, new Attributes(['invocation' => 'invocation'])), new EvaluationOptions([$mockHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.3.2
     * -----------------
     * The before stage MUST run before flag resolution occurs. It accepts a hook context (required) and hook hints (optional) as parameters and returns either an evaluation context or nothing.
     *
     * -----------------
     * Requirement 4.3.5
     * -----------------
     * The after stage MUST run after flag resolution occurs. It accepts a hook context (required), flag evaluation details (required) and hook hints (optional). It has no return value.
     *
     * -----------------
     * Requirement 4.3.7
     * -----------------
     * The finally hook MUST run after the before, after, and error stages. It accepts a hook context (required) and hook hints (optional). There is no return value.
     */
    public function testHooksAndExecutionMustRunInOrder(): void
    {
        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);

        $client = new OpenFeatureClient($api, 'name', 'version');

        $mockHook->shouldReceive('before')->andReturnUsing(function () use ($mockHook, $mockProvider) {
            $mockHook->shouldNotHaveReceived('after');
            $mockProvider->shouldNotHaveReceived('resolveBooleanValue');
            $mockHook->shouldNotHaveReceived('error');
            $mockHook->shouldNotHaveReceived('finally');
        });

        $mockProvider->shouldReceive('resolveBooleanValue')->andReturnUsing(function () use ($mockHook) {
            $mockHook->shouldHaveReceived('before');
            $mockHook->shouldNotHaveReceived('after');
            $mockHook->shouldNotHaveReceived('error');
            $mockHook->shouldNotHaveReceived('finally');

            return ResolutionDetailsFactory::fromSuccess(true);
        });

        $mockHook->shouldReceive('after')->andReturnUsing(function () use ($mockHook, $mockProvider) {
            $mockHook->shouldHaveReceived('before');
            $mockProvider->shouldHaveReceived('resolveBooleanValue');
            $mockHook->shouldNotHaveReceived('error');
            $mockHook->shouldNotHaveReceived('finally');
        });

        $mockHook->shouldNotReceive('error');

        $mockHook->shouldReceive('finally')->andReturnUsing(function () use ($mockHook, $mockProvider) {
            $mockHook->shouldHaveReceived('before');
            $mockProvider->shouldHaveReceived('resolveBooleanValue');
            $mockHook->shouldHaveReceived('after');
            $mockHook->shouldNotHaveReceived('error');
        });

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(), new EvaluationOptions([$mockHook]));

        $this->assertEquals($actualValue, true);
    }

    /**
     * -----------------
     * Requirement 4.3.4
     * -----------------
     * When before hooks have finished executing, any resulting evaluation context MUST be merged with the existing evaluation context.
     */
    public function testBeforeHooksReturnedEvaluationContextMustBeMergedWithExistingEvaluationContext(): void
    {
        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        $mockHook->shouldReceive('supportsFlagValueType')->andReturns(true);
        $mockHook->shouldReceive('before')->andReturnUsing(function (HookContext $context) {
            $customFields = $context->getEvaluationContext()->getAttributes();

            $this->assertEquals('oldValue', $customFields->get('oldKey'));

            return new EvaluationContext(null, new Attributes(['newKey' => 'newValue', 'oldKey' => 'newValue']));
        });

        $mockHook->shouldReceive('after')->andReturnUsing(function (HookContext $context) {
            $customFields = $context->getEvaluationContext()->getAttributes();

            $this->assertEquals('newValue', $customFields->get('oldKey'));
            $this->assertEquals('newValue', $customFields->get('newKey'));
        });

        $mockHook->shouldNotReceive('error');

        $mockHook->shouldReceive('finally')->andReturnUsing(function (HookContext $context) {
            $customFields = $context->getEvaluationContext()->getAttributes();

            $this->assertEquals('newValue', $customFields->get('oldKey'));
            $this->assertEquals('newValue', $customFields->get('newKey'));
        });

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturn(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);

        $client = new OpenFeatureClient($api, 'name', 'version');

        $evaluationContext = new EvaluationContext(null, new Attributes([
            'oldKey' => 'oldValue',
        ]));

        $actualValue = $client->getBooleanValue('key', false, $evaluationContext, new EvaluationOptions([$mockHook]));

        $this->assertEquals($actualValue, true);
    }

    /**
     * -----------------
     * Requirement 4.3.3
     * -----------------
     * Any evaluation context returned from a before hook MUST be passed to subsequent before hooks (via HookContext).
     */
    public function testBeforeHookReturnedEvaluationContextMustBePassedToSubsequentHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $firstMockHook */
        $firstMockHook = $this->mockery(TestHook::class)->makePartial();
        $firstMockHook->shouldReceive('supportsFlagValueType')->andReturns(true);
        $firstMockHook->shouldReceive('before')->andReturnUsing(function (HookContext $context) {
            $customFields = $context->getEvaluationContext()->getAttributes();

            $this->assertEquals('initialValue', $customFields->get('initialKey'));
            $this->assertEquals(null, $customFields->get('hook1'));
            $this->assertEquals(null, $customFields->get('hook2'));

            return new EvaluationContext(null, new Attributes(['hook1' => 'hook1']));
        });

        /** @var Mockery\MockInterface|Hook $secondMockHook */
        $secondMockHook = $this->mockery(TestHook::class)->makePartial();
        $secondMockHook->shouldReceive('supportsFlagValueType')->andReturns(true);
        $secondMockHook->shouldReceive('before')->andReturnUsing(function (HookContext $context) {
            $customFields = $context->getEvaluationContext()->getAttributes();

            $this->assertEquals('initialValue', $customFields->get('initialKey'));
            $this->assertEquals('hook1', $customFields->get('hook1'));
            $this->assertEquals(null, $customFields->get('hook2'));

            return new EvaluationContext(null, new Attributes(['hook2' => 'hook2']));
        });

        /** @var Mockery\MockInterface|Hook $thirdMockHook */
        $thirdMockHook = $this->mockery(TestHook::class)->makePartial();
        $thirdMockHook->shouldReceive('supportsFlagValueType')->andReturns(true);
        $thirdMockHook->shouldReceive('before')->andReturnUsing(function (HookContext $context) {
            $customFields = $context->getEvaluationContext()->getAttributes();

            $this->assertEquals('initialValue', $customFields->get('initialKey'));
            $this->assertEquals('hook1', $customFields->get('hook1'));
            $this->assertEquals('hook2', $customFields->get('hook2'));

            return new EvaluationContext(null, new Attributes(['hook3' => 'hook3']));
        });

        $api = APITestHelper::new();
        $client = new OpenFeatureClient($api, 'name', 'version');

        $evaluationContext = new EvaluationContext(null, new Attributes([
            'initialKey' => 'initialValue',
        ]));

        $actualValue = $client->getBooleanValue('key', false, $evaluationContext, new EvaluationOptions([$firstMockHook, $secondMockHook, $thirdMockHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.3.6
     * -----------------
     * The error hook MUST run when errors are encountered in the before stage, the after stage or during flag resolution. It accepts hook context (required), exception representing what went wrong (required), and hook hints (optional). It has no return value.
     */
    public function testErrorHookMustRunWhenErrorsAreEncounteredInTheBeforeStage(): void
    {
        $testRunner = $this;
        $exception = new Exception('Error in before stage');

        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        $mockHook->shouldReceive('before')->andThrow($exception);
        $mockHook->shouldNotReceive('after');
        $mockHook->shouldReceive('error')->andReturnUsing(function ($ctx, $err) use ($exception, $testRunner) {
            $testRunner->assertEquals($exception, $err);
        });
        $mockHook->shouldReceive('finally');

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturn(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($mockHook);

        $client = new OpenFeatureClient($api, 'name', 'version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        // the return value should be the default value, not the value expected to be returned by
        // the provider in the happy path
        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.3.6
     * -----------------
     * The error hook MUST run when errors are encountered in the before stage, the after stage or during flag resolution. It accepts hook context (required), exception representing what went wrong (required), and hook hints (optional). It has no return value.
     */
    public function testErrorHookMustRunWhenErrorsAreEncounteredInTheAfterStage(): void
    {
        $testRunner = $this;
        $exception = new Exception('Error in after stage');

        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        $mockHook->shouldReceive('before');
        $mockHook->shouldReceive('after')->andThrow($exception);
        $mockHook->shouldReceive('error')->andReturnUsing(function ($ctx, $err) use ($exception, $testRunner) {
            $testRunner->assertEquals($exception, $err);
        });
        $mockHook->shouldReceive('finally');

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturn(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($mockHook);

        $client = new OpenFeatureClient($api, 'name', 'version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        // the return value should be the default value, not the value expected to be returned by
        // the provider in the happy path
        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.3.6
     * -----------------
     * The error hook MUST run when errors are encountered in the before stage, the after stage or during flag resolution. It accepts hook context (required), exception representing what went wrong (required), and hook hints (optional). It has no return value.
     */
    public function testErrorHookMustRunWhenErrorsAreEncounteredInFlagResolution(): void
    {
        $testRunner = $this;
        $exception = new Exception('Error in flag resolution');

        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        $mockHook->shouldReceive('before');
        $mockHook->shouldNotReceive('after');
        $mockHook->shouldReceive('error')->andReturnUsing(function ($ctx, $err) use ($exception, $testRunner) {
            $testRunner->assertEquals($exception, $err);
        });
        $mockHook->shouldReceive('finally');

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')->andThrow($exception);

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($mockHook);

        $client = new OpenFeatureClient($api, 'name', 'version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        // the return value should be the default value, not the value expected to be returned by
        // the provider in the happy path
        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.1
     * -----------------
     * The API, Client, Provider, and invocation MUST have a method for registering hooks.
     */
    public function testXMustHaveMethodForRegisteringHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $mockHook */
        $mockHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->setEvaluationContext(new EvaluationContext(null, new Attributes(['api' => 'api'])));

        $client = new OpenFeatureClient($api, 'name', 'version');
        $client->setEvaluationContext(new EvaluationContext(null, new Attributes(['client' => 'client'])));

        $testRunner = $this;

        $mockHook->shouldReceive('before')->andReturnUsing(function (HookContext $ctx, $hints) use ($testRunner) {
            $attributes = $ctx->getEvaluationContext()->getAttributes();

            $testRunner->assertEquals('api', $attributes->get('api'));
            $testRunner->assertEquals('client', $attributes->get('client'));
            $testRunner->assertEquals('invocation', $attributes->get('invocation'));
        });

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(null, new Attributes(['invocation' => 'invocation'])), new EvaluationOptions([$mockHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.2
     * -----------------
     * Hooks MUST be evaluated in the following order:
     *
     *     before: API, Client, Invocation, Provider
     */
    public function testHooksAreEvaluatedInTheCorrectOrderInBeforeHook(): void
    {
        /** @var Mockery\MockInterface|Hook $apiHook */
        $apiHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $clientHook */
        $clientHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $providerHook */
        $providerHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $invocationHook */
        $invocationHook = $this->mockery(TestHook::class)->makePartial();

        $apiHook->shouldReceive('before')->andReturnUsing(function () use ($clientHook, $providerHook, $invocationHook) {
            $clientHook->shouldNotHaveReceived('before');
            $providerHook->shouldNotHaveReceived('before');
            $invocationHook->shouldNotHaveReceived('before');
        });
        $clientHook->shouldReceive('before')->andReturnUsing(function () use ($apiHook, $providerHook, $invocationHook) {
            $apiHook->shouldHaveReceived('before');
            $providerHook->shouldNotHaveReceived('before');
            $invocationHook->shouldNotHaveReceived('before');
        });
        $invocationHook->shouldReceive('before')->andReturnUsing(function () use ($apiHook, $clientHook, $providerHook) {
            $apiHook->shouldHaveReceived('before');
            $clientHook->shouldHaveReceived('before');
            $providerHook->shouldNotHaveReceived('before');
        });
        $providerHook->shouldReceive('before')->andReturnUsing(function () use ($apiHook, $clientHook, $invocationHook) {
            $apiHook->shouldHaveReceived('before');
            $clientHook->shouldHaveReceived('before');
            $invocationHook->shouldHaveReceived('before');
        });

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->allows('getHooks')->andReturns([$providerHook]);

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($apiHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');
        $client->addHooks($clientHook);

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(null, new Attributes(['invocation' => 'invocation'])), new EvaluationOptions([$invocationHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.2
     * -----------------
     * Hooks MUST be evaluated in the following order:
     *
     *     after: Provider, Invocation, Client, API
     */
    public function testHooksAreEvaluatedInTheCorrectOrderInAfterHook(): void
    {
        /** @var Mockery\MockInterface|Hook $apiHook */
        $apiHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $clientHook */
        $clientHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $providerHook */
        $providerHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $invocationHook */
        $invocationHook = $this->mockery(TestHook::class)->makePartial();

        $providerHook->shouldReceive('after')->andReturnUsing(function () use ($apiHook, $clientHook, $invocationHook) {
            $invocationHook->shouldNotHaveReceived('after');
            $clientHook->shouldNotHaveReceived('after');
            $apiHook->shouldNotHaveReceived('after');
        });
        $invocationHook->shouldReceive('after')->andReturnUsing(function () use ($apiHook, $clientHook, $providerHook) {
            $providerHook->shouldHaveReceived('after');
            $clientHook->shouldNotHaveReceived('after');
            $apiHook->shouldNotHaveReceived('after');
        });
        $clientHook->shouldReceive('after')->andReturnUsing(function () use ($apiHook, $providerHook, $invocationHook) {
            $invocationHook->shouldHaveReceived('after');
            $providerHook->shouldHaveReceived('after');
            $apiHook->shouldNotHaveReceived('after');
        });
        $apiHook->shouldReceive('after')->andReturnUsing(function () use ($clientHook, $providerHook, $invocationHook) {
            $invocationHook->shouldHaveReceived('after');
            $providerHook->shouldHaveReceived('after');
            $clientHook->shouldHaveReceived('after');
        });

        $testProvider = new TestProvider();
        $testProvider->setHooks([$providerHook]);

        $api = APITestHelper::new();
        $api->setProvider($testProvider);
        $api->addHooks($apiHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');
        $client->addHooks($clientHook);

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(null, new Attributes(['invocation' => 'invocation'])), new EvaluationOptions([$invocationHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.2
     * -----------------
     * Hooks MUST be evaluated in the following order:
     *
     *     error (if applicable): Provider, Invocation, Client, API
     */
    public function testHooksAreEvaluatedInTheCorrectOrderInErrorHook(): void
    {
        /** @var Mockery\MockInterface|Hook $apiHook */
        $apiHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $clientHook */
        $clientHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $providerHook */
        $providerHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $invocationHook */
        $invocationHook = $this->mockery(TestHook::class)->makePartial();

        $providerHook->shouldReceive('error')->andReturnUsing(function () use ($apiHook, $clientHook, $invocationHook) {
            $invocationHook->shouldNotHaveReceived('error');
            $clientHook->shouldNotHaveReceived('error');
            $apiHook->shouldNotHaveReceived('error');
        });
        $invocationHook->shouldReceive('error')->andReturnUsing(function () use ($apiHook, $clientHook, $providerHook) {
            $providerHook->shouldHaveReceived('error');
            $clientHook->shouldNotHaveReceived('error');
            $apiHook->shouldNotHaveReceived('error');
        });
        $clientHook->shouldReceive('error')->andReturnUsing(function () use ($apiHook, $providerHook, $invocationHook) {
            $invocationHook->shouldHaveReceived('error');
            $providerHook->shouldHaveReceived('error');
            $apiHook->shouldNotHaveReceived('error');
        });
        $apiHook->shouldReceive('error')->andReturnUsing(function () use ($clientHook, $providerHook, $invocationHook) {
            $invocationHook->shouldHaveReceived('error');
            $providerHook->shouldHaveReceived('error');
            $clientHook->shouldHaveReceived('error');
        });

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->allows('getHooks')->andReturns([$providerHook]);
        $mockProvider->shouldReceive('resolveBooleanValue')->andThrow(new Exception('Error'));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($apiHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');
        $client->addHooks($clientHook);

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(null, new Attributes(['invocation' => 'invocation'])), new EvaluationOptions([$invocationHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.2
     * -----------------
     * Hooks MUST be evaluated in the following order:
     *
     *     finally: Provider, Invocation, Client, API
     */
    public function testHooksAreEvaluatedInTheCorrectOrderInFinallyHook(): void
    {
        /** @var Mockery\MockInterface|Hook $apiHook */
        $apiHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $clientHook */
        $clientHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $providerHook */
        $providerHook = $this->mockery(TestHook::class)->makePartial();
        /** @var Mockery\MockInterface|Hook $invocationHook */
        $invocationHook = $this->mockery(TestHook::class)->makePartial();

        $providerHook->shouldReceive('finally')->andReturnUsing(function () use ($apiHook, $clientHook, $invocationHook) {
            $invocationHook->shouldNotHaveReceived('finally');
            $clientHook->shouldNotHaveReceived('finally');
            $apiHook->shouldNotHaveReceived('finally');
        });
        $invocationHook->shouldReceive('finally')->andReturnUsing(function () use ($apiHook, $clientHook, $providerHook) {
            $providerHook->shouldHaveReceived('finally');
            $clientHook->shouldNotHaveReceived('finally');
            $apiHook->shouldNotHaveReceived('finally');
        });
        $clientHook->shouldReceive('finally')->andReturnUsing(function () use ($apiHook, $providerHook, $invocationHook) {
            $invocationHook->shouldHaveReceived('finally');
            $providerHook->shouldHaveReceived('finally');
            $apiHook->shouldNotHaveReceived('finally');
        });
        $apiHook->shouldReceive('finally')->andReturnUsing(function () use ($clientHook, $providerHook, $invocationHook) {
            $invocationHook->shouldHaveReceived('finally');
            $providerHook->shouldHaveReceived('finally');
            $clientHook->shouldHaveReceived('finally');
        });

        $testProvider = new TestProvider();
        $testProvider->setHooks([$providerHook]);

        $api = APITestHelper::new();
        $api->setProvider($testProvider);
        $api->addHooks($apiHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');
        $client->addHooks($clientHook);

        $actualValue = $client->getBooleanValue('key', false, new EvaluationContext(null, new Attributes(['invocation' => 'invocation'])), new EvaluationOptions([$invocationHook]));

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.3
     * -----------------
     * If a finally hook abnormally terminates, evaluation MUST proceed, including the execution of any remaining finally hooks.
     */
    public function testFinallyHookAbnormalExecutionMustContinueRemainingFinallyHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $erroringHook */
        $erroringHook = $this->mockery(TestHook::class)->makePartial();
        $erroringHook->shouldReceive('finally')->andThrow(new Exception('finally error'));
        /** @var Mockery\MockInterface|Hook $subsequentHook */
        $subsequentHook = $this->mockery(TestHook::class)->makePartial();
        $subsequentHook->shouldReceive('finally');

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturn(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($erroringHook, $subsequentHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        $this->assertEquals($actualValue, true);
    }

    /**
     * -----------------
     * Requirement 4.4.4
     * -----------------
     * If an error hook abnormally terminates, evaluation MUST proceed, including the execution of any remaining error hooks.
     */
    public function testErrorHookAbnormalExecutionMustContinueRemainingErrorHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $erroringHook */
        $erroringHook = $this->mockery(TestHook::class)->makePartial();
        $erroringHook->shouldReceive('error')->andThrow(new Exception('Error'));
        /** @var Mockery\MockInterface|Hook $subsequentHook */
        $subsequentHook = $this->mockery(TestHook::class)->makePartial();
        $subsequentHook->shouldReceive('error');

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')->andThrow(new Exception('Resolution failure'));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($erroringHook, $subsequentHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.5
     * -----------------
     * If an error occurs in the before or after hooks, the error hooks MUST be invoked.
     */
    public function testErrorsInBeforeHooksMustInvokeErrorHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $errorHook */
        $errorHook = $this->mockery(TestHook::class)->makePartial();
        $errorHook->shouldReceive('error');
        /** @var Mockery\MockInterface|Hook $failingBeforeHook */
        $failingBeforeHook = $this->mockery(TestHook::class)->makePartial();
        $failingBeforeHook->shouldReceive('before')->andThrow(new Exception('Before Error'));

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->allows('resolveBooleanValue')->andReturns(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($errorHook, $failingBeforeHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.5
     * -----------------
     * If an error occurs in the before or after hooks, the error hooks MUST be invoked.
     */
    public function testErrorsInAfterHooksMustInvokeErrorHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $errorHook */
        $errorHook = $this->mockery(TestHook::class)->makePartial();
        $errorHook->shouldReceive('error');
        /** @var Mockery\MockInterface|Hook $failingAfterHook */
        $failingAfterHook = $this->mockery(TestHook::class)->makePartial();
        $failingAfterHook->shouldReceive('after')->andThrow(new Exception('After Error'));

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->allows('resolveBooleanValue')->andReturns(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($errorHook, $failingAfterHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.6
     * -----------------
     * If an error occurs during the evaluation of before or after hooks, any remaining hooks in the before or after stages MUST NOT be invoked.
     */
    public function testErrorsInBeforeHooksStopsFurtherExecutionOfRemainingSaidHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $erroringHook */
        $erroringHook = $this->mockery(TestHook::class)->makePartial();
        $erroringHook->shouldReceive('before')->andThrow(new Exception('Before Error'));
        /** @var Mockery\MockInterface|Hook $subsequentHook */
        $subsequentHook = $this->mockery(TestHook::class)->makePartial();
        $subsequentHook->shouldNotReceive('before');

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->allows('resolveBooleanValue')->andReturns(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($erroringHook, $subsequentHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.6
     * -----------------
     * If an error occurs during the evaluation of before or after hooks, any remaining hooks in the before or after stages MUST NOT be invoked.
     */
    public function testErrorsInAfterHooksStopsFurtherExecutionOfRemainingSaidHooks(): void
    {
        /** @var Mockery\MockInterface|Hook $erroringHook */
        $erroringHook = $this->mockery(TestHook::class)->makePartial();
        $erroringHook->shouldReceive('after')->andThrow(new Exception('After Error'));
        /** @var Mockery\MockInterface|Hook $subsequentHook */
        $subsequentHook = $this->mockery(TestHook::class)->makePartial();
        $subsequentHook->shouldNotReceive('after');

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->allows('resolveBooleanValue')->andReturns(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($erroringHook, $subsequentHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        $this->assertEquals($actualValue, false);
    }

    /**
     * -----------------
     * Requirement 4.4.7
     * -----------------
     * If an error occurs in the before hooks, the default value MUST be returned.
     */
    public function testErrorInBeforeHooksMustReturnDefaultValue(): void
    {
        /** @var Mockery\MockInterface|Hook $erroringBeforeHook */
        $erroringBeforeHook = $this->mockery(TestHook::class)->makePartial();
        $erroringBeforeHook->shouldReceive('before')->andThrow(new Exception('Before Error'));

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->allows('resolveBooleanValue')->andReturns(ResolutionDetailsFactory::fromSuccess(true));

        $api = APITestHelper::new();
        $api->setProvider($mockProvider);
        $api->addHooks($erroringBeforeHook);

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $actualValue = $client->getBooleanValue('key', false, null, null);

        $this->assertEquals($actualValue, false);
    }

    /**
     * @return Provider|MockInterface
     */
    private function getDefaultMockProvider(string $name = 'mock-provider')
    {
        /** @var Provider|MockInterface $mockProvider */
        $mockProvider = $this->mockery(Provider::class);

        $mockProvider->allows('getMetadata')->andReturns(new Metadata($name));
        $mockProvider->allows('getHooks')->andReturns([]);

        return $mockProvider;
    }

    /**
     * @return Hook|MockInterface
     */
    private function getMockedHook(string $supportedFlagValueType = '')
    {
        /** @var Hook|MockInterface $mockHook */
        $mockHook = $this->mockery(Hook::class);

        if ($supportedFlagValueType !== '') {
            $mockHook->allows('supportsFlagValueType')->andReturnUsing(fn ($type) => $type === $supportedFlagValueType);
        } else {
            $mockHook->allows('supportsFlagValueType')->andReturns(true);
        }

        return $mockHook;
    }
}
