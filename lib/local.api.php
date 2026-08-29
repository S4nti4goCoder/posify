<?php

// Resolves an API call inside this process instead of sending it over HTTP to
// api.posify.com.
//
// The CMS used to loop back through the network for every single read, so one
// page could mean hundreds of round trips to its own machine. The API keeps
// working over HTTP for anyone else; this is only the shortcut for the CMS.

final class LocalApi
{
    private static bool $booted = false;
    private static ?string $apiPath = null;

    public static function isAvailable(): bool
    {
        return is_file(self::apiPath() . "/controllers/routes.controller.php");
    }

    /** @return mixed The decoded API response, exactly as cURL returned it. */
    public static function request(string $url, string $method, $fields)
    {
        $savedGet         = $_GET;
        $savedPost        = $_POST;
        $savedServer      = $_SERVER;
        $savedIncludePath = get_include_path();
        $savedStatus      = http_response_code();

        try {

            self::boot();

            $method = strtoupper($method);
            $query  = explode("?", $url, 2)[1] ?? "";

            $_GET  = array();
            $_POST = array();

            parse_str((string) $query, $_GET);

            if ($method === "POST") {

                if (is_array($fields)) {

                    $_POST = $fields;

                } else {

                    parse_str((string) $fields, $_POST);
                }
            }

            RequestContext::$authorization = Connection::apikey();
            RequestContext::$body = is_array($fields)
                ? http_build_query($fields)
                : (string) $fields;

            $_SERVER["REQUEST_METHOD"] = $method;
            $_SERVER["REQUEST_URI"]    = "/" . ltrim($url, "/");

            ob_start();

            $routes = new RoutesController();
            $routes->index();

            $output = (string) ob_get_clean();

        } catch (Throwable $e) {

            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            error_log("Local API call failed: " . $e->getMessage() . " | " . $method . " " . $url);

            $output = "";

        } finally {

            RequestContext::clear();

            $_GET    = $savedGet;
            $_POST   = $savedPost;
            $_SERVER = $savedServer;

            set_include_path($savedIncludePath);

            /*=============================================
            The API controllers set the status code of their own response.
            Left alone, that would become the status of the CMS page.
            =============================================*/

            if (is_int($savedStatus)) {
                http_response_code($savedStatus);
            }
        }

        $decoded = json_decode($output);

        /*=============================================
        A failure used to come back as null, and every caller reading
        ->status on it raised a warning that broke the JSON response
        =============================================*/

        if ($decoded === null) {

            return (object) ["status" => 500, "results" => "Local API call failed"];
        }

        return $decoded;
    }

    private static function boot(): void
    {
        $apiPath = self::apiPath();

        /*=============================================
        The API requires its own files by relative path, so it has to be on
        the include path for them to resolve from inside the CMS.
        =============================================*/

        set_include_path($apiPath . PATH_SEPARATOR . get_include_path());

        if (self::$booted) {
            return;
        }

        require_once $apiPath . "/models/request.context.php";
        require_once $apiPath . "/models/connection.php";
        require_once $apiPath . "/controllers/routes.controller.php";

        self::$booted = true;
    }

    private static function apiPath(): string
    {
        if (self::$apiPath === null) {
            self::$apiPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "api";
        }

        return self::$apiPath;
    }
}
