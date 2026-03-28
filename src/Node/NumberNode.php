<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class NumberNode implements NodeInterface
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
        return (string) $this->value;
    }
}
