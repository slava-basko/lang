<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\EvaluateContext;

class VarsTest extends BaseCase
{
    protected function setUp(): void
    {
        $this->evaluateContext = new EvaluateContext();
        $this->evaluateContext->addVariable('var1', 1);
        $this->evaluateContext->addVariable('var2', 2);
        $this->evaluateContext->addVariable('address', [
            'country' => 'CA',
            'province' => 'BC',
            'city' => 'Burnaby',
            'street' => 'Rosser Ave.',
        ]);
        parent::setUp();
    }

    public function testVars()
    {
        $this->assertTrue($this->evalExp('var1 == 1'));
        $this->assertTrue($this->evalExp('var2 == 2'));
        $this->assertTrue($this->evalExp('var2 > var1'));
        $this->assertEquals(3, $this->evalExp('var2 + var1'));
        //        $this->assertEquals('CA', $this->evalExp('address.country'));
        $this->assertEquals('CA', $this->evalExp('address["country"]'));
    }
}
