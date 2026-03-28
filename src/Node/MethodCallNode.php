<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class MethodCallNode implements NodeInterface
{
    /**
     * @var \Basko\Lang\Node\NodeInterface
     */
    private $object;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $args;

    /**
     * @param \Basko\Lang\Node\NodeInterface $object
     * @param string $method
     * @param array $args
     */
    public function __construct(NodeInterface $object, $method, array $args)
    {
        $this->object = $object;
        $this->method = $method;
        $this->args = $args;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(EvaluateContext $context)
    {
        $obj = $this->object->evaluate($context);

        if (!\is_object($obj)) {
            throw new EvaluateException("Can't call method on non-object, node {$this->toString()}");
        }

        if (!\is_callable([$obj, $this->method])) {
            throw new EvaluateException("Undefined method, node {$this->toString()}");
        }

        $evaluatedArgs = [];
        foreach ($this->args as $arg) {
            $evaluatedArgs[] = $arg->evaluate($context);
        }

        return \call_user_func_array([$obj, $this->method], $evaluatedArgs);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        $argStrings = [];
        foreach ($this->args as $arg) {
            $argStrings[] = $arg->toString();
        }
        $args = \implode(', ', $argStrings);

        return "{$this->object->toString()}.{$this->method}({$args})";
    }
}
