<?php

require_once __DIR__ . "/../../lib/office.guard.php";

require_once __DIR__ . "/../../lib/password.hasher.php";

require_once __DIR__ . "/../../lib/password.policy.php";

require_once __DIR__ . "/../../lib/view.php";

require_once __DIR__ . "/../../lib/cash.session.php";

require_once __DIR__ . "/../../lib/inventory.php";

class DynamicController{

	/*=============================================
	Dynamic data management
	=============================================*/	

	/*=============================================
	The update path sends a query string and parse_str decodes it again,
	so encoding there is transport. The create path sends an array that
	nothing decodes, and encoding there is what stored Green+Nike+Fe
	=============================================*/

	private static function value($type, $value, $forQueryString){

		$value = trim((string) $value);

		// a raw date, or MySQL stores 0000-00-00
		if (in_array($type, array("date", "datetime", "time", "timestamp"), true)) {

			return $value;
		}

		return $forQueryString ? urlencode($value) : $value;
	}

	public function manage(){

		if(isset($_POST["module"])){

			echo '<script>

				fncMatPreloader("on");
			    fncSweetAlert("loading", "Procesando...", "");

			</script>';

			$module = json_decode($_POST["module"]);

			CashSession::stampDates($module->suffix_module, isset($_POST["idItem"]));

			/*=============================================
			Edit data
			=============================================*/

			if(isset($_POST["idItem"])){

				/*=============================================
				Update data
				=============================================*/

				$url = $module->title_module."?id=".base64_decode($_POST["idItem"])."&nameId=id_".$module->suffix_module."&token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
				$method = "PUT";
				$fields = "";
				$count = 0;

				foreach ($module->columns as $key => $value) {

					/*=============================================
					A user tied to one branch may only write for that branch
					=============================================*/

					if (strpos($value->title_column, "id_office_") === 0 && !OfficeGuard::canSwitch()) {

						$_POST[$value->title_column] = OfficeGuard::current();
					}

					// stock is per branch and lives in its own table, not in this row
					if($value->type_column == "stock"){

						$stockValue = (int) ($_POST[$value->title_column] ?? 0);

					}else if($value->type_column == "password" && !empty($_POST[$value->title_column])){

						$failed = PasswordPolicy::check(trim($_POST[$value->title_column]));

						if($failed !== []){

							echo '<script>
									fncMatPreloader("off");
									fncSweetAlert("error", ' . View::js(PasswordPolicy::message($failed)) . ', "");
								</script>';

							return;
						}

						$fields.= $value->title_column."=".PasswordHasher::hash(trim($_POST[$value->title_column]))."&";

					}else if($value->type_column == "email"){

						$fields.= $value->title_column."=".trim($_POST[$value->title_column])."&";

					}else{
					
						$fields.= $value->title_column."=".self::value($value->type_column, $_POST[$value->title_column], true)."&";

					}
					
					$count++;

					if($count == count($module->columns)){

						$fields = substr($fields,0,-1);

						$update = CurlController::request($url,$method,$fields);

						if($update->status == 200){

							// the stock the form carried belongs to the branch, not to the row
							if(isset($stockValue)){

								Inventory::setFor((int) base64_decode($_POST["idItem"]), (int) OfficeGuard::current(), $stockValue);
							}


							echo '

								<script>

									fncMatPreloader("off");
									fncFormatInputs();
								    fncSweetAlert("success","El registro ha sido actualizado con éxito", setTimeout(()=>window.location="/'.$module->url_page.'",1000));
									

								</script>

							';
							
						}
					}
				
				}


			}else{
		
				/*=============================================
				Create data
				=============================================*/

				$url = $module->title_module."?token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";
				$method = "POST";
				$fields = array();
				$count = 0;

				foreach ($module->columns as $key => $value) {

					/*=============================================
					A user tied to one branch may only write for that branch
					=============================================*/

					if (strpos($value->title_column, "id_office_") === 0 && !OfficeGuard::canSwitch()) {

						$_POST[$value->title_column] = OfficeGuard::current();
					}

					// stock is per branch and lives in its own table, not in this row
					if($value->type_column == "stock"){

						$stockValue = (int) ($_POST[$value->title_column] ?? 0);

					}else if($value->type_column == "password"){

						$failed = PasswordPolicy::check(trim($_POST[$value->title_column]));

						if($failed !== []){

							echo '<script>
									fncMatPreloader("off");
									fncSweetAlert("error", ' . View::js(PasswordPolicy::message($failed)) . ', "");
								</script>';

							return;
						}

						$fields[$value->title_column] = PasswordHasher::hash(trim($_POST[$value->title_column]));
					
					}else if($value->type_column == "email"){

						$fields[$value->title_column] = trim($_POST[$value->title_column]);
					}else{
					
						$fields[$value->title_column] = self::value($value->type_column, $_POST[$value->title_column], false);

					}
					
					$count++;

					if($count == count($module->columns)){

						$fields["date_created_".$module->suffix_module] = date("Y-m-d");

						$save = CurlController::request($url,$method,$fields);

						if($save->status == 200){

							if(isset($stockValue) && isset($save->results->lastId)){

								Inventory::setFor((int) $save->results->lastId, (int) OfficeGuard::current(), $stockValue);
							}


							echo '

								<script>

									fncMatPreloader("off");
									fncFormatInputs();
								    fncSweetAlert("success","El registro ha sido guardado con éxito", setTimeout(()=>window.location="/'.$module->url_page.'",1000));
									

								</script>

							';
							
						}
					}
				
				}

			}

		}

	}

}