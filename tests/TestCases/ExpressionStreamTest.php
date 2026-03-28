<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\Stream\Exception\StreamException;
use Basko\Lang\Stream\ExpressionStream;

class ExpressionStreamTest extends BaseCase
{
    public function testExpressionStream()
    {
        $stream = new ExpressionStream('1 + 2');

        $this->assertEquals('1', $stream->consume());
        $this->assertEquals(' ', $stream->consume());
        $this->assertEquals('+', $stream->consume());
        $this->assertEquals(' ', $stream->consume());
        $this->assertEquals('2', $stream->consume());
    }

    public function testExpressionStreamExpect()
    {
        $stream = new ExpressionStream('1+2');
        $stream->consume();

        try {
            $stream->expect('-');
        } catch (StreamException $streamException) {
            $this->assertEquals(
                "Expected '-' but got '+' at position 1:2",
                $streamException->getMessage(),
            );
            $this->assertEquals("Parse error: Expected '-' but got '+' at position 1:2
1   | 1+2
~~~ | ~^
", $streamException->getSnippet());
        }
    }

    public function testExpressionStreamExpect2()
    {
        $stream = new ExpressionStream('1 + 2');
        $stream->consume();
        $stream->consume();

        try {
            $stream->expect('-');
        } catch (StreamException $streamException) {
            $this->assertEquals(
                "Expected '-' but got '+' at position 1:3",
                $streamException->getMessage(),
            );
            $this->assertEquals("Parse error: Expected '-' but got '+' at position 1:3
1   | 1 + 2
~~~ | ~~^
", $streamException->getSnippet());
        }
    }

    public function testExpressionStreamExpect3()
    {
        $stream = new ExpressionStream('1 
        +
        2');
        $stream->consume();
        $stream->consume();

        try {
            $stream->expect('-');
        } catch (StreamException $streamException) {
            $this->assertEquals(
                "Expected '-' but got '\n' at position 1:3",
                $streamException->getMessage(),
            );
            $this->assertEquals("Parse error: Expected '-' but got '\n' at position 1:3
1   | 1 
~~~ | ~~^
2   |         +
3   |         2
", $streamException->getSnippet());
        }
    }
}
