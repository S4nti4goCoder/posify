<?php

require_once __DIR__ . "/../../lib/csrf.guard.php";
require_once __DIR__ . "/../../lib/upload.guard.php";

CsrfGuard::enforce();

define('DIR', __DIR__);

ini_set("display_errors",1);
ini_set("log_errors",1);
ini_set("error_log", DIR."/php_error_log");

require_once "../controllers/template.controller.php";
require_once "../controllers/curl.controller.php";

class FilesController{

	/*=============================================
	Upload files to the servers
	=============================================*/

	public $file;
	public $folder;
	public $token;

	public function ajaxUploadFiles(){

		/*=============================================
		Read the folder data
		=============================================*/

		$url = "folders?linkTo=id_folder&equalTo=".$this->folder;
		$method = "GET";
		$fields = array();

		$folder = CurlController::request($url,$method,$fields);

		if($folder->status == 200){

			$folder = $folder->results[0];

			/*=============================================
			Check the file against the server size limit
			=============================================*/

			if($this->file["size"] > $folder->max_upload_folder){

				$response = array(

					"status" => 404,
					"error" => "Los archivos que pesan más de ".($folder->max_upload_folder/1000000)."MB no suben al servidor ".$folder->name_folder

				);

				echo json_encode($response);

				return;
			}

			/*=============================================
			Read the file extension
			=============================================*/

			/*=============================================
			The extension used to be whatever the browser sent, so a .php file
			landed in a folder Apache serves and runs
			=============================================*/

			if(!UploadGuard::isAllowed($this->file["name"])){

				$response = array(

					"status" => 404,
					"error" => "Ese tipo de archivo no se permite. Admitidos: " . UploadGuard::allowedList()

				);

				echo json_encode($response);

				return;
			}

			$extension = explode(".",$this->file["name"]);

			/*=============================================
			Build the file name
			=============================================*/

			$fileName = UploadGuard::safeName($this->file["name"]);
	
			/*=============================================
			Uploading files to our own server
			=============================================*/

			if($this->folder == 1){

				/*=============================================
				Build the path the file is saved to
				=============================================*/

				$path = "../views/assets/files/".$fileName;

				/*=============================================
				Move the temp file to that path
				=============================================*/

				if(move_uploaded_file($this->file["tmp_name"], $path)){

					/*=============================================
					Save the file data
					=============================================*/

					$url = "files?token=".$this->token."&table=admins&suffix=admin";
					$method = "POST";
					$fields = array(

						"id_folder_file" => $this->folder,
						"extension_file" => end($extension),
						"name_file" => str_replace(".".end($extension), "", $this->file["name"]),
						"type_file" => $this->file["type"],
						"size_file" => $this->file["size"],
						"link_file" =>str_replace("..",$folder->url_folder, $path),
						"date_created_file" => date("Y-m-d")
					);

					$uploadData = CurlController::request($url,$method,$fields);

					if($uploadData->status == 200){

						/*=============================================
						Return the data to javascript
						=============================================*/

						$response = array(

							"status" => 200,
							"id_file" => $uploadData->results->lastId,
							"link" => $fields["link_file"],
							"reduce_link" => TemplateController::reduceText($fields["link_file"],35)."...",
							"date" => $fields["date_created_file"].", ".date("H:m:s")

						);

						echo json_encode($response);

					}

				}

			}
		
		}
	
	}

	/*=============================================
	Total size of the files in a folder
	=============================================*/

	public $idFolder;

	public function updateServer(){

		/*=============================================
		Read every file in the folder
		=============================================*/

		$url = "files?linkTo=id_folder_file&equalTo=".$this->idFolder."&select=size_file";
		$method = "GET";
		$fields = array();

		$files = CurlController::request($url,$method,$fields);

		if($files->status == 200){

			$files = $files->results;
			$totalSize = 0;
			$countFiles = 0;

			foreach ($files as $key => $value) {
				
				$totalSize += $value->size_file;
				$countFiles++;

				if($countFiles == count($files)){

					/*=============================================
					Update folders
					=============================================*/

					$url = 	"folders?id=".$this->idFolder."&nameId=id_folder&token=".$this->token."&table=admins&suffix=admin";
					$method = "PUT";
					$fields = "total_folder=".$totalSize;

					$folders = CurlController::request($url,$method,$fields);

					if($folders->status == 200){

						echo $folders->status;
					}
				}
			}
		}


	}

	/*=============================================
	Delete the file from the server and the database
	=============================================*/

	public $idFileDelete;
	public $idFolderDelete;

