<?php

require_once __DIR__ . "/../../../../../../lib/inventory.php";

/**
 * Included from tables.php, which defines the variables below.
 *
 * @var object   $module      Table module being rendered
 * @var string[] $routesArray Url segments: page / manage / id / copy
 */

/*=============================================
Read the data to edit
=============================================*/

/*=============================================
A till is opened and closed through its own modals, which compute the
takings from the sales. Reaching this form by url would let someone
type those figures by hand and the arqueo would stop meaning anything.
=============================================*/

if ($module->suffix_module == "cash") {

	echo '<script>window.location = "/' . $module->url_page . '";</script>';

	return;
}

$data      = null;
$recordGone = false;

if(!empty($routesArray[2])){

	$requestedId = base64_decode($routesArray[2], true);

	/*=============================================
	A record outside the user's own branch is not theirs to edit
	=============================================*/

	$url = $module->title_module."?linkTo=id_".$module->suffix_module."&equalTo=".$requestedId;

	if($requestedId !== false
		&& !OfficeGuard::canSwitch()
		&& in_array("id_office_".$module->suffix_module, array_column($module->columns, "title_column"))){

		$url = $module->title_module
			."?linkTo=id_".$module->suffix_module.",id_office_".$module->suffix_module
			."&equalTo=".$requestedId.",".OfficeGuard::current();
	}

	$method = "GET";
	$fields = Array();

	$response = $requestedId === false || $requestedId === ""
		? null
		: CurlController::request($url,$method,$fields);

	/*=============================================
	Only a real hit becomes $data. Anything else used to leave the raw
	response object in place, which the form then read as an array and
	crashed on.
	=============================================*/

	if(!empty($response) && $response->status == 200){

		$data = json_decode(json_encode($response->results[0]),true);

		// the stock shown is the one of the branch being worked in
		if ($module->title_module == "products") {

			$data["qty_stock"] = Inventory::available((int) $data["id_product"], (int) OfficeGuard::current());
		}

	}else{

		$recordGone = true;
	}
}


/*=============================================
Defining the blocks
=============================================*/

$block1 = ceil(count($module->columns)/2);
$block2 = count($module->columns) - $block1;

?>

<?php if ($recordGone): ?>

	<div class="col">
		<div class="alert alert-warning rounded">
			El registro no existe o no pertenece a tu sucursal.
		</div>
		<a href="/<?php echo $module->url_page ?>" class="btn btn-dark rounded">Regresar</a>
	</div>

<?php else: ?>

<div class="col">

	<form method="POST" class="needs-validation" novalidate>

		<?php echo CsrfGuard::field() ?>


		<?php 

			require_once "controllers/dynamic.controller.php";
			$manageData = new DynamicController();
			$manageData -> manage();

		?>

		<div class="card rounded">

			<input type="hidden" name="module" value='<?php echo json_encode($module) ?>'>

			<?php if (!empty($data) && empty($routesArray[3])): ?>
			
				<input type="hidden" name="idItem" value="<?php echo $routesArray[2] ?>">	
							
			<?php endif ?>

			<!--=========================================
			Header
			===========================================-->
			
			<div class="card-header bg-white rounded-top py-3">

				<div class="d-flex justify-content-between">

					<div>
						<a href="/<?php echo $module->url_page ?>" class="btn btn-default border btn-sm rounded px-3 py-2">Regresar</a>
					</div>

					<div>
						<button type="submit" class="btn btn-default btn-sm rounded backColor py-2 px-3">Guardar Registro</button>
					</div>

				</div>
				

			</div>

			<!--=========================================
			Body
			===========================================-->

			<div class="card-body">

				<div class="row row-cols-1 row-cols-lg-2">


					<!--=========================================
					Block 1
					===========================================-->

					<div class="col">

						<?php for ($i = 0; $i < $block1; $i++): ?>

							<?php include "blocks/blocks.php" ?>
							
						<?php endfor ?>

					</div>

					<?php if ($block2 > 0): ?>

						<!--=========================================
						Block 2
						===========================================-->

						<div class="col">

							<?php for ($i = $block1; $i < count($module->columns); $i++): ?>

								<?php include "blocks/blocks.php" ?>

							<?php endfor ?>
							
						</div>

					<?php endif ?>

				</div>

			</div>

			<!--=========================================
			Footer
			===========================================-->

			<div class="card-footer bg-white rounded-bottom py-3">

				<div class="d-flex justify-content-between">

					<div>
						<a href="/<?php echo $module->url_page ?>" class="btn btn-default border btn-sm rounded px-3 py-2">Regresar</a>
					</div>

					<div>
						<button type="submit" class="btn btn-default btn-sm rounded backColor py-2 px-3">Guardar Registro</button>
					</div>

				</div>
				
			</div>

		</div>

	</form>

</div>
<?php endif ?>
