<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class UnaryNode implements NodeInterface
{
    private $operator;
    private $operand;

    public function __construct($operator, NodeInterface $operand)
    {
        $this->operator = $operator;
        $this->operand = $operand;
    }

    public function evaluate(EvaluateContext $context)
    {
        $val = $this->operand->evaluate($context);

        switch ($this->operator) {
            case '-':
                return "-$val";
            case '!':
                return !$val;
            default:
                throw new EvaluateException("Unknown unary operator: $this->operator");
        }
    }

    public function toString()
    {
        return "{$this->operator}{$this->operand->toString()}";
    }
}