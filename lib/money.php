<?php

// Colombian pesos: dot for thousands, no centavos.
//
// Rounding lives here too. Printing whole pesos while the arithmetic keeps
// decimals makes the lines disagree with the total, which breaks a till count.

final class Money
{
    public const SYMBOL   = "$";
    public const CURRENCY = "COP";
    public const DECIMALS = 0;

    public static function round($value): float
    {
        return round((float) $value, self::DECIMALS);
    }

    /** The number alone: 1.399 */
    public static function amount($value): string
    {
        return number_format(self::round($value), self::DECIMALS, ",", ".");
    }

    /** With the symbol: $ 1.399 */
    public static function format($value): string
    {
        return self::SYMBOL . " " . self::amount($value);
    }
}