	public function deleteFile(){

		/*=============================================
		Read the file data
		=============================================*/

		$url = "files?linkTo=id_file&equalTo=".$this->idFileDelete;
		$method = "GET";
		$fields = array();

		$getFile = CurlController::request($url, $method, $fields);

		if($getFile->status == 200){

			$getFile = $getFile->results[0];

		}

		/*=============================================
		Read the folder data
		=============================================*/

		$url = "folders?linkTo=id_folder&equalTo=".$this->idFolderDelete;

		$getFolder = CurlController::request($url, $method, $fields);

		if($getFolder->status == 200){

			$getFolder = $getFolder->results[0];

		}

		/*=============================================
		Deleting the file from the local server
		=============================================*/

		if($this->idFolderDelete == 1){

			/*=============================================
			Delete the file from the server
			=============================================*/
			unlink(".." . parse_url($getFile->link_file, PHP_URL_PATH));
			
		}

		/*=============================================
		Update the total server capacity
		=============================================*/

		$url = "folders?id=".$this->idFolderDelete."&nameId=id_folder&token=".$this->token."&table=admins&suffix=admin";
		$method = "PUT";
		$fields = "total_folder=".$getFolder->total_folder-$getFile->size_file;

		$updateFolder = CurlController::request($url,$method,$fields);

		/*=============================================
		Delete the record
		=============================================*/

		$url = "files?id=".$this->idFileDelete."&nameId=id_file&token=".$this->token."&table=admins&suffix=admin";
		$method = "DELETE";
		$fields = array();

		$deleteFile = CurlController::request($url,$method,$fields);

		if($updateFolder->status == 200 && $deleteFile->status == 200){

			echo $deleteFile->status;
		}

	}

	/*=============================================
	Rename the file
	=============================================*/

	public $name;
	public $idFile;

	public function updateName(){


		$url = "files?id=".$this->idFile."&nameId=id_file&token=".$this->token."&table=admins&suffix=admin";
		$method = "PUT";
		$fields = "name_file=".$this->name;

		$update = CurlController::request($url,$method,$fields);

		if($update->status == 200){

			echo $update->status;
		} 
	}

	/*=============================================
	Load files
	=============================================*/

	public $search;
	public $sortBy;
	public $filterBy;
	public $arrayFolders;
	public $startAt;
	public $endAt;

