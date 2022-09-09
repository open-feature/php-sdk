<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\interfaces\flags\Attributes as AttributesInterface;
use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;

class EvaluationContext implements EvaluationContextInterface
{
    use EvaluationContextMerger;

    private ?string $targetingKey;
    protected AttributesInterface $attributes;

    public function __construct(?string $targetingKey = null, ?AttributesInterface $attributes = null)
    {
        $this->targetingKey = $targetingKey;
        $this->attributes = $attributes ?? new Attributes();
    }

    public function getTargetingKey(): ?string
    {
        return $this->targetingKey;
    }

    public function setTargetingKey(?string $targetingKey): void
    {
        $this->targetingKey = $targetingKey;
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }

    public static function createNull(): EvaluationContext
    {
        return new EvaluationContext();
    }
}
