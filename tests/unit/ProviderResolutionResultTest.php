<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use DateTime;
use Exception;
use Mockery;
use Mockery\MockInterface;
use OpenFeature\Test\TestCase;
use OpenFeature\implementation\multiprovider\ProviderResolutionResult;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;

class ProviderResolutionResultTest extends TestCase
{
    /** @var Provider&MockInterface */
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = Mockery::mock(Provider::class);
        $this->provider->shouldReceive('getMetadata->getName')->andReturn('TestProvider');
    }

    /**
     * @param bool|string|int|float|DateTime|array<mixed>|null $value
     */
    private function details(bool | string | int | float | DateTime | array | null $value): ResolutionDetails
    {
        return (new ResolutionDetailsBuilder())->withValue($value)->build();
    }

    /**
     * @param bool|string|int|float|DateTime|array<mixed>|null $value
     * @param array<string,bool|string|int>|null $metadata
     */
    private function detailsWithMetadata(bool | string | int | float | DateTime | array | null $value, ?array $metadata): ResolutionDetails
    {
        return (new ResolutionDetailsBuilder())->withValue($value)->withMetadata($metadata)->build();
    }

    public function testSuccessfulResult(): void
    {
        $details = $this->details(true);
        $result = new ProviderResolutionResult('TestProvider', $this->provider, $details, null);

        $this->assertSame('TestProvider', $result->getProviderName());
        $this->assertSame($this->provider, $result->getProvider());
        $this->assertSame($details, $result->getDetails());
        $this->assertNull($result->getError());
        $this->assertFalse($result->hasError());
        $this->assertTrue($result->isSuccessful());
    }

    public function testErrorResult(): void
    {
        $error = new Exception('failure');
        $result = new ProviderResolutionResult('TestProvider', $this->provider, null, $error);

        $this->assertSame('TestProvider', $result->getProviderName());
        $this->assertNull($result->getDetails());
        $this->assertSame($error, $result->getError());
        $this->assertTrue($result->hasError());
        $this->assertFalse($result->isSuccessful());
    }

    public function testEmptyResultNeitherSuccessNorError(): void
    {
        $result = new ProviderResolutionResult('TestProvider', $this->provider, null, null);

        $this->assertNull($result->getDetails());
        $this->assertNull($result->getError());
        $this->assertFalse($result->hasError());
        $this->assertFalse($result->isSuccessful());
    }

    public function testResultWithNotEmptyMetadata(): void
    {
        $details = $this->detailsWithMetadata(true, [
            'test_bool' => true,
            'test_int' => 10,
            'test_string' => 'OK',
        ]);
        $result = new ProviderResolutionResult('TestProvider', $this->provider, null, null);

        $this->assertNull($result->getDetails());
        $this->assertNull($result->getError());
        $this->assertFalse($result->hasError());
        $this->assertFalse($result->isSuccessful());
        $this->assertIsArray($details->getMetadata());
        $this->assertArrayHasKey('test_bool', $details->getMetadata());
        $this->assertArrayHasKey('test_int', $details->getMetadata());
        $this->assertArrayHasKey('test_string', $details->getMetadata());
        $this->assertEquals(true, $details->getMetadata()['test_bool']);
        $this->assertEquals('OK', $details->getMetadata()['test_string']);
        $this->assertEquals(10, $details->getMetadata()['test_int']);
    }

    public function testResultWithEmptyMetadata(): void
    {
        $details = $this->detailsWithMetadata(true, null);
        $result = new ProviderResolutionResult('TestProvider', $this->provider, null, null);

        $this->assertNull($result->getDetails());
        $this->assertNull($result->getError());
        $this->assertFalse($result->hasError());
        $this->assertFalse($result->isSuccessful());
        $this->assertNull($details->getMetadata());
        $this->assertIsNotArray($details->getMetadata());
    }
}
