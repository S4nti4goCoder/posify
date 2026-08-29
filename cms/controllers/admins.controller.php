<?php

require_once __DIR__ . "/../../lib/password.hasher.php";
require_once __DIR__ . "/../../lib/office.guard.php";
require_once __DIR__ . "/../../lib/login.throttle.php";
require_once __DIR__ . "/../../lib/password.reset.php";
require_once __DIR__ . "/../../lib/password.policy.php";
require_once __DIR__ . "/../../lib/view.php";

class AdminsController{

	/*=============================================
	Administrator login
	=============================================*/	

	public function login(){

		if(isset($_POST["email_admin"])){

			echo '<script>

				fncMatPreloader("on");
				fncSweetAlert("loading", "Ingresando...", "");

			</script>';

			/*=============================================
			Refused before the password is even checked, so guessing costs
			the attacker time instead of costing the server work
			=============================================*/

			if(LoginThrottle::tooMany($_POST["email_admin"])){

				$minutes = LoginThrottle::waitFor($_POST["email_admin"]);

				echo '<div class="alert alert-danger mt-3 rounded">Demasiados intentos fallidos. Espera '.$minutes.' minutos.</div>

				<script>

					fncMatPreloader("off");
					fncFormatInputs();
					fncToastr("error", "Demasiados intentos fallidos. Espera '.$minutes.' minutos.");

				</script>';

				return;
			}

			$url = "admins?login=true&suffix=admin";
			$method = "POST";
			$fields = array(
				"email_admin" => $_POST["email_admin"],
				"password_admin" => $_POST["password_admin"]
			);

			$login = CurlController::request($url,$method,$fields);
			
			if($login->status == 200){

				/*=============================================
				Check the administrator status
				=============================================*/

				if($login->results[0]->status_admin == 0){

					echo '<div class="alert alert-danger mt-3 rounded">Error al ingresar: Administrador desactivado</div>

					<script>

						fncMatPreloader("off");
						fncFormatInputs();
						fncToastr("error", "Error al ingresar: Administrador desactivado");

					</script>';

					return;
				}

				/*=============================================
				Read the branch data
				=============================================*/
				if($login->results[0]->id_office_admin > 0){
					$url = "relations?rel=admins,offices&type=admin,office&linkTo=id_admin,id_office&equalTo=".$login->results[0]->id_admin.",".$login->results[0]->id_office_admin;
					$method = "GET";
					$fields = array();
					$login = CurlController::request($url,$method,$fields);
				}

				/*=============================================
				Create the session variable
				=============================================*/
				/*=============================================
				A new session id, so one known before the login is not the
				one that ends up authenticated
				=============================================*/

				LoginThrottle::clear($_POST["email_admin"] ?? "");

				session_regenerate_id(true);

				$_SESSION["admin"] = $login->results[0];
				OfficeGuard::remember($login->results[0]);
				echo '<script>
					localStorage.removeItem("tokenAdmin");
					fncMatPreloader("off");
					fncFormatInputs();
					location.reload();
				</script>';

				/*=============================================
				Generate the security code and email it
				=============================================*/

				/*
				$securityCode = TemplateController::genPassword(6);

				$url = "admins?id=".$login->results[0]->id_admin."&nameId=id_admin&token=no&except=scode_admin";
				$method = "PUT";
				$fields = "scode_admin=".$securityCode;

				$updateAdmin = CurlController::request($url,$method,$fields);

				if($updateAdmin->status == 200){	

					$subject = "Códido de seguridad para ingresar";
					$email = $login->results[0]->email_admin;
					$title = 'CÓDIGO DE SEGURIDAD';
					$message = '<h4 style="font-weight: 100; color:#999; padding:0px 20px"><strong>Su código de seguridad: '.$securityCode.'</strong></4><h4 style="font-weight: 100; color:#999; padding:0px 20px">Ingrese nuevamente al sitio con este código de seguridad</4>';
					$link = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"]."?scode=".base64_encode($login->results[0]->email_admin);

					$sendEmail = TemplateController::sendEmail($subject, $email, $title, $message, $link);

					if($sendEmail == "ok"){

						echo '<script>

								fncFormatInputs();
								fncMatPreloader("off");
								fncSweetAlert("success", 
								"Se ha enviado un código de seguridad para ingresar al sistema, por favor revise su correo electrónico o bandeja SPAM",
								setTimeout(()=>window.location="'.$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].'?scode='.base64_encode($login->results[0]->email_admin).'",2000));

							</script>
						';

						return;

					}else{

						echo '<script>

							fncFormatInputs();
							fncMatPreloader("off");
							fncNotie("error", "'.$sendEmail.'");

							</script>
						';

						return;

					}

				}
				*/

			}else{

				// a wrong password is what the limit counts
				LoginThrottle::fail($_POST["email_admin"]);

				echo '<div class="alert alert-danger mt-3 rounded">Correo o contraseña incorrectos</div>

				<script>

					fncMatPreloader("off");
					fncFormatInputs();
					fncToastr("error", "Correo o contraseña incorrectos");

				</script>';
			}


		}

	}


	/*=============================================
	Check the security code
	=============================================*/

