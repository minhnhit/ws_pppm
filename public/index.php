<?php

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$codename = getenv('CODENAME');
if($codename == 'B1') {
    defined('MONGO_DB_PAYMENT_SOURCE') || define('MONGO_DB_PAYMENT_SOURCE', 'b1_payment');
}

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
call_user_func(function () {
    $dotenv = new \Dotenv\Dotenv(dirname(__DIR__));
    $dotenv->load();

    /** @var \Interop\Container\ContainerInterface $container */
    $container = require 'config/container.php';

    /** Logger */
    \LosMiddleware\LosLog\ExceptionLogger::registerHandlers('exception.log');

    /** @var \Zend\Expressive\Application $app */
    $app = $container->get(\Zend\Expressive\Application::class);

    // Import programmatic/declarative middleware pipeline and routing
    // configuration statements
    require 'config/pipeline.php';
    require 'config/routes.php';

    $app->run();
});
