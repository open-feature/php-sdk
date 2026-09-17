<?php

declare(strict_types=1);

namespace OpenFeature\Test;

use OpenFeature\OpenFeatureAPI;
use OpenFeature\interfaces\flags\ProviderLifecycleAPI;

class APITestHelper
{
    public static function createAPI(): ProviderLifecycleAPI
    {
        return OpenFeatureAPI::getInstance();
    }

    public static function resetAPI(): void
    {
        $api = self::createAPI();

        $api->shutdown();
    }

    public static function new(): ProviderLifecycleAPI
    {
        $api = self::createAPI();

        self::resetAPI();

        return $api;
    }
}
