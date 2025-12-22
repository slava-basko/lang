<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class ArrAccessNode implements NodeInterface
{
    private $array;
    private $key;

    public function __construct(NodeInterface $array, NodeInterface $key)
    {
        $this->array = $array;
        $this->key = $key;
    }

    public function evaluate(EvaluateContext $context)
    {
        $arr = $this->array->evaluate($context);
        $key = $this->key->evaluate($context);

        if (
            !is_array($arr)
            && !($arr instanceof \ArrayAccess)
        ) {
            throw new EvaluateException("Cannot access index on non-array, node {$this->toString()}");
        }

        if (
            (is_array($arr) && !array_key_exists($key, $arr))
            || ($arr instanceof \ArrayAccess && !$arr->offsetExists($key))
        ) {
            throw new EvaluateException("Undefined array key: {$key}, node {$this->toString()}");
        }

        return $arr[$key];
    }

    public function toString()
    {
        return "{$this->array->toString()}[{$this->key->toString()}]";
    }
}