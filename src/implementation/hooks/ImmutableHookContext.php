<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use DateTime;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HookContext as HookContextInterface;

class ImmutableHookContext extends AbstractHookContext implements HookContextInterface
{
    public function getFlagKey(): string
    {
        return $this->flagKey;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function getDefaultValue()
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
