<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use DateTime;
use Mockery;
use Mockery\MockInterface;
use OpenFeature\Test\TestCase;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;

class ResolutionDetailsMetadataTest extends TestCase
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
     * @param array<string,bool|string|int> $metadata
     */
    private function detailsWithMetadata(bool | string | int | float | DateTime | array | null $value, array $metadata): ResolutionDetails
    {
        return (new ResolutionDetailsBuilder())->withValue($value)->withMetadata($metadata)->build();
    }

    public function testResolutionDetailsWithoutMetadata(): void
    {
        $details = $this->details(true);
        $this->assertNull($details->getMetadata());
    }

    public function testResolutionDetailsWithEmptyMetadata(): void
    {
        $details = $this->detailsWithMetadata(true, []);
        $metadata = $details->getMetadata();
        $this->assertNotNull($metadata);
        $this->assertIsArray($metadata);
        $this->assertEmpty($metadata);
    }

    public function testResolutionDetailsWithNotEmptyMetadata(): void
    {
        $details = $this->detailsWithMetadata(true, [
            'bool_value' => true,
            'string_value' => 'OK',
        ]);
        $metadata = $details->getMetadata();
        $this->assertNotNull($metadata);
        $this->assertIsArray($metadata);
        $this->assertNotEmpty($metadata);
        $this->assertArrayHasKey('bool_value', $metadata);
        $this->assertArrayHasKey('string_value', $metadata);
        $this->assertEquals(true, $metadata['bool_value']);
        $this->assertEquals('OK', $metadata['string_value']);
    }

    public function testResolutionDetailsMetadataImmutability(): void
    {
        $details = $this->detailsWithMetadata(true, [
            'bool_value' => true,
            'string_value' => 'OK',
        ]);
        $metadata = $details->getMetadata();
        // let's add a new key/value and let's change the value of "bool_value"
        $metadata['number_value'] = 7;
        $metadata['bool_value'] = false;
        // get again the metadata
        $newMetadata = $details->getMetadata();

        $this->assertNotSame($metadata, $newMetadata);
        $this->assertIsArray($newMetadata);
        $this->assertArrayNotHasKey('number_value', $newMetadata);
        $this->assertArrayHasKey('bool_value', $newMetadata);
        $this->assertNotEquals(false, $newMetadata['bool_value']);
        $this->assertNotSameSize($metadata, $newMetadata);
    }
}
