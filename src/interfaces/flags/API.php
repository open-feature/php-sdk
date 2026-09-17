<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\events\ProviderStatusAccessor;
use OpenFeature\interfaces\hooks\HooksAdder;
use OpenFeature\interfaces\hooks\HooksGetter;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ProviderAware;
use Psr\Log\LoggerAwareInterface;

interface API extends EvaluationContextAware, HooksAdder, HooksGetter, LoggerAwareInterface, ProviderAware, ProviderStatusAccessor
{
    public function getProviderMetadata(): Metadata;

    public function getClient(?string $name, ?string $version): Client;

    public function clearHooks(): void;

    public function setProviderAndWait(Provider $provider): void;

    public function shutdown(): void;
}
