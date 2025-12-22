<?php

namespace Basko\Lang;

class Token
{
    public const BOOLEAN = 'boolean';
    public const NUMBER = 'number';
    public const STRING = 'string';
    public const IDENTIFIER = 'identifier';
    public const OPERATOR = 'operator';
    public const LPAREN = 'lparen';
    public const RPAREN = 'rparen';
    public const LBRACKET = 'lbracket';
    public const RBRACKET = 'rbracket';
    public const COMMA = 'comma';
    public const QUESTION = 'question';
    public const COLON = 'colon';
    public const DOT = 'dot';
    public const EOF = 'eof';

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
