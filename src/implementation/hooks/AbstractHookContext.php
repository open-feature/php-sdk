<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use DateTime;
use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\interfaces\common\Metadata as MetadataInterface;
use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;
use OpenFeature\interfaces\hooks\HookContext;

use function get_class_methods;
use function is_array;
use function preg_replace;

abstract class AbstractHookContext
{
    protected string $flagKey = '';
    protected string $type = '';
    /** @var bool|string|int|float|DateTime|mixed[]|null $defaultValue */
    protected $defaultValue = null;
    protected EvaluationContextInterface $evaluationContext;
    protected MetadataInterface $clientMetadata;
    protected MetadataInterface $providerMetadata;

    /**
     * @param HookContext|mixed[]|null $hookContext
     */
    public function __construct($hookContext = null)
    {
        /**
         * utility constructor to build a HookContext from an existing
         * HookContext if one is provided
         */
        $this->evaluationContext = EvaluationContext::createNull();
        $this->clientMetadata = new Metadata('empty-client');
        $this->providerMetadata = new Metadata('empty-provider');

        if ($hookContext instanceof HookContext) {
            $methods = get_class_methods($hookContext);
            foreach ($methods as $method) {
                $property = preg_replace('/^get/', '', $method, 1);
                $this->$property = $hookContext->$method();
            }
        } elseif (is_array($hookContext)) {
            /** @var string $property */
            /** @var mixed $value */
            foreach ($hookContext as $property => $value) {
                $this->$property = $value;
            }
        }
    }
}
