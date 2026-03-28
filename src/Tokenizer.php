<?php

namespace Basko\Lang;

use Basko\Lang\Exception\ParseException;
use Basko\Lang\Stream\ExpressionStream;

class Tokenizer
{
    /**
     * @var array<\Basko\Lang\Token>
     */
    private $tokens;

    /**
     * @var \Basko\Lang\Stream\ExpressionStream
     */
    private $expressionStream;

    /**
     * @param string $input
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function __construct($input)
    {
        $this->expressionStream = new ExpressionStream($input);
    }

    /**
     * @return \Basko\Lang\Stream\ExpressionStream
     */
    public function getExpressionStream()
    {
        return $this->expressionStream;
    }

    /**
     * @return array<\Basko\Lang\Token>
     * @throws \Basko\Lang\Exception\ParseException
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    public function tokenize()
    {
        $this->expressionStream->reset();
        $this->tokens = [];

        while (!$this->expressionStream->isEof()) {
            $char = $this->expressionStream->peek();

            if (Utils::isWhitespace($char)) {
                $this->expressionStream->consume();
                continue;
            }

            $position = $this->expressionStream->getPosition();

            if (Utils::isDigit($char)) {
                $this->tokenizeNumber();
            } elseif ($char === '"' || $char === "'") {
                $this->tokenizeString();
            } elseif (Utils::isLetter($char)) {
                $this->tokenizeIdentifier();
            } elseif ($char === '(') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::LPAREN, '(', $position);
            } elseif ($char === ')') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::RPAREN, ')', $position);
            } elseif ($char === '[') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::LBRACKET, '[', $position);
            } elseif ($char === ']') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::RBRACKET, ']', $position);
            } elseif ($char === ',') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::COMMA, ',', $position);
            } elseif ($char === '?') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::QUESTION, '?', $position);
            } elseif ($char === ':') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::COLON, ':', $position);
            } elseif ($char === '.') {
                $this->expressionStream->consume();
                $this->tokens[] = new Token(Token::DOT, '.', $position);
            } else {
                $this->tokenizeOperator();
            }
        }

        $this->tokens[] = new Token(Token::EOF, null, $this->expressionStream->getPosition());

        return $this->tokens;
    }

    /**
     * @return void
     * @throws \Basko\Lang\Exception\ParseException
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    private function tokenizeNumber()
    {
        $position = $this->expressionStream->getPosition();
        $value = $this->expressionStream->consume();
        $hasDot = false;

        while (!$this->expressionStream->isEof()) {
            $char = $this->expressionStream->peek();

            if (Utils::isDigit($char) || $char === '.') {
                if ($char === '.' && $hasDot) {
                    throw ParseException::create("Invalid symbol '$char', only one dot allowed in number", $position, $this->expressionStream->getString());
                }

                if ($char === '.') {
                    $hasDot = true;
                }

                $value .= $this->expressionStream->consume();

                if ($char === '.' && $this->expressionStream->isEof()) {
                    throw ParseException::create("Invalid symbol 'EOF' right after dot", $position, $this->expressionStream->getString());
                }

                if ($char === '.' && !Utils::isDigit($this->expressionStream->peek())) {
                    throw ParseException::create("Invalid invalid float number", $position, $this->expressionStream->getString());
                }
            } else {
                break;
            }
        }

        $this->tokens[] = new Token(Token::NUMBER, $value, $position);
    }

    /**
     * @return void
     * @throws \Basko\Lang\Exception\ParseException
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    private function tokenizeString()
    {
        $position = $this->expressionStream->getPosition();
        $quote = $this->expressionStream->consume(); // " or '
        $value = '';

        while (!$this->expressionStream->isEof()) {
            $char = $this->expressionStream->consume();

            if ($char === '\\') {
                if ($this->expressionStream->isEof()) {
                    throw ParseException::create("Unterminated escape in string", $position, $this->expressionStream->getString());
                }
                $esc = $this->expressionStream->consume();
                switch ($esc) {
                    case 'n':
                        $value .= "\n";
                        break;
                    case 'r':
                        $value .= "\r";
                        break;
                    case 't':
                        $value .= "\t";
                        break;
                    case '\\':
                        $value .= "\\";
                        break;
                    case '"':
                        $value .= '"';
                        break;
                    case "'":
                        $value .= "'";
                        break;
                    default:
                        // либо сохраняем как-is, либо бросаем ошибку
                        $value .= $esc;
                }
                continue;
            }

            if ($char === $quote) {
                // closing quote
                $this->tokens[] = new Token(Token::STRING, $value, $position);

                return;
            }

            $value .= $char;
        }

        // если вышли из цикла — EOF до закрывающей кавычки
        throw ParseException::create("Unterminated string literal", $position, $this->expressionStream->getString());
    }

    /**
     * @return void
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    private function tokenizeIdentifier()
    {
        $position = $this->expressionStream->getPosition();
        $value = $this->expressionStream->consume();

        while (!$this->expressionStream->isEof() && Utils::isLetterOrDigit($this->expressionStream->peek())) {
            $value .= $this->expressionStream->consume();
        }

        $lower = \strtolower($value);

        if ($lower === 'true' || $lower === 'false') {
            $this->tokens[] = new Token(Token::BOOLEAN, $lower === 'true', $position);

            return;
        }

        $operatorKeywords = ['and' => '&&', 'or' => '||', 'not' => '!', 'in' => 'in'];

        if (isset($operatorKeywords[$lower])) {
            // OPERATOR with normalized (canonical) value
            $this->tokens[] = new Token(Token::OPERATOR, $operatorKeywords[$lower], $position);
        } else {
            $this->tokens[] = new Token(Token::IDENTIFIER, $value, $position);
        }
    }

    /**
     * @return void
     * @throws \Basko\Lang\Exception\ParseException
     * @throws \Basko\Lang\Stream\Exception\StreamException
     */
    private function tokenizeOperator()
    {
        $position = $this->expressionStream->getPosition();
        $value = $this->expressionStream->consume();
        $twoChar = !$this->expressionStream->isEof() ? $value . $this->expressionStream->peek() : null;

        $allowedTwoChar = ['==', '!=', '<=', '>=', '&&', '||'];
        $allowedSingle = ['+', '-', '*', '/', '%', '^', '<', '>', '!'];

        if ($twoChar !== null && \in_array($twoChar, $allowedTwoChar, true)) {
            $this->tokens[] = new Token(Token::OPERATOR, $twoChar, $position);
            $this->expressionStream->consume();

            return;
        }

        if (\in_array($value, $allowedSingle, true)) {
            $this->tokens[] = new Token(Token::OPERATOR, $value, $position);

            return;
        }

        // unknown symbol -> throw ParseException
        throw ParseException::create("Invalid symbol '$value'", $position, $this->expressionStream->getString());
    }
}
