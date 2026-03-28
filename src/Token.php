<?php

namespace Basko\Lang;

use Basko\Lang\Stream\Position;

class Token
{
    const BOOLEAN = 'boolean';
    const NUMBER = 'number';
    const STRING = 'string';
    const IDENTIFIER = 'identifier';
    const OPERATOR = 'operator';
    const LPAREN = 'lparen';
    const RPAREN = 'rparen';
    const LBRACKET = 'lbracket';
    const RBRACKET = 'rbracket';
    const COMMA = 'comma';
    const QUESTION = 'question';
    const COLON = 'colon';
    const DOT = 'dot';
    const EOF = 'eof';

    /**
     * @var string
     */
    public $type;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var \Basko\Lang\Stream\Position
     */
    public $pos;

    /**
     * @param string $type
     * @param mixed $value
     * @param \Basko\Lang\Stream\Position $pos
     */
    public function __construct($type, $value, Position $pos)
    {
        $this->type = $type;
        $this->value = $value;
        $this->pos = $pos;
    }
}
