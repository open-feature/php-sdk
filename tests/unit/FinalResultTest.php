<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use DateTime;
use Exception;
use OpenFeature\Test\TestCase;
use OpenFeature\implementation\multiprovider\FinalResult;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\interfaces\provider\ResolutionDetails;

class FinalResultTest extends TestCase
{
    /**
     * @param bool|string|int|float|DateTime|array<mixed>|null $value
     */
    private function details(bool | string | int | float | DateTime | array | null $value): ResolutionDetails
    {
        return (new ResolutionDetailsBuilder())->withValue($value)->build();
    }

    public function testSuccessfulResult(): void
    {
        $details = $this->details(true);
        $final = new FinalResult($details, 'ProviderA', null);

        $this->assertTrue($final->isSuccessful());
        $this->assertFalse($final->hasErrors());
        $this->assertSame($details, $final->getDetails());
        $this->assertEquals('ProviderA', $final->getProviderName());
        $this->assertNull($final->getErrors());
    }

    public function testResultWithErrors(): void
    {
        $errors = [
            ['providerName' => 'ProviderA', 'error' => new Exception('fail A')],
            ['providerName' => 'ProviderB', 'error' => new Exception('fail B')],
        ];
        $final = new FinalResult(null, null, $errors);

        $this->assertFalse($final->isSuccessful());
        $this->assertTrue($final->hasErrors());
        $this->assertNull($final->getDetails());
        $this->assertNull($final->getProviderName());
        $errors = $final->getErrors();
        $this->assertNotNull($errors);
        $this->assertIsArray($errors);
        $this->assertCount(2, $errors);
    }

    public function testEmptyErrorsArrayTreatedAsNoErrors(): void
    {
        $final = new FinalResult(null, null, []);
        $this->assertFalse($final->isSuccessful());
        $this->assertFalse($final->hasErrors());
        $this->assertSame([], $final->getErrors());
    }
}
