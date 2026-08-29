<?php

require_once __DIR__ . "/../../lib/view.php";

/*=============================================
Start the session
=============================================*/

ob_start();
require_once __DIR__ . "/../../lib/csrf.guard.php";
require_once __DIR__ . "/../../lib/theme.php";

CsrfGuard::start();
date_default_timezone_set("America/Bogota");

/*=============================================
Read the url parameters
=============================================*/

$routesArray = explode("/", $_SERVER["REQUEST_URI"]);

array_shift($routesArray);

foreach ($routesArray as $key => $value) {
	
	$routesArray[$key] = explode("?",$value)[0];
}

/*=============================================
Check whether the database has an admins table
=============================================*/

$url = "admins";
$method = "GET";
$fields = array();

$adminTable = CurlController::request($url,$method,$fields);

if($adminTable->status == 404){

	$admin = null;

}else{

	$admin = $adminTable->results[0];
	// echo '<pre>'; print_r($admin); echo '</pre>';
}

/*=============================================
Switch branch, only for accounts that belong to none.

This runs before anything is drawn, so the header shows the branch that was
just picked. Doing it further down left the header one render behind.
=============================================*/

if (isset($_GET["offices"]) && isset($_SESSION["admin"]) && OfficeGuard::canSwitch()) {

	$requestedOffice = (int) explode("_", $_GET["offices"])[0];

	/*=============================================
	Zero is every branch at once, not a branch. Looking it up in offices
	found nothing, so going back to Multi-Sucursal silently did nothing
	=============================================*/

	if ($requestedOffice === 0) {

		$_SESSION["admin"]->id_office_admin = 0;
		$_SESSION["admin"]->title_office    = "Multi-Sucursal";

	} else {

		$office = CurlController::request(
			"offices?linkTo=id_office&equalTo=" . $requestedOffice . "&select=id_office,title_office",
			"GET",
			array()
		);

		if ($office->status == 200) {

			$_SESSION["admin"]->id_office_admin = (int) $office->results[0]->id_office;
			$_SESSION["admin"]->title_office    = $office->results[0]->title_office;
		}
	}
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="/favicon.svg" type="image/svg+xml">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

	<!--=============================================
	Check whether the admin exists
	===============================================-->

	<?php if (!empty($admin)): ?>

		<!--=============================================
		Dashboard title
		===============================================-->

		<title><?php echo View::text($admin->title_admin) ?></title>

		<!--=============================================
		Dashboard typeface
		===============================================-->

		<?php echo Theme::fontLink($admin->font_admin) ?>

		<!--=============================================
		Dashboard styles
		===============================================-->

		<style>
			
			/*=============================================
			Dashboard typeface
			=============================================*/

			body{
				font-family: <?php echo Theme::fontFamilyCss($admin->font_admin) ?>, sans-serif !important;
			}

			/*=============================================
			Dashboard color
			=============================================*/

			.backColor{
				background: <?php echo $admin->color_admin ?> !important;
				color: #FFF !important;
				border: 0 !important;
			}

			.form-check-input:checked{
				background-color: <?php echo $admin->color_admin ?> !important;
			    border-color: <?php echo $admin->color_admin ?> !important;
			}

			.textColor{
				color: <?php echo $admin->color_admin ?> !important;
			}

			.page-item.active .page-link {
				z-index: 3;
				color: #fff !important;
				background-color: <?php echo $admin->color_admin ?> !important;
				border-color: <?php echo $admin->color_admin ?> !important;
			}

			.page-link {
				color: <?php echo $admin->color_admin ?> !important;		
			}

		</style>

	<?php else: ?>

		<title>CMS Builder</title>

	<?php endif ?>

	<!--=============================================
	CUSTOM JS SERVER
	===============================================-->

	<script src="<?php echo View::asset('/views/assets/js/alerts/alerts.js') ?>"></script>
	<script src="<?php echo View::asset('/views/assets/js/password/password.js') ?>"></script>

	<!--=============================================
	PLUGINS CSS
	===============================================-->

	<!-- https://www.w3schools.com/bootstrap5/ -->
	<link rel="stylesheet" href="/views/assets/plugins/bootstrap5/bootstrap.min.css" >
	<!-- https://fontawesome.com/v5/search -->
	<link rel="stylesheet" href="/views/assets/plugins/fontawesome-free/css/all.min.css">
	<!-- https://icons.getbootstrap.com/ -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css">
	<!-- https://www.jqueryscript.net/demo/Google-Inbox-Style-Linear-Preloader-Plugin-with-jQuery-CSS3/#google_vignette -->
	<link rel="stylesheet" href="/views/assets/plugins/material-preloader/material-preloader.css">
	<!-- https://codeseven.github.io/toastr/demo.html -->
	<link rel="stylesheet" href="/views/assets/plugins/toastr/toastr.min.css">
	<!--  https://www.daterangepicker.com/ -->
	<link rel="stylesheet" href="/views/assets/plugins/daterangepicker/daterangepicker.css">
	<!-- https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/examples/ -->
	<link rel="stylesheet" href="/views/assets/plugins/tags-input/tags-input.css">
	<!-- https://select2.org/ -->
	<link rel="stylesheet" href="/views/assets/plugins/select2/select2.min.css">
    <link rel="stylesheet" href="/views/assets/plugins/select2/select2-bootstrap4.min.css">
    <!-- https://xdsoft.net/jqplugins/datetimepicker/ -->
    <link rel="stylesheet" href="/views/assets/plugins/datetimepicker/datetimepicker.min.css">
    <!-- https://summernote.org -->	
    <link rel="stylesheet" href="/views/assets/plugins/summernote/summernote-bs4.min.css"> 
    <link rel="stylesheet" href="/views/assets/plugins/summernote/summernote.min.css">
    <link rel="stylesheet" href="/views/assets/plugins/summernote/emoji.css">
    <!-- https://codemirror.net/ -->
    <link rel="stylesheet" href="/views/assets/plugins/codemirror/codemirror.css">
	<link rel="stylesheet" href="/views/assets/plugins/codemirror/monokai.css">
	<!-- https://www.jqueryscript.net/slider/Carousel-Slideshow-jdSlider.html -->
    <link rel="stylesheet" href="/views/assets/plugins/jdSlider/jdSlider.css">

	<!--=============================================
	PLUGINS JS
	===============================================-->

	<!-- https://jquery.com/ -->
	<script src="/views/assets/plugins/jquery/jquery.min.js"></script>
	<script>
		$.ajaxSetup({ headers: { "X-CSRF-Token": "<?php echo CsrfGuard::token() ?>" } });
	</script>
	<!-- https://jqueryui.com/ -->
	<script src="/views/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
	<!-- https://www.w3schools.com/bootstrap5/ -->
	<script src="/views/assets/plugins/bootstrap5/bootstrap.bundle.min.js"></script>
	<!-- https://sweetalert2.github.io/ -->
	<script src="/views/assets/plugins/sweetalert/sweetalert.min.js"></script> 
	<!-- https://www.jqueryscript.net/demo/Google-Inbox-Style-Linear-Preloader-Plugin-with-jQuery-CSS3/ -->
	<script src="/views/assets/plugins/material-preloader/material-preloader.js"></script> 
	<!-- https://codeseven.github.io/toastr/demo.html -->
	<script src="/views/assets/plugins/toastr/toastr.min.js"></script>
	<!-- http://josecebe.github.io/twbs-pagination/ -->
	<script src="/views/assets/plugins/twbs-pagination/twbs-pagination.min.js"></script> 
	<!-- https://momentjs.com/ -->
	<script src="/views/assets/plugins/moment/moment.min.js"></script>
	<script src="/views/assets/plugins/moment/moment-with-locales.min.js"></script>
	<!--  https://www.daterangepicker.com/ -->
	<script src="/views/assets/plugins/daterangepicker/daterangepicker.js"></script>	
	<!-- https://bootstrap-tagsinput.github.io/bootstrap-tagsinput/examples/ -->
	<script src="/views/assets/plugins/tags-input/tags-input.js"></script> 
	<!-- https://select2.org/ -->
	<script src="/views/assets/plugins/select2/select2.full.min.js"></script>
	<!-- https://xdsoft.net/jqplugins/datetimepicker/ -->
	<script src="/views/assets/plugins/datetimepicker/datetimepicker.full.min.js"></script>
	<!-- https://summernote.org -->	
	<script src="/views/assets/plugins/summernote/summernote.min.js"></script>
	<script src="/views/assets/plugins/summernote/summernote-bs4.js"></script>
    <script src="/views/assets/plugins/summernote/summernote-code-beautify-plugin.js"></script>
	<script src="/views/assets/plugins/summernote/emoji.config.js"></script>
	<script src="/views/assets/plugins/summernote/tam-emoji.min.js"></script>
	<!-- https://codemirror.net/ -->
	<script src="/views/assets/plugins/codemirror/codemirror.js"></script>
	<script src="/views/assets/plugins/codemirror/xml.js"></script>
	<script src="/views/assets/plugins/codemirror/formatting.js"></script>
	<!-- https://www.chartjs.org/ -->
	<script src="/views/assets/plugins/chartjs/chartjs.min.js"></script>
	<!-- https://www.jqueryscript.net/slider/Carousel-Slideshow-jdSlider.html -->
	<script src="/views/assets/plugins/jdSlider/jdSlider.js"></script>

	<!--=============================================
	CUSTOM CSS
	===============================================-->
	<link rel="stylesheet" href="<?php echo View::asset('/views/assets/css/custom/custom.css') ?>">
	<link rel="stylesheet" href="<?php echo View::asset('/views/assets/css/ticket/ticket.css') ?>">
	<link rel="stylesheet" href="<?php echo View::asset('/views/assets/css/dashboard/dashboard.css') ?>">
	<link rel="stylesheet" href="<?php echo View::asset('/views/assets/css/colors/colors.css') ?>">
	<link rel="stylesheet" href="<?php echo View::asset('/views/assets/css/fms/fms.css') ?>">


</head>
<body>

	<?php 

	if(!isset($_SESSION["admin"])){

		if($admin == null){

			include "pages/install/install.php";

		}else{

			include "pages/login/login.php";
		}

	}

	?>

	<?php if (isset($_SESSION["admin"])): ?>

		<!--=============================================
		DASHBOARD TEMPLATE
		===============================================-->

		<div class="d-flex backDashboard" id="wrapper">
			
			<!--=============================================
			SIDEBAR
			===============================================-->

			<?php include "modules/sidebar.php" ?>

			<div id="page-content-wrapper">
				
				<!--=============================================
				NAV
				===============================================-->

				<?php include "modules/nav.php" ?>

				<?php

				/*=============================================
				A till left open on an earlier day stops the POS from selling,
				so it is said on every page and not only inside Caja
				=============================================*/

				require_once __DIR__ . "/../../lib/cash.session.php";

				$staleTill = CashSession::open((int) OfficeGuard::current());

				if ($staleTill !== null && $staleTill["date_created_cash"] < date("Y-m-d") && $routesArray[0] != "caja"): ?>

					<div class="alert alert-warning rounded d-flex justify-content-between align-items-center m-3 mb-0">
						<span>La caja del <?php echo View::raw($staleTill["date_created_cash"]) ?> sigue abierta. No se puede vender hasta cerrarla.</span>
						<a href="/caja" class="btn btn-danger btn-sm rounded">Ir a Caja</a>
					</div>

				<?php endif ?>

				<!--=============================================
				MAIN PAGE
				===============================================-->

				<?php if (!empty($routesArray[0])): ?>

					<?php if ($routesArray[0] == "logout"): ?>

						<?php include "pages/".$routesArray[0]."/".$routesArray[0].".php"; ?>

					<?php else: ?>

						<!--=========================================
						Check permissions
						===========================================-->

						<?php if ($_SESSION["admin"]->rol_admin == "superadmin" || $_SESSION["admin"]->rol_admin == "admin" || $_SESSION["admin"]->rol_admin == "editor" && isset(json_decode((string) ($_SESSION["admin"]->permissions_admin ?? "{}"), true)[$routesArray[0]]) && json_decode((string) ($_SESSION["admin"]->permissions_admin ?? "{}"), true)[$routesArray[0]] == "on"): ?>

							<!--=========================================
							Add dynamic and custom pages
							===========================================-->

							<?php 

								$url = "pages?linkTo=url_page&equalTo=".$routesArray[0];
								$method = "GET";
								$fields = array();

								$page = CurlController::request($url,$method,$fields);
								
								if($page->status == 200 && $page->results[0]->type_page == "modules"){

									include "pages/dynamic/dynamic.php";
								
								}else if($page->status == 200 && $page->results[0]->type_page == "custom"){

									include "pages/custom/".$routesArray[0]."/".$routesArray[0].".php";
								
								}else{

									include "pages/404/404.php";
								
								}

							?>

						<?php else: ?>

							<?php include "pages/404/404.php"; ?>

						<?php endif ?>
						
					<?php endif ?>

				<?php else: ?>


					<!--=========================================
				 	Check superadmin and admin permissions
					===========================================-->

					<?php if ($_SESSION["admin"]->rol_admin == "superadmin" || $_SESSION["admin"]->rol_admin == "admin"): ?>

						<!--=========================================
						Add the home page
						===========================================-->

						<?php 

							$url = "pages?linkTo=order_page&equalTo=1";
							$method = "GET";
							$fields = array();

							$page = CurlController::request($url,$method,$fields);

							if($page->status == 200 && $page->results[0]->type_page == "modules"){

								include "pages/dynamic/dynamic.php";
							
							}else if($page->status == 200 && $page->results[0]->type_page == "custom"){

								include "pages/custom/".$page->results[0]->url_page."/".$page->results[0]->url_page.".php";
							
							}else{

								include "pages/404/404.php";
							
							}
						
						?>

					<?php else: ?>

					<!--=========================================
				 	Check editor permissions
					===========================================-->

						<?php if ($_SESSION["admin"]->rol_admin == "editor"): ?>

							<?php

								/*=============================================
								An editor with no page granted has nowhere to land, and taking
								the first key of an empty list used to be a fatal error
								=============================================*/

								$granted = json_decode((string) ($_SESSION["admin"]->permissions_admin ?? "{}"), true);
								$granted = is_array($granted) ? array_keys($granted) : array();

								if (empty($granted)) {

									echo '<div class="alert alert-warning m-4 rounded">Tu cuenta no tiene ninguna página asignada. Pídele a un administrador que te dé permisos.</div>';

								} else {

									$url = "pages?linkTo=url_page&equalTo=" . $granted[0];
								$method = "GET";
								$fields = array();

								$page = CurlController::request($url,$method,$fields);

								$routesArray[0] = $granted[0];

								if($page->status == 200 && $page->results[0]->type_page == "modules"){

									include "pages/dynamic/dynamic.php";
								
								}else if($page->status == 200 && $page->results[0]->type_page == "custom"){

									include "pages/custom/".$page->results[0]->url_page."/".$page->results[0]->url_page.".php";
								
								}else{

									include "pages/404/404.php";
								
								}
							}

							?>

						<?php endif ?>

					<?php endif ?>

				<?php endif ?>

			</div>

		</div>

		<?php 

		/*=============================================
    	Include the profile modal
    	=============================================*/

    	include "modules/modals/profile.php"; 
		require_once "controllers/admins.controller.php";
		$update = new AdminsController();
	    $update->updateAdmin();

	    if($_SESSION["admin"]->rol_admin == "superadmin"){

	    	/*=============================================
	    	Include the pages modal
	    	=============================================*/

		    include "views/modules/modals/pages.php";

		    require_once "controllers/pages.controller.php";
			$managePage = new PagesController();
		    $managePage->managePage();

		    /*=============================================
	    	Include the modules modal
	    	=============================================*/

		    include "views/modules/modals/modules.php";

		    require_once "controllers/modules.controller.php";
			$manageModule = new ModulesController();
			$manageModule->manageModule();
   
		}

		?>

	<!--=============================================
	CUSTOM JS
	===============================================-->

	<script src="<?php echo View::asset('/views/assets/js/dashboard/dashboard.js') ?>"></script>
	<script src="<?php echo View::asset('/views/assets/js/pages/pages.js') ?>"></script>
	<script src="<?php echo View::asset('/views/assets/js/modules/modules.js') ?>"></script>
	<script src="<?php echo View::asset('/views/assets/js/dynamic-forms/dynamic-forms.js') ?>"></script>
	<script src="<?php echo View::asset('/views/assets/js/dynamic-tables/dynamic-tables.js') ?>"></script>
	<script src="<?php echo View::asset('/views/assets/js/fms/fms.js') ?>"></script>
	<script src="<?php echo View::asset('/views/assets/js/purchase/purchase.js') ?>"></script>
		
	<?php endif ?>

	<script src="<?php echo View::asset('/views/assets/js/forms/forms.js') ?>"></script>
	
	
</body>
</html>