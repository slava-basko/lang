<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class TernaryNode implements NodeInterface
{
    private $condition;
    private $trueExpr;
    private $falseExpr;

    public function __construct(NodeInterface $condition, NodeInterface $trueExpr, NodeInterface $falseExpr)
    {
        $this->condition = $condition;
        $this->trueExpr = $trueExpr;
        $this->falseExpr = $falseExpr;
    }

    public function evaluate(EvaluateContext $context)
    {
        return $this->condition->evaluate($context)
            ? $this->trueExpr->evaluate($context)
            : $this->falseExpr->evaluate($context);
    }

    public function toString()
    {
        return "({$this->condition->toString()} ? {$this->trueExpr->toString()} : {$this->falseExpr->toString()})";
    }
}
