<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

interface EvaluationContextAware
{
    /**
     * Return an optional client-level evaluation context.
     */
    public function getEvaluationContext(): ?EvaluationContext;

    /**
     * Set the client-level evaluation context.
     *
     * @param EvaluationContext $context Client level context.
     */
    public function setEvaluationContext(EvaluationContext $context): void;
}