	public function securityCode(){

		if(isset($_POST["scode_admin"])){

			echo '

			<script>

				fncMatPreloader("on");
			    fncSweetAlert("loading", "Procesando...", "");

			</script>

			';

			/*=============================================
			Check the admin
			=============================================*/

			$url = "admins?linkTo=scode_admin&equalTo=".$_POST["scode_admin"];
			$method = "GET";
			$fields = array();

			$admin = CurlController::request($url,$method,$fields);
			
			if($admin->status == 200){

				/*=============================================
				Create the session variable
				=============================================*/

				/*=============================================
				A new session id, so one known before the login is not the
				one that ends up authenticated
				=============================================*/

				session_regenerate_id(true);

				$_SESSION["admin"] = $admin->results[0];
				OfficeGuard::remember($admin->results[0]);

				echo '<script>
					localStorage.removeItem("tokenAdmin");
					fncMatPreloader("off");
					fncFormatInputs();
					location.reload();

				</script>';

			}else{

				echo '<div class="alert alert-danger mt-3 rounded">Error al ingresar: Código de seguridad no coincide</div>

				<script>

					fncMatPreloader("off");
					fncFormatInputs();
					fncToastr("error", "Error al ingresar: Código de seguridad no coincide");

				</script>';
			}

		}

	}

	/*=============================================
	Update an administrator
	=============================================*/

	public function updateAdmin(){

		if(isset($_POST["id_admin"])){

			echo '

			<script>

				fncMatPreloader("on");
			    fncSweetAlert("loading", "Procesando...", "");

			</script>

			';

			/*=============================================
			Check the admin
			=============================================*/

			$url = "admins?linkTo=id_admin&equalTo=".base64_decode($_POST["id_admin"])."&select=id_admin,password_admin,rol_admin";
			$method = "GET";
			$fields = array();

			$admin = CurlController::request($url,$method,$fields);
			
			if($admin->status == 200){

				/*=============================================
				Only when the password changed
				=============================================*/

				if(!empty($_POST["password_admin"])){

					$failed = PasswordPolicy::check((string) $_POST["password_admin"]);

					if($failed !== []){

						echo '<script>
								fncMatPreloader("off");
								fncFormatInputs();
								fncToastr("error", ' . View::js(PasswordPolicy::message($failed)) . ');
							</script>';

						return;
					}

					$crypt = PasswordHasher::hash($_POST["password_admin"]);

				}else{

					$crypt = $admin->results[0]->password_admin;
					
				}

				/*=============================================
				Save the changes
				=============================================*/

				$url = "admins?id=".$admin->results[0]->id_admin."&nameId=id_admin&token=".$_SESSION["admin"]->token_admin."&table=admins&suffix=admin";	
				$method = "PUT";

				if($admin->results[0]->rol_admin == "superadmin"){

					$fields = "email_admin=".$_POST["email_admin"]."&password_admin=".$crypt."&title_admin=".$_POST["title_admin"]."&symbol_admin=".$_POST["symbol_admin"]."&font_admin=".urlencode($_POST["font_admin"])."&color_admin=".$_POST["color_admin"]."&back_admin=".$_POST["back_admin"];

				}else{

					$fields = "email_admin=".$_POST["email_admin"]."&password_admin=".$crypt;
				}

				$updateAdmin = CurlController::request($url,$method,$fields);

				if($updateAdmin->status == 200){

					$_SESSION["admin"]->email_admin = $_POST["email_admin"];

					echo '

					<script>

						fncMatPreloader("off");
						fncFormatInputs();
					    fncSweetAlert("success","El registro ha sido actualizado con éxito",setTimeout(()=>location.reload(),1250));
						
					</script>

					';

				}

			}else{

				echo '

				<script>

				    fncToastr("error","El registro no existe");
					fncMatPreloader("off");
					fncFormatInputs();

				</script>

				';
			}



		}

	}

	/*=============================================
	Password recovery
	=============================================*/

	/*=============================================
	Password recovery.

	The old version generated a password, wrote it to the account and only
	then tried to email it. Typing somebody's address was enough to lock
	them out, and with mail unconfigured the new one went nowhere.

	Nothing is written to the password now until the code from the message
	comes back through completeReset().
	=============================================*/

	public function resetPassword(){

		if(!isset($_POST["resetPassword"])){

			return;
		}

		$code = PasswordReset::open((string) $_POST["resetPassword"]);

		if($code !== null){

			$link = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"]."?reset=".$code;

			TemplateController::sendEmail(
				"Recuperar contraseña",
				(string) $_POST["resetPassword"],
				"RECUPERAR CONTRASEÑA",
				'<h4 style="font-weight:100; color:#999; padding:0px 20px">Abre este enlace para elegir una contraseña nueva. Caduca en una hora y solo sirve una vez.</h4>',
				$link
			);
		}

		/*=============================================
		The same answer either way: a different one would turn this screen
		into a way of finding out which addresses are registered
		=============================================*/

		echo '<script>

				fncFormatInputs();
				fncMatPreloader("off");
				fncToastr("success", "Si el correo está registrado, recibirás un enlace para elegir una contraseña nueva.");

			</script>';
	}

	/*=============================================
	The second half: the code came back, so the password may change
	=============================================*/

	public function completeReset(){

		if(!isset($_POST["resetCode"], $_POST["newPassword"])){

			return;
		}

		$failed = PasswordPolicy::check((string) $_POST["newPassword"]);

		if($failed !== []){

			echo '<script>
					fncMatPreloader("off");
					fncToastr("error", ' . View::js(PasswordPolicy::message($failed)) . ');
				</script>';

			return;
		}

		if(!PasswordReset::complete((string) $_POST["resetCode"], (string) $_POST["newPassword"])){

			echo '<script>
					fncMatPreloader("off");
					fncToastr("error", "El enlace ya caducó o no es válido. Pide uno nuevo.");
				</script>';

			return;
		}

		echo '<script>
				fncMatPreloader("off");
				fncSweetAlert("success", "Tu contraseña ha sido cambiada. Ya puedes ingresar.", "/");
			</script>';
	}

}