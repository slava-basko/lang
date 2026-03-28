<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class BooleanNode implements NodeInterface
{
    /**
     * @var bool
     */
    private $value;

    /**
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(EvaluateContext $context)
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->value ? 'true' : 'false';
    }
}
