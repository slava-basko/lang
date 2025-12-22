<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class PropAccessNode implements NodeInterface
{
    private $object;
    private $property;

    public function __construct(NodeInterface $object, $property)
    {
        $this->object = $object;
        $this->property = $property;
    }

    public function evaluate(EvaluateContext $context)
    {
        $obj = $this->object->evaluate($context);

        if (is_object($obj)) {
            if (property_exists($obj, $this->property)) {
                return $obj->{$this->property};
            }
            throw new EvaluateException("Undefined property: {$this->property}, node {$this->toString()}");
        } else {
            throw new EvaluateException("Cannot access property on non-object, node {$this->toString()}");
        }
    }

    public function toString()
    {
        return "{$this->object->toString()}.{$this->property}";
    }
}