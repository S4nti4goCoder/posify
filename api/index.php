<?php

/*=============================================
Errors go to the log, not to the client, unless debug is on
=============================================*/

define('DIR',__DIR__);

require_once __DIR__ . "/../config/config.php";

ini_set("display_errors", Config::isDebug() ? "1" : "0");
ini_set("log_errors", "1");
ini_set("error_log", DIR."/php_error_log");

/*=============================================
CORS: only the CMS origin is trusted
=============================================*/

header('Access-Control-Allow-Origin: '.Config::get('cms_origin'));
header('Vary: Origin');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');

/*=============================================
Requirements
=============================================*/

require_once "controllers/routes.controller.php";

$index = new RoutesController();
$index -> index();