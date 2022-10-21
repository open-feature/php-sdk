<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Exception;
use Mockery;
use OpenFeature\OpenFeatureClient;
use OpenFeature\Test\APITestHelper;
use OpenFeature\Test\TestCase;
use OpenFeature\Test\TestHook;
use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\flags\Attributes;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\flags\EvaluationOptions;
use OpenFeature\implementation\hooks\HookContextBuilder;
use OpenFeature\implementation\hooks\HookContextFactory;
use OpenFeature\implementation\hooks\HookExecutor;
use OpenFeature\implementation\hooks\HookHints;
use OpenFeature\implementation\hooks\ImmutableHookContext;
use OpenFeature\implementation\provider\ResolutionDetailsFactory;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\hooks\HookHints as HookHintsInterface;
use Throwable;

use function method_exists;

class HooksTest extends TestCase
{
    /**
     * Requirement 4.1.1
     *
     * Hook context MUST provide: the flag key, flag value type, evaluation context, and the default value.
     */
    public function testHookContextMustProvideArguments(): void
    {
        $flagKey = 'test-key';
        $flagValueType = FlagValueType::BOOLEAN;
        $evaluationContext = new EvaluationContext();
        $defaultValue = false;

        $hookContext = HookContextFactory::from($flagKey, $flagValueType, $defaultValue, $evaluationContext, new Metadata('client'), new Metadata('provider'));

        $this->assertEquals($flagKey, $hookContext->getFlagKey());
        $this->assertEquals($flagValueType, $hookContext->getType());
        $this->assertEquals($evaluationContext, $hookContext->getEvaluationContext());
        $this->assertEquals($defaultValue, $hookContext->getDefaultValue());
    }

    /**
     * Requirement 4.1.2
     *
     * The hook context SHOULD provide: access to the client metadata and the provider metadata fields.
     */
    public function testHookContextShouldProvideAccessToMetadataFields(): void
    {
        $clientMetadata = new Metadata('client');
        $providerMetadata = new Metadata('provider');

        $hookContext = HookContextFactory::from('key', FlagValueType::BOOLEAN, false, new EvaluationContext(), $clientMetadata, $providerMetadata);

        $this->assertEquals($clientMetadata, $hookContext->getClientMetadata());
        $this->assertEquals($providerMetadata, $hookContext->getProviderMetadata());
    }

    /**
     * Requirement 4.1.3
     *
     * The flag key, flag type, and default value properties MUST be immutable. If the language does not support immutability, the hook MUST NOT modify these properties.
     */
    public function testValuePropertiesMustBeImmutable(): void
    {
        $expectedHookContext = HookContextFactory::from('key', FlagValueType::BOOLEAN, false, new EvaluationContext(), new Metadata('client'), new Metadata('provider'));
        $testRunner = $this;

        $hook = $this->mockery(TestHook::class)->makePartial();

        $hook->shouldReceive('before')->andReturnUsing(function (HookContext $context, HookHints $hints) use ($testRunner, $expectedHookContext) {
            // there are no setters on any interfaces provided by HookContext or its children...
            $testRunner->assertEquals($expectedHookContext, $context);
        });

        (new HookExecutor(null))->beforeHooks(FlagValueType::BOOLEAN, $expectedHookContext, [$hook], new HookHints());
    }

    /**
     * Requirement 4.1.4
     *
     * The evaluation context MUST be mutable only within the before hook.
     */
    public function testEvaluationContextIsMutableWithinBeforeHook(): void
    {
        $testRunner = $this;

        /** @var Mockery\MockInterface|Hook $mutationHook */
        $mutationHook = $this->mockery(TestHook::class)->makePartial();
        $mutationHook->shouldReceive('before')->andReturnUsing(function (HookContext $ctx) use ($testRunner) {
            $actualValue = $ctx->getEvaluationContext()->getAttributes()->get('newKey');
            $testRunner->assertEquals(null, $actualValue);

            return new EvaluationContext(null, new Attributes(['newKey' => 'newValue']));
        });

        $additionalEvaluationContext = (new HookExecutor())
                                ->beforeHooks(
                                    FlagValueType::BOOLEAN,
                                    HookContextFactory::from('flagKey', FlagValueType::BOOLEAN, false, new EvaluationContext(), new Metadata('client'), new Metadata('provider')),
                                    [$mutationHook],
                                    new HookHints(),
                                );

        $this->assertNotNull($additionalEvaluationContext);
        $this->assertEquals('newValue', $additionalEvaluationContext->getAttributes()->get('newKey'));
    }

