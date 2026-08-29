<?php

// Where the API reads the parts of a request that do not live in $_GET or
// $_POST. Over HTTP they come from the headers and the raw body; when the CMS
// dispatches a call inside this same process they are injected instead.

final class RequestContext
{
    public static ?string $authorization = null;
    public static ?string $body = null;

    public static function authorization(): ?string
    {
        if (self::$authorization !== null) {

            return self::$authorization;
        }

        $headers = function_exists("getallheaders") ? (array) getallheaders() : array();

        foreach ($headers as $name => $value) {

            if (strcasecmp((string) $name, "Authorization") === 0) {

                return (string) $value;
            }
        }

        return $_SERVER["HTTP_AUTHORIZATION"] ?? null;
    }

    public static function body(): string
    {
        if (self::$body !== null) {

            return self::$body;
        }

        return (string) file_get_contents("php://input");
    }

    public static function clear(): void
    {
        self::$authorization = null;
        self::$body = null;
    }
}
