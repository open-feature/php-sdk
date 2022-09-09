<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

use DateTime;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\EvaluationContext;

interface HookContext
{
    /**
     * -----------------
     * Requirement 4.1.1
     * -----------------
     * Hook context MUST provide: the flag key
     *
     * -----------------
     * Requirement 4.1.3
     * -----------------
     * The flag key, flag type, and default value properties MUST be immutable. If
     * the language does not support immutability, the hook MUST NOT modify these properties.
     */
    public function getFlagKey(): string;

    /**
     * -----------------
     * Requirement 4.1.1
     * -----------------
     * Hook context MUST provide: the flag value type
     *
     * -----------------
     * Requirement 4.1.3
     * -----------------
     * The flag key, flag type, and default value properties MUST be immutable. If
     * the language does not support immutability, the hook MUST NOT modify these properties.
     */
    public function getType(): string;

    /**
     * -----------------
     * Requirement 4.1.1
     * -----------------
     * Hook context MUST provide: the default value.
     *
     * -----------------
     * Requirement 4.1.3
     * -----------------
     * The flag key, flag type, and default value properties MUST be immutable. If
     * the language does not support immutability, the hook MUST NOT modify these properties.
     *
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function getDefaultValue();

    /**
     * -----------------
     * Requirement 4.1.1
     * -----------------
     * Hook context MUST provide: the evaluation context
     */
    public function getEvaluationContext(): EvaluationContext;

    /**
     * -----------------
     * Requirement 4.1.2
     * -----------------
     * The hook context SHOULD provide: access to the client metadata fields
     */
    public function getClientMetadata(): Metadata;

    /**
     * -----------------
     * Requirement 4.1.2
     * -----------------
     * The hook context SHOULD provide: access to the provider metadata fields
     */
    public function getProviderMetadata(): Metadata;
}
