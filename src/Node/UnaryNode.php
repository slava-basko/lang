<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class UnaryNode implements NodeInterface
{
    /**
     * @var string
     */
    private $operator;

    /**
     * @var \Basko\Lang\Node\NodeInterface
     */
    private $operand;

    /**
     * @param string $operator
     * @param \Basko\Lang\Node\NodeInterface $operand
     */
    public function __construct($operator, NodeInterface $operand)
    {
        $this->operator = $operator;
        $this->operand = $operand;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return "{$this->operator}{$this->operand->toString()}";
    }
}
