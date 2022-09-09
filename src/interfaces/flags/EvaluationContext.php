<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

interface EvaluationContext
{
    public function getTargetingKey(): ?string;

    public function getAttributes(): Attributes;
}
