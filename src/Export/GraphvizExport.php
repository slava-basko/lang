<?php

namespace Basko\Lang\Export;

use Basko\Lang\Node\ArrAccessNode;
use Basko\Lang\Node\ArrayLiteralNode;
use Basko\Lang\Node\BinaryNode;
use Basko\Lang\Node\BooleanNode;
use Basko\Lang\Node\FunctionCallNode;
use Basko\Lang\Node\IdentifierNode;
use Basko\Lang\Node\MethodCallNode;
use Basko\Lang\Node\NodeInterface;
use Basko\Lang\Node\NumberNode;
use Basko\Lang\Node\PropAccessNode;
use Basko\Lang\Node\StringNode;
use Basko\Lang\Node\TernaryNode;
use Basko\Lang\Node\UnaryNode;
use ReflectionClass;

class GraphvizExport implements ExportInterface
{
    private $nodes = [];
    private $edges = [];
    private $counter = 0;

    /**
     * @inheritdoc
     */
    public function build(NodeInterface $node)
    {
        $this->nodes = [];
        $this->edges = [];
        $this->counter = 0;

        $this->visit($node);

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
        if ($node instanceof BinaryNode) {
            $leftId = $this->visit($this->getPrivate($node, 'left'));
            $rightId = $this->visit($this->getPrivate($node, 'right'));
            $this->edges[] = [$id, $leftId];
            $this->edges[] = [$id, $rightId];
        } elseif ($node instanceof UnaryNode) {
            $operandId = $this->visit($this->getPrivate($node, 'operand'));
            $this->edges[] = [$id, $operandId];
        } elseif ($node instanceof NumberNode
            || $node instanceof StringNode
            || $node instanceof BooleanNode
            || $node instanceof IdentifierNode) {
            // Leafs — nothing to do
        } elseif ($node instanceof FunctionCallNode) {
            $args = $this->getPrivate($node, 'args');
            foreach ($args as $arg) {
                $childId = $this->visit($arg);
                $this->edges[] = [$id, $childId];
            }
        } elseif ($node instanceof ArrayLiteralNode) {
            $els = $this->getPrivate($node, 'elements');
            foreach ($els as $el) {
                $childId = $this->visit($el);
                $this->edges[] = [$id, $childId];
            }
        } elseif ($node instanceof ArrAccessNode) {
            $arr = $this->getPrivate($node, 'array');
            $key = $this->getPrivate($node, 'key');
            $arrId = $this->visit($arr);
            $keyId = $this->visit($key);
            $this->edges[] = [$id, $arrId];
            $this->edges[] = [$id, $keyId];
        } elseif ($node instanceof PropAccessNode) {
            $obj = $this->getPrivate($node, 'object');
            $objId = $this->visit($obj);
            $this->edges[] = [$id, $objId];
        } elseif ($node instanceof MethodCallNode) {
            $obj = $this->getPrivate($node, 'object');
            $args = $this->getPrivate($node, 'args');
            $objId = $this->visit($obj);
            $this->edges[] = [$id, $objId];
            foreach ($args as $arg) {
                $childId = $this->visit($arg);
                $this->edges[] = [$id, $childId];
            }
        } elseif ($node instanceof TernaryNode) {
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
        if ($node instanceof NumberNode) {
            return "Number\n" . $this->getPrivate($node, 'value');
        }
        if ($node instanceof StringNode) {
            return "String\n" . $this->getPrivate($node, 'value');
        }
        if ($node instanceof BooleanNode) {
            return "Boolean\n" . ($this->getPrivate($node, 'value') ? 'true' : 'false');
        }
        if ($node instanceof IdentifierNode) {
            return "Identifier\n" . $this->getPrivate($node, 'name');
        }
        if ($node instanceof BinaryNode) {
            return "BinaryOp\n" . $this->getPrivate($node, 'operator');
        }
        if ($node instanceof UnaryNode) {
            return "UnaryOp\n" . $this->getPrivate($node, 'operator');
        }
        if ($node instanceof FunctionCallNode) {
            return "FunctionCall\n" . $this->getPrivate($node, 'name');
        }
        if ($node instanceof ArrayLiteralNode) {
            return "ArrayLiteral";
        }
        if ($node instanceof ArrAccessNode) {
            return "ArrayAccess";
        }
        if ($node instanceof PropAccessNode) {
            return "PropertyAccess\n" . $this->getPrivate($node, 'property');
        }
        if ($node instanceof MethodCallNode) {
            return "MethodCall\n" . $this->getPrivate($node, 'method');
        }
        if ($node instanceof TernaryNode) {
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

        if (PHP_VERSION_ID < 80100) {
            $p->setAccessible(true);
        }

        return $p->getValue($obj);
    }
}
