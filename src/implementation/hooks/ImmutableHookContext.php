<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\HookContext as HookContextInterface;

class ImmutableHookContext extends AbstractHookContext implements HookContextInterface
{
    public function getFlagKey(): string
    {
        return $this->flagKey;
    }

    public function getType(): FlagValueType
    {
        return $this->type;
    }

    /**
     * @return bool|string|int|float|mixed[]|null
     */
    public function getDefaultValue(): bool | string | int | float | array | null
    {
        return $this->defaultValue;
    }

    public function getEvaluationContext(): EvaluationContext
    {
        return $this->evaluationContext;
    }

    public function getClientMetadata(): Metadata
    {
        return $this->clientMetadata;
    }

    public function getProviderMetadata(): Metadata
    {
        return $this->providerMetadata;
    }
}
