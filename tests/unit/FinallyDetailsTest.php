<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use Exception;
use OpenFeature\Test\APITestHelper;
use OpenFeature\Test\TestCase;
use OpenFeature\Test\TestHook;
use OpenFeature\Test\TestProvider;
use OpenFeature\implementation\flags\EvaluationOptions;
use OpenFeature\implementation\hooks\HookHints;
use OpenFeature\implementation\provider\ResolutionDetailsFactory;

class FinallyDetailsTest extends TestCase
{
    /**
     * @dataProvider evaluationPaths
     */
    public function testFinallyReceivesReturnedEvaluationDetails(string $failureStage): void
    {
        $api = APITestHelper::new();
        $provider = $this->mockery(TestProvider::class)->makePartial();
        $hook = $this->mockery(TestHook::class)->makePartial();
        $hints = new HookHints(['request' => 'example']);
        $seen = [];

        $hook->shouldReceive('finally')->once()->andReturnUsing(
            static function (mixed ...$arguments) use (&$seen): void {
                $seen = $arguments;
            },
        );

        if ($failureStage === 'provider') {
            $provider->shouldReceive('resolveBooleanValue')->andThrow(new Exception('provider failed'));
        } else {
            $provider->shouldReceive('resolveBooleanValue')->andReturn(ResolutionDetailsFactory::fromSuccess(true));
        }

        if ($failureStage === 'before' || $failureStage === 'after') {
            $hook->shouldReceive($failureStage)->andThrow(new Exception($failureStage . ' failed'));
        }

        $api->setProvider($provider);
        $details = $api->getClient('finally-test', '1.0')->getBooleanDetails('flag-key', false, null, new EvaluationOptions([$hook], $hints));

        $this->assertCount(3, $seen);
        $this->assertSame($details, $seen[1]);
        $this->assertSame($hints, $seen[2]);
        $this->assertSame('flag-key', $details->getFlagKey());
        $this->assertSame($failureStage === '', $details->getValue());

        if ($failureStage === '') {
            $this->assertNull($details->getError());
        } else {
            $this->assertNotNull($details->getError());
            $this->assertSame($failureStage . ' failed', $details->getError()->getResolutionErrorMessage());
        }
    }

    /**
     * @return array<string,array{string}>
     */
    public function evaluationPaths(): array
    {
        return [
            'success' => [''],
            'provider error' => ['provider'],
            'before hook error' => ['before'],
            'after hook error' => ['after'],
        ];
    }
}
