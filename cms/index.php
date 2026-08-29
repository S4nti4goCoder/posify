<?php 

/*=============================================
Errors go to the log, not to the browser, unless debug is on
=============================================*/

define('DIR',__DIR__);

require_once __DIR__ . "/../config/config.php";

ini_set("display_errors", Config::isDebug() ? "1" : "0");
ini_set("log_errors", "1");
ini_set("error_log", DIR."/php_error_log");

/*=============================================
Requirements
=============================================*/

require_once __DIR__ . "/../lib/office.guard.php";
require_once __DIR__ . "/../lib/csrf.guard.php";

CsrfGuard::enforce();

require_once "controllers/template.controller.php";
require_once "controllers/curl.controller.php";
require_once "extensions/vendor/autoload.php";

/*=============================================
Template
=============================================*/

$index = new TemplateController();
$index -> index();

?>