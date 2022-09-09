<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use DateTime;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;
use OpenFeature\interfaces\hooks\HookContext;

class HookContextFactory
{
    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $defaultValue
     */
    public static function from(
        string $flagKey,
        string $type,
        $defaultValue,
        ?EvaluationContextInterface $evaluationContext,
        Metadata $clientMetadata,
        Metadata $providerMetadata
    ): HookContext {
        $builder = new HookContextBuilder();

        return $builder
        ->withFlagKey($flagKey)
        ->withType($type)
        ->withDefaultValue($defaultValue)
        ->withEvaluationContext($evaluationContext ?? EvaluationContext::createNull())
        ->withClientMetadata($clientMetadata)
        ->withProviderMetadata($providerMetadata)
        ->build();
    }
}
