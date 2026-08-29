<?php

/**
 * Included from routes/routes.php, which defines the variables below.
 *
 * @var string $table Table name from the first URI segment
 */

require_once "models/connection.php";
require_once "controllers/post.controller.php";

if(isset($_POST)){

	/*=============================================
	Collect the submitted column names
	=============================================*/

	$columns = array();
	
	foreach (array_keys($_POST) as $key => $value) {

		array_push($columns, $value);
			
	}

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

	$response = new PostController();

	/*=============================================
	POST to register a user
	=============================================*/	

	if(isset($_GET["register"]) && $_GET["register"] == true){

		$suffix = $_GET["suffix"] ?? "user";

		$response -> postRegister($table,$_POST,$suffix);

	/*=============================================
	POST to log a user in
	=============================================*/	

	}else if(isset($_GET["login"]) && $_GET["login"] == true){

		$suffix = $_GET["suffix"] ?? "user";

		$response -> postLogin($table,$_POST,$suffix);

	}else{


		if(isset($_GET["token"])){

			/*=============================================
			POST without a session token
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

				if(!Connection::isUnauthenticatedWriteAllowed($table, $_GET["except"], array_keys($_POST))){

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

				$response -> postData($table,$_POST);

			/*=============================================
			POST for authenticated users
			=============================================*/

			}else{

				$tableToken = $_GET["table"] ?? "users";
				$suffix = $_GET["suffix"] ?? "user";

				$validate = Connection::tokenValidate($_GET["token"],$tableToken,$suffix);

				/*=============================================
				Write to any table
				=============================================*/		

				if($validate == "ok"){
		
					$response -> postData($table,$_POST);

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

}
