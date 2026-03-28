<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Exception\ParseException;
use Basko\Lang\Exception\Exception;

class ObjectTest extends BaseCase
{
    protected function setUp(): void
    {
        $this->evaluateContext = new EvaluateContext();
        $this->evaluateContext->addVariable('user', new \User('Slav', 30, 'some@email.com'));
        parent::setUp();
    }

    public function testObject()
    {
        $this->assertEquals('Slav', $this->evalExp('user.name'));
        $this->assertEquals('30', $this->evalExp('user.age'));

        $this->assertEquals('30', $this->evalExp('user.getAge()'));
        $this->assertEquals('some@email.com', $this->evalExp('user.getEmail()'));

        $this->assertTrue($this->evalExp('user.isAdult()'));
    }

    public function testMalformedMethodCall()
    {
        try {
            $this->evalExp('user.getEmail(');
        } catch (ParseException $parseException) {
            $this->assertEquals("Parse error: Unexpected token 'eof' with value '' at position 1:15
1   | user.getEmail(
~~~ | ~~~~~~~~~~~~~~^
", $parseException->getSnippet());
        }
    }
}
