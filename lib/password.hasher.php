<?php

// Shared by the API and the CMS. Lives outside both document roots.
//
// Old hashes used bcrypt with one salt hardcoded for every user, so two people
// with the same password got the same hash. password_verify() still reads them,
// and needsRehash() flags them so the login can upgrade them in place.

final class PasswordHasher
{
    public static function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public static function verify(string $plain, string $stored): bool
    {
        return $stored !== '' && password_verify($plain, $stored);
    }

    public static function needsRehash(string $stored): bool
    {
        return $stored !== '' && password_needs_rehash($stored, PASSWORD_DEFAULT);
    }

    /*=============================================
    Burns the same time as a real check when there is no user to check.
    Without it the answer came back sooner for an address that does not
    exist, and the delay alone told an attacker which ones do.
    =============================================*/

    public static function burn(string $plain): void
    {
        password_verify($plain, '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy');
    }
}
