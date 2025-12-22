<?php

namespace Basko\Lang;

use Basko\Lang\Exception\Exception;
use Basko\Lang\Exception\ParseException;
use Basko\Lang\Node\ArrAccessNode;
use Basko\Lang\Node\ArrayLiteralNode;
use Basko\Lang\Node\BinaryNode;
use Basko\Lang\Node\BooleanNode;
use Basko\Lang\Node\FunctionCallNode;
use Basko\Lang\Node\IdentifierNode;
use Basko\Lang\Node\MethodCallNode;
use Basko\Lang\Node\NumberNode;
use Basko\Lang\Node\PropAccessNode;
use Basko\Lang\Node\StringNode;
use Basko\Lang\Node\TernaryNode;
use Basko\Lang\Node\UnaryNode;
use Basko\Lang\Stream\TokenStream;

class Parser
{
    /**
     * @var \Basko\Lang\Tokenizer
     */
    private $tokenizer;

    /**
     * @var \Basko\Lang\Stream\TokenStream
     */
    private $tokenStream;

    /**
     * @var array<non-empty-string, array{0: float, 1: float}>
     */
    private static $bindingPower = [
        '||' => [1.1, 1.0],
        '&&' => [2.1, 2.0],
        '==' => [3.1, 3.0], '!=' => [3.1, 3.0],
        '<' => [4.1, 4.0], '>' => [4.1, 4.0], '<=' => [4.1, 4.0], '>=' => [4.1, 4.0],
        'in' => [4.1, 4.0],
        '+' => [5.1, 5.0], '-' => [5.1, 5.0],
        '*' => [6.1, 6.0], '/' => [6.1, 6.0], '%' => [6.1, 6.0],
        '^' => [7.1, 7.0],
        '?' => [4.9, 4.8],
    ];

    /**
     * 0 - left binding power
     * 1 - right binding power
     *
     * @param non-empty-string $operator
     * @return array{0: float, 1: float}
     * @throws \Basko\Lang\Exception\Exception
     */
    private function getInfixBindingPower($operator)
    {
        if (!array_key_exists($operator, self::$bindingPower)) {
            throw new Exception("Unknown operator '$operator'");
        }

        return self::$bindingPower[$operator];
    }

    public function parse($input)
    {
        $this->tokenizer = new Tokenizer($input);
        $this->tokenStream = new TokenStream($this->tokenizer->tokenize());
        $this->tokenStream->reset();

        return $this->parseExpression(0);
    }

    private function parseExpression($minBindingPower)
    {
        $left = $this->parsePrimary();

        while (true) {
            $token = $this->tokenStream->peek();

            if (
                $token->type === Token::EOF
                || $token->type === Token::RPAREN
                || $token->type === Token::RBRACKET
                || $token->type === Token::COLON
                || $token->type === Token::COMMA
            ) {
                break;
            }

            // Ternary operator
            if ($token->type === Token::QUESTION) {
                $bindingPower = $this->getInfixBindingPower('?');
                $lbp = $bindingPower[0];
                $rbp = $bindingPower[1];
                if ($lbp < $minBindingPower) {
                    break;
                }
                $this->tokenStream->consume();
                $trueExpr = $this->parseExpression(0);
                $this->tokenStream->expect(Token::COLON);
                $this->tokenStream->consume();
                $falseExpr = $this->parseExpression($rbp);
                $left = new TernaryNode($left, $trueExpr, $falseExpr);
                continue;
            }

            // Binary operators
            if ($token->type === Token::OPERATOR) {
                $op = $token->value;
                $bindingPower = $this->getInfixBindingPower($op);
                $lbp = $bindingPower[0];
                $rbp = $bindingPower[1];

                if ($lbp < $minBindingPower) {
                    break;
                }

                $this->tokenStream->consume();
                $right = $this->parseExpression($rbp);
                $left = new BinaryNode($op, $left, $right);
                continue;
            }

//            throw new Exception("Bad token '{$token->value}' at position {$token->pos} (operator expected)");
            throw new ParseException("Bad token '{$token->value}' (operator expected)", $token->pos, $this->tokenizer->getExpressionStream()->getString());
        }

        return $left;
    }

