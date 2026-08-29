<?php

require_once __DIR__ . "/../api/models/connection.php";

// Slows down password guessing.
//
// bcrypt makes each attempt expensive, but nothing stopped a script from
// trying forever, and several demo accounts still carry weak passwords.
//
// The counter lives in a table so the increment is one atomic statement. Two
// requests arriving together cannot both read the same count and overwrite
// each other, which is what a file based counter could not prevent.
//
// Keyed by email and address together: keying by email alone would let anyone
// lock a real user out by failing on purpose.

final class LoginThrottle
{
    private const MAX_TRIES = 5;
    private const MINUTES   = 15;

    public static function tooMany(string $email): bool
    {
        $stmt = Connection::connect()->prepare(
            "SELECT tries_attempt FROM login_attempts
              WHERE who_attempt = :who
                AND date_first_attempt > NOW() - INTERVAL " . self::MINUTES . " MINUTE"
        );

        $stmt->execute([":who" => self::who($email)]);

        return (int) $stmt->fetchColumn() >= self::MAX_TRIES;
    }

    /** Minutes left before another try is allowed. */
    public static function waitFor(string $email): int
    {
        $stmt = Connection::connect()->prepare(
            "SELECT CEIL((TIMESTAMPDIFF(SECOND, NOW(), date_first_attempt) + :seconds) / 60)
               FROM login_attempts WHERE who_attempt = :who"
        );

        $stmt->execute([":who" => self::who($email), ":seconds" => self::MINUTES * 60]);

        $left = (int) $stmt->fetchColumn();

        return $left > 0 ? $left : 1;
    }

    /**
     * One statement: it inserts the first failure, adds to the count, or starts
     * over when the window has already passed.
     */
    public static function fail(string $email): void
    {
        Connection::connect()->prepare(
            "INSERT INTO login_attempts
                (who_attempt, tries_attempt, date_first_attempt, date_created_attempt)
             VALUES (:who, 1, NOW(), CURDATE())
             ON DUPLICATE KEY UPDATE
                tries_attempt = IF(date_first_attempt < NOW() - INTERVAL " . self::MINUTES . " MINUTE,
                                   1, tries_attempt + 1),
                date_first_attempt = IF(date_first_attempt < NOW() - INTERVAL " . self::MINUTES . " MINUTE,
                                        NOW(), date_first_attempt)"
        )->execute([":who" => self::who($email)]);
    }

    public static function clear(string $email): void
    {
        Connection::connect()
            ->prepare("DELETE FROM login_attempts WHERE who_attempt = :who")
            ->execute([":who" => self::who($email)]);
    }

    private static function who(string $email): string
    {
        return hash("sha256", strtolower(trim($email)) . "|" . ($_SERVER["REMOTE_ADDR"] ?? ""));
    }
}
