<?php

namespace Basko\Lang\Node;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Node\Exception\EvaluateException;

class BinaryNode implements NodeInterface
{
    private $operator;
    private $left;
    private $right;
    private $bcPresent = false;

    public function __construct($operator, NodeInterface $left, NodeInterface $right)
    {
        $this->operator = $operator;
        $this->left = $left;
        $this->right = $right;

        if (function_exists("bcscale")) {
            $this->bcPresent = true;
            bcscale(20);
        }
    }

    public function evaluate(EvaluateContext $context)
    {
        $l = $this->left->evaluate($context);

        switch ($this->operator) {
            case '&&':
                // короткое замыкание: если левый false -> false, иначе вычислить правый
                if (!$l) {
                    return false;
                }
                $r = $this->right->evaluate($context);

                return (bool)$l && (bool)$r;

            case '||':
                if ($l) {
                    return true;
                }
                $r = $this->right->evaluate($context);

                return (bool)$l || (bool)$r;

            // остальные операторы — для них нужно вычислять правый
            default:
                $r = $this->right->evaluate($context);

                $useBc = $this->bcPresent && $this->isNumber($l) && $this->isNumber($r);

                switch ($this->operator) {
                    case '+':
                        return $useBc ? $this->bcHuman(bcadd($l, $r)) : $l + $r;
                    case '-':
                        return $useBc ? $this->bcHuman(bcsub($l, $r)) :  $l - $r;
                    case '*':
                        return $useBc ? $this->bcHuman(bcmul($l, $r)) : $l * $r;
                    case '/':
                        if ($useBc) {
                            if (bccomp($r, "0") === 0) {
                                throw new EvaluateException("Division by zero: {$this->toString()}");
                            }
                            return $this->bcHuman(bcdiv($l, $r));
                        }
                        return $l / $r;
                    case '%':
                        if ($useBc) {
                            if (bccomp($r, "0") === 0) {
                                throw new EvaluateException("Division by zero: {$this->toString()}");
                            }

                            if (str_contains($l, '.') || str_contains($r, '.')) {
                                throw new EvaluateException("Modulo with non-integers: {$this->toString()}");
                            }
                            return $this->bcHuman(bcmod($l, $r));
                        }
                        return $l % $r;
                    case '^':
                        if ($useBc) {
                            if (str_contains($r, '.')) {
                                throw new EvaluateException("Exponent must be integer: {$this->toString()}");
                            }
                            return $this->bcHuman(bcpow($l, $r));
                        }
                        return pow($l, $r);
                    case '==':
                        return $useBc ? (bccomp($l, $r) === 0) : ($l == $r);
                    case '!=':
                        return $useBc ? (bccomp($l, $r) !== 0) : ($l != $r);
                    case '<':
                        return $useBc ? (bccomp($l, $r) === -1) : ($l < $r);
                    case '>':
                        return $useBc ? (bccomp($l, $r) === 1) : ($l > $r);
                    case '<=':
                        return $useBc ? (bccomp($l, $r) !== 1) : ($l <= $r);
                    case '>=':
                        return $useBc ? (bccomp($l, $r) !== -1) : ($l >= $r);
                    case 'in':
                        if (is_array($r)) {
                            return in_array($l, $r, true);
                        }
                        if (is_string($r) && is_string($l)) {
                            return strpos($r, $l) !== false;
                        }

                        return false;
                    default:
                        throw new EvaluateException("Unknown operator: {$this->operator}, node  {$this->toString()}");
                }
        }
    }

    private function bcHuman(string $num): string {
        return rtrim(rtrim($num, '0'), '.');
    }

    private function isNumber($value): bool
    {
        // если NumberNode всегда возвращает строку
        return is_string($value) && preg_match('/^-?\d+(\.\d+)?$/', $value);
    }

    public function toString()
    {
        return "({$this->left->toString()} {$this->operator} {$this->right->toString()})";
    }
}