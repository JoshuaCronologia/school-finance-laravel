<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Dynamic Connection Helper
|--------------------------------------------------------------------------
| Builds a Laravel database connection array from creds.json entry.
| Supports both MySQL and PostgreSQL drivers.
*/

function connection_template(array $config, string $driver = 'mysql'): array
{
    if ($driver === 'pgsql') {
        return [
            'driver'         => 'pgsql',
            'host'           => $config['host'],
            'port'           => $config['port'] ?? 5432,
            'database'       => $config['database'],
            'username'       => $config['username'],
            'password'       => $config['password'],
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => $config['schema'] ?? 'public',
            'sslmode'        => $config['sslmode'] ?? 'require',
            'options'        => [PDO::ATTR_EMULATE_PREPARES => true],
        ];
    }

    // Default: MySQL
    return [
        'driver'         => 'mysql',
        'host'           => $config['host'],
        'port'           => $config['port'] ?? 3306,
        'database'       => $config['database'],
        'username'       => $config['username'],
        'password'       => $config['password'],
        'unix_socket'    => '',
        'charset'        => 'utf8mb4',
        'collation'      => 'utf8mb4_unicode_ci',
        'prefix'         => '',
        'prefix_indexes' => true,
        'strict'         => false,
        'engine'         => null,
    ];
}

/*
|--------------------------------------------------------------------------
| Load creds.json and register dynamic connections
|--------------------------------------------------------------------------
*/

$dynamicConnections = [];

$credsPath  = str_replace('config', 'creds.json', __DIR__);
$configKey  = env('DB_CONFIG_KEY', '');

if ($configKey && file_exists($credsPath)) {
    $jsonConfig = json_decode(file_get_contents($credsPath), true) ?? [];

    if (array_key_exists($configKey, $jsonConfig)) {
        $envConfig = $jsonConfig[$configKey];
        $driver    = $envConfig['driver'] ?? env('DB_CONNECTION', 'mysql');

        // Main accounting database — register under its driver name
        if (isset($envConfig['databases']['main'])) {
            $dynamicConnections[$driver] = connection_template($envConfig['databases']['main'], $driver);
        }

        // Inventory database (optional)
        if (isset($envConfig['databases']['inventory'])) {
            $dynamicConnections['inventory'] = connection_template($envConfig['databases']['inventory'], $driver);
        }

        // Branch databases (SIS connections)
        foreach ($envConfig['databases']['branches'] ?? [] as $branch) {
            $code = strtolower($branch['code']);

            if (in_array('1', $branch['school_types'] ?? []) && isset($branch['kto12'])) {
                $dynamicConnections[$code . '_kto12'] = connection_template($branch['kto12'], $driver);
            }
            if (in_array('2', $branch['school_types'] ?? []) && isset($branch['college'])) {
                $dynamicConnections[$code . '_college'] = connection_template($branch['college'], $driver);
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
*/

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => array_merge([

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'postgres'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'require'),
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => true,
            ],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'school_finance'),
            'username' => env('DB_USERNAME', 'laravel'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

    ], $dynamicConnections), // <-- Dynamic connections merged here

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_each_run' => true,
    ],

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
