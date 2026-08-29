<?php

/**
 * Included from routes/routes.php, which defines the variables below.
 *
 * @var string $table Table name from the first URI segment
 */

require_once "controllers/get.controller.php";
require_once "models/connection.php";

$select = $_GET["select"] ?? "*";
$orderBy = $_GET["orderBy"] ?? null;
$orderMode = $_GET["orderMode"] ?? null;
$startAt = $_GET["startAt"] ?? null;
$endAt = $_GET["endAt"] ?? null;
$filterTo = $_GET["filterTo"] ?? null;
$inTo = $_GET["inTo"] ?? null;

$response = new GetController();

/*=============================================
GET with filter
=============================================*/

if(isset($_GET["linkTo"]) && isset($_GET["equalTo"]) && !isset($_GET["rel"]) && !isset($_GET["type"]) ){

	$response -> getDataFilter($table, $select,$_GET["linkTo"],$_GET["equalTo"],$orderBy,$orderMode,$startAt,$endAt);

/*=============================================
GET across related tables
=============================================*/

}else if(isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && !isset($_GET["linkTo"]) && !isset($_GET["equalTo"])){

	$response -> getRelData($_GET["rel"],$_GET["type"],$select,$orderBy,$orderMode,$startAt,$endAt);
	
/*=============================================
GET across related tables, with filter
=============================================*/

}else if(isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && isset($_GET["linkTo"]) && isset($_GET["equalTo"])){

	$response -> getRelDataFilter($_GET["rel"],$_GET["type"],$select,$_GET["linkTo"],$_GET["equalTo"],$orderBy,$orderMode,$startAt,$endAt);

/*=============================================
GET for the search box
=============================================*/

}else if(!isset($_GET["rel"]) && !isset($_GET["type"]) && isset($_GET["linkTo"]) && isset($_GET["search"])){

	$response -> getDataSearch($table, $select,$_GET["linkTo"],$_GET["search"],$orderBy,$orderMode,$startAt,$endAt);

/*=============================================
GET for the search box, across related tables
=============================================*/

}else if(isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && isset($_GET["linkTo"]) && isset($_GET["search"])){


	$response -> getRelDataSearch($_GET["rel"],$_GET["type"],$select,$_GET["linkTo"],$_GET["search"],$orderBy,$orderMode,$startAt,$endAt);

/*=============================================
GET by range
=============================================*/

}else if(!isset($_GET["rel"]) && !isset($_GET["type"]) && isset($_GET["linkTo"]) && isset($_GET["between1"]) && isset($_GET["between2"])){

	$response -> getDataRange($table,$select,$_GET["linkTo"],$_GET["between1"],$_GET["between2"],$orderBy,$orderMode,$startAt,$endAt, $filterTo, $inTo);

/*=============================================
GET by range, across related tables
=============================================*/

}else if(isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && isset($_GET["linkTo"]) && isset($_GET["between1"]) && isset($_GET["between2"])){

	$response -> getRelDataRange($_GET["rel"],$_GET["type"],$select,$_GET["linkTo"],$_GET["between1"],$_GET["between2"],$orderBy,$orderMode,$startAt,$endAt, $filterTo, $inTo);

}else{

	/*=============================================
	GET without filter
	=============================================*/

	$response -> getData($table, $select,$orderBy,$orderMode,$startAt,$endAt);


}









