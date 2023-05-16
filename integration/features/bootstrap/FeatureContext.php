<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

declare(strict_types=1);

use Behat\Behat\Context\Context as BehatContext;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenFeature\OpenFeatureAPI;
use OpenFeature\OpenFeatureClient;
use OpenFeature\Providers\Flagd\FlagdProvider;
use OpenFeature\Providers\Flagd\config\HttpConfig;
use OpenFeature\implementation\flags\Attributes;
use OpenFeature\implementation\flags\MutableEvaluationContext;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\EvaluationDetails;
use OpenFeature\interfaces\flags\EvaluationOptions;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\provider\ErrorCode;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 *
 * Future Enhancement: https://github.com/open-feature/php-sdk/issues/4
 */
class FeatureContext implements BehatContext
{
    private OpenFeatureClient $client;

    private string $flagType;
    private string $inputFlagKey;
    private mixed $inputFlagDefaultValue;
    private ?EvaluationContext $inputContext = null;
    private ?EvaluationOptions $inputOptions = null;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $api = OpenFeatureAPI::getInstance();

        $client = new Client();
        $httpFactory = new HttpFactory();

        // @todo: set provider to flagd
        $provider = new FlagdProvider(
            [
                'hostname' => 'localhost',
                'port' => 8013,
                'protocol' => 'http',
                'secure' => false,
                'httpConfig' => new HttpConfig(
                    $client,
                    $httpFactory,
                    $httpFactory,
                ),
            ],
        );

        $api->setProvider($provider);

