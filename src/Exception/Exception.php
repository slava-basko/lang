<?php

namespace Basko\Lang\Exception;

use Basko\Lang\Stream\Position;

class Exception extends \Exception {
    /**
     * @var Position
     */
    private $position;

    /**
     * @var string
     */
    private $expression;

    /**
     * @param string $message
     * @param \Basko\Lang\Stream\Position $position
     * @param string $expression
     * @return static
     */
    static public function create($message, Position $position, $expression)
    {
        $e = new static($message);
        $e->position = $position;
        $e->expression = (string) $expression;

        return $e;
    }

    /**
     * @return \Basko\Lang\Stream\Position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return string
     */
    public function getSnippet()
    {
        $lines = \explode("\n", $this->expression);
        $lineCount = \count($lines);

        for ($i = 0; $i < $lineCount; $i++) {
            $lines[$i] = sprintf("%-3s | %s", $i + 1, $lines[$i]);
        }

        if ($lineCount === 1) {
            $lines[] = sprintf("%-3s | %s", '~~~', str_repeat('~', $this->position->column - 1) . "^");
        } else {
            array_splice($lines, $this->position->line, 0, sprintf("%-3s | %s", '~~~', str_repeat('~', $this->position->column - 1) . "^"));
        }

        $header = sprintf("Parse error: %s", $this->getMessage());

        return $header . "\n" . implode("\n", $lines) . "\n";
    }
}
