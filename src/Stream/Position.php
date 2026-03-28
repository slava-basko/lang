<?php

namespace Basko\Lang\Stream;

use Basko\Lang\Stream\Exception\StreamException;

class Position
{
    /**
     * @var int
     */
    public $line = 0;

    /**
     * @var int
     */
    public $column = 0;

    /**
     * @param int $line
     * @param int $column
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function __construct($line, $column)
    {
        if (!\is_int($line) || !\is_int($column)) {
            throw new StreamException('Invalid position, expected int');
        }

        $this->line = $line;
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \sprintf('%d:%d', $this->line, $this->column);
    }
}