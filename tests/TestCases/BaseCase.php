<?php

namespace Basko\Lang\TestCases;

use Basko\Lang\EvaluateContext;
use Basko\Lang\Parser;
use PHPUnit\Framework\TestCase;

abstract class BaseCase extends TestCase
{
    /**
     * @var \Basko\Lang\Parser
     */
    protected $parser;

    /**
     * @var EvaluateContext|null
     */
    protected $evaluateContext;

    protected function setUp(): void
    {
        $this->parser = new Parser();
        if (!$this->evaluateContext instanceof EvaluateContext) {
            $this->evaluateContext = new EvaluateContext();
        }
    }

    protected function evalExp(string $expression, ?EvaluateContext $context = null)
    {
        return $this->parser->parse($expression)->evaluate($context ?? $this->evaluateContext);
    }
}