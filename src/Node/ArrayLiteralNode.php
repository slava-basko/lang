<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class ArrayLiteralNode implements NodeInterface
{
    private $elements;

    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function evaluate(EvaluateContext $context)
    {
        $result = [];
        foreach ($this->elements as $el) {
            $result[] = $el->evaluate($context);
        }

        return $result;
    }

    public function toString()
    {
        $elStrings = [];
        foreach ($this->elements as $el) {
            $elStrings[] = $el->toString();
        }
        $els = implode(', ', $elStrings);

        return "[{$els}]";
    }
}
