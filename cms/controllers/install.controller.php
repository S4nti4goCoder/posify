<?php

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../lib/password.policy.php";
require_once __DIR__ . "/../../lib/view.php";

class InstallController{

	/*=============================================
	Database credentials from config/config.local.php
	=============================================*/

	static public function infoDatabase(){

		$infoDB = array(
			"host" => Config::get("db_host"),
			"database" => Config::get("db_name"),
			"user" => Config::requireSecret("db_user"),
			"pass" => Config::requireSecret("db_password")
		);

		return $infoDB;

	}

	/*=============================================
	Database connection
	=============================================*/

	static public function connect(){

		try{

			$link = new PDO("mysql:host=".InstallController::infoDatabase()["host"].";dbname=".InstallController::infoDatabase()["database"],
		                    InstallController::infoDatabase()["user"],
		                    InstallController::infoDatabase()["pass"]
		                );

			$link->exec("set names utf8");

		}catch(PDOException $e){

			die("Error: ".$e->getMessage());
		}

		return $link;

	}

	/*=============================================
	System install
	=============================================*/

	public function install(){

		if(isset($_POST["email_admin"])){

			echo '<script>
					fncMatPreloader("on");
					fncSweetAlert("loading", "Instalando...", "");
				</script>';

			$failed = PasswordPolicy::check(trim((string) $_POST["password_admin"]));

			if($failed !== []){

				echo '<script>
						fncMatPreloader("off");
						fncSweetAlert("error", ' . View::js(PasswordPolicy::message($failed)) . ', "");
					</script>';

				return;
			}

			/*=============================================
			Create the admins table
			=============================================*/
			
			$sqlAdmins = "CREATE TABLE admins ( 
				id_admin INT NOT NULL AUTO_INCREMENT,
				email_admin TEXT NULL DEFAULT NULL,
				password_admin TEXT NULL DEFAULT NULL, 
				rol_admin TEXT NULL DEFAULT NULL,
				permissions_admin TEXT NULL DEFAULT NULL, 
				token_admin TEXT NULL DEFAULT NULL,
				token_exp_admin TEXT NULL DEFAULT NULL,
				status_admin INT NULL DEFAULT '1',
				title_admin TEXT NULL DEFAULT NULL,  
				symbol_admin TEXT NULL DEFAULT NULL,
				font_admin TEXT NULL DEFAULT NULL,
				color_admin TEXT NULL DEFAULT NULL,
				back_admin TEXT NULL DEFAULT NULL, 
				scode_admin TEXT NULL DEFAULT NULL, 
				chatgpt_admin TEXT NULL DEFAULT NULL, 
				date_created_admin DATE NULL DEFAULT NULL,
				date_updated_admin TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id_admin))";

			$stmtAdmins = InstallController::connect()->prepare($sqlAdmins);

			/*=============================================
			Create the pages table
			=============================================*/
			
