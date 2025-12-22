<?php

namespace Basko\Lang\Exception;

class ParseException extends Exception
{
    private $position; // 0-based offset в исходной строке
    private $expression;   // исходная (нормализованная) строка

    public function __construct($message, $position, $expression)
    {
        parent::__construct($message);
        $this->position = (int) $position;
        $this->expression = (string) $expression;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Возвращает сниппет (одна строка) с caret под проблемным символом.
     * $radius — сколько символов контекста показывать по бокам.
     */
    public function getSnippet($radius = 40)
    {
        $pos = $this->position;
        $src = $this->expression;
        $len = strlen($src);

        // безопасные границы
        $start = max(0, $pos - $radius);
        $end = min($len, $pos + $radius + 1);

        $snippet = substr($src, $start, $end - $start);

        // индекс позиции внутри сниппета (0-based)
        $caretPos = $pos - $start;
        if ($caretPos < 0) {
            $caretPos = 0;
        } elseif ($caretPos > strlen($snippet)) {
            $caretPos = strlen($snippet);
        }

        if (strlen($snippet) < strlen($src)) {
            $snippet = '...' . $snippet . '...';
            $caretPos += 3;
        }

        // показать позицию как число (0-based) — если нужно, можно +1
        $header = sprintf("Parse error: %s at offset %d", $this->getMessage(), $this->position);
        $pointer = str_repeat('~', $caretPos) . "^";

        return $header . "\n" . $snippet . "\n" . $pointer . "\n";
    }
}
