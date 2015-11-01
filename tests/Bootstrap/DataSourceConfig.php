<?php
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => env('DOCTRINE_DRIVER', 'Doctrine\DBAL\Driver\PDOMySql\Driver'),
                'params' => array(
                    'host' => env('DOCTRINE_HOST', 'mysql'),
                    'port' => env('DOCTRINE_PORT', '3306'),
                    'user' => env('DOCTRINE_USER', 'root'),
                    'password' => env('DOCTRINE_PASSWORD', 'toor'),
                    'dbname' => env('DOCTRINE_DATABASE', 'ci_tests'),
                )
            )
        )
    ),
);