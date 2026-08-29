<?php

// Turns stored data into something safe to put on a page.
//
// Values were printed with a bare urldecode(), so anything a user typed reached
// the browser as markup: a product named with a script tag ran on every till
// that opened the catalogue.
//
// Storage is no longer url encoded, so text() only escapes.

final class View
{
    /** Safe inside a text node or a quoted attribute. */
    public static function text($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
    }

    /** Kept as the name half the views already call. */
    public static function raw($value): string
    {
        return self::text($value);
    }

    /**
     * Our own css and js, stamped with the file's modification time.
     *
     * Without it the browser keeps serving whatever it cached, so a fix to a
     * script looks like it did nothing until someone clears the cache by hand.
     */
    public static function asset(string $path): string
    {
        $file = dirname(__DIR__) . "/cms" . $path;
        $stamp = is_file($file) ? filemtime($file) : time();

        return htmlspecialchars($path . "?v=" . $stamp, ENT_QUOTES, "UTF-8");
    }

    /**
     * Safe inside a <script> block. Returns a JSON literal, so quotes and a
     * stray </script> in the data cannot end the block early.
     */
    public static function js($value): string
    {
        return (string) json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Safe inside href or src. Only http, https and relative paths survive,
     * so a stored "javascript:" link cannot run.
     */
    public static function url($value): string
    {
        $url = trim((string) $value);

        if ($url === "") {
            return "";
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme !== "" && $scheme !== "http" && $scheme !== "https") {
            return "";
        }

        return htmlspecialchars($url, ENT_QUOTES, "UTF-8");
    }
}
