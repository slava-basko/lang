<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\EvaluateContext;

class TernaryOperatorTest extends BaseCase
{
    public function testTernaryOperator()
    {
        $context = new EvaluateContext();
        $context->addVariable('config', [
            'debug' => 'dev',
        ]);

        $this->assertEquals(
            'dev',
            $this->evalExp('config["debug"] ? "dev" : "prod"', $context)
        );
    }

    public function testTernaryComplexOperator()
    {
        $context = new EvaluateContext();
        $context->addVariable('arg', 'T');

        $this->assertEquals(
            'train',
            $this->evalExp("( ( arg == 'B' ) ? 'bus' :
                                        ( arg == 'A' ) ? 'airplane' :
                                        ( arg == 'T' ) ? 'train' :
                                        ( arg == 'C' ) ? 'car' :
                                        ( arg == 'H' ) ? 'horse' :
                                        'feet' )", $context)
        );
    }
}
