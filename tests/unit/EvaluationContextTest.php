<?php

declare(strict_types=1);

namespace OpenFeature\Test\unit;

use DateTime;
use OpenFeature\Test\TestCase;
use OpenFeature\implementation\flags\Attributes;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\flags\MutableEvaluationContext;

class EvaluationContextTest extends TestCase
{
    /**
     * Requirement 3.1.1
     *
     * The evaluation context structure MUST define an optional targeting key field of type string, identifying the subject of the flag evaluation.
     */
    public function testEvaluationContextMustDefineOptionalTargetingKeyField(): void
    {
        // test it can be populated
        $expectedTargetingKey = 'key';

        $evaluationContext = new EvaluationContext($expectedTargetingKey, new Attributes());

        $actualTargetingKey = $evaluationContext->getTargetingKey();

        $this->assertEquals($expectedTargetingKey, $actualTargetingKey);

        // test it is optional
        $expectedTargetingKey = null;

        $evaluationContext = new EvaluationContext();

        $actualTargetingKey = $evaluationContext->getTargetingKey();

        $this->assertEquals($expectedTargetingKey, $actualTargetingKey);
    }

    /**
     * Requirement 3.1.2
     *
     * The evaluation context MUST support the inclusion of custom fields, having keys of type string, and values of type boolean | string | number | datetime | structure.
     */
    public function testEvaluationContextMustSupportInclusionOfCustomFields(): void
    {
        $customFields = [
            'string' => 'stringValue',
            'datetime' => new DateTime('now'),
            'integer' => 42,
            'float' => 3.14,
            'structure' => [
                'a' => 0,
                'b' => 1,
                'c' => 2,
            ],
        ];

        $expectedAttributes = new Attributes($customFields);

        $evaluationContext = new EvaluationContext(null, $expectedAttributes);

        $actualCustomFields = $evaluationContext->getAttributes();

        $this->assertEquals($expectedAttributes, $actualCustomFields);
    }

    /**
     * Requirement 3.1.3
     *
     * The evaluation context MUST support fetching the custom fields by key and also fetching all key value pairs.
     */
    public function testEvaluationContextMustSupportFetchingOfCustomFieldsAndAllKeyValues(): void
    {
        $name = 'Flash';
        $speed = 9001;
        $customFields = [
            'name' => $name,
            'speed' => $speed,
        ];

        $evaluationContext = new EvaluationContext(null, new Attributes($customFields));

        $actualName = $evaluationContext->getAttributes()->get('name');
        $actualSpeed = $evaluationContext->getAttributes()->get('speed');

        $this->assertEquals($actualName, $name);
        $this->assertEquals($actualSpeed, $speed);

        $attributes = $evaluationContext->getAttributes()->toArray();

        $this->assertEquals($customFields, $attributes);
    }

    /**
     * Requirement 3.1.4
     *
     * The evaluation context fields MUST have an unique key.
     */
    public function testEvaluationContextFieldsMustHaveUniqueKeys(): void
    {
        $evaluationContext = new MutableEvaluationContext();

        $attributes = $evaluationContext->getAttributes();

        $attributes->add('key', 'value');

        $this->assertEquals($attributes->toArray(), ['key' => 'value']);

        // verify that adding the same key again will override the previous value

        $attributes->add('key', 'new_value');

        $this->assertEquals($attributes->toArray(), ['key' => 'new_value']);
    }

    public function testEvaluationContextMerging(): void
    {
        $firstEvaluationContext = new EvaluationContext(null, new Attributes(['a' => 0]));
        $secondEvaluationContext = new EvaluationContext(null, new Attributes(['b' => 1]));

        $expectedEvaluationContextAttributes = ['a' => 0, 'b' => 1];

        $actualEvaluationContext = EvaluationContext::merge($firstEvaluationContext, $secondEvaluationContext);
        $actualEvaluationContextAttributes = $actualEvaluationContext->getAttributes()->toArray();

        $this->assertEquals($expectedEvaluationContextAttributes, $actualEvaluationContextAttributes);
    }
}
