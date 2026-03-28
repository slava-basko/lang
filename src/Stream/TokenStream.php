<?php

namespace Basko\Lang\Stream;

use Basko\Lang\Stream\Exception\StreamException;

class TokenStream
{
    /**
     * @var array<array-key, \Basko\Lang\Token>
     */
    private $tokens;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $len;

    /**
     * @param array<\Basko\Lang\Token> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->len = \count($tokens);
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->position = 0;
    }

    /**
     * @return bool
     */
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
     * @param string $compareTo
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function expect($compareTo)
    {
        $token = $this->peek();
        if ($token->type !== $compareTo) {
            throw new StreamException("Expected '$compareTo' but got '{$token->type}' at position {$this->position}");
        }
    }
}
