<?php

require_once __DIR__ . "/session.php";

// The branch a user works in is decided by the server, never by the request.
//
// An account with id_office_admin = 0 belongs to no single branch and may look at
// any of them. Everybody else stays pinned to their own, whatever the URL or the
// form says. The account's real branch is stored at login as "home_office", so a
// switch can never be mistaken for membership.

final class OfficeGuard
{
    public static function start(): void
    {
        Session::start();
    }

    // Call right after the session is created.
    public static function remember($admin): void
    {
        $_SESSION["home_office"] = (int) ($admin->id_office_admin ?? 0);
    }

    // Null when the session predates this check, which blocks switching.
    public static function homeOffice(): ?int
    {
        return isset($_SESSION["home_office"]) ? (int) $_SESSION["home_office"] : null;
    }

    public static function canSwitch(): bool
    {
        return self::homeOffice() === 0;
    }

    // The branch being looked at. Null when there is no session.
    public static function current(): ?int
    {
        if (!isset($_SESSION["admin"]->id_office_admin)) {
            return null;
        }

        return (int) $_SESSION["admin"]->id_office_admin;
    }

    // The signed in administrator, so sales cannot be attributed to someone else.
    public static function currentAdmin(): ?int
    {
        if (!isset($_SESSION["admin"]->id_admin)) {
            return null;
        }

        return (int) $_SESSION["admin"]->id_admin;
    }
}