	public function loadFiles(){

		$htmlList = "";
		$htmlGrid = "";
		$load = array();

		if(count(json_decode($this->arrayFolders)) == 5){
			
			if($this->filterBy == "ALL"){
		
				if(!empty($this->search)){

					$url = "relations?rel=files,folders&type=file,folder&linkTo=name_file&search=".urlencode($this->search)."&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

				
				}else{

					$url = "relations?rel=files,folders&type=file,folder&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

				}

			}else{

				if(!empty($this->search)){

					$url = "relations?rel=files,folders&type=file,folder&linkTo=name_file,type_file&search=".urlencode($this->search).",".urlencode($this->filterBy)."&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

				
				}else{

					$url = "relations?rel=files,folders&type=file,folder&linkTo=type_file&equalTo=".urlencode($this->filterBy)."&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

				}
			}

			$method = "GET";
			$fields = array();

			$loadFolders = CurlController::request($url,$method,$fields);

			if($loadFolders->status == 200){

				$load = $loadFolders->results;

			}

		}else{

			foreach (json_decode($this->arrayFolders) as $key => $value) {
				
				if($this->filterBy == "ALL"){
		
					if(!empty($this->search)){

						$url = "relations?rel=files,folders&type=file,folder&linkTo=name_file,id_folder&search=".urlencode($this->search).",".$value."&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

					
					}else{

						$url = "relations?rel=files,folders&type=file,folder&linkTo=id_folder&equalTo=".$value."&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

					}

				}else{

					if(!empty($this->search)){

						$url = "relations?rel=files,folders&type=file,folder&linkTo=name_file,type_file,id_folder&search=".urlencode($this->search).",".urlencode($this->filterBy).",".$value."&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

					
					}else{

						$url = "relations?rel=files,folders&type=file,folder&linkTo=type_file,id_folder&equalTo=".urlencode($this->filterBy).",".$value."&orderBy=".explode("-",$this->sortBy)[0]."&orderMode=".explode("-",$this->sortBy)[1]."&startAt=".$this->startAt."&endAt=".$this->endAt;

					}
				}

				$method = "GET";
				$fields = array();

				$loadFolders = CurlController::request($url,$method,$fields);

				if($loadFolders->status == 200){

					$load = array_merge($load, $loadFolders->results);

				}


			}

		}

		$countFiles = 0;

		if(!empty($load)){

			foreach ($load as $key => $value) {

				$countFiles++;

				/*=============================================
				Build the list view
				=============================================*/

				$pathList = TemplateController::returnThumbnailList($value);

				$htmlList .= '<tr style="height:100px">

						<td>
							'.$pathList.'
						</td>

						<td class="align-middle">
							<div class="input-group">
								<input type="text" class="form-control changeName" value="'.$value->name_file.'" idFile="'.$value->id_file.'">
								<span class="input-group-text">.'.$value->extension_file.'</span>
							</div>
						</td>

						<td class="align-middle">'.number_format($value->size_file/1000000,2).' MB</td>

						<td class="align-middle">
							<span class="badge bg-dark rounded px-3 py-2 text-white">'.$value->name_folder.'</span>
						</td>

						<td class="align-middle">
							<a href="'.$value->link_file.'" target="_blank">
								'.TemplateController::reduceText($value->link_file,35).'...
								<i class="bi bi-box-arrow-up-right ps-2 btn"></i>
							</a>
						</td>

						<td class="align-middle">'.$value->date_updated_file.'</td>

						<td class="align-middle">
						  <svg class="bi bi-copy copyLink" copy="'.$value->link_file.'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="cursor:pointer">
							  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
							</svg>
						  <i class="bi bi-trash ps-2 btn deleteFile" idFile="'.$value->id_file.'" idFolder="'.$value->id_folder.'" mode="list"></i>
						</td>

					</tr>';

				/*=============================================
				Build the grid view
				=============================================*/

				$pathGrid = TemplateController::returnThumbnailGrid($value);

				$htmlGrid .= '<div class="col">
	 			
				 			<div class="card rounded p-3 border-0 shadow my-3">
				 				
				 				<div class="card-header bg-white border-0 p-0">
				 					
				 					<div class="d-flex justify-content-between mb-2">
				 						
				 						<div class="bg-white">
				 							<a href="'.$value->link_file.'" target="_blank">
											<i class="bi bi-box-arrow-up-right ps-2 btn p-0"></i>
											</a>
										</div>

										<div class="bg-white m-0">
											<svg  class="bi bi-copy copyLink" copy="'.$value->link_file.'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="cursor:pointer">
												<path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
											</svg>
											<i class="bi bi-trash p-0 ps-2 btn deleteFile" idFile="'.$value->id_file.'" idFolder="'.$value->id_folder.'" mode="grid"></i>
										</div>

				 					</div>
				 				</div>

				 				'.$pathGrid.'

				 				<div class="card-body p-1">
				 					
				 					<p class="card-text">
				 						
				 						<div class="input-group">
											<input type="text" class="form-control changeName" value="'.$value->name_file.'" idFile="'.$value->id_file.'">
											<span class="input-group-text">.'.$value->extension_file.'</span>
										</div>

										<div class="d-flex justify-content-between mt-3">

											<div class="bg-white">
												<p class="small mt-1">'.number_format($value->size_file/1000000,2).' MB</p>
											</div>

											<div class="bg-white m-0">
												<span class="badge bg-dark border rounded px-3 py-2 text-white">'.$value->name_folder.'</span>
											</div>
										</div>

										<h6 class="float-end my-0 py-0">
											<small>'.$value->date_updated_file.'</small>
										</h6>

				 					</p>

				 				</div>

				 			</div>

				 		</div>';

				/*=============================================
				End of the foreach
				=============================================*/

				if($countFiles == count($load)){

					$response = array(

						"htmlList" => $htmlList,
						"htmlGrid" => $htmlGrid

					);

					echo json_encode($response);

				}
			}

		}else{

			$response = array(

				"htmlList" => $htmlList,
				"htmlGrid" => $htmlGrid

			);

			echo json_encode($response);

		}

	}


}

/*=============================================
Upload files to the servers
=============================================*/

if(isset($_FILES["file"])){

	$ajax = new FilesController();
	$ajax -> file = $_FILES["file"];
	$ajax -> folder  = $_POST["folder"];
	$ajax -> token = Session::token();
	$ajax -> ajaxUploadFiles();

}

/*=============================================
Total size of the files in a folder
=============================================*/

if(isset($_POST["idFolder"])){

	$ajax = new FilesController();
	$ajax -> idFolder  = $_POST["idFolder"];
	$ajax -> token = Session::token();
	$ajax -> updateServer();

}

/*=============================================
Delete the file from the server and the database
=============================================*/

if(isset($_POST["idFolderDelete"])){

	$ajax = new FilesController();
	$ajax -> idFileDelete  = $_POST["idFileDelete"];
	$ajax -> idFolderDelete  = $_POST["idFolderDelete"];
	$ajax -> token = Session::token();
	$ajax -> deleteFile();

}

/*=============================================
Rename the file
=============================================*/

if(isset($_POST["name"])){

	$ajax = new FilesController();
	$ajax -> name  = $_POST["name"];
	$ajax -> idFile  = $_POST["idFile"];
	$ajax -> token = Session::token();
	$ajax -> updateName();

}

/*=============================================
Load files
=============================================*/

if(isset($_POST["search"])){

	$ajax = new FilesController();
	$ajax -> search = $_POST["search"];
	$ajax -> sortBy = $_POST["sortBy"];
	$ajax -> filterBy = $_POST["filterBy"];
	$ajax -> arrayFolders = $_POST["arrayFolders"];
	$ajax -> startAt = $_POST["startAt"];
	$ajax -> endAt = $_POST["endAt"];
	$ajax -> loadFiles();

}


