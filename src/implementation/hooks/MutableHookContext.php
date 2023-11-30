<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use DateTime;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\HookContext as HookContextInterface;
use OpenFeature\interfaces\hooks\MutableHookContext as MutableHookContextInterface;

class MutableHookContext extends ImmutableHookContext implements HookContextInterface, MutableHookContextInterface
{
    public function setFlagKey(string $flagKey): void
    {
        $this->flagKey = $flagKey;
    }

    public function setType(FlagValueType $type): void
    {
        $this->type = $type;
    }

    public function setDefaultValue(bool | string | int | float | DateTime | array | null $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function setEvaluationContext(EvaluationContext $evaluationContext): void
    {
        $this->evaluationContext = $evaluationContext;
    }

    public function setClientMetadata(Metadata $clientMetadata): void
    {
        $this->clientMetadata = $clientMetadata;
    }

    public function setProviderMetadata(Metadata $providerMetadata): void
    {
        $this->providerMetadata = $providerMetadata;
    }
}
