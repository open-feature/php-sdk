<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

interface MutableEvaluationContext extends EvaluationContext
{
    public function getAttributes(): MutableAttributes;
}
