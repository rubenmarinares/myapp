<?php
// Con el servidor de desarrollo de PHP (php -S localhost:8080 -t public public/index.php) los ficheros estáticos se sirven tal cual.
if (PHP_SAPI === 'cli-server') {
    $file = realpath(__DIR__ . (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($file !== false && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR) && is_file($file)) {
        return false;
    }
}

require is_file(__DIR__ . '/../vendor/autoload.php') ? __DIR__ . '/../vendor/autoload.php' : __DIR__ . '/../../vendor/autoload.php';

Nucleo\Core\Boot::run(dirname(__DIR__));
