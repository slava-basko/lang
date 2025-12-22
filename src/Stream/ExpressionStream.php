<?php

namespace Basko\Lang\Stream;

use Basko\Lang\ExpressionContext;
use Basko\Lang\Stream\Exception\StreamException;

class ExpressionStream implements StreamInterface
{
    /**
     * @var string
     */
    private $string;

    private $position = 0;

    private $len;

    public function __construct($string)
    {
        $this->string = $string;
        $this->len = strlen($string);
    }

    public function reset()
    {
        $this->position = 0;
    }

    public function isEof()
    {
        return $this->position >= $this->len;
    }

    /**
     * @return string
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function peek()
    {
        if ($this->isEof()) {
            throw new StreamException('End of expression stream was reached');
        }

        return $this->string[$this->position];
    }

    public function consume()
    {
        $char = $this->peek();
        $this->position++;

        return $char;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    public function expect($compareTo)
    {
        $char = $this->peek();
        if ($char !== $compareTo) {
            throw new StreamException("Expected '$compareTo' but got '$char' at position {$this->position}");
        }
    }
}
