<?php

namespace Basko\Lang;

use Basko\Lang\Exception\ContextException;

class EvaluateContext
{
    /**
     * @var array<string, mixed>
     */
    private $variables = [];

    /**
     * @var array<string, callable>
     */
    private $functions = [];

    public function __construct()
    {
        $this->functions['max'] = function () {
            return \call_user_func_array('max', \func_get_args());
        };

        $this->functions['min'] = function () {
            return \call_user_func_array('min', \func_get_args());
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

            $a = $a instanceof \Traversable ? iterator_to_array($a) : $a;

            return \count($a);
        };
    }

    /**
     * @param string $var
     * @param mixed $value
     * @return void
     */
    public function addVariable($var, $value)
    {
        $this->variables[$var] = $value;
    }

    /**
     * @param string $fnName
     * @param callable $fn
     * @return void
     */
    public function addFunction($fnName, $fn)
    {
        $this->functions[$fnName] = $fn;
    }

    /**
     * @param string $var
     * @return bool
     */
    public function hasVariable($var)
    {
        return \array_key_exists($var, $this->variables);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Basko\Lang\Exception\ContextException
     */
    public function getVariable($name)
    {
        if (!$this->hasVariable($name)) {
            throw new ContextException("Variable '$name' does not exist");
        }

        return $this->variables[$name];
    }

    /**
     * @param string $fnName
     * @return bool
     */
    public function hasFunction($fnName)
    {
        return \array_key_exists($fnName, $this->functions);
    }

    /**
     * @param string $fnName
     * @return callable
     * @throws \Basko\Lang\Exception\ContextException
     */
    public function getFunction($fnName)
    {
        if (!$this->hasFunction($fnName)) {
            throw new ContextException("Function '$fnName' does not exist");
        }

        return $this->functions[$fnName];
    }
}
