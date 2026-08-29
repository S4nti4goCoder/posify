<?php

require_once __DIR__ . "/../api/models/connection.php";
require_once __DIR__ . "/password.hasher.php";
require_once __DIR__ . "/password.policy.php";

// Recovery that proves who is asking before it changes anything.
//
// The old flow generated a password, wrote it to the account and only then
// tried to email it. Typing someone's address was enough to lock them out, and
// with mail not configured the new password went nowhere at all.
//
// Nothing is written to password_admin until the code from the message comes
// back. Only the hash of that code is stored, so a dump of the table does not
// hand out working codes.

final class PasswordReset
{
    private const MINUTES = 60;

    /**
     * Starts a recovery. Answers the same whether or not the address is
     * registered, so the screen cannot be used to discover accounts.
     *
     * @return string|null the code to email, null when there is nobody to email
     */
    public static function open(string $email): ?string
    {
        $stmt = Connection::connect()->prepare(
            "SELECT id_admin FROM admins WHERE email_admin = :email AND status_admin = 1"
        );

        $stmt->execute([":email" => trim($email)]);

        $id = $stmt->fetchColumn();

        if ($id === false) {

            return null;
        }

        $code = bin2hex(random_bytes(32));

        Connection::connect()->prepare(
            "UPDATE admins
                SET reset_admin = :hash,
                    date_reset_admin = DATE_ADD(NOW(), INTERVAL " . self::MINUTES . " MINUTE)
              WHERE id_admin = :id"
        )->execute([":hash" => hash("sha256", $code), ":id" => (int) $id]);

        return $code;
    }

    /** The account the code belongs to, or null when it is wrong or expired. */
    public static function accountFor(string $code): ?array
    {
        if (trim($code) === "") {

            return null;
        }

        $stmt = Connection::connect()->prepare(
            "SELECT id_admin, email_admin FROM admins
              WHERE reset_admin = :hash AND date_reset_admin > NOW()"
        );

        $stmt->execute([":hash" => hash("sha256", trim($code))]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** Sets the new password and burns the code so it cannot serve twice. */
    public static function complete(string $code, string $newPassword): bool
    {
        $account = self::accountFor($code);

        if ($account === null || !PasswordPolicy::passes($newPassword)) {

            return false;
        }

        Connection::connect()->prepare(
            "UPDATE admins
                SET password_admin = :password,
                    reset_admin = NULL,
                    date_reset_admin = NULL
              WHERE id_admin = :id"
        )->execute([
            ":password" => PasswordHasher::hash($newPassword),
            ":id"       => (int) $account["id_admin"],
        ]);

        return true;
    }
}
