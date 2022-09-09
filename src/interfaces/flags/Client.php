<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use OpenFeature\interfaces\common\MetadataGetter;
use OpenFeature\interfaces\hooks\HooksAware;

/**
 * Interface used to resolve flags
 */
interface Client extends EvaluationContextAware, FeatureDetails, FeatureValues, HooksAware, MetadataGetter
{
}
