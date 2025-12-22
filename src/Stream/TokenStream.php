<?php

namespace Basko\Lang\Stream;

use Basko\Lang\ExpressionContext;
use Basko\Lang\Stream\Exception\StreamException;

class TokenStream implements StreamInterface
{
    /**
     * @var array<array-key, \Basko\Lang\Token>
     */
    private $tokens;

    private $position = 0;

    private $len;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->len = count($tokens);
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
     * @return \Basko\Lang\Token
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function peek()
    {
        if ($this->isEof()) {
            throw new StreamException('End of token stream was reached');
        }

        return $this->tokens[$this->position];
    }

    /**
     * @return \Basko\Lang\Token
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function consume()
    {
        $token = $this->peek();
        $this->position++;

        return $token;
    }

    /**
     * @param $compareTo
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function expect($compareTo)
    {
        $token = $this->peek();
        if ($token->type !== $compareTo) {
            throw new StreamException("Expected '$compareTo' but got '{$token->type}' at position {$this->position}");
        }
    }

    public function getPosition()
    {
        return $this->position;
    }
}
