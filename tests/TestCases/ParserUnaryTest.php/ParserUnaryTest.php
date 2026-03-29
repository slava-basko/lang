<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class ParserUnaryTest extends BaseCase
{
    public function testUnaryNumericStringVariable()
    {
        $this->evaluateContext->addVariable('foo', '5');
        $this->assertEquals('-5', $this->evalExp('-foo'));
    }

    public function testUnaryNumericIntVariable()
    {
        $this->evaluateContext->addVariable('foo', 5);
        $this->assertEquals(-5, $this->evalExp('-foo'));
    }

    public function testUnaryNonNumericStringVariableThrows()
    {
        $this->expectException(EvaluateException::class);
        $this->evaluateContext->addVariable('foo', 'abc');
        $this->evalExp('-foo');
    }

    public function testUnaryNonNumericStringLiteralThrows()
    {
        $this->expectException(EvaluateException::class);
        $this->evalExp('-"abc"');
    }

    public function testUnaryNumericStringLiterals()
    {
        $this->assertEquals('-10', $this->evalExp('-"10"'));
        $this->assertEquals('-0.5', $this->evalExp('-"0.5"'));
    }

    public function testUnarySignedStringVariable()
    {
        $this->evaluateContext->addVariable('foo', '+5');
        $this->assertEquals('-5', $this->evalExp('-foo'));

        $this->evaluateContext->addVariable('foo', '-5');
        $this->assertEquals('5', $this->evalExp('-foo'));
    }
}
