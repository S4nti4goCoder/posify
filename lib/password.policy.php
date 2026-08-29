<?php

// What counts as an acceptable password, in one place.
//
// The five composition rules are the ones people expect to see. They are not
// enough on their own: "Password1!" satisfies every one of them and sits in
// every cracking dictionary, so the list of known passwords runs as well.
//
// The browser shows the same rules live, but the check that matters is here.

final class PasswordPolicy
{
    public const MIN_LENGTH = 8;

    /** bcrypt stops reading at 72 bytes, so anything past that is not a password. */
    public const MAX_LENGTH = 64;

    private static ?array $common = null;

    /**
     * The rules, in the order the form lists them.
     *
     * @return array<string,string>
     */
    public static function rules(): array
    {
        return [
            "length" => "Al menos " . self::MIN_LENGTH . " caracteres",
            "upper"  => "Una letra mayúscula",
            "lower"  => "Una letra minúscula",
            "number" => "Un número",
            "symbol" => "Un símbolo (!@#$...)",
            "common" => "Que no sea una contraseña común",
        ];
    }

    /**
     * Which rules the password breaks.
     *
     * @return string[] the failing keys, empty when the password is good
     */
    public static function check(string $password): array
    {
        $failed = [];

        if (mb_strlen($password) < self::MIN_LENGTH || mb_strlen($password) > self::MAX_LENGTH) {
            $failed[] = "length";
        }

        if (!preg_match("/\p{Lu}/u", $password)) {
            $failed[] = "upper";
        }

        if (!preg_match("/\p{Ll}/u", $password)) {
            $failed[] = "lower";
        }

        if (!preg_match("/[0-9]/", $password)) {
            $failed[] = "number";
        }

        if (!preg_match("/[^\p{L}\p{N}]/u", $password)) {
            $failed[] = "symbol";
        }

        if (self::isCommon($password)) {
            $failed[] = "common";
        }

        return $failed;
    }

    public static function passes(string $password): bool
    {
        return self::check($password) === [];
    }

    /** One line naming what is missing, for the response the form shows. */
    public static function message(array $failed): string
    {
        if ($failed === []) {
            return "";
        }

        if ($failed === ["common"]) {
            return "Esa contraseña es demasiado común, elige otra";
        }

        $rules = self::rules();
        $parts = [];

        foreach ($failed as $key) {

            if (isset($rules[$key])) {
                $parts[] = mb_strtolower(mb_substr($rules[$key], 0, 1)) . mb_substr($rules[$key], 1);
            }
        }

        return "La contraseña necesita: " . implode(", ", $parts);
    }

    /** Case does not save a bad password, so both sides are lowercased. */
    private static function isCommon(string $password): bool
    {
        if (self::$common === null) {

            self::$common = [];

            $file = __DIR__ . "/common.passwords.txt";

            if (is_file($file)) {

                foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {

                    $line = trim($line);

                    if ($line !== "" && $line[0] !== "#") {
                        self::$common[mb_strtolower($line)] = true;
                    }
                }
            }
        }

        return isset(self::$common[mb_strtolower(trim($password))]);
    }
}
