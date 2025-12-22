<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class FunctionCallNode implements NodeInterface
{
    private $name;
    private $args;

    public function __construct($name, array $args)
    {
        $this->name = $name;
        $this->args = $args;
    }

    public function evaluate(EvaluateContext $context)
    {
        $evaluatedArgs = [];
        foreach ($this->args as $arg) {
            $evaluatedArgs[] = $arg->evaluate($context);
        }

        if (!$context->hasFunction($this->name)) {
            throw new EvaluateException("Function {$this->name} does not exist, node {$this->toString()}");
        }

        return call_user_func_array($context->getFunction($this->name), $evaluatedArgs);
    }

    public function toString()
    {
        $argStrings = [];
        foreach ($this->args as $arg) {
            $argStrings[] = $arg->toString();
        }
        $args = implode(', ', $argStrings);

        return "{$this->name}({$args})";
    }
}
