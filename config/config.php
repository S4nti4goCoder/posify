<?php

// Sits outside both document roots, so it is never web servable.
// Secrets live in config.local.php. POS_ env vars override it.
final class Config
{
    private static ?array $values = null;

    public static function all(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        $defaults = [
            'db_host'      => 'localhost',
            'db_name'      => 'posify_db',
            'db_user'      => '',
            'db_password'  => '',
            'db_charset'   => 'utf8mb4',
            'api_key'      => '',
            'jwt_secret'   => '',
            'api_base_url' => 'http://api.posify.com/',
            'cms_origin'   => 'http://cms.posify.com',
            'debug'        => false,

            // Resolve API calls inside the CMS process instead of over HTTP.
            // Turn off to fall back to the old loopback.
            'api_in_process' => true,

            // Widens the unauthenticated write allowlist so the first-run
            // installer can seed pages, modules, folders and columns.
            // Must stay false on any installed system.
            'allow_installer' => false,
        ];

        $localFile = __DIR__ . '/config.local.php';
        $values    = is_file($localFile)
            ? array_merge($defaults, (array) require $localFile)
            : $defaults;

        foreach (array_keys($defaults) as $key) {
            $fromEnv = getenv('POS_' . strtoupper($key));

            if ($fromEnv !== false && $fromEnv !== '') {
                $values[$key] = $fromEnv;
            }
        }

        $values['debug'] = filter_var($values['debug'], FILTER_VALIDATE_BOOL);

        self::$values = $values;

        return self::$values;
    }

    public static function get(string $key)
    {
        $values = self::all();

        if (!array_key_exists($key, $values)) {
            throw new RuntimeException(sprintf('Unknown configuration key "%s".', $key));
        }

        return $values[$key];
    }

    // Like get(), but refuses to return an empty value
    public static function requireSecret(string $key): string
    {
        $value = (string) self::get($key);

        if ($value === '') {
            throw new RuntimeException(sprintf(
                'Missing configuration value "%s". Copy config/config.local.example.php '
                . 'to config/config.local.php and fill it in.',
                $key
            ));
        }

        return $value;
    }

    public static function isDebug(): bool
    {
        return (bool) self::get('debug');
    }
}
