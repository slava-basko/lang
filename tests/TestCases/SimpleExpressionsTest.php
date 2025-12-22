<?php

namespace Basko\Lang\TestCases;

class SimpleExpressionsTest extends BaseCase
{
    public function testBool()
    {
        $this->assertTrue($this->evalExp('true'));
        $this->assertFalse($this->evalExp('false'));
    }

    public function testFunctions()
    {
        $this->assertEquals(2, $this->evalExp('len(["CA", "US"])'));
        $this->assertTrue($this->evalExp('min(4, 5) == 4'));
        $this->assertEquals('SLAV', $this->evalExp('upper("slav")'));
    }

    public function testOperators()
    {
        $this->assertFalse($this->evalExp('true && false'));
        $this->assertTrue($this->evalExp('true && true'));
        $this->assertTrue($this->evalExp('true && not false'));
        $this->assertTrue($this->evalExp('true || true'));
        $this->assertTrue($this->evalExp('true || not false'));
        $this->assertTrue($this->evalExp('"CA" in ["CA", "US"]'));
        $this->assertFalse($this->evalExp('"MX" in ["CA", "US"]'));
        $this->assertTrue($this->evalExp('"slav" in "slava"'));
        $this->assertFalse($this->evalExp('"serg" in "slava"'));
        $this->assertTrue($this->evalExp('5 > 4'));
        $this->assertFalse($this->evalExp('5 < 4'));
        $this->assertTrue($this->evalExp('4 == 4'));
        $this->assertFalse($this->evalExp('5 == 4'));
    }
}