    /**
     * Requirement 4.1.4
     *
     * The evaluation context MUST be mutable only within the before hook.
     */
    public function testEvaluationContextIsImmutableInAfterHooks(): void
    {
        $testRunner = $this;

        /** @var Mockery\MockInterface|Hook $mutationHook */
        $mutationHook = $this->mockery(TestHook::class)->makePartial();
        $mutationHook->shouldReceive('after')->andReturnUsing(function (HookContext $ctx) use ($testRunner) {
            $actualValue = $ctx->getEvaluationContext()->getAttributes()->get('newKey');
            $testRunner->assertEquals(null, $actualValue);

            return new EvaluationContext(null, new Attributes(['newKey' => 'newValue']));
        });

        // @phpstan-ignore-next-line
        $additionalEvaluationContext = (new HookExecutor())
                                ->afterHooks(
                                    FlagValueType::BOOLEAN,
                                    HookContextFactory::from('flagKey', FlagValueType::BOOLEAN, false, new EvaluationContext(), new Metadata('client'), new Metadata('provider')),
                                    ResolutionDetailsFactory::fromSuccess(true),
                                    [$mutationHook],
                                    new HookHints(),
                                );
        // @phpstan-ignore-next-line
        $this->assertNull($additionalEvaluationContext);
    }

    /**
     * Requirement 4.1.4
     *
     * The evaluation context MUST be mutable only within the before hook.
     */
    public function testEvaluationContextIsImmutableInErrorHooks(): void
    {
        $testRunner = $this;

        /** @var Mockery\MockInterface|Hook $mutationHook */
        $mutationHook = $this->mockery(TestHook::class)->makePartial();
        $mutationHook->shouldReceive('error')->andReturnUsing(function (HookContext $ctx) use ($testRunner) {
            $actualValue = $ctx->getEvaluationContext()->getAttributes()->get('newKey');
            $testRunner->assertEquals(null, $actualValue);

            return new EvaluationContext(null, new Attributes(['newKey' => 'newValue']));
        });

        //@phpstan-ignore-next-line
        $additionalEvaluationContext = (new HookExecutor())
                                ->errorHooks(
                                    FlagValueType::BOOLEAN,
                                    HookContextFactory::from('flagKey', FlagValueType::BOOLEAN, false, new EvaluationContext(), new Metadata('client'), new Metadata('provider')),
                                    new Exception('Error'),
                                    [$mutationHook],
                                    new HookHints(),
                                );

        //@phpstan-ignore-next-line
        $this->assertNull($additionalEvaluationContext);
    }

    /**
     * Requirement 4.1.4
     *
     * The evaluation context MUST be mutable only within the before hook.
     */
    public function testEvaluationContextIsImmutableInFinallyHooks(): void
    {
        $testRunner = $this;

        /** @var Mockery\MockInterface|Hook $mutationHook */
        $mutationHook = $this->mockery(TestHook::class)->makePartial();
        $mutationHook->shouldReceive('finally')->andReturnUsing(function (HookContext $ctx) use ($testRunner) {
            $actualValue = $ctx->getEvaluationContext()->getAttributes()->get('newKey');
            $testRunner->assertEquals(null, $actualValue);

            return new EvaluationContext(null, new Attributes(['newKey' => 'newValue']));
        });

        // @phpstan-ignore-next-line
        $additionalEvaluationContext = (new HookExecutor())
                                ->finallyHooks(
                                    FlagValueType::BOOLEAN,
                                    HookContextFactory::from('flagKey', FlagValueType::BOOLEAN, false, new EvaluationContext(), new Metadata('client'), new Metadata('provider')),
                                    [$mutationHook],
                                    new HookHints(),
                                );

        // @phpstan-ignore-next-line
        $this->assertNull($additionalEvaluationContext);
    }

