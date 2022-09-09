<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use DateTime;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HookContext as HookContextInterface;

class HookContextBuilder
{
    private MutableHookContext $hookContext;
    private bool $isImmutable = false;

    public function __construct()
    {
        $this->hookContext = new MutableHookContext();
    }

    public function withFlagKey(string $flagKey): self
    {
        $this->hookContext->setFlagKey($flagKey);

        return $this;
    }

    public function withType(string $type): self
    {
        $this->hookContext->setType($type);

        return $this;
    }

    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $defaultValue
     */
    public function withDefaultValue($defaultValue): self
    {
        $this->hookContext->setDefaultValue($defaultValue);

        return $this;
    }

    public function withEvaluationContext(EvaluationContext $evaluationContext): self
    {
        $this->hookContext->setEvaluationContext($evaluationContext);

        return $this;
    }

    public function withClientMetadata(Metadata $clientMetadata): self
    {
        $this->hookContext->setClientMetadata($clientMetadata);

        return $this;
    }

    public function withProviderMetadata(Metadata $providerMetadata): self
    {
        $this->hookContext->setProviderMetadata($providerMetadata);

        return $this;
    }

    public function asMutable(): self
    {
        $this->isImmutable = false;

        return $this;
    }

    public function asImmutable(): self
    {
        $this->isImmutable = true;

        return $this;
    }

    public function build(): HookContextInterface
    {
        $context = $this->hookContext;

        if ($this->isImmutable) {
            $context = HookContextTransformer::toImmutable($context);
        }

        return $context;
    }
}
