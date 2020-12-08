<?php

require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/application/lib/Dev.php';

use application\core\Router;

session_start();

$router = new Router();
$router->run();