        $this->client = $api->getClient('features', '1.0');
    }

    /**
     * @Given a provider is registered with cache disabled
     */
    public function aProviderIsRegisteredWithCacheDisabled()
    {
        // TODO: we disable cache by default already. Will update once we implement caching.
    }

    /**
     * @When a boolean flag with key :flagKey is evaluated with default value :defaultValue
     */
    public function aBooleanFlagWithKeyIsEvaluatedWithDefaultValue(string $flagKey, bool $defaultValue)
    {
        $this->flagType = FlagValueType::BOOLEAN;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved boolean value should be :resolvedValue
     */
    public function theResolvedBooleanValueShouldBe(bool $resolvedValue)
    {
        Assert::assertEquals(
            $resolvedValue,
            $this->calculateValue(),
        );
    }

    /**
     * @When a string flag with key :flagKey is evaluated with default value :defaultValue
     */
    public function aStringFlagWithKeyIsEvaluatedWithDefaultValue(string $flagKey, string $defaultValue)
    {
        $this->flagType = FlagValueType::STRING;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved string value should be :resolvedValue
     */
    public function theResolvedStringValueShouldBe(string $resolvedValue)
    {
        Assert::assertEquals(
            $resolvedValue,
            $this->calculateValue(),
        );
    }

    /**
     * @When an integer flag with key :flagKey is evaluated with default value :defaultValue
     */
    public function anIntegerFlagWithKeyIsEvaluatedWithDefaultValue(string $flagKey, int $defaultValue)
    {
        $this->flagType = FlagValueType::INTEGER;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
        print_r("Setting integer...\n");
    }

    /**
     * @Then the resolved integer value should be :resolvedValue
     */
    public function theResolvedIntegerValueShouldBe(int $resolvedValue)
    {
        Assert::assertEquals(
            $resolvedValue,
            $this->calculateValue(),
        );
    }

    /**
     * @When a float flag with key :flagKey is evaluated with default value :defaultValue
     */
    public function aFloatFlagWithKeyIsEvaluatedWithDefaultValue(string $flagKey, float $defaultValue)
    {
        $this->flagType = FlagValueType::FLOAT;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved float value should be :resolvedValue
     */
    public function theResolvedFloatValueShouldBe(float $resolvedValue)
    {
        Assert::assertEquals(
            $resolvedValue,
            $this->calculateValue(),
        );
    }

    /**
     * @When an object flag with key :flagKey is evaluated with a :defaultValue default value
     */
    public function anObjectFlagWithKeyIsEvaluatedWithANullDefaultValue(string $flagKey, mixed $defaultValue)
    {
        $this->flagType = FlagValueType::OBJECT;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved object value should be contain fields :key1, :key2, and :key3, with values :value1, :value2 and :value3, respectively
     */
    public function theResolvedObjectValueShouldBeContainFieldsAndWithValuesAndRespectively(string $key1, string $key2, string $key3, mixed $value1, mixed $value2, mixed $value3)
    {
        Assert::assertEquals(
            [
                $key1 => $value1,
                $key2 => $value2,
                $key3 => $value3,
            ],
            $this->calculateValue(),
        );
    }

    /**
     * @When a boolean flag with key :flagKey is evaluated with details and default value :defaultValue
     */
    public function aBooleanFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue(string $flagKey, bool $defaultValue)
    {
        $this->flagType = FlagValueType::BOOLEAN;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved boolean details value should be :value, the variant should be :variant, and the reason should be :reason
     */
    public function theResolvedBooleanDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe(bool $value, string $variant, string $reason)
    {
        $details = $this->calculateDetails();

        Assert::assertEquals($value, $details->getValue());
        Assert::assertEquals($variant, $details->getVariant());
        Assert::assertEquals($reason, $details->getReason());
    }

    /**
     * @When a string flag with key :flagKey is evaluated with details and default value :defaultValue
     */
    public function aStringFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue(string $flagKey, string $defaultValue)
    {
        $this->flagType = FlagValueType::STRING;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved string details value should be :value, the variant should be :variant, and the reason should be :reason
     */
    public function theResolvedStringDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe(string $value, string $variant, string $reason)
    {
        $details = $this->calculateDetails();

        Assert::assertEquals($value, $details->getValue());
        Assert::assertEquals($variant, $details->getVariant());
        Assert::assertEquals($reason, $details->getReason());
    }

    /**
     * @When an integer flag with key :flagKey is evaluated with details and default value :defaultValue
     */
    public function anIntegerFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue(string $flagKey, int $defaultValue)
    {
        $this->flagType = FlagValueType::INTEGER;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved integer details value should be :value, the variant should be :variant, and the reason should be :reason
     */
    public function theResolvedIntegerDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe(int $value, string $variant, string $reason)
    {
        $details = $this->calculateDetails();

        Assert::assertEquals($value, $details->getValue());
        Assert::assertEquals($variant, $details->getVariant());
        Assert::assertEquals($reason, $details->getReason());
    }

    /**
     * @When a float flag with key :flagKey is evaluated with details and default value :defaultValue
     */
    public function aFloatFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue(string $flagKey, float $defaultValue)
    {
        $this->flagType = FlagValueType::FLOAT;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved float details value should be :value, the variant should be :variant, and the reason should be :reason
     */
    public function theResolvedFloatDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe(float $value, string $variant, string $reason)
    {
        $details = $this->calculateDetails();

        Assert::assertEquals($value, $details->getValue());
        Assert::assertEquals($variant, $details->getVariant());
        Assert::assertEquals($reason, $details->getReason());
    }

    /**
     * @When an object flag with key :flagKey is evaluated with details and a :defaultValue default value
     */
    public function anObjectFlagWithKeyIsEvaluatedWithDetailsAndANullDefaultValue(string $flagKey, mixed $defaultValue)
    {
        $this->flagType = FlagValueType::OBJECT;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the resolved object details value should be contain fields :key1, :key2, and :key3, with values :value1, :value2 and :value3, respectively
     */
    public function theResolvedObjectDetailsValueShouldBeContainFieldsAndWithValuesAndRespectively(string $key1, string $key2, string $key3, mixed $value1, mixed $value2, mixed $value3)
    {
        $details = $this->calculateDetails();

        Assert::assertEquals([
            $key1 => $value1,
            $key2 => $value2,
            $key3 => $value3,
        ], $details->getValue());
    }

    /**
     * @Then the variant should be :variant, and the reason should be :reason
     */
    public function theVariantShouldBeAndTheReasonShouldBe(string $variant, string $reason)
    {
        $details = $this->calculateDetails();

        Assert::assertEquals($variant, $details->getVariant());
        Assert::assertEquals($reason, $details->getReason());
    }

    /**
     * @When context contains keys :key1, :key2, :key3, :key4 with values :value1, :value2, :value3, :value4
     */
    public function contextContainsKeysWithValues(string $key1, string $key2, string $key3, string $key4, mixed $value1, mixed $value2, mixed $value3, mixed $value4)
    {
        if ($this->isBooleanLikeString($value1)) {
            $value1 = $this->stringAsBool($value1);
        }

        if ($this->isBooleanLikeString($value2)) {
            $value2 = $this->stringAsBool($value2);
        }

        if ($this->isBooleanLikeString($value3)) {
            $value3 = $this->stringAsBool($value3);
        }

        if ($this->isBooleanLikeString($value4)) {
            $value4 = $this->stringAsBool($value4);
        }

        $this->inputContext = (new MutableEvaluationContext(null, new Attributes([
            $key1 => $value1,
            $key2 => $value2,
            $key3 => $value3,
            $key4 => $value4,
        ])));
    }

    /**
     * @When a flag with key :flagKey is evaluated with default value :defaultValue
     */
    public function aFlagWithKeyIsEvaluatedWithDefaultValue(string $flagKey, mixed $defaultValue)
    {
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
        $this->setFlagTypeIfNullByValue($defaultValue);
    }

    /**
     * @Then the resolved string response should be :resolvedValue
     */
    public function theResolvedStringResponseShouldBe(string $resolvedValue)
    {
        Assert::assertEquals($resolvedValue, $this->calculateValue());
    }

    /**
     * @Then the resolved flag value is :value when the context is empty
     */
    public function theResolvedFlagValueIsWhenTheContextIsEmpty(mixed $value)
    {
        $this->inputContext = null;

        Assert::assertEquals(
            $value,
            $this->calculateValue(),
        );
    }

    /**
     * @When a non-existent string flag with key :flagKey is evaluated with details and a default value :defaultValue
     */
    public function aNonExistentStringFlagWithKeyIsEvaluatedWithDetailsAndADefaultValue(string $flagKey, string $defaultValue)
    {
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
        $this->setFlagTypeIfNullByValue($defaultValue);
    }

    /**
     * @Then the default string value should be returned
     */
    public function thenTheDefaultStringValueShouldBeReturned()
    {
        Assert::assertEquals(
            $this->inputFlagDefaultValue,
            $this->calculateValue(),
        );
    }

    /**
     * @Then the reason should indicate an error and the error code should indicate a missing flag with :errorCode
     */
    public function theReasonShouldIndicateAnErrorAndTheErrorCodeShouldIndicateAMissingFlagWith(string $errorCode)
    {
        $details = $this->calculateDetails();

        $error = $details->getError();

        Assert::assertNotNull($error);
        Assert::assertEquals($errorCode, (string) $error->getResolutionErrorCode());
    }

    /**
     * @When a string flag with key :flagKey is evaluated as an integer, with details and a default value :defaultValue
     */
    public function aStringFlagWithKeyIsEvaluatedAsAnIntegerWithDetailsAndADefaultValue(string $flagKey, int $defaultValue)
    {
        $this->flagType = FlagValueType::INTEGER;
        $this->inputFlagKey = $flagKey;
        $this->inputFlagDefaultValue = $defaultValue;
    }

    /**
     * @Then the default integer value should be returned
     */
    public function thenTheDefaultIntegerValueShouldBeReturned()
    {
        Assert::assertEquals(
            $this->inputFlagDefaultValue,
            $this->calculateValue(),
        );
    }

    /**
     * @Then the reason should indicate an error and the error code should indicate a type mismatch with :errorCode
     */
    public function theReasonShouldIndicateAnErrorAndTheErrorCodeShouldIndicateATypeMismatchWith(string $errorCode)
    {
        $details = $this->calculateDetails();

        $error = $details->getError();

        Assert::assertNotNull($error);
        Assert::assertEquals($error->getResolutionErrorCode(), ErrorCode::TYPE_MISMATCH());
    }

    /**
     * Ensures the value is only calculated once the first time this is called, memoizing its value
     *
     * @return mixed
     */
    private function calculateValue()
    {
        $value = null;
        switch ($this->flagType) {
            case FlagValueType::BOOLEAN:
                $value = $this->client->getBooleanValue($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::FLOAT:
                $value = $this->client->getFloatValue($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::INTEGER:
                $value = $this->client->getIntegerValue($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::OBJECT:
                $value = $this->client->getObjectValue($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::STRING:
                $value = $this->client->getStringValue($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
        }

        return $value;
    }

    /**
     * Ensures the details are only calculated once the first time this is called, memoizing its details
     */
    private function calculateDetails(): EvaluationDetails
    {
        $details = null;
        switch ($this->flagType) {
            case FlagValueType::BOOLEAN:
                $details = $this->client->getBooleanDetails($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::FLOAT:
                $details = $this->client->getFloatDetails($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::INTEGER:
                $details = $this->client->getIntegerDetails($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::OBJECT:
                $details = $this->client->getObjectDetails($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
            case FlagValueType::STRING:
                $details = $this->client->getStringDetails($this->inputFlagKey, $this->inputFlagDefaultValue, $this->inputContext, $this->inputOptions);

                break;
        }

        return $details;
    }

    private function setFlagTypeIfNullByValue(mixed $value): void
    {
        if (!isset($this->flagType)) {
            $flagType = $this->getFlagTypeOf($value);

            if (!is_null($flagType)) {
                $this->flagType = $flagType;
            }
        }
    }

    private function getFlagTypeOf(mixed $value): ?string
    {
        if (is_string($value)) {
            return FlagValueType::STRING;
        }

        if (is_array($value)) {
            return FlagValueType::OBJECT;
        }

        if (is_float($value)) {
            return FlagValueType::FLOAT;
        }

        if (is_int($value)) {
            return FlagValueType::INTEGER;
        }

        if (is_bool($value)) {
            return FlagValueType::BOOLEAN;
        }
    }

    private function isBooleanLikeString(mixed $value): bool
    {
        return $value === 'true' || $value === 'false';
    }

    private function stringAsBool(string $value): bool
    {
        return $value === 'true';
    }
}
