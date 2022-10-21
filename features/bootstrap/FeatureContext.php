<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use OpenFeature\OpenFeatureAPI;
use OpenFeature\OpenFeatureClient;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private OpenFeatureClient $client;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->client = OpenFeatureAPI::getInstance()->getClient('features', '1.0');
    }

    /**
     * @When a boolean flag with key :arg1 is evaluated with default value :arg2
     */
    public function aBooleanFlagWithKeyIsEvaluatedWithDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved boolean value should be :arg1
     */
    public function theResolvedBooleanValueShouldBe($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When a string flag with key :arg1 is evaluated with default value :arg2
     */
    public function aStringFlagWithKeyIsEvaluatedWithDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved string value should be :arg1
     */
    public function theResolvedStringValueShouldBe($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When an integer flag with key :arg1 is evaluated with default value :arg2
     */
    public function anIntegerFlagWithKeyIsEvaluatedWithDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved integer value should be :arg1
     */
    public function theResolvedIntegerValueShouldBe($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When a float flag with key :arg1 is evaluated with default value :arg2
     */
    public function aFloatFlagWithKeyIsEvaluatedWithDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved float value should be :arg1
     */
    public function theResolvedFloatValueShouldBe($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When an object flag with key :arg1 is evaluated with a null default value
     */
    public function anObjectFlagWithKeyIsEvaluatedWithANullDefaultValue($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved object value should be contain fields :arg1, :arg2, and :arg3, with values :arg4, :arg5 and 100, respectively
     */
    public function theResolvedObjectValueShouldBeContainFieldsAndWithValuesAndRespectively($arg1, $arg2, $arg3, $arg4, $arg5)
    {
        throw new PendingException();
    }

    /**
     * @When a boolean flag with key :arg1 is evaluated with details and default value :arg2
     */
    public function aBooleanFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved boolean details value should be :arg1, the variant should be :arg2, and the reason should be :arg3
     */
    public function theResolvedBooleanDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe($arg1, $arg2, $arg3)
    {
        throw new PendingException();
    }

    /**
     * @When a string flag with key :arg1 is evaluated with details and default value :arg2
     */
    public function aStringFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved string details value should be :arg1, the variant should be :arg2, and the reason should be :arg3
     */
    public function theResolvedStringDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe($arg1, $arg2, $arg3)
    {
        throw new PendingException();
    }

    /**
     * @When an integer flag with key :arg1 is evaluated with details and default value :arg2
     */
    public function anIntegerFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved integer details value should be 10, the variant should be :arg1, and the reason should be :arg2
     */
    public function theResolvedIntegerDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @When a float flag with key :arg1 is evaluated with details and default value :arg2
     */
    public function aFloatFlagWithKeyIsEvaluatedWithDetailsAndDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved float details value should be 0.5, the variant should be :arg1, and the reason should be :arg2
     */
    public function theResolvedFloatDetailsValueShouldBeTheVariantShouldBeAndTheReasonShouldBe($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @When an object flag with key :arg1 is evaluated with details and a null default value
     */
    public function anObjectFlagWithKeyIsEvaluatedWithDetailsAndANullDefaultValue($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved object details value should be contain fields :arg1, :arg2, and :arg3, with values :arg4, :arg5 and 100, respectively
     */
    public function theResolvedObjectDetailsValueShouldBeContainFieldsAndWithValuesAndRespectively($arg1, $arg2, $arg3, $arg4, $arg5)
    {
        throw new PendingException();
    }

    /**
     * @Then the variant should be :arg1, and the reason should be :arg2
     */
    public function theVariantShouldBeAndTheReasonShouldBe($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @When context contains keys :arg1, :arg2, :arg3, :arg4 with values :arg5, :arg6, 29, :arg7
     */
    public function contextContainsKeysWithValues($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7)
    {
        throw new PendingException();
    }

    /**
     * @When a flag with key :arg1 is evaluated with default value :arg2
     */
    public function aFlagWithKeyIsEvaluatedWithDefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved string response should be :arg1
     */
    public function theResolvedStringResponseShouldBe($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the resolved flag value is :arg1 when the context is empty
     */
    public function theResolvedFlagValueIsWhenTheContextIsEmpty($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When a non-existent string flag with key :arg1 is evaluated with details and a default value :arg2
     */
    public function aNonExistentStringFlagWithKeyIsEvaluatedWithDetailsAndADefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then then the default string value should be returned
     */
    public function thenTheDefaultStringValueShouldBeReturned()
    {
        throw new PendingException();
    }

    /**
     * @Then the reason should indicate an error and the error code should indicate a missing flag with :arg1
     */
    public function theReasonShouldIndicateAnErrorAndTheErrorCodeShouldIndicateAMissingFlagWith($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When a string flag with key :arg1 is evaluated as an integer, with details and a default value :arg2
     */
    public function aStringFlagWithKeyIsEvaluatedAsAnIntegerWithDetailsAndADefaultValue($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then then the default integer value should be returned
     */
    public function thenTheDefaultIntegerValueShouldBeReturned()
    {
        throw new PendingException();
    }

    /**
     * @Then the reason should indicate an error and the error code should indicate a type mismatch with :arg1
     */
    public function theReasonShouldIndicateAnErrorAndTheErrorCodeShouldIndicateATypeMismatchWith($arg1)
    {
        throw new PendingException();
    }
}
