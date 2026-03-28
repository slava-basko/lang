<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;

class ArrayLiteralNode implements NodeInterface
{
    /**
     * @var array<\Basko\Lang\Node\NodeInterface>
     */
    private $elements;

    /**
     * @param array<\Basko\Lang\Node\NodeInterface> $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(EvaluateContext $context)
    {
        $result = [];
        foreach ($this->elements as $el) {
            $result[] = $el->evaluate($context);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
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
