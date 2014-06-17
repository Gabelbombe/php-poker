<?php
error_reporting(-1);
ini_set('display_errors', 1);

putenv('APP_ENV=dev');

define('ENV_MODE', (getenv('ENV_MODE')?getenv('ENV_MODE'):'Development'));
define('ENV_FILE', dirname(__DIR__) . '/config/' . (getenv('APP_ENV') ?: 'base') . '.json');
define('APP_PATH', dirname(__DIR__));


require APP_PATH . '/src/Helpers/AutoLoader.php';

$autoLoader = New AutoLoader(APP_PATH . '/src/');

$autoLoader->registerNamespaces()
           ->registerGenericNamespace('Helpers')
           ->registerGenericNamespace('Database')
           ->registerGenericNamespace('Poker');

$payload =
[
    'type' => (! isset($argv) ?: 0),
    'args' => (! isset($argv) ? $_GET : $argv),
];

$bootstrap = New \Helpers\Bootstrap($payload);
$bootstrap->run();