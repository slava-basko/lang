<?php

namespace Basko\Lang;

class Utils
{
    /**
     * @param string $char
     * @return bool
     */
    public static function isWhitespace($char)
    {
        return $char === ' '
            || $char === "\n"
            || $char === "\r"
            || $char === "\t"
            || $char === "\0"
            || $char === "\x0B"
        ;
    }

    /**
     * @param string $char
     * @return bool
     */
    public static function isNewLine($char)
    {
        return $char === "\n" || $char === "\r";
    }

    /**
     * @param string $char
     * @return bool
     */
    public static function isDigit($char)
    {
        return $char >= '0' && $char <= '9';
    }

    /**
     * @param string $char
     * @return bool
     */
    public static function isLetter($char)
    {
        return ($char >= 'a' && $char <= 'z') || ($char >= 'A' && $char <= 'Z') || $char === '_';
    }

    /**
     * @param string $char
     * @return bool
     */
    public static function isLetterOrDigit($char)
    {
        return self::isLetter($char) || self::isDigit($char);
    }
}
