<?php

namespace Basko\Lang;

use Basko\Lang\Exception\ParseException;
use Basko\Lang\Stream\ExpressionStream;

class Tokenizer
{
    private $tokens;
    /**
     * @var \Basko\Lang\Stream\ExpressionStream
     */
    private $expressionStream;

    public function __construct($input)
    {
        $this->expressionStream = new ExpressionStream(str_replace(["\r", "\n", "\t", "\v", "\f"], ' ', $input));
    }

    public function getExpressionStream()
    {
        return $this->expressionStream;
    }

    private function isWhitespace($char)
    {
        return $char === ' ' || $char === "\t" || $char === "\n" || $char === "\r";
    }

    private function isDigit($char)
    {
        return $char >= '0' && $char <= '9';
    }

    private function isLetter($char)
    {
        return ($char >= 'a' && $char <= 'z') || ($char >= 'A' && $char <= 'Z') || $char === '_';
    }

    public function isLetterOrDigit($char)
    {
        return $this->isLetter($char) || $this->isDigit($char);
    }

    public function tokenize()
    {
        $this->expressionStream->reset();
        $this->tokens = [];

        while (!$this->expressionStream->isEof()) {
            $char = $this->expressionStream->peek();
            $position = $this->expressionStream->getPosition();

            if ($this->isWhitespace($char)) {
                $this->expressionStream->consume();
                continue;
            }

            if ($this->isDigit($char)) {
                $this->tokenizeNumber();
            } elseif ($char === '"' || $char === "'") {
                $this->tokenizeString();
            } elseif ($this->isLetter($char)) {
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

    private function tokenizeNumber()
    {
        $position = $this->expressionStream->getPosition();
        $value = $this->expressionStream->consume();
        $hasDot = false;

        while (!$this->expressionStream->isEof()) {
            $char = $this->expressionStream->peek();

            if ($this->isDigit($char) || $char === '.') {
                if ($char === '.' && $hasDot) {
                    throw new ParseException("Invalid symbol '$char', only one dot allowed in number", $position, $this->expressionStream->getString());
                }

                if ($char === '.') {
                    $hasDot = true;
                }

                $value .= $this->expressionStream->consume();

                if ($char === '.' && $this->expressionStream->isEof()) {
                    throw new ParseException("Invalid symbol 'EOF' right after dot", $position, $this->expressionStream->getString());
                }

                if ($char === '.' && !$this->isDigit($this->expressionStream->peek())) {
                    break;
                }
            } else {
                break;
            }
        }

        $this->tokens[] = new Token(
            Token::NUMBER,
            //            $hasDot ? (float)$value : (int)$value,
            $value,
            $position
        );
    }

    private function tokenizeString()
    {
        $position = $this->expressionStream->getPosition();
        $quote = $this->expressionStream->consume(); // " or '
        $value = '';

        while (!$this->expressionStream->isEof()) {
            $char = $this->expressionStream->consume();

            if ($char === '\\') {
                if ($this->expressionStream->isEof()) {
                    throw new ParseException("Unterminated escape in string", $position, $this->expressionStream->getString());
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
        throw new ParseException("Unterminated string literal", $position, $this->expressionStream->getString());
    }

    private function tokenizeIdentifier()
    {
        $position = $this->expressionStream->getPosition();
        $value = $this->expressionStream->consume();

        while (!$this->expressionStream->isEof() && $this->isLetterOrDigit($this->expressionStream->peek())) {
            $value .= $this->expressionStream->consume();
        }

        $lower = strtolower($value);

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

    private function tokenizeOperator()
    {
        $position = $this->expressionStream->getPosition();
        $value = $this->expressionStream->consume();
        $twoChar = !$this->expressionStream->isEof() ? $value . $this->expressionStream->peek() : null;

        $allowedTwoChar = ['==', '!=', '<=', '>=', '&&', '||'];
        $allowedSingle = ['+', '-', '*', '/', '%', '^', '<', '>', '!'];

        if ($twoChar !== null && in_array($twoChar, $allowedTwoChar, true)) {
            $this->tokens[] = new Token(Token::OPERATOR, $twoChar, $position);
            $this->expressionStream->consume();

            return;
        }

        if (in_array($value, $allowedSingle, true)) {
            $this->tokens[] = new Token(Token::OPERATOR, $value, $position);

            return;
        }

        // unknown symbol -> throw ParseException
        throw new ParseException("Invalid symbol '$value'", $position, $this->expressionStream->getString());
    }
}
