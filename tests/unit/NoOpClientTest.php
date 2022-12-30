<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use OpenFeature\Test\TestCase;
use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\flags\EvaluationDetailsBuilder;
use OpenFeature\implementation\flags\NoOpClient;

class NoOpClientTest extends TestCase
{
    private NoOpClient $client;
    private const TEST_KEY = 'test-key';

    protected function setUp(): void
    {
        $this->client = new NoOpClient();
    }

    public function testGetBooleanValue(): void
    {
        $booleanValue = true;

        $expectedValue = $booleanValue;

        $actualValue = $this->client->getBooleanValue(self::TEST_KEY, $booleanValue);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetBooleanDetails(): void
    {
        $booleanDetails = true;

        $expectedValue = (new EvaluationDetailsBuilder())->withValue($booleanDetails)->withFlagKey(self::TEST_KEY)->build();

        $actualValue = $this->client->getBooleanDetails(self::TEST_KEY, $booleanDetails);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetStringValue(): void
    {
        $stringValue = 'test-string-value';

        $expectedValue = $stringValue;

        $actualValue = $this->client->getStringValue(self::TEST_KEY, $stringValue);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetStringDetails(): void
    {
        $stringDetails = 'test-string-value';

        $expectedValue = (new EvaluationDetailsBuilder())->withValue($stringDetails)->withFlagKey(self::TEST_KEY)->build();

        $actualValue = $this->client->getStringDetails(self::TEST_KEY, $stringDetails);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetIntegerValue(): void
    {
        $integerValue = 42;

        $expectedValue = $integerValue;

        $actualValue = $this->client->getIntegerValue(self::TEST_KEY, $integerValue);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetIntegerDetails(): void
    {
        $integerDetails = 42;

        $expectedValue = (new EvaluationDetailsBuilder())->withValue($integerDetails)->withFlagKey(self::TEST_KEY)->build();

        $actualValue = $this->client->getIntegerDetails(self::TEST_KEY, $integerDetails);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetFloatValue(): void
    {
        $floatValue = 3.14;

        $expectedValue = $floatValue;

        $actualValue = $this->client->getFloatValue(self::TEST_KEY, $floatValue);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetFloatDetails(): void
    {
        $floatDetails = 3.14;

        $expectedValue = (new EvaluationDetailsBuilder())->withValue($floatDetails)->withFlagKey(self::TEST_KEY)->build();

        $actualValue = $this->client->getFloatDetails(self::TEST_KEY, $floatDetails);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetObjectValue(): void
    {
        $objectValue = ['key' => 'value'];

        $expectedValue = $objectValue;

        $actualValue = $this->client->getObjectValue(self::TEST_KEY, $objectValue);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetObjectDetails(): void
    {
        $objectDetails = ['key' => 'value'];

        $expectedValue = (new EvaluationDetailsBuilder())->withValue($objectDetails)->withFlagKey(self::TEST_KEY)->build();

        $actualValue = $this->client->getObjectDetails(self::TEST_KEY, $objectDetails);

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetMetadata(): void
    {
        $expectedValue = new Metadata('no-op-client');

        $actualValue = $this->client->getMetadata();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetEvaluationContext(): void
    {
        $expectedValue = new EvaluationContext('no-op-targeting-key');

        $actualValue = $this->client->getEvaluationContext();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testGetHooks(): void
    {
        $expectedValue = [];

        $actualValue = $this->client->getHooks();

        $this->assertEquals($expectedValue, $actualValue);
    }
}
