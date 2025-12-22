<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class NumberNode implements NodeInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function evaluate(EvaluateContext $context)
    {
        return $this->value;
    }

    public function toString()
    {
        return (string) $this->value;
    }
}
