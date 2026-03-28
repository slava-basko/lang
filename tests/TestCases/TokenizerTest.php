<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\Stream\Position;
use Basko\Lang\Stream\TokenStream;
use Basko\Lang\Token;
use Basko\Lang\Tokenizer;

class TokenizerTest extends BaseCase
{
    public function testTokenizeSimple()
    {
        $tokenizer = new Tokenizer('1 + 2');
        $tokenStream = new TokenStream($tokenizer->tokenize());

        $tokens = [];
        while (!$tokenStream->isEof()) {
            $tokens[] = $tokenStream->consume();
        }

        $this->assertEquals(4, \count($tokens));

        $this->assertEquals(new Token(Token::NUMBER, '1', new Position(1, 1)), $tokens[0]);
        $this->assertEquals(new Token(Token::OPERATOR, '+', new Position(1, 3)), $tokens[1]);
        $this->assertEquals(new Token(Token::NUMBER, '2', new Position(1, 5)), $tokens[2]);
        $this->assertEquals(new Token(Token::EOF, '', new Position(1, 6)), $tokens[3]);
    }

    public function testTokenizeKindaReal()
    {
        $tokenizer = new Tokenizer('user.getAge() > 21 && country in ["CA", "US"]');
        $tokenStream = new TokenStream($tokenizer->tokenize());

        $tokens = [];
        while (!$tokenStream->isEof()) {
            $tokens[] = $tokenStream->consume();
        }

        $this->assertEquals(16, \count($tokens));
    }
}
