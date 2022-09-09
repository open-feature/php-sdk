<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

use DateTime;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext;

interface MutableHookContext
{
    public function setFlagKey(string $flagKey): void;

    public function setType(string $type): void;

    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $defaultValue
     */
    public function setDefaultValue($defaultValue): void;

    public function setEvaluationContext(EvaluationContext $evaluationContext): void;

    public function setClientMetadata(Metadata $clientMetadata): void;

    public function setProviderMetadata(Metadata $providerMetadata): void;
}
