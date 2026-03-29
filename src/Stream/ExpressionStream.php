<?php

namespace Basko\Lang\Stream;

use Basko\Lang\Stream\Exception\StreamException;
use Basko\Lang\Utils;

class ExpressionStream
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * @var \Basko\Lang\Stream\Position
     */
    private $position;

    /**
     * @var int
     */
    private $len;

    /**
     * @param string $string
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function __construct($string)
    {
        $this->string = \preg_replace("/\r\n?/", "\n", $string); // Only LF is ok
        $this->len = \strlen($this->string);
        $this->position = new Position(1, 1);
    }

    /**
     * @return void
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function reset()
    {
        $this->cursor = 0;
        $this->position = new Position(1, 1);
    }

    /**
     * @return bool
     */
    public function isEof()
    {
        return $this->cursor >= $this->len;
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

        return $this->string[$this->cursor];
    }

    /**
     * @return string
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function consume()
    {
        $char = $this->peek();
        $this->cursor++;

        $this->position->column++;
        if (Utils::isNewLine($char)) {
            $this->position->line++;
            $this->position->column = 1;
        }

        return $char;
    }

    /**
     * @return \Basko\Lang\Stream\Position
     */
    public function getPosition()
    {
        return clone $this->position;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @throws \Basko\Lang\Stream\Exception\StreamException
     * @throws \Basko\Lang\Exception\Exception
     */
    public function expect($compareTo)
    {
        $char = $this->peek();
        if ($char !== $compareTo) {
            $line = $this->position->line;
            $column = $this->position->column;

            throw StreamException::create(
                "Expected '$compareTo' but got '$char' at position $line:$column",
                $this->getPosition(),
                $this->getString()
            );
        }
    }
}
