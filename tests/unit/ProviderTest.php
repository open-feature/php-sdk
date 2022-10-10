<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Mockery;
use OpenFeature\Test\TestCase;
use OpenFeature\Test\TestHook;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\implementation\provider\ResolutionDetailsFactory;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\Provider;

class ProviderTest extends TestCase
{
    /**
     * Requirement 2.1
     *
     * The provider interface MUST define a metadata member or accessor, containing a name field or accessor of type string, which identifies the provider implementation.
     */
    public function testProviderMustDefineMetadataAccessor(): void
    {
        $provider = new TestProvider();

        $providerName = $provider->getMetadata()->getName();

        $this->assertEquals('TestProvider', $providerName);
    }

    /**
     * Requirement 2.2
     *
     * The feature provider interface MUST define methods to resolve flag values, with parameters flag key (string, required), default value (boolean | number | string | structure, required) and evaluation context (optional), which returns a flag resolution structure.
     */
    public function testMustDefineMethodsToResolveFlagValues(): void
    {
        $provider = new TestProvider();

        $expectedValue = true;
        $expectedResolution = ResolutionDetailsFactory::fromSuccess($expectedValue);

        $actualResolution = $provider->resolveBooleanValue('flagKey', $expectedValue, null);

        $this->assertEquals($expectedResolution, $actualResolution);
    }

    /**
     * Conditional Requirement 2.3.1
     *
     * The feature provider interface MUST define methods for typed flag resolution, including boolean, numeric, string, and structure.
     */
    public function testMustDefineMethodsForTypedFlagResolutionForBooleans(): void
    {
        $provider = new TestProvider();

        $expectedValue = true;
        $expectedResolution = ResolutionDetailsFactory::fromSuccess($expectedValue);

        $actualResolution = $provider->resolveBooleanValue('flagKey', $expectedValue, null);

        $this->assertEquals($expectedResolution, $actualResolution);
    }

    /**
     * Conditional Requirement 2.3.1
     *
     * The feature provider interface MUST define methods for typed flag resolution, including boolean, numeric, string, and structure.
     */
    public function testMustDefineMethodsForTypedFlagResolutionForIntegers(): void
    {
        $provider = new TestProvider();

        $expectedValue = 42;
        $expectedResolution = ResolutionDetailsFactory::fromSuccess($expectedValue);

        $actualResolution = $provider->resolveIntegerValue('flagKey', $expectedValue, null);

        $this->assertEquals($expectedResolution, $actualResolution);
    }

    /**
     * Conditional Requirement 2.3.1
     *
     * The feature provider interface MUST define methods for typed flag resolution, including boolean, numeric, string, and structure.
     */
    public function testMustDefineMethodsForTypedFlagResolutionForFloats(): void
    {
        $provider = new TestProvider();

        $expectedValue = 3.14;
        $expectedResolution = ResolutionDetailsFactory::fromSuccess($expectedValue);

        $actualResolution = $provider->resolveFloatValue('flagKey', $expectedValue, null);

        $this->assertEquals($expectedResolution, $actualResolution);
    }

    /**
     * Conditional Requirement 2.3.1
     *
     * The feature provider interface MUST define methods for typed flag resolution, including boolean, numeric, string, and structure.
     */
    public function testMustDefineMethodsForTypedFlagResolutionForStrings(): void
    {
        $provider = new TestProvider();

        $expectedValue = 'a string';
        $expectedResolution = ResolutionDetailsFactory::fromSuccess($expectedValue);

        $actualResolution = $provider->resolveStringValue('flagKey', $expectedValue, null);

        $this->assertEquals($expectedResolution, $actualResolution);
    }

    /**
     * Conditional Requirement 2.3.1
     *
     * The feature provider interface MUST define methods for typed flag resolution, including boolean, numeric, string, and structure.
     */
    public function testMustDefineMethodsForTypedFlagResolutionForObjects(): void
    {
        $provider = new TestProvider();

        $expectedValue = ['property' => 'value'];
        $expectedResolution = ResolutionDetailsFactory::fromSuccess($expectedValue);

        $actualResolution = $provider->resolveObjectValue('flagKey', $expectedValue, null);

        $this->assertEquals($expectedResolution, $actualResolution);
    }

