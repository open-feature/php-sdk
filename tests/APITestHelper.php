<?php

declare(strict_types=1);

namespace OpenFeature\Test;

use OpenFeature\OpenFeatureAPI;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\provider\NoOpProvider;
use OpenFeature\interfaces\flags\API;

class APITestHelper
{
    public static function createAPI(): API
    {
        return OpenFeatureAPI::getInstance();
    }

    public static function resetAPI(): void
    {
        $api = self::createAPI();

        $api->setProvider(new NoOpProvider());
        $api->clearHooks();
        $api->setEvaluationContext(new EvaluationContext());
    }

    public static function new(): API
    {
        $api = self::createAPI();

        self::resetAPI();

        return $api;
    }
}
