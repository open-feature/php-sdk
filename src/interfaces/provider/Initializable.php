<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use OpenFeature\implementation\flags\EvaluationContext;

interface Initializable
{
    public function initialize(EvaluationContext $evaluationContext): void;
}