    /**
     * Requirement 4.2.1
     *
     * hook hints MUST be a structure supports definition of arbitrary properties, with keys of type string, and values of type boolean | string | number | datetime | structure..
     */
    public function testHookHintsHasArbitraryProperties(): void
    {
        $propertyA = 'value a';
        $propertyB = 'value b';

        /** @var HookHintsInterface $hints */
        $hints = new HookHints([
            'a' => $propertyA,
            'b' => $propertyB,
        ]);

        $this->assertEquals($propertyA, $hints->get('a'));
        $this->assertEquals($propertyB, $hints->get('b'));
    }

    /**
     * Conditional Requirement 4.2.2.1
     *
     * Condition: Hook hints MUST be immutable.
     */
    public function testHookHintsIsImmutable(): void
    {
        /** @var HookHintsInterface $hints */
        $hints = new HookHints();

        $methodName = 'set';
        $key = 'newKey';
        $value = 'newValue';

        $this->assertNull($hints->get($key));

        try {
            $hints->{$methodName}($key, $value);

            $this->fail('It should not be possible to call `set` on HookHints');
        } catch (Throwable $err) {
            // no-op
        }

        $this->assertNull($hints->get($key));
    }

    /**
     * Conditional Requirement 4.2.2.2
     *
     * Condition: The client metadata field in the hook context MUST be immutable.
     */
    public function testClientMetadataInHookContextIsImmutable(): void
    {
        $testRunner = $this;

        $originalName = 'test-name';
        $clientMetadata = new Metadata($originalName);

        /** @var Mockery\MockInterface|Hook $hook */
        $hook = $this->mockery(TestHook::class)->makePartial();
        $hook->shouldReceive('before')->andReturnUsing(function (HookContext $ctx) use ($testRunner, $originalName) {
            $clientMetadata = $ctx->getClientMetadata();

            $testRunner->assertEquals($originalName, $clientMetadata->getName());
            $methodName = 'setName';
            $newName = 'new-name';
            try {
                $clientMetadata->{$methodName}($newName);

                $testRunner->fail('It should not be possible to call `set` on HookHints');
            } catch (Throwable $err) {
                // no-op
            }
            $testRunner->assertEquals($originalName, $clientMetadata->getName());
        });

        $hookContext = (new HookContextBuilder())->withClientMetadata($clientMetadata)->build();

        (new HookExecutor())->beforeHooks(FlagValueType::BOOLEAN, $hookContext, [], new HookHints());

        $this->assertEquals($originalName, $hookContext->getClientMetadata()->getName());
    }

    /**
     * Conditional Requirement 4.2.2.3
     *
     * Condition: The provider metadata field in the hook context MUST be immutable.
     */
    public function testProviderMetadataInHookContextIsImmutable(): void
    {
        $testRunner = $this;

        $originalName = 'test-name';
        $providerMetadata = new Metadata($originalName);

        /** @var Mockery\MockInterface|Hook $hook */
        $hook = $this->mockery(TestHook::class)->makePartial();
        $hook->shouldReceive('before')->andReturnUsing(function (HookContext $ctx) use ($testRunner, $originalName) {
            $providerMetadata = $ctx->getProviderMetadata();

            $testRunner->assertEquals($originalName, $providerMetadata->getName());
            $methodName = 'setName';
            $newName = 'new-name';
            try {
                $providerMetadata->{$methodName}($newName);

                $testRunner->fail('It should not be possible to call `set` on HookHints');
            } catch (Throwable $err) {
                // no-op
            }
            $testRunner->assertEquals($originalName, $providerMetadata->getName());
        });

        $hookContext = (new HookContextBuilder())->withProviderMetadata($providerMetadata)->build();

        (new HookExecutor())->beforeHooks(FlagValueType::BOOLEAN, $hookContext, [], new HookHints());

        $this->assertEquals($originalName, $hookContext->getProviderMetadata()->getName());
    }

