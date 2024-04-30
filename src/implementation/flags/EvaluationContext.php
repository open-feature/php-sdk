<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\interfaces\flags\Attributes as AttributesInterface;
use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;

class EvaluationContext implements EvaluationContextInterface
{
    use EvaluationContextMerger;

    public function __construct(
        private ?string $targetingKey = null,
        protected readonly AttributesInterface $attributes = new Attributes(),
    ) {
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
