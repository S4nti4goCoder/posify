<?php

require_once __DIR__ . "/../../lib/csrf.guard.php";

CsrfGuard::enforce();

require_once "../controllers/curl.controller.php";

class PagesAjax{

	/*=============================================
	Reorder the page
	=============================================*/ 

	public $idPage;
	public $index; 
	public $token; 

	public function updatePageOrder(){

		$url = "pages?id=".base64_decode($this->idPage)."&nameId=id_page&token=".$this->token."&table=admins&suffix=admin";
		$method = "PUT";
		$fields = "order_page=".$this->index;

		$updateOrder = CurlController::request($url,$method,$fields);

		if($updateOrder->status == 200){

			echo $updateOrder->status;
		
		}

	}

	/*=============================================
	Delete a page
	=============================================*/ 

	public $idPageDelete;

	public function deletePage(){

		/*=============================================
		Check the modules linked to the page
		=============================================*/

		$url = "modules?linkTo=id_page_module&equalTo=".base64_decode($this->idPageDelete);
		$method = "GET";
		$fields = array();

		$getModule = CurlController::request($url,$method,$fields);

		if($getModule->status == 200){

			echo "error";
		
		}else{

			$url = "pages?id=".base64_decode($this->idPageDelete)."&nameId=id_page&token=".$this->token."&table=admins&suffix=admin";
			$method = "DELETE";
			$fields = array();

			$deletePage = CurlController::request($url,$method,$fields);

			if($deletePage->status == 200){

				echo $deletePage->status;
			}

		}

	}

}

if(isset($_POST["idPage"])){

	$ajax = new PagesAjax();
	$ajax -> idPage = $_POST["idPage"];
	$ajax -> index = $_POST["index"];
	$ajax -> token = Session::token();
	$ajax -> updatePageOrder();
}



if(isset($_POST["idPageDelete"])){

	$ajax = new PagesAjax();
	$ajax -> idPageDelete = $_POST["idPageDelete"];
	$ajax -> token = Session::token();
	$ajax -> deletePage();
}