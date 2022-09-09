<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\implementation\hooks\HookHints;
use OpenFeature\interfaces\flags\EvaluationOptions as EvaluationOptionsInterface;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HooksAwareTrait;

class EvaluationOptions implements EvaluationOptionsInterface
{
    use HooksAwareTrait;

    private ?HookHints $hookHints;

    /**
     * @param Hook[] $hooks
     */
    public function __construct(array $hooks = [], ?HookHints $hookHints = null)
    {
        $this->setHooks($hooks);
        $this->hookHints = $hookHints;
    }

    public function getHookHints(): ?HookHints
    {
        return $this->hookHints;
    }
}
