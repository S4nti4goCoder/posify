<?php 

require_once "models/delete.model.php";

class DeleteController{

	/*=============================================
	DELETE to remove data
	=============================================*/

	static public function deleteData($table, $id, $nameId){

		$response = DeleteModel::deleteData($table, $id, $nameId);
		
		$return = new DeleteController();
		$return -> fncResponse($response);

	}

	/*=============================================
	Controller responses
	=============================================*/

	public function fncResponse($response){

		// the row is still referenced, which is not a failure to hide
		if(isset($response["error"]) && $response["error"] === "in_use"){

			$json = array(

				'status' => 409,
				'results' => $response["child"] ?? ""

			);

		}else if(!empty($response)){

			$json = array(

				'status' => 200,
				'results' => $response

			);

		}else{

			$json = array(

				'status' => 404,
				'results' => 'Not Found',
				'method' => 'delete'

			);

		}

		echo json_encode($json, http_response_code($json["status"]));

	}

}