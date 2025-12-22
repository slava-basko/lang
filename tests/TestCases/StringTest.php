<?php

namespace Basko\Lang\TestCases;

class StringTest extends BaseCase
{
    public function testString()
    {
        $this->assertEquals("string", $this->evalExp("'string'"));
        $this->assertEquals("some string", $this->evalExp("'some string'"));
        $this->assertEquals("str'ing", $this->evalExp("'str\'ing'"));
        $this->assertEquals("str\"ing", $this->evalExp("'str\"ing'"));
        $this->assertEquals("string", $this->evalExp("'str\ing'"));
    }
}