    /**
     * Requirement 2.4
     *
     * In cases of normal execution, the provider MUST populate the flag resolution structure's value field with the resolved flag value.
     *
     * Requirement 2.5
     *
     * In cases of normal execution, the provider SHOULD populate the flag resolution structure's variant field with a string identifier corresponding to the returned flag value.
     */
    public function testMustPopulateTheFlagResolutionStructuresValueFieldWithResolvedValue(): void
    {
        $expectedValue = true;
        $defaultValue = false;

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')->andReturn(ResolutionDetailsFactory::fromSuccess($expectedValue));

        $actualResolution = $mockProvider->resolveBooleanValue('flagKey', $defaultValue, null);

        $this->assertNotEquals($expectedValue, $defaultValue);
        $this->assertEquals($expectedValue, $actualResolution->getValue());
    }

    /**
     * Requirement 2.5
     *
     * In cases of normal execution, the provider SHOULD populate the flag resolution structure's variant field with a string identifier corresponding to the returned flag value.
     */
    public function testShouldPopulateVariantField(): void
    {
        $expectedVariant = 'variant-code';

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')
                     ->andReturn((new ResolutionDetailsBuilder())
                                    ->withValue(true)
                                    ->withReason('reason-code')
                                    ->withVariant($expectedVariant)
                                    ->build());

        $actualResolution = $mockProvider->resolveBooleanValue('flagKey', false, null);

        $this->assertEquals($expectedVariant, $actualResolution->getVariant());
    }

    /**
     * Requirement 2.6
     *
     * The provider SHOULD populate the flag resolution structure's reason field with a string indicating the semantic reason for the returned flag value.
     */
    public function testShouldPopulateReasonField(): void
    {
        $expectedReason = 'reason-code';

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')
                     ->andReturn((new ResolutionDetailsBuilder())
                                    ->withValue(true)
                                    ->withReason($expectedReason)
                                    ->withVariant('variant-code')
                                    ->build());

        $actualResolution = $mockProvider->resolveBooleanValue('flagKey', false, null);

        $this->assertEquals($expectedReason, $actualResolution->getReason());
    }

    /**
     * Requirement 2.7
     *
     * In cases of normal execution, the provider MUST NOT populate the flag resolution structure's error code field, or otherwise must populate it with a null or falsy value.
     */
    public function testMustNotPopulateErrorFieldInNormalExecution(): void
    {
        $expectedErrorCode = null;

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')
                     ->andReturn((new ResolutionDetailsBuilder())
                                    ->withValue(true)
                                    ->withReason('reason-code')
                                    ->withVariant('variant-code')
                                    ->build());

        $actualResolution = $mockProvider->resolveBooleanValue('flagKey', false, null);

        $this->assertEquals($expectedErrorCode, $actualResolution->getErrorCode());
    }

    /**
     * Requirement 2.8
     *
     * In cases of abnormal execution, the provider MUST indicate an error using the idioms of the implementation language, with an associated error code having possible values "PROVIDER_NOT_READY", "FLAG_NOT_FOUND", "PARSE_ERROR", "TYPE_MISMATCH", or "GENERAL".
     */
    public function testMustPopulateErrorFieldInAbnormalExecution(): void
    {
        $expectedErrorCode = ErrorCode::GENERAL();

        /** @var Mockery\MockInterface|Provider $mockProvider */
        $mockProvider = $this->mockery(TestProvider::class)->makePartial();
        $mockProvider->shouldReceive('resolveBooleanValue')
                     ->andReturn((new ResolutionDetailsBuilder())
                                    ->withErrorCode($expectedErrorCode)
                                    ->build());

        $actualResolution = $mockProvider->resolveBooleanValue('flagKey', false, null);

        $this->assertEquals($expectedErrorCode, $actualResolution->getErrorCode());
    }

    /**
     * Requirement 2.10
     *
     * The provider interface MUST define a provider hook mechanism which can be optionally implemented in order to add hook instances to the evaluation life-cycle.
     */
    public function testMustDefineProviderHookMechanism(): void
    {
        $provider = new TestProvider();

        $hooks = [new TestHook(), new TestHook(), new TestHook()];

        $provider->setHooks($hooks);

        $this->assertEquals($hooks, $provider->getHooks());
    }
}