    /**
     * Requirement 4.3.1
     *
     * Hooks MUST specify at least one stage.
     */
    public function testHooksMustSpecifyAtLeastOneStage(): void
    {
        /** @var Hook $testHook */
        $testHook = new TestHook();

        $this->assertInstanceOf(Hook::class, $testHook);
        $this->assertTrue(method_exists($testHook, 'before'));
        $this->assertTrue(method_exists($testHook, 'after'));
        $this->assertTrue(method_exists($testHook, 'error'));
        $this->assertTrue(method_exists($testHook, 'finally'));
    }

    /**
     * Requirement 4.5.1
     *
     * Flag evaluation options MAY contain hook hints, a map of data to be provided to hook invocations.
     */
    public function testEvaluationOptionsMayContainHookHints(): void
    {
        $hookHints = new HookHints(['key' => 'value']);

        $evaluationOptions = new EvaluationOptions([], $hookHints);

        $actualHookHints = $evaluationOptions->getHookHints();

        $this->assertEquals($hookHints, $actualHookHints);
    }

    /**
     * Requirement 4.5.2
     *
     * hook hints MUST be passed to each hook.
     */
    public function testHookHintsMustBePassedToEachHook(): void
    {
        $testRunner = $this;

        $expectedHookHints = new HookHints([
            'key' => 'value',
        ]);

        /** @var Mockery\MockInterface|Hook $hook */
        $hook = $this->mockery(TestHook::class)->makePartial();
        $hook->shouldReceive('before')->andReturnUsing(function ($ctx, $hints) use ($testRunner, $expectedHookHints) {
            $testRunner->assertInstanceOf(HookHintsInterface::class, $hints);
            $testRunner->assertEquals($expectedHookHints, $hints);
        });
        $hook->shouldReceive('after')->andReturnUsing(function ($ctx, $details, $hints) use ($testRunner, $expectedHookHints) {
            $testRunner->assertInstanceOf(HookHintsInterface::class, $hints);
            $testRunner->assertEquals($expectedHookHints, $hints);
        });
        $hook->shouldReceive('error')->andReturnUsing(function ($ctx, $err, $hints) use ($testRunner, $expectedHookHints) {
            $testRunner->assertInstanceOf(HookHintsInterface::class, $hints);
            $testRunner->assertEquals($expectedHookHints, $hints);
        });
        $hook->shouldReceive('finally')->andReturnUsing(function ($ctx, $hints) use ($testRunner, $expectedHookHints) {
            $testRunner->assertInstanceOf(HookHintsInterface::class, $hints);
            $testRunner->assertEquals($expectedHookHints, $hints);
        });

        $api = APITestHelper::new();

        $client = new OpenFeatureClient($api, 'test-name', 'test-version');

        $client->getBooleanValue('flagKey', false, null, new EvaluationOptions([$hook], $expectedHookHints));
    }

    /**
     * Requirement 4.5.3
     *
     * The hook MUST NOT alter the hook hints structure.
     */
    public function testHooksMustNotAlterHookHints(): void
    {
        $testRunner = $this;

        $expectedHookHints = new HookHints([
            'key' => 'value',
        ]);

        /** @var Mockery\MockInterface|Hook $hook */
        $hook = $this->mockery(TestHook::class)->makePartial();
        $hook->shouldReceive('before')->andReturnUsing(function (HookContext $ctx, HookHintsInterface $hints) use ($testRunner, $expectedHookHints) {
            $this->assertEquals($expectedHookHints, $hints);

            $testRunner->assertEquals('value', $hints->get('key'));
            $methodName = 'set';
            $newValue = 'newValue';
            try {
                $hints->{$methodName}($newValue);

                $testRunner->fail('It should not be possible to call `set` on HookHints');
            } catch (Throwable $err) {
                // no-op
            }
            $testRunner->assertEquals('value', $hints->get('key'));
        });

        $hookContext = new ImmutableHookContext();

        (new HookExecutor())->beforeHooks(FlagValueType::BOOLEAN, $hookContext, [], $expectedHookHints);

        $this->assertEquals('value', $expectedHookHints->get('key'));
    }
}
