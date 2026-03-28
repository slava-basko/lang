<?php

namespace Basko\Lang\Dumper;

use Basko\Lang\Node\NodeInterface;
use ReflectionClass;

/**
 * TODO: it's a draft.
 */
class ASTDotBuilder
{
    private $nodes = [];
    private $edges = [];
    private $counter = 0;

    /**
     * Построить DOT-строку для данного expression (корня AST)
     *
     * @param NodeInterface $expr
     * @return string DOT
     */
    public function build(NodeInterface $expr)
    {
        $this->nodes = [];
        $this->edges = [];
        $this->counter = 0;

        $this->visit($expr);

        $dot = "digraph AST {\n";
        $dot .= "  node [shape=box, fontname=\"Helvetica\"];\n\n";

        foreach ($this->nodes as $id => $label) {
            // json_encode даёт корректную экранированную строку в кавычках
            $dot .= "  n{$id} [label=" . \json_encode($label) . "];\n";
        }

        $dot .= "\n";
        foreach ($this->edges as $e) {
            $dot .= "  n{$e[0]} -> n{$e[1]};\n";
        }

        $dot .= "}\n";

        return $dot;
    }

    private function visit($node)
    {
        $id = ++$this->counter;

        $label = $this->nodeLabel($node);
        $this->nodes[$id] = $label;

        // рекурсивно посетить детей в зависимости от типа
        if ($node instanceof \Basko\Lang\Node\BinaryNode) {
            $leftId = $this->visit($this->getPrivate($node, 'left'));
            $rightId = $this->visit($this->getPrivate($node, 'right'));
            $this->edges[] = [$id, $leftId];
            $this->edges[] = [$id, $rightId];
        } elseif ($node instanceof \Basko\Lang\Node\UnaryNode) {
            $operandId = $this->visit($this->getPrivate($node, 'operand'));
            $this->edges[] = [$id, $operandId];
        } elseif ($node instanceof \Basko\Lang\Node\NumberNode
            || $node instanceof \Basko\Lang\Node\StringNode
            || $node instanceof \Basko\Lang\Node\BooleanNode
            || $node instanceof \Basko\Lang\Node\IdentifierNode) {
            // листья — ничего не делать
        } elseif ($node instanceof \Basko\Lang\Node\FunctionCallNode) {
            $args = $this->getPrivate($node, 'args');
            foreach ($args as $arg) {
                $childId = $this->visit($arg);
                $this->edges[] = [$id, $childId];
            }
        } elseif ($node instanceof \Basko\Lang\Node\ArrayLiteralNode) {
            $els = $this->getPrivate($node, 'elements');
            foreach ($els as $el) {
                $childId = $this->visit($el);
                $this->edges[] = [$id, $childId];
            }
        } elseif ($node instanceof \Basko\Lang\Node\ArrAccessNode) {
            $arr = $this->getPrivate($node, 'array');
            $key = $this->getPrivate($node, 'key');
            $arrId = $this->visit($arr);
            $keyId = $this->visit($key);
            $this->edges[] = [$id, $arrId];
            $this->edges[] = [$id, $keyId];
        } elseif ($node instanceof \Basko\Lang\Node\PropAccessNode) {
            $obj = $this->getPrivate($node, 'object');
            $objId = $this->visit($obj);
            $this->edges[] = [$id, $objId];
        } elseif ($node instanceof \Basko\Lang\Node\MethodCallNode) {
            $obj = $this->getPrivate($node, 'object');
            $args = $this->getPrivate($node, 'args');
            $objId = $this->visit($obj);
            $this->edges[] = [$id, $objId];
            foreach ($args as $arg) {
                $childId = $this->visit($arg);
                $this->edges[] = [$id, $childId];
            }
        } elseif ($node instanceof \Basko\Lang\Node\TernaryNode) {
            $c = $this->getPrivate($node, 'condition');
            $t = $this->getPrivate($node, 'trueExpr');
            $f = $this->getPrivate($node, 'falseExpr');
            $cid = $this->visit($c);
            $tid = $this->visit($t);
            $fid = $this->visit($f);
            $this->edges[] = [$id, $cid];
            $this->edges[] = [$id, $tid];
            $this->edges[] = [$id, $fid];
        } else {
            // На будущее: добавьте обработку новых типов узлов
        }

        return $id;
    }

    private function nodeLabel($node)
    {
        if ($node instanceof \Basko\Lang\Node\NumberNode) {
            return "Number\n" . $this->getPrivate($node, 'value');
        }
        if ($node instanceof \Basko\Lang\Node\StringNode) {
            return "String\n" . $this->getPrivate($node, 'value');
        }
        if ($node instanceof \Basko\Lang\Node\BooleanNode) {
            return "Boolean\n" . ($this->getPrivate($node, 'value') ? 'true' : 'false');
        }
        if ($node instanceof \Basko\Lang\Node\IdentifierNode) {
            return "Identifier\n" . $this->getPrivate($node, 'name');
        }
        if ($node instanceof \Basko\Lang\Node\BinaryNode) {
            return "BinaryOp\n" . $this->getPrivate($node, 'operator');
        }
        if ($node instanceof \Basko\Lang\Node\UnaryNode) {
            return "UnaryOp\n" . $this->getPrivate($node, 'operator');
        }
        if ($node instanceof \Basko\Lang\Node\FunctionCallNode) {
            return "FunctionCall\n" . $this->getPrivate($node, 'name');
        }
        if ($node instanceof \Basko\Lang\Node\ArrayLiteralNode) {
            return "ArrayLiteral";
        }
        if ($node instanceof \Basko\Lang\Node\ArrAccessNode) {
            return "ArrayAccess";
        }
        if ($node instanceof \Basko\Lang\Node\PropAccessNode) {
            return "PropertyAccess\n" . $this->getPrivate($node, 'property');
        }
        if ($node instanceof \Basko\Lang\Node\MethodCallNode) {
            return "MethodCall\n" . $this->getPrivate($node, 'method');
        }
        if ($node instanceof \Basko\Lang\Node\TernaryNode) {
            return "Ternary (?:)";
        }

        return \get_class($node);
    }

    /**
     * Утилитка: получить приватное/защищённое свойство через Reflection.
     * Используется чтобы не менять существующие классы.
     */
    private function getPrivate($obj, $prop)
    {
        $refClass = new ReflectionClass($obj);
        if (!$refClass->hasProperty($prop)) {
            return null;
        }
        $p = $refClass->getProperty($prop);
        $p->setAccessible(true);

        return $p->getValue($obj);
    }
}
