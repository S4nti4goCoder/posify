<?php

require_once __DIR__ . "/session.php";

// One token per session, required on every state changing request.
// Forms carry it in a hidden field, ajax calls in the X-CSRF-Token header.
//
// Without it, a page on another site could make the browser submit a form or
// fire a request here using the cashier's open session.

final class CsrfGuard
{
    private const KEY = "csrf_token";

    public static function start(): void
    {
        Session::start();
    }

    public static function token(): string
    {
        self::start();

        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, "UTF-8") . '">';
    }

    public static function isValid(): bool
    {
        self::start();

        if (empty($_SESSION[self::KEY])) {
            return false;
        }

        $sent = $_POST["csrf_token"] ?? self::headerToken();

        return is_string($sent) && hash_equals($_SESSION[self::KEY], $sent);
    }

    // Stops the request when the token is missing or wrong.
    public static function enforce(): void
    {
        if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
            return;
        }

        if (self::isValid()) {
            return;
        }

        http_response_code(403);

        if (self::headerToken() !== null || self::isAjax()) {

            exit("csrf");
        }

        exit('<!doctype html><meta charset="utf-8">'
            . '<div style="font-family:system-ui;max-width:32rem;margin:4rem auto;line-height:1.6">'
            . '<h2>Tu sesion expiro</h2>'
            . '<p>Por seguridad, el formulario no se envio. Vuelve a iniciar sesion e intentalo de nuevo.</p>'
            . '<p><a href="/">Volver al inicio</a></p>'
            . '</div>');
    }

    private static function isAjax(): bool
    {
        return strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "") === "xmlhttprequest";
    }

    private static function headerToken(): ?string
    {
        return $_SERVER["HTTP_X_CSRF_TOKEN"] ?? null;
    }
}
