<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class IdentifierNode implements NodeInterface
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function evaluate(EvaluateContext $context)
    {
        if (!$context->hasVariable($this->name)) {
            throw new EvaluateException("Variable {$this->name} does not exist, node {$this->toString()}");
        }

        return $context->getVariable($this->name);
    }

    public function toString()
    {
        return $this->name;
    }
}
