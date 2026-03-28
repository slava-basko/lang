<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class TernaryNode implements NodeInterface
{
    /**
     * @var \Basko\Lang\Node\NodeInterface
     */
    private $condition;

    /**
     * @var \Basko\Lang\Node\NodeInterface
     */
    private $trueExpr;

    /**
     * @var \Basko\Lang\Node\NodeInterface
     */
    private $falseExpr;

    /**
     * @param \Basko\Lang\Node\NodeInterface $condition
     * @param \Basko\Lang\Node\NodeInterface $trueExpr
     * @param \Basko\Lang\Node\NodeInterface $falseExpr
     */
    public function __construct(NodeInterface $condition, NodeInterface $trueExpr, NodeInterface $falseExpr)
    {
        $this->condition = $condition;
        $this->trueExpr = $trueExpr;
        $this->falseExpr = $falseExpr;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(EvaluateContext $context)
    {
        return $this->condition->evaluate($context)
            ? $this->trueExpr->evaluate($context)
            : $this->falseExpr->evaluate($context);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return "({$this->condition->toString()} ? {$this->trueExpr->toString()} : {$this->falseExpr->toString()})";
    }
}
