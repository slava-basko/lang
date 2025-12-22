<?php

namespace Basko\Lang;

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

    public $type;
    public $value;
    public $pos;

    public function __construct($type, $value, $pos)
    {
        $this->type = $type;
        $this->value = $value;
        $this->pos = $pos;
    }
}