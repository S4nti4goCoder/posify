<?php

/**
 * Included from routes/routes.php, which defines the variables below.
 *
 * @var string $table Table name from the first URI segment
 */

require_once "models/connection.php";
require_once "controllers/put.controller.php";

if(isset($_GET["id"]) && isset($_GET["nameId"])){

	/*=============================================
	Read the submitted body
	=============================================*/

	$data = array();
	
	parse_str(RequestContext::body(), $data);
		
	/*=============================================
	Collect the submitted column names
	=============================================*/

	$columns = array();
		
	foreach (array_keys($data) as $key => $value) {

		array_push($columns, $value);
		
	}

	array_push($columns, $_GET["nameId"]);

	$columns = array_unique($columns);

	/*=============================================
	Check the table and its columns
	=============================================*/

	if(empty(Connection::getColumnsData($table, $columns))){

		$json = array(
		 	'status' => 400,
		 	'results' => "Error: Fields in the form do not match the database"
		);

		echo json_encode($json, http_response_code($json["status"]));

		return;

	}

	if(isset($_GET["token"])){

		/*=============================================
		PUT without a session token
		=============================================*/

		if($_GET["token"] == "no" && isset($_GET["except"])){

			/*=============================================
			Check the table and its columns
			=============================================*/

			$columns = array($_GET["except"]);

			if(empty(Connection::getColumnsData($table, $columns))){

				$json = array(
				 	'status' => 400,
				 	'results' => "Error: Fields in the form do not match the database"
				);

				echo json_encode($json, http_response_code($json["status"]));

				return;

			}

			/*=============================================
			No session token here, so only allowlisted columns are writable
			=============================================*/

			if(!Connection::isUnauthenticatedWriteAllowed($table, $_GET["except"], array_keys($data))){

				$json = array(
					'status' => 403,
					'results' => "Error: This table cannot be written without authentication"
				);

				echo json_encode($json, http_response_code($json["status"]));

				return;

			}

			/*=============================================
			Write to any table
			=============================================*/

			$response = new PutController();
			$response -> putData($table,$data,$_GET["id"],$_GET["nameId"]);
			
		/*=============================================
		PUT for authenticated users
		=============================================*/

		}else{

			$tableToken = $_GET["table"] ?? "users";
			$suffix = $_GET["suffix"] ?? "user";

			$validate = Connection::tokenValidate($_GET["token"],$tableToken,$suffix);

			/*=============================================
			Update any table
			=============================================*/		

			if($validate == "ok"){
				
				$response = new PutController();
				$response -> putData($table,$data,$_GET["id"],$_GET["nameId"]);

			}

			/*=============================================
			Token expired
			=============================================*/	

			if($validate == "expired"){

				$json = array(
				 	'status' => 303,
				 	'results' => "Error: The token has expired"
				);

				echo json_encode($json, http_response_code($json["status"]));

				return;

			}

			/*=============================================
			Token not found
			=============================================*/	

			if($validate == "no-auth"){

				$json = array(
				 	'status' => 400,
				 	'results' => "Error: The user is not authorized"
				);

				echo json_encode($json, http_response_code($json["status"]));

				return;

			}

		}

	/*=============================================
	No token sent
	=============================================*/	

	}else{

		$json = array(
		 	'status' => 400,
		 	'results' => "Error: Authorization required"
		);

		echo json_encode($json, http_response_code($json["status"]));

		return;	

	}	


}
