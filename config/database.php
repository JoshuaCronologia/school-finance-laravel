<?php

use Illuminate\Support\Str;

$db_config = [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => true,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
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

/*
|--------------------------------------------------------------------------
| Dynamic Connection Registration from creds.json
|--------------------------------------------------------------------------
*/

$cred_path    = str_replace('config', 'creds.json', __DIR__);
$config_key   = env('DB_CONFIG_KEY', 'local');

if (file_exists($cred_path)) {
    $json_config = json_decode(file_get_contents($cred_path), true);

    if ($json_config && array_key_exists($config_key, $json_config)) {
        $config = $json_config[$config_key];
        $driver = $config['driver'] ?? env('DB_CONNECTION', 'mysql');

        // Main accounting database
        $db_config['connections']['mysql'] = connection_template($config['databases']['main'], $driver);

        // Inventory database (optional)
        if (isset($config['databases']['inventory'])) {
            $db_config['connections']['inventory'] = connection_template($config['databases']['inventory'], $driver);
        }

        // Finance database (optional)
        if (isset($config['databases']['finance'])) {
            $db_config['connections']['finance'] = connection_template($config['databases']['finance'], $driver);
        }

        // Branch databases (SIS)
        foreach ($config['databases']['branches'] ?? [] as $branch) {
            $code = $branch['code'];
            if (in_array('1', $branch['school_types'] ?? [])) {
                $db_config['connections'][$code . '_kto12'] = connection_template($branch['kto12'], $driver);
            }
            if (in_array('2', $branch['school_types'] ?? []) && isset($branch['college'])) {
                $db_config['connections'][$code . '_college'] = connection_template($branch['college'], $driver);
            }
        }
    }
}

function connection_template($config, $driver = 'mysql')
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
        'url'            => env('DATABASE_URL'),
        'host'           => $config['host'],
        'port'           => $config['port'] ?? 3306,
        'database'       => $config['database'],
        'username'       => $config['username'],
        'password'       => $config['password'],
        'unix_socket'    => env('DB_SOCKET', ''),
        'charset'        => 'utf8mb4',
        'collation'      => 'utf8mb4_unicode_ci',
        'prefix'         => '',
        'prefix_indexes' => true,
        'strict'         => false,
        'engine'         => null,
        'options'        => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
    ];
}

return $db_config;