			$sqlPages = "CREATE TABLE pages ( 
				id_page INT NOT NULL AUTO_INCREMENT,
				title_page TEXT NULL DEFAULT NULL,
				url_page TEXT NULL DEFAULT NULL,
				icon_page TEXT NULL DEFAULT NULL,
				type_page TEXT NULL DEFAULT NULL,
				order_page INT NULL DEFAULT '1',
				date_created_page DATE NULL DEFAULT NULL,
				date_updated_page TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id_page))";

			$stmtPages = InstallController::connect()->prepare($sqlPages);

			/*=============================================
			Create the modules table
			=============================================*/
			
			$sqlModules = "CREATE TABLE modules ( 
				id_module INT NOT NULL AUTO_INCREMENT,
				id_page_module INT NULL DEFAULT '0',
				type_module TEXT NULL DEFAULT NULL,
				title_module TEXT NULL DEFAULT NULL,
				suffix_module TEXT NULL DEFAULT NULL,
				content_module TEXT NULL DEFAULT NULL,
				width_module INT NULL DEFAULT '100',
				editable_module INT NULL DEFAULT '1',
				date_created_module DATE NULL DEFAULT NULL,
				date_updated_module TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id_module))";

			$stmtModules = InstallController::connect()->prepare($sqlModules);

			/*=============================================
			Create the columns table
			=============================================*/
			
			$sqlColumns = "CREATE TABLE columns ( 
				id_column INT NOT NULL AUTO_INCREMENT,
				id_module_column INT NULL DEFAULT '0',
				title_column TEXT NULL DEFAULT NULL,
				alias_column TEXT NULL DEFAULT NULL,
				type_column TEXT NULL DEFAULT NULL,
				matrix_column TEXT NULL DEFAULT NULL,
				visible_column INT NULL DEFAULT '1',
				date_created_column DATE NULL DEFAULT NULL,
				date_updated_column TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id_column))";

			$stmtColumns = InstallController::connect()->prepare($sqlColumns);

			/*=============================================
			Create the folders table
			=============================================*/

			$sqlFolders = "CREATE TABLE folders ( 
				id_folder INT NOT NULL AUTO_INCREMENT,
				name_folder TEXT NULL DEFAULT NULL,
				size_folder TEXT NULL DEFAULT NULL,
				total_folder DOUBLE NULL DEFAULT '0',
				max_upload_folder TEXT NULL DEFAULT NULL,
				url_folder TEXT NULL DEFAULT NULL,
				keys_folder TEXT NULL DEFAULT NULL,
				date_created_folder DATE NULL DEFAULT NULL,
				date_updated_folder TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id_folder))";

			$stmtFolders = InstallController::connect()->prepare($sqlFolders);

			/*=============================================
			Create the files table
			=============================================*/

			$sqlFiles = "CREATE TABLE files ( 
				id_file INT NOT NULL AUTO_INCREMENT,
				id_folder_file INT NULL DEFAULT '0',
				name_file TEXT NULL DEFAULT NULL,
				extension_file TEXT NULL DEFAULT NULL,
				type_file TEXT NULL DEFAULT NULL,
				size_file DOUBLE NULL DEFAULT '0',
				link_file TEXT NULL DEFAULT NULL,
				thumbnail_vimeo_file TEXT NULL DEFAULT NULL,
				id_mailchimp_file TEXT NULL DEFAULT NULL,
				date_created_file DATE NULL DEFAULT NULL,
				date_updated_file TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id_file))";

			$stmtFiles = InstallController::connect()->prepare($sqlFiles);

			if($stmtAdmins->execute() && 
			   $stmtPages->execute() &&
			   $stmtModules->execute() &&
			   $stmtColumns->execute() &&
			   $stmtFolders->execute() &&
			   $stmtFiles->execute()
			){

				/*=============================================
				Create the super administrator
				=============================================*/

				$url = "admins?register=true&suffix=admin";
				$method = "POST";
				$fields = array(
					"email_admin" => trim($_POST["email_admin"]),
					"password_admin" => trim($_POST["password_admin"]),
					"rol_admin" => "superadmin",
					"permissions_admin" => '{"todo":"on"}',		
					"title_admin" => trim($_POST["title_admin"]),
					"symbol_admin" => trim($_POST["symbol_admin"]),
					"font_admin" => trim($_POST["font_admin"]),
					"color_admin" => trim($_POST["color_admin"]),
					"back_admin" => trim($_POST["back_admin"]),
					"date_created_admin" => date("Y-m-d")
				);

				$register = CurlController::request($url,$method,$fields);

				/*=============================================
				Create the home page
				=============================================*/

				$url = "pages?token=no&except=id_page";
				$method = "POST";
				$fields = array(
					"title_page" => "POSify",
					"url_page" => "posify",
					"icon_page" => "bi bi-house-door-fill",
					"type_page" => "modules",
					"order_page" => 1,
					"date_created_page" => date("Y-m-d")
				);

				$homePage = CurlController::request($url,$method,$fields);

				/*=============================================
				Create the admins page
				=============================================*/

				$url = "pages?token=no&except=id_page";
				$method = "POST";
				$fields = array(
					"title_page" => "Admins",
					"url_page" => "admins",
					"icon_page" => "bi bi-person-fill-gear",
					"type_page" => "modules",
					"order_page" => 2,
					"date_created_page" => date("Y-m-d")
				);

				$adminPage = CurlController::request($url,$method,$fields);

				/*=============================================
				Create the files page
				=============================================*/

				$url = "pages?token=no&except=id_page";
				$method = "POST";
				$fields = array(
					"title_page" => "Archivos",
					"url_page" => "archivos",
					"icon_page" => "bi bi-file-earmark-image",
					"type_page" => "custom",
					"order_page" => 3,
					"date_created_page" => date("Y-m-d")
				);

				$filesPage = CurlController::request($url,$method,$fields);

				/*=============================================
				Create the breadcrumb module for the admins page
				=============================================*/

				$url = "modules?token=no&except=id_module";
				$method = "POST";
				$fields = array(
					"id_page_module" => $adminPage->results->lastId,
					"type_module" => "breadcrumbs",
					"title_module" => "Administradores",
					"date_created_module"  => date("Y-m-d")
				);

				$breadcrumbModule = CurlController::request($url,$method,$fields);

				/*=============================================
				Create the table module for the admins page
				=============================================*/

				$url = "modules?token=no&except=id_module";
				$method = "POST";
				$fields = array(
					"id_page_module" => $adminPage->results->lastId,
					"type_module" => "tables",
					"title_module" => "admins",
					"suffix_module" => "admin",
					"editable_module" => 0,
					"date_created_module"  => date("Y-m-d")
				);

				$tableModule = CurlController::request($url,$method,$fields);

				/*=============================================
				Create the server folder
				=============================================*/

				$url = "folders?token=no&except=id_folder";
				$method = "POST";
				$fields = array(
					"name_folder" => "Server",
					"size_folder" => "200000000000",
					"max_upload_folder" => "500000000",
					"url_folder" => $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"],
					"date_created_folder"  => date("Y-m-d")
				);

				$serverFolder = CurlController::request($url,$method,$fields);

				if($register->status == 200 && 
				   $homePage->status == 200 &&
				   $adminPage->status == 200 &&
				   $filesPage->status == 200 &&
				   $breadcrumbModule->status == 200 &&
				   $tableModule->status == 200 &&
				   $serverFolder->status == 200
				){

					/*=============================================
					Create every column of the admins table
					=============================================*/

					$columns = array(
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "rol_admin",
							"alias_column" => "rol",
							"type_column" =>  "select",
							"matrix_column"  => "superadmin,admin,vendedor",
							"visible_column" => 1,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "permissions_admin",
							"alias_column" => "permisos",
							"type_column" =>  "object",
							"matrix_column"  => "",
							"visible_column" => 1,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "email_admin",
							"alias_column" => "email",
							"type_column" =>  "email",
							"matrix_column"  => "",
							"visible_column" => 1,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "password_admin",
							"alias_column" => "pass",
							"type_column" =>  "password",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "token_admin",
							"alias_column" => "token",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "token_exp_admin",
							"alias_column" => "expiración",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "status_admin",
							"alias_column" => "estado",
							"type_column" =>  "boolean",
							"matrix_column"  => "",
							"visible_column" => 1,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "title_admin",
							"alias_column" => "título",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "symbol_admin",
							"alias_column" => "simbolo",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "font_admin",
							"alias_column" => "tipografía",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "color_admin",
							"alias_column" => "color",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "back_admin",
							"alias_column" => "fondo",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						],
						[	
							"id_module_column" => $tableModule->results->lastId,
							"title_column" =>  "scode_admin",
							"alias_column" => "seguridad",
							"type_column" =>  "text",
							"matrix_column"  => "",
							"visible_column" => 0,
							"date_created_column" => date("Y-m-d")
						]
					);

					$countColumns = 0;

					foreach ($columns as $key => $value) {
						
						$url = "columns?token=no&except=id_column";
						$method = "POST";
						$fields = array(
							"id_module_column" => $value["id_module_column"],
							"title_column" =>  $value["title_column"],
							"alias_column" => $value["alias_column"],
							"type_column" =>  $value["type_column"],
							"matrix_column"  => $value["matrix_column"],
							"visible_column" => $value["visible_column"],
							"date_created_column" => $value["date_created_column"]
						);

						$createColumn = CurlController::request($url,$method,$fields);

						if($createColumn->status == 200){

							$countColumns++;

						}
							
					}

					if($countColumns == count($columns)){

						echo '<script>
						fncMatPreloader("off");
						fncFormatInputs();
						fncSweetAlert("success","La instalación se realizó exitosamente",setTimeout(()=>location.reload(),1250));
						</script>';

					}	

				}

			}		

		}

	}

	/*=============================================
	Check that a table exists
	=============================================*/

	static public function getTable($table){

		$database = InstallController::infoDatabase()["database"];
		$validate = InstallController::connect()->query("SELECT COLUMN_NAME AS item FROM information_schema.columns WHERE table_schema = '$database' AND table_name = '$table'")->fetchAll(PDO::FETCH_OBJ);

		/*=============================================
		The table must exist
		=============================================*/

		if(!empty($validate)){

			return 200;
		
		}else{

			return 404;
		}

	}

	/*=============================================
	Read the database tables
	=============================================*/

	static public function getTables(){

		$tables = InstallController::connect()->query("SHOW FULL TABLES")->fetchAll(PDO::FETCH_COLUMN);

		/*=============================================
		The tables must exist
		=============================================*/

		if(!empty($tables)){

			return $tables;
		}


	}

}

?>