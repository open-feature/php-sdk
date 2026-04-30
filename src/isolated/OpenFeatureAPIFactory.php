<?php

declare(strict_types=1);

namespace OpenFeature\isolated;

use OpenFeature\OpenFeatureAPI;
use OpenFeature\interfaces\flags\API;

/**
 * Factory for creating isolated OpenFeature API instances.
 *
 * -----------------
 * Requirement 1.8.1
 * -----------------
 * The API MUST expose a factory function which creates and returns a new,
 * independent instance of the API.
 *
 * Each instance returned by this factory function maintains its own state,
 * including providers, evaluation context, hooks, and event handlers.
 * Instances created by the factory function do not share state with the
 * "default" global singleton or with each other.
 *
 * -----------------
 * Requirement 1.8.3
 * -----------------
 * The factory function for creating isolated instances SHOULD be housed in a
 * distinct module, import path, package, or namespace from the global
 * singleton API.
 *
 * @see https://openfeature.dev/specification/sections/flag-evaluation#18-isolated-api-instances
 */
final class OpenFeatureAPIFactory
{
    /**
     * Creates a new, independent API instance with fully isolated state.
     *
     * Usage:
     *   $api = OpenFeatureAPIFactory::createAPI();
     *   $api->setProvider(new MyProvider());
     *   $client = $api->getClient();
     */
    public static function createAPI(): API
    {
        return new OpenFeatureAPI();
    }
}
