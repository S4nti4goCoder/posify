<?php

/**
 * Included from routes/routes.php, which defines the variables below.
 *
 * @var string $table Table name from the first URI segment
 */

require_once "models/connection.php";
require_once "controllers/delete.controller.php";

if(isset($_GET["id"]) && isset($_GET["nameId"])){

	$columns = array($_GET["nameId"]);

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

	/*=============================================
	DELETE for authenticated users
	=============================================*/

	if(isset($_GET["token"])){

		if($_GET["token"] == "no" && isset($_GET["except"])){

			/*=============================================
			Nothing in the CMS deletes without a session token
			=============================================*/

			$json = array(
				'status' => 403,
				'results' => "Error: Delete requires authentication"
			);

			echo json_encode($json, http_response_code($json["status"]));

			return;	


		}else{

			$tableToken = $_GET["table"] ?? "users";
			$suffix = $_GET["suffix"] ?? "user";

			$validate = Connection::tokenValidate($_GET["token"],$tableToken,$suffix);

			/*=============================================
			Delete from any table
			=============================================*/	
				
			if($validate == "ok"){
		
				$response = new DeleteController();
				$response -> deleteData($table,$_GET["id"],$_GET["nameId"]);

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

