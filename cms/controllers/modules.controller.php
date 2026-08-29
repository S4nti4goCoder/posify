<?php 

require_once "controllers/install.controller.php";
require_once "controllers/template.controller.php";

class ModulesController{

	/*=============================================
	Manage a module
	=============================================*/

	public function manageModule(){

		if(isset($_POST["title_module"])){

			echo '<script>

				fncMatPreloader("on");
			    fncSweetAlert("loading", "Procesando...", "");

			</script>';

			/*=============================================
			Editing a module
			=============================================*/

			if(isset($_POST["id_module"])){

				$url = "modules?id=".base64_decode($_POST["id_module"])."&nameId=id_module&token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
				$method = "PUT";
				$fields = "title_module=".strtolower(trim($_POST["title_module"]))."&suffix_module=".strtolower(trim($_POST["suffix_module"]))."&content_module=".$_POST["content_module"]."&width_module=".$_POST["width_module"]."&editable_module=".$_POST["editable_module"];

				$title_module = strtolower(trim($_POST["title_module"]));
				$suffix_module = strtolower(trim($_POST["suffix_module"]));

				$updateModule = CurlController::request($url,$method,$fields);

				if($updateModule->status == 200){

					/*=============================================
					Editing a table module
					=============================================*/

					if($_POST["type_module"] == "tables"){

						/*=============================================
						Edit columns
						=============================================*/

						$countColumns = 0;
						$indexColumns = array();

						if(isset($_POST["indexColumns"])){

							$indexColumns = json_decode($_POST["indexColumns"], true);	

							if(!empty($indexColumns)){

								foreach ($indexColumns as $key => $value) {

									$type = TemplateController::typeColumn($_POST["type_column_".$value]);

									/*=============================================
									Update the columns table
									=============================================*/

									if($_POST["id_column_".$value] > 0){

										$url = "columns?id=".$_POST["id_column_".$value]."&nameId=id_column&token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
										$method = "PUT";
										$data = "title_column=".str_replace(" ","_",$_POST["title_column_".$value])."&alias_column=".$_POST["alias_column_".$value]."&type_column=".$_POST["type_column_".$value]."&visible_column=".$_POST["visible_column_".$value];

										$updateColumn = CurlController::request($url,$method,$data);

										if($updateColumn->status == 200){
										
											/*=============================================
											Edit the columns in MySQL
											=============================================*/

											$sqlUpdateColumn = "ALTER TABLE ".$title_module." CHANGE ".$_POST["original_title_column_".$value]." ".str_replace(" ","_",$_POST["title_column_".$value])." ".$type;

											$stmtUpdateColumn = InstallController::connect()->prepare($sqlUpdateColumn);

											if($stmtUpdateColumn->execute()){

												$countColumns++;
											
											}	

										}

									/*=============================================
									Creating new columns
									=============================================*/

									}else{

										$url = "columns?token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
										$method = "POST";
										$data = array(
											"id_module_column" => base64_decode($_POST["id_module"]),
											"title_column" => str_replace(" ","_",$_POST["title_column_".$value]),
											"alias_column" => $_POST["alias_column_".$value],
											"type_column" => $_POST["type_column_".$value],
											"visible_column" => $_POST["visible_column_".$value],
											"date_created_column" => date("Y-m-d")
										);

										$createColumn = CurlController::request($url,$method,$data);

										if($createColumn->status == 200){

											/*=============================================
											Create the columns in MySQL
											=============================================*/

											if($key == 0){

												$after = "id_".$suffix_module;
											
											}else{

												$after = $_POST["title_column_".($indexColumns[($key-1)])];
											}

											$sqlCreateColumns = "ALTER TABLE ".$title_module." ADD ".str_replace(" ","_",$_POST["title_column_".$value])." ".$type." AFTER ".$after;

											$stmtCreateColumns = InstallController::connect()->prepare($sqlCreateColumns);	

											if($stmtCreateColumns->execute()){

												$countColumns++;

											}

										}

									}

								}
							
							}

						}

						/*=============================================
						Delete columns
						=============================================*/

						$countDeleteColumns = 0;
						$deleteColumns = array();

						if(isset($_POST["deleteColumns"])){

							$deleteColumns = json_decode($_POST["deleteColumns"], true);

							if(!empty($deleteColumns)){

								foreach ($deleteColumns as $key => $value) {

									/*=============================================
									Read the column name
									=============================================*/

									$url = "columns?linkTo=id_column&equalTo=".$value."&select=title_column";
									$method = "GET";
									$fields = array();

									$column = CurlController::request($url,$method,$fields);

									/*=============================================
									Delete from the columns table
									=============================================*/

									$url = "columns?id=".$value."&nameId=id_column&token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
									$method = "DELETE";
									$fields = array();

									$deleteColumn = CurlController::request($url,$method,$fields);

									if($deleteColumn->status == 200){

										/*=============================================
										Drop the column in MySQL
										=============================================*/

										$sqlDeleteColumn = "ALTER TABLE $title_module DROP ".$column->results[0]->title_column;

										$stmtDeleteColumn = InstallController::connect()->prepare($sqlDeleteColumn);

										if($stmtDeleteColumn->execute()){

											$countDeleteColumns++;

										}	

	
									}

								}

							}


						}

						/*=============================================
						The SQL step must have finished
						=============================================*/	

						if($countDeleteColumns == count($deleteColumns) && count($indexColumns) == $countColumns){

							echo'

							<script>

								fncMatPreloader("off");
								fncFormatInputs();
							    fncSweetAlert("success","El módulo ha sido actualizado con éxito",setTimeout(()=>location.reload(),1250));	

							</script>

							';

						}


					}else{

						echo'

						<script>

							fncMatPreloader("off");
							fncFormatInputs();
						    fncSweetAlert("success","El módulo ha sido actualizado con éxito",setTimeout(()=>location.reload(),1250));	

						</script>

						';

					}

				}

			/*=============================================
			Creating a module
			=============================================*/

			}else{

				/*=============================================
				The module must not exist yet
				=============================================*/

				$url = "modules?linkTo=title_module,type_module&equalTo=".urlencode($_POST["title_module"]).",".$_POST["type_module"];
				$method = "GET";
				$fields = array();

				$getModule = CurlController::request($url,$method,$fields);
				
				if(isset($getModule->status) && $getModule->status == 200){

					echo '

					<script>

						fncMatPreloader("off");
						fncFormatInputs();
					    fncToastr("error","ERROR: Este módulo ya existe");	

					</script>

					';

					return;

				}

				/*=============================================
				The table must not exist yet
				=============================================*/

				if($_POST["type_module"] == "tables"){

					$validate = InstallController::getTable(strtolower(trim($_POST["title_module"])));

					if($validate == 200){

						echo '

						<script>

							fncMatPreloader("off");
							fncFormatInputs();
						    fncToastr("error","ERROR: Esa tabla ya existe en la BD");	

						</script>

						';

						return;

					}

				}

				/*=============================================
				Build the module data
				=============================================*/

				$url = "modules?token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
				$method = "POST";
				$fields = array(
					"id_page_module" => base64_decode($_POST["id_page_module"]),
					"type_module" => $_POST["type_module"],
					"title_module" => strtolower(trim($_POST["title_module"])),
					"suffix_module" => strtolower(trim($_POST["suffix_module"])),
					"content_module" => $_POST["content_module"],
					"width_module" => $_POST["width_module"],
				    "editable_module" => $_POST["editable_module"],
				    "date_created_module" => date("Y-m-d")
				);

				$createModule = CurlController::request($url,$method,$fields);

				if($createModule->status == 200){

					/*=============================================
					The module created is a table
					=============================================*/

					if($_POST["type_module"] == "tables"){

						/*=============================================
						Create the table in MySQL
						=============================================*/

						$sqlNewTable = "CREATE TABLE ".str_replace(" ","_",$fields["title_module"])." ( 
										id_".$fields["suffix_module"]." INT NOT NULL AUTO_INCREMENT,
										date_created_".$fields["suffix_module"]." DATE NULL DEFAULT NULL,
										date_updated_".$fields["suffix_module"]." TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
										PRIMARY KEY (id_".$fields["suffix_module"]."))";

						$stmtNewTable = InstallController::connect()->prepare($sqlNewTable);

						if($stmtNewTable->execute()){

							$countColumns = 0;
							$indexColumns = array();

							if(isset($_POST["indexColumns"])){

								$indexColumns = json_decode($_POST["indexColumns"], true);	

								if(!empty($indexColumns)){

									foreach ($indexColumns as $key => $value) {

										/*=============================================
										Create new columns
										=============================================*/

										$url = "columns?token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
										$method = "POST";
										$data = array(
											"id_module_column" => $createModule->results->lastId,
											"title_column" => str_replace(" ","_",$_POST["title_column_".$value]),
											"alias_column" => $_POST["alias_column_".$value],
											"type_column" => $_POST["type_column_".$value],
											"visible_column" => $_POST["visible_column_".$value],
											"date_created_column" => date("Y-m-d")
										);

										$createColumn = CurlController::request($url,$method,$data);

										if($createColumn->status == 200){

											$type = TemplateController::typeColumn($_POST["type_column_".$value]);

											/*=============================================
											Create the columns in MySQL
											=============================================*/

											if($key == 0){

												$after = "id_".$fields["suffix_module"];
											
											}else{

												$after = str_replace(" ","_",$_POST["title_column_".($indexColumns[($key-1)])]);
											}

											$sqlCreateColumns = "ALTER TABLE ".str_replace(" ","_",$fields["title_module"])." ADD ".str_replace(" ","_",$_POST["title_column_".$value])." ".$type." AFTER ".$after;

											$stmtCreateColumns = InstallController::connect()->prepare($sqlCreateColumns);	

											if($stmtCreateColumns->execute()){

												$countColumns++;

											}

											if(count($indexColumns) == $countColumns){

												echo'

												<script>

													fncMatPreloader("off");
													fncFormatInputs();
												    fncSweetAlert("success","El módulo ha sido creado con éxito",setTimeout(()=>location.reload(),1250));	

												</script>

												';

											}
										}
										
									}
								}
							}
						}
					
					/*=============================================
					The module created is a table
					=============================================*/

					}else if($_POST["type_module"] == "custom"){

						/*=============================================
						Create the custom module folder
						=============================================*/

						$directory = DIR."/views/pages/dynamic/custom/".str_replace(" ","_",$fields["title_module"]);

						if(!file_exists($directory)){

							mkdir($directory, 0755);
						}

						/*=============================================
						Copy the custom file under the new name
						=============================================*/	

						$from = DIR."/views/pages/dynamic/custom/custom.php";

						if(copy($from, $directory.'/'.str_replace(" ","_",$fields["title_module"]).'.php')){

							echo '

							<script>

								fncMatPreloader("off");
								fncFormatInputs();
							    fncSweetAlert("success","El módulo ha sido creado con éxito",setTimeout(()=>location.reload(),1250));		

							</script>

							';

						}


					}else{

						echo'

						<script>

							fncMatPreloader("off");
							fncFormatInputs();
						    fncSweetAlert("success","El módulo ha sido creado con éxito",setTimeout(()=>location.reload(),1250));	

						</script>

						';

					}

					
				}

			}

		}

	}


}