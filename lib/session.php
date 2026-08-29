<?php

// One place to open the session, with the flags XAMPP leaves unset.
//
// httponly keeps the cookie out of reach of javascript, so an injected script
// cannot read it. samesite stops the browser from sending it along with a
// request started by another site. strict mode makes PHP refuse a session id
// it did not generate itself, which is the condition that made fixation
// possible in the first place.

final class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {

            return;
        }

        ini_set("session.use_strict_mode", "1");

        $https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
            || (($_SERVER["SERVER_PORT"] ?? "") === "443");

        session_set_cookie_params([
            "lifetime" => 0,
            "path"     => "/",
            "domain"   => "",
            "secure"   => $https,
            "httponly" => true,
            "samesite" => "Lax",
        ]);

        session_start();
    }

    /*=============================================
    The api token of the signed in administrator.

    Reading it straight off the session raised a warning once the session
    had expired, and a warning printed before the json is what breaks the
    response. The callers already know how to handle "logout".
    =============================================*/

    public static function token(): string
    {
        self::start();

        if (!isset($_SESSION["admin"]->token_admin)) {

            http_response_code(401);
            echo "logout";
            exit;
        }

        return (string) $_SESSION["admin"]->token_admin;
    }
}
