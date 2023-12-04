<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use Exception;
use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\interfaces\common\Metadata as MetadataInterface;
use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\HookContext;

use function is_array;

abstract class AbstractHookContext
{
    protected string $flagKey = '';
    protected FlagValueType $type = FlagValueType::Boolean;
    /** @var bool|string|int|float|mixed[]|null $defaultValue */
    protected bool | string | int | float | array | null $defaultValue = null;
    protected EvaluationContextInterface $evaluationContext;
    protected MetadataInterface $clientMetadata;
    protected MetadataInterface $providerMetadata;

    private const REQUIRED_PROPERTIES = ['flagKey', 'type'];

    /**
     * @param HookContext|mixed[]|null $hookContext
     */
    public function __construct(HookContext | array | null $hookContext = null)
    {
        /**
         * utility constructor to build a HookContext from an existing
         * HookContext if one is provided
         */
        $this->evaluationContext = EvaluationContext::createNull();
        $this->clientMetadata = new Metadata('empty-client');
        $this->providerMetadata = new Metadata('empty-provider');

        if ($hookContext instanceof HookContext) {
            $this->flagKey = $hookContext->getFlagKey();
            $this->type = $hookContext->getType();
            $this->evaluationContext = $hookContext->getEvaluationContext();
            $this->clientMetadata = $hookContext->getClientMetadata();
            $this->providerMetadata = $hookContext->getProviderMetadata();
        } elseif (is_array($hookContext)) {
            foreach (self::REQUIRED_PROPERTIES as $requiredProperty) {
                if (!isset($hookContext[$requiredProperty])) {
                    throw new Exception('Required property missing from hook context');
                }
            }

            /** @var string $property */
            /** @var mixed $value */
            foreach ($hookContext as $property => $value) {
                $this->$property = $value;
            }
        }
    }
}
