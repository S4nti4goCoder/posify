<?php

require_once "models/request.context.php";
require_once "models/connection.php";
require_once "controllers/get.controller.php";

$routesArray = explode("/", $_SERVER['REQUEST_URI']);
$routesArray = array_filter($routesArray);

/*=============================================
No request was made to the API
=============================================*/

if(count($routesArray) == 0){

	$json = array(

		'status' => 404,
		'results' => 'Not Found'

	);

	echo json_encode($json, http_response_code($json["status"]));

	return;

}

/*=============================================
A request was made to the API
=============================================*/

if(count($routesArray) == 1 && isset($_SERVER['REQUEST_METHOD'])){

	$table = explode("?", $routesArray[1])[0];

	/*=============================================
	Check the secret key
	=============================================*/

	if(RequestContext::authorization() !== Connection::apikey()){

		if(in_array($table, Connection::publicAccess()) == 0){
	
			$json = array(
		
				'status' => 400,
				"results" => "You are not authorized to make this request"
			);

			echo json_encode($json, http_response_code($json["status"]));

			return;

		}else{

			/*=============================================
			Public access
			=============================================*/
			$response = new GetController();
			$response -> getData($table, "*",null,null,null,null);

			return;
		}
	
	}

	/*=============================================
	GET requests
	=============================================*/

	if($_SERVER['REQUEST_METHOD'] == "GET"){

		include "services/get.php";

	}

	/*=============================================
	POST requests
	=============================================*/

	if($_SERVER['REQUEST_METHOD'] == "POST"){

		include "services/post.php";

	}

	/*=============================================
	PUT requests
	=============================================*/

	if($_SERVER['REQUEST_METHOD'] == "PUT"){

		include "services/put.php";

	}

	/*=============================================
	DELETE requests
	=============================================*/

	if($_SERVER['REQUEST_METHOD'] == "DELETE"){

		include "services/delete.php";

	}

}


