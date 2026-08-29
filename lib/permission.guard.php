<?php

require_once __DIR__ . "/../api/models/connection.php";

// Permissions were only checked where pages are rendered, so the menu hid what
// a vendedor could not open. The ajax endpoints checked nothing: a session with
// access to two modules could delete rows in any other by naming its table.
//
// The permission is stored per page url, and a table belongs to a page through
// its module, so the table name is resolved back to that url.

final class PermissionGuard
{
    /** @var array<string,?string> page per table, one lookup per request */
    private static array $pages = [];

    public static function canReach(string $table): bool
    {
        if (!isset($_SESSION["admin"])) {

            return false;
        }

        $role = (string) ($_SESSION["admin"]->rol_admin ?? "");

        if ($role === "superadmin" || $role === "admin") {

            return true;
        }

        if ($role !== "vendedor") {

            return false;
        }

        $page = self::pageOf($table);

        if ($page === null) {

            return false;
        }

        $granted = json_decode((string) ($_SESSION["admin"]->permissions_admin ?? "{}"), true);

        return is_array($granted)
            && isset($granted[$page])
            && $granted[$page] === "on";
    }

    /** Stops the request with a plain answer the callers already understand. */
    public static function enforce(string $table): void
    {
        if (self::canReach($table)) {

            return;
        }

        http_response_code(403);
        echo "forbidden";
        exit;
    }

    private static function pageOf(string $table): ?string
    {
        if (array_key_exists($table, self::$pages)) {

            return self::$pages[$table];
        }

        $stmt = Connection::connect()->prepare(
            "SELECT p.url_page
               FROM modules m
               INNER JOIN pages p ON p.id_page = m.id_page_module
              WHERE m.title_module = :table AND m.type_module = 'tables'
              LIMIT 1"
        );

        $stmt->execute([":table" => $table]);

        $page = $stmt->fetchColumn();

        return self::$pages[$table] = $page === false ? null : (string) $page;
    }
}
