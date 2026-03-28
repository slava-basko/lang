<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class StringNode implements NodeInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
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
        return '"' . $this->value . '"';
    }
}
