<?php

if (isset($_GET["order"])) {
	$url = "orders?linkTo=transaction_order&equalTo=" . $_GET["order"];
	$method = "GET";
	$fields = array();

	$getOrder = CurlController::request($url, $method, $fields);
	if ($getOrder->status == 200) {
		if ($getOrder->results[0]->status_order == "Completada") {
			$order = null;
			echo '<script>
				fncSweetAlert("error","Esta orden ya ha sido completada y no se puede editar", "/");
			</script>';
			return;
		}
		$order = $getOrder->results[0];
	} else {
		$order = null;
	}
}

?>

<!--==============================
Custom
================================-->
<div class="<?php if ($module->width_module == "100"): ?> col-lg-12 <?php endif ?><?php if ($module->width_module == "75"): ?> col-lg-9 <?php endif ?><?php if ($module->width_module == "50"): ?> col-lg-6 <?php endif ?><?php if ($module->width_module == "33"): ?> col-lg-4 <?php endif ?><?php if ($module->width_module == "25"): ?> col-lg-3 <?php endif ?> col-12 mb-3 position-relative">

	<?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>

		<div class="position-absolute border rounded" style="top:0px; right:12px; z-index:100">

			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 myModule" item='<?php echo json_encode($module) ?>' idPage="<?php echo $page->results[0]->id_page ?>">
				<i class="bi bi-pencil-square"></i>
			</button>

			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 deleteModule" idModule=<?php echo base64_encode($module->id_module) ?>>
				<i class="bi bi-trash"></i>
			</button>


		</div>

	<?php endif ?>

	<!--==============================
    	Start Custom
  	================================-->
	<button type="button" class="btn btn-default rounded backColor newOrder"><i class="bi bi-cart4"></i> Crear Orden</button>
	<button type="button" class="btn btn-default rounded bg-orange mx-1 removeOrder" <?php if (!empty($order)): ?>idOrder="<?php echo $order->id_order ?>" <?php else: ?> idOrder <?php endif ?>><i class="fas fa-broom"></i> Remover Orden</button>
	<button type="button" class="btn btn-default rounded bg-teal" data-bs-toggle="modal" data-bs-target="#modalSearchOrder"><i class="bi bi-search"></i> Buscar Orden</button>

<?php

/*=============================================
The day at a glance, above the catalogue. Same numbers the till closing
reconciles against, so both screens cannot disagree
=============================================*/

require_once __DIR__ . "/../../../../../../lib/cash.session.php";
require_once __DIR__ . "/../../../../../../lib/money.php";
require_once __DIR__ . "/../../../../../../lib/office.guard.php";
require_once __DIR__ . "/../../../../../../lib/view.php";

$posOffice = (int) OfficeGuard::current();
$posDate   = date("Y-m-d");
$posTill   = $posOffice > 0 ? CashSession::open($posOffice) : null;
$posStart  = $posTill !== null ? (float) $posTill["start_cash"] : 0.0;

$posReport = $posOffice > 0
    ? CashSession::report($posOffice, $posDate)
    : ["orders" => 0, "total" => 0.0, "top" => []];

$posBills = $posOffice > 0
    ? CashSession::summary($posOffice, $posDate, $posStart)["bills"]
    : 0.0;

?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-2 mt-3">

    <div class="col">
        <div class="card rounded h-100">
            <div class="card-body py-3">
                <small class="text-muted">Ventas hoy</small>
                <h4 class="mb-0 font-weight-bold"><?php echo (int) $posReport["orders"] ?></h4>
                <small class="text-muted">$ <?php echo Money::amount($posReport["total"]) ?></small>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card rounded h-100">
            <div class="card-body py-3">
                <small class="text-muted">Base del día</small>
                <?php if ($posTill !== null): ?>
                    <h4 class="mb-0 font-weight-bold text-green">$ <?php echo Money::amount($posStart) ?></h4>
                    <small class="text-muted">Caja abierta</small>
                <?php else: ?>
                    <h4 class="mb-0 font-weight-bold text-muted">$ 0</h4>
                    <small class="text-red">Caja cerrada</small>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card rounded h-100">
            <div class="card-body py-3">
                <small class="text-muted">Egresos</small>
                <h4 class="mb-0 font-weight-bold">$ <?php echo Money::amount($posBills) ?></h4>
                <small class="text-muted">Gastos del día</small>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card rounded h-100">
            <div class="card-body py-3">
                <small class="text-muted">Más vendidos hoy</small>
                <?php if (!empty($posReport["top"])): ?>
                    <ol class="ps-3 mb-0 mt-1">
                        <?php foreach ($posReport["top"] as $posItem): ?>
                            <li class="small text-truncate"><?php echo View::text($posItem["name"]) ?> <span class="text-muted">x<?php echo (int) $posItem["qty"] ?></span></li>
                        <?php endforeach ?>
                    </ol>
                <?php else: ?>
                    <p class="small text-muted mb-0 mt-2">Sin ventas todavía</p>
                <?php endif ?>
            </div>
        </div>
    </div>

</div>
</div>