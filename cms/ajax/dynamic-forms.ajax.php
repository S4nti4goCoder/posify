<?php

require_once __DIR__ . "/../../lib/csrf.guard.php";
require_once __DIR__ . "/../../lib/permission.guard.php";

CsrfGuard::enforce();

require_once "../controllers/curl.controller.php";

class DynamicFormsController{

	public $matrix_column;
	public $id_column;
	public $pre_value;
	public $token;

	public function updateMatrixColumn(){

		$url = "columns?id=".$this->id_column."&nameId=id_column&token=".$this->token."&table=admins&suffix=admin";
		$method = "PUT";
		$fields = "matrix_column=".$this->matrix_column;

		$updateMatrix = CurlController::request($url,$method,$fields);

		if($updateMatrix->status == 200){

			$url = "columns?linkTo=id_column&equalTo=".$this->id_column."&select=matrix_column";
			$method = "GET";
			$fields = array();

			$getMatrix = CurlController::request($url,$method,$fields);

			if($getMatrix->status == 200){

				$html = "";
				$count = 0;

				foreach (explode(",",$getMatrix->results[0]->matrix_column) as $key => $value) {

					$selected = "";

					if($this->pre_value != null){

						if($value == $this->pre_value){

							$selected = "selected";
						}
					}
					
					$html .= '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';
					
					$count++;

					if($count == count(explode(",",$getMatrix->results[0]->matrix_column))){

						echo $html;
					}

				}

			}

		}

	}

	/*=============================================
	Return the table data
	=============================================*/

	public $table;

	public function getTable(){

		$url = "columns?id=".$this->id_column."&nameId=id_column&token=".$this->token."&table=admins&suffix=admin";
		$method = "PUT";
		$fields = "matrix_column=".$this->table;

		$updateMatrix = CurlController::request($url,$method,$fields);

		if($updateMatrix->status == 200){

			$url = $this->table;
			$method = "GET";
			$fields = array();

			$columns = CurlController::request($url,$method,$fields);

			if($columns->status == 200){

				echo json_encode($columns->results);
			
			}

		}
	}


	/*=============================================
	Update the matrix and return the ChatGPT reply
	=============================================*/

	public $matrix_prompt;
	public $id_prompt;

	public function updateMatrixPrompt(){

		$url = "columns?id=".$this->id_prompt."&nameId=id_column&token=".$this->token."&table=admins&suffix=admin";
		$method = "PUT";
		$fields = "matrix_column=".$this->matrix_prompt;

		$updateMatrix = CurlController::request($url,$method,$fields);

		if($updateMatrix->status == 200 && !empty($this->matrix_prompt)){

			/*=============================================
			Read the administrator data
			=============================================*/

			$url = "admins?linkTo=token_admin&equalTo=".$this->token."&select=chatgpt_admin";
			$method = "GET";
			$fields = array();

			$admin = CurlController::request($url,$method,$fields);

			if($admin->status == 200){

				$content = $this->matrix_prompt;
				$token = json_decode($admin->results[0]->chatgpt_admin)->token;	
				$org = json_decode($admin->results[0]->chatgpt_admin)->org;		

				$chatGPT = CurlController::chatGPT($content,$token,$org);
				
				echo $chatGPT;
			}

		}


	}
}

/*=============================================
POST variables
=============================================*/ 

if(isset($_POST["matrix_column"])){

	$ajax = new DynamicFormsController();
	$ajax -> matrix_column = $_POST["matrix_column"];
	$ajax -> id_column = $_POST["id_column"];
	$ajax -> pre_value = $_POST["pre_value"];
	$ajax -> token = Session::token();
	$ajax -> updateMatrixColumn(); 

}

if(isset($_POST["table"])){

	$ajax = new DynamicFormsController();
	PermissionGuard::enforce($_POST["table"]);

	$ajax -> table = $_POST["table"];
	$ajax -> id_column = $_POST["id_column"];
	$ajax -> token = Session::token();
	$ajax -> getTable(); 

}

if(isset($_POST["matrix_prompt"])){

	$ajax = new DynamicFormsController();
	$ajax -> matrix_prompt = $_POST["matrix_prompt"];
	$ajax -> id_prompt = $_POST["id_prompt"];
	$ajax -> token = Session::token();
	$ajax -> updateMatrixPrompt(); 

}
