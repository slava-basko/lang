<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\Exception\ParseException;
use Basko\Lang\Node\Exception\EvaluateException;

class MathTest extends BaseCase
{
    public function testCalc()
    {
        $this->assertEquals(10, $this->evalExp('1 + 2 + 3 + 4'));
        $this->assertEquals(12, $this->evalExp('2 + 2 * 4 + 4 / 2'));
        $this->assertEquals(18, $this->evalExp('(2 + 2) * 4 + 4 / 2'));
        $this->assertEquals(15, $this->evalExp('(1 + 2) * (3 + 2)'));
    }

    public function testStrictCalc()
    {
        $this->assertEquals(0.3, $this->evalExp('0.1 + 0.2'));
        $this->assertEquals(3, $this->evalExp('1.5 * 2'));
        $this->assertEquals(0.3, $this->evalExp('(1 + 2) / 10'));
    }

    public function testDivZero()
    {
        $this->expectException(EvaluateException::class);
        $this->evalExp('1 / 0');
    }

    public function testDivZeroFloat()
    {
        $this->expectException(EvaluateException::class);
        $this->evalExp('1 / 0.0');
    }

    public function testModuleZero()
    {
        $this->expectException(EvaluateException::class);
        $this->evalExp('5 % 0');
    }

    public function testModuleZeroFloat()
    {
        $this->expectException(EvaluateException::class);
        $this->evalExp('5 % 0.0');
    }

    public function testPow()
    {
        $this->assertEquals(8, $this->evalExp('2 ^ 3'));
        $this->assertEquals(1, $this->evalExp('5 ^ 0'));
        $this->assertEquals(5, $this->evalExp('5 ^ 1'));
    }

    public function testPowFloat()
    {
        $this->expectException(EvaluateException::class);
        $this->evalExp('2 ^ 1.5');
    }

    public function testFraction()
    {
        $this->assertEquals('0.33333333333333333333', $this->evalExp('1 / 3'));
        $this->assertEquals('0.66666666666666666666', $this->evalExp('2 / 3'));
    }

    public function testSumSmallFraction()
    {
        $this->assertEquals('1.2', $this->evalExp('0.1 + 0.2 + 0.3 + 0.6'));
    }

    public function testSumIntAndFloat()
    {
        $this->assertEquals('7.5', $this->evalExp('5 + 2.5'));
    }

    public function testFractionComp()
    {
        $this->assertTrue($this->evalExp('0.1 + 0.2 == 0.3'));
        $this->assertFalse($this->evalExp('0.1 + 0.2 != 0.3'));
        $this->assertTrue($this->evalExp('0.30000000000000000001 > 0.3'));
    }

    public function testNegatives()
    {
        $this->assertEquals(-3, $this->evalExp('-1 - 2'));
        $this->assertEquals(-0.3, $this->evalExp('-0.1 - 0.2'));
    }

    public function testDotAtTheEnd()
    {
        try {
            $this->evalExp('(1 + 1.) > 0');
        } catch (ParseException $e) {
            $this->assertEquals('Invalid invalid float number', $e->getMessage());
        }
    }

    public function testInvalidMathOperation()
    {
        try {
            $this->evalExp('1 2');
        } catch (ParseException $e) {
            $this->assertEquals("Bad token '2' (operator expected)", $e->getMessage());
        }
    }
}
