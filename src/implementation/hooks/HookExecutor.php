<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use OpenFeature\implementation\flags\EvaluationContext as FlagsEvaluationContext;
use OpenFeature\implementation\flags\MutableEvaluationContext;
use OpenFeature\interfaces\common\LoggerAwareTrait;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\hooks\HookHints as HookHintsInterface;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function call_user_func;
use function sprintf;

class HookExecutor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * The beforeHook differentiate from the other lifecycle hooks in that the return value
     * is a new EvaluationContext to be used in the evaluation of the flags, whereas no other
     * lifecycle hook provides context mutation.
     *
     * @param Hook[] $mergedHooks
     */
    public function beforeHooks(string $type, HookContext $hookContext, array $mergedHooks, HookHintsInterface $hints): ?EvaluationContext
    {
        $additionalContext = new MutableEvaluationContext();

        foreach ($mergedHooks as $hook) {
            if ($hook->supportsFlagValueType($type)) {
                $additionalContext = FlagsEvaluationContext::merge(
                    $additionalContext,
                    $this->executeHookUnsafe(fn () => $hook->before($hookContext, $hints)),
                );
            }
        }

        return $additionalContext;
    }

    /**
     * @param Hook[] $mergedHooks
     */
    public function afterHooks(string $type, HookContext $hookContext, ResolutionDetails $details, array $mergedHooks, HookHintsInterface $hints): void
    {
        foreach ($mergedHooks as $hook) {
            if ($hook->supportsFlagValueType($type)) {
                $this->executeHookUnsafe(fn () => $hook->after($hookContext, $details, $hints));
            }
        }
    }

    /**
     * @param Hook[] $mergedHooks
     */
    public function errorHooks(string $type, HookContext $hookContext, Throwable $err, array $mergedHooks, HookHintsInterface $hints): void
    {
        foreach ($mergedHooks as $hook) {
            if ($hook->supportsFlagValueType($type)) {
                $this->executeHookSafe(fn () => $hook->error($hookContext, $err, $hints), 'error');
            }
        }
    }

    /**
     * @param Hook[] $mergedHooks
     */
    public function finallyHooks(string $type, HookContext $hookContext, array $mergedHooks, HookHintsInterface $hints): void
    {
        foreach ($mergedHooks as $hook) {
            if ($hook->supportsFlagValueType($type)) {
                $this->executeHookSafe(fn () => $hook->finally($hookContext, $hints), 'finally');
            }
        }
    }

    private function executeHookSafe(callable $func, string $hookMethod): ?EvaluationContext
    {
        try {
            return $this->executeHookUnsafe($func);
        } catch (Throwable $err) {
            $this->getLogger()->error(
                sprintf('Error %s occurred while executing the %s hook', $err->getMessage(), $hookMethod),
            );

            return null;
        }
    }

    private function executeHookUnsafe(callable $func): ?EvaluationContext
    {
        /** @var EvaluationContext|null $value */
        $value = call_user_func($func);

        if ($value instanceof EvaluationContext) {
            return $value;
        }

        return null;
    }
}
