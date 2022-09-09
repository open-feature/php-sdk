<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;

use function is_null;
use function sizeof;
use function strlen;

trait EvaluationContextMerger
{
    public static function merge(?EvaluationContextInterface ...$contexts): EvaluationContextInterface
    {
        $i = 0;
        $totalContexts = sizeof($contexts);
        /** @var EvaluationContextInterface|null $initialContext */
        $initialContext = null;

        // find the first non-null context
        while ($i < $totalContexts) {
            if (isset($contexts[$i])) {
                $initialContext = $contexts[$i];

                break;
            }
            $i += 1;
        }

        // Exit early if no non-null contexts were found
        if (is_null($initialContext)) {
            return EvaluationContext::createNull();
        }

        $calculatedContext = $initialContext;

        // begin merging contexts
        $i += 1;
        while ($i < $totalContexts) {
            if (isset($contexts[$i])) {
                $additionalContext = $contexts[$i];

                $calculatedTargetingKey = $calculatedContext->getTargetingKey();
                $additionalTargetingKey = $additionalContext->getTargetingKey();

                /** @var ?string $newTargetingKey */
                $newTargetingKey = null;
                if (!is_null($calculatedTargetingKey) && strlen($calculatedTargetingKey) > 0) {
                    $newTargetingKey = $calculatedTargetingKey;
                } elseif (!is_null($additionalTargetingKey) && strlen($additionalTargetingKey) > 0) {
                    $newTargetingKey = $additionalTargetingKey;
                }

                $mergedAttributes = AttributesMerger::merge(
                    $calculatedContext->getAttributes(),
                    $additionalContext->getAttributes(),
                );

                $calculatedContext = new MutableEvaluationContext(
                    $newTargetingKey,
                    $mergedAttributes,
                );
            }

            $i = $i + 1;
        }

        return $calculatedContext;
    }
}
