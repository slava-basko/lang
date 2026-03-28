<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\Export\GraphvizExport;
use Basko\Lang\EvaluateContext;
use Basko\Lang\Parser;

class GraphvizExportTest extends BaseCase
{
    public function testGraph()
    {
        $context = new EvaluateContext();
        $context->addfunction('user', new \User('Slav', 30, 'some@email.com'));
        $context->addFunction('order', ['items' => 8]);

        $parser = new Parser();
        $ast = $parser->parse('user.age > 30 && len(user.getEmail()) > 0 || order["items"] > 5');

        $builder = new GraphvizExport();

        $this->assertEquals('digraph AST {
  node [shape=box, fontname="Helvetica"];

  n1 [label="BinaryOp\n||"];
  n2 [label="BinaryOp\n&&"];
  n3 [label="BinaryOp\n>"];
  n4 [label="PropertyAccess\nage"];
  n5 [label="Identifier\nuser"];
  n6 [label="Number\n30"];
  n7 [label="BinaryOp\n>"];
  n8 [label="FunctionCall\nlen"];
  n9 [label="MethodCall\ngetEmail"];
  n10 [label="Identifier\nuser"];
  n11 [label="Number\n0"];
  n12 [label="BinaryOp\n>"];
  n13 [label="ArrayAccess"];
  n14 [label="Identifier\norder"];
  n15 [label="String\nitems"];
  n16 [label="Number\n5"];

  n4 -> n5;
  n3 -> n4;
  n3 -> n6;
  n9 -> n10;
  n8 -> n9;
  n7 -> n8;
  n7 -> n11;
  n2 -> n3;
  n2 -> n7;
  n13 -> n14;
  n13 -> n15;
  n12 -> n13;
  n12 -> n16;
  n1 -> n2;
  n1 -> n12;
}
', $builder->build($ast));
    }
}
