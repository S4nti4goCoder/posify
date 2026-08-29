<?php 

require_once __DIR__ . "/../../lib/password.hasher.php";

require_once "models/get.model.php";
require_once "models/post.model.php";
require_once "models/connection.php";

require_once "vendor/autoload.php";
use Firebase\JWT\JWT;

require_once "models/put.model.php";

class PostController{

	/*=============================================
	POST to create data
	=============================================*/

	static public function postData($table, $data){

		$response = PostModel::postData($table, $data);
		
		$return = new PostController();
		$return -> fncResponse($response,null,null);

	}

	/*=============================================
	POST to register a user
	=============================================*/

	static public function postRegister($table, $data, $suffix){

		if(isset($data["password_".$suffix]) && $data["password_".$suffix] != null){

			$data["password_".$suffix] = PasswordHasher::hash((string) $data["password_".$suffix]);

			$response = PostModel::postData($table, $data);

			$return = new PostController();
			$return -> fncResponse($response,null,$suffix);

		}else{

			/*=============================================
			A password is mandatory to register
			=============================================*/

			$return = new PostController();
			$return -> fncResponse(null, "Password required", $suffix);

			return;

		}

	}

	/*=============================================
	POST to log a user in
	=============================================*/

	static public function postLogin($table, $data, $suffix){

		/*=============================================
		The user must exist
		=============================================*/

		$response = GetModel::getDataFilter($table, "*", "email_".$suffix, $data["email_".$suffix], null,null,null,null);
		
		if(!empty($response)){	

			if($response[0]->{"password_".$suffix} != null)	{

				$stored = (string) $response[0]->{"password_".$suffix};
				$plain  = (string) ($data["password_".$suffix] ?? "");

				if(PasswordHasher::verify($plain, $stored)){

					/*=============================================
					Upgrade hashes still on the old fixed salt
					=============================================*/

					if(PasswordHasher::needsRehash($stored)){

						PutModel::putData(
							$table,
							array("password_".$suffix => PasswordHasher::hash($plain)),
							$response[0]->{"id_".$suffix},
							"id_".$suffix
						);

					}

					$token = Connection::jwt($response[0]->{"id_".$suffix}, $response[0]->{"email_".$suffix});

					$jwt = JWT::encode($token, Config::requireSecret("jwt_secret"));

					/*=============================================
					Store the token
					=============================================*/

					$data = array(

						"token_".$suffix => $jwt,
						"token_exp_".$suffix => $token["exp"]

					);

					$update = PutModel::putData($table, $data, $response[0]->{"id_".$suffix}, "id_".$suffix);

					if(isset($update["comment"]) && $update["comment"] == "The process was successful" ){

						$response[0]->{"token_".$suffix} = $jwt;
						$response[0]->{"token_exp_".$suffix} = $token["exp"];

						$return = new PostController();
						$return -> fncResponse($response, null,$suffix);

					}
					
					
				}else{

					$response = null;
					$return = new PostController();
					$return -> fncResponse($response, "invalid_credentials",$suffix);

				}

			}else{

				/*=============================================
				No password stored, so nothing to verify against.
				Same error as a wrong password, to avoid enumeration.
				=============================================*/

				$response = null;
				$return = new PostController();
				$return -> fncResponse($response, "invalid_credentials",$suffix);

			}

		}else{

			/*=============================================
			No such address. The same answer as a wrong password, and the
			same time spent, so neither the text nor the delay says which
			addresses are registered.
			=============================================*/

			PasswordHasher::burn((string) ($data["password_".$suffix] ?? ""));

			$response = null;
			$return = new PostController();
			$return -> fncResponse($response, "invalid_credentials",$suffix);

		}


	}

	/*=============================================
	Controller responses
	=============================================*/

	public function fncResponse($response,$error,$suffix){

		if(!empty($response)){

			/*=============================================
			Never return the password
			=============================================*/

			if(isset($response[0]->{"password_".$suffix})){

				unset($response[0]->{"password_".$suffix});

			}

			$json = array(

				'status' => 200,
				'results' => $response

			);

		}else{

			if($error != null){

				$json = array(
					'status' => 400,
					"results" => $error
				);

			}else{

				$json = array(

					'status' => 404,
					'results' => 'Not Found',
					'method' => 'post'

				);
			}

		}

		echo json_encode($json, http_response_code($json["status"]));

	}

}