    private function parsePrimary()
    {
        $token = $this->tokenStream->peek();

        // Number
        if ($token->type === Token::NUMBER) {
            $this->tokenStream->consume();

            return $this->parsePostfix(new NumberNode($token->value));
        }

        // String
        if ($token->type === Token::STRING) {
            $this->tokenStream->consume();

            return $this->parsePostfix(new StringNode($token->value));
        }

        // Boolean
        if ($token->type === Token::BOOLEAN) {
            $this->tokenStream->consume();

            if ($token->value === true) {
                return $this->parsePostfix(new BooleanNode(true));
            }
            if ($token->value === false) {
                return $this->parsePostfix(new BooleanNode(false));
            }
        }

        // Identifier, boolean, or function call
        if ($token->type === Token::IDENTIFIER) {
            $this->tokenStream->consume();
            $name = $token->value;

            // Function call
            if ($this->tokenStream->peek()->type === Token::LPAREN) {
                $this->tokenStream->consume();
                $args = $this->parseArguments();
                $this->tokenStream->expect(Token::RPAREN);
                $this->tokenStream->consume();

                return $this->parsePostfix(new FunctionCallNode($name, $args));
            }

            return $this->parsePostfix(new IdentifierNode($name));
        }

        // Unary operators (-, !, not)
        if ($token->type === Token::OPERATOR && in_array($token->value, ['-', '!'])) {
            $this->tokenStream->consume();
            $operand = $this->parsePrimary();

            return new UnaryNode($token->value, $operand);
        }

        // Parentheses
        if ($token->type === Token::LPAREN) {
            $this->tokenStream->consume();
            $expr = $this->parseExpression(0);
            $this->tokenStream->expect(Token::RPAREN);
            $this->tokenStream->consume();

            return $this->parsePostfix($expr);
        }

        // Array literal
        if ($token->type === Token::LBRACKET) {
            $this->tokenStream->consume();
            $elements = $this->parseArguments();
            $this->tokenStream->expect(Token::RBRACKET);
            $this->tokenStream->consume();

            return $this->parsePostfix(new ArrayLiteralNode($elements));
        }

        throw new Exception(\sprintf(
            "Unexpected token '%s' with value '%s' at position %s",
            $token->type,
            $token->value,
            $token->pos
        ));
    }

    private function parsePostfix($left)
    {
        while (true) {
            $token = $this->tokenStream->peek();

            // Array access: expr[key]
            if ($token->type === Token::LBRACKET) {
                $this->tokenStream->consume();
                $key = $this->parseExpression(0);
                $this->tokenStream->expect(Token::RBRACKET);
                $this->tokenStream->consume();
                $left = new ArrAccessNode($left, $key);
                continue;
            }

            // Property access or method call: expr.property or expr.method()
            if ($token->type === Token::DOT) {
                $this->tokenStream->consume();
                $this->tokenStream->expect(Token::IDENTIFIER);
                $propertyToken = $this->tokenStream->consume();
                $propertyName = $propertyToken->value;

                // Check if it's a method call
                if ($this->tokenStream->peek()->type === Token::LPAREN) {
                    $this->tokenStream->consume();
                    $args = $this->parseArguments();
                    $this->tokenStream->expect(Token::RPAREN);
                    $this->tokenStream->consume();
                    $left = new MethodCallNode($left, $propertyName, $args);
                } else {
                    // Property access
                    $left = new PropAccessNode($left, $propertyName);
                }
                continue;
            }

            break;
        }

        return $left;
    }

    private function parseArguments()
    {
        $args = [];

        if ($this->tokenStream->peek()->type === Token::RPAREN ||
            $this->tokenStream->peek()->type === Token::RBRACKET) {
            return $args;
        }

        do {
            $args[] = $this->parseExpression(0);
            if ($this->tokenStream->peek()->type === Token::COMMA) {
                $this->tokenStream->consume();
            } else {
                break;
            }
        } while (true);

        return $args;
    }
}