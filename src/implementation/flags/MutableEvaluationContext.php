<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\interfaces\flags\Attributes as AttributesInterface;
use OpenFeature\interfaces\flags\MutableAttributes as MutableAttributesInterface;
use OpenFeature\interfaces\flags\MutableEvaluationContext as MutableEvaluationContextInterface;

class MutableEvaluationContext extends EvaluationContext implements MutableEvaluationContextInterface
{
    public function __construct(?string $targetingKey = null, ?AttributesInterface $attributes = null)
    {
        $attributes = $attributes ?? new Attributes();

        parent::__construct(
            $targetingKey,
            $attributes instanceof MutableAttributes ? $attributes : MutableAttributes::from($attributes),
        );
    }

    public function getAttributes(): MutableAttributesInterface
    {
        return new MutableAttributes($this->attributes->toArray());
    }
}
