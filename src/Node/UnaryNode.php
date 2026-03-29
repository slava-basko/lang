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
                // Preserve string representation for numeric strings (used by bcmath)
                if (\is_string($val)) {
                    if (\is_numeric($val)) {
                        if (\strlen($val) > 0 && $val[0] === '-') {
                            return \substr($val, 1);
                        }
                        if (\strlen($val) > 0 && $val[0] === '+') {
                            return '-' . \substr($val, 1);
                        }

                        return '-' . $val;
                    }

                    // Strict behavior: unary minus on non-numeric string is an error
                    throw new EvaluateException(\sprintf('Unary minus applied to non-numeric string: %s', $val));
                }

                return -$val;
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
