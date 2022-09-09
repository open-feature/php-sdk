<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use DateTime;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HookContext as HookContextInterface;
use OpenFeature\interfaces\hooks\MutableHookContext as MutableHookContextInterface;

class MutableHookContext extends ImmutableHookContext implements HookContextInterface, MutableHookContextInterface
{
    public function setFlagKey(string $flagKey): void
    {
        $this->flagKey = $flagKey;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $defaultValue
     */
    public function setDefaultValue($defaultValue): void
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
