<?php

namespace Basko\Lang;

use Basko\Lang\Exception\ContextException;

class EvaluateContext {
    private $variables = [];
    private $functions = [];

    public function __construct()
    {
        $this->functions['max'] = function () {
            return \call_user_func_array('max', func_get_args());
        };

        $this->functions['min'] = function () {
            return \call_user_func_array('min', func_get_args());
        };

        $this->functions['abs'] = function ($a) {
            return abs($a);
        };

        $this->functions['round'] = function ($num, $precision) {
            return round($num, $precision);
        };

        $this->functions['upper'] = function ($str) {
            return strtoupper($str);
        };

        $this->functions['lower'] = function ($str) {
            return strtolower($str);
        };

        $this->functions['len'] = function ($a) {
            if (\is_string($a)) {
                return \strlen($a);
            }

            if ($a instanceof \Countable) {
                return $a->count();
            }

            $a = $a instanceof \Traversable ? \iterator_to_array($a) : $a;

            return \count($a);
        };

//        $this->functions['contains'] = function ($needle, $haystack) {
//            if (\is_string($haystack)) {
//                return $needle === '' || false !== \strpos($haystack, $needle);
//            }
//
//            foreach ($haystack as $element) {
//                if ($needle === $element) {
//                    return true;
//                }
//            }
//
//            return false;
//        };
    }

    public function addVariable($var, $value)
    {
        $this->variables[$var] = $value;
    }

    public function addFunction($fnName, $fn)
    {
        $this->functions[$fnName] = $fn;
    }

    public function hasVariable($var)
    {
        return array_key_exists($var, $this->variables);
    }

    public function getVariable($name)
    {
        if (!$this->hasVariable($name)) {
            throw new ContextException("Variable '$name' does not exist");
        }

        return $this->variables[$name];
    }

    public function hasFunction($fnName)
    {
        return array_key_exists($fnName, $this->functions);
    }

    public function getFunction($fnName)
    {
        if (!$this->hasFunction($fnName)) {
            throw new ContextException("Function '$fnName' does not exist");
        }

        return $this->functions[$fnName];
    }
}