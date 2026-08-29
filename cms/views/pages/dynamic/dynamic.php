<?php

/*=============================================
The branch switch happens in template.php, before anything is drawn, so the
header reflects it on the same render.
=============================================*/

/*=============================================
Open the matching dashboard page
=============================================*/
if (!empty($routesArray[0])) {
    $url = "relations?rel=modules,pages&type=module,page&linkTo=url_page&equalTo=" . $routesArray[0];

} else {
    $url = "relations?rel=modules,pages&type=module,page&linkTo=order_page&equalTo=1";

    if ($_SESSION["admin"]->id_office_admin == 0 && !isset($_GET["offices"])) {
        /*=============================================
        Bootstrap loads at the end of the page, so a fixed 100ms wait was a
        race: sometimes the modal opened, sometimes .modal was not a function
        yet and the console filled with an error
        =============================================*/

        echo '<script>
        window.addEventListener("load", function () {
            $("#myOffices").modal("show");
        });
        </script>';
    }
}

$method = "GET";
$fields = array();

$modules = CurlController::request($url, $method, $fields);

if ($modules->status == 200) {
    $modules = $modules->results;
} else {
    $modules = array();
}

/*=============================================
Stock is kept by Inventory now: every sale writes a ledger movement and
moves the balance inside the checkout transaction.

This used to recompute it from scratch on every page load, asking for each
product's purchases and sales one by one. Two cashiers selling at the same
time simply overwrote each other, and it cost about 3 requests per product.
=============================================*/

/*=============================================
Look for an open order
=============================================*/
$url = "orders?linkTo=id_admin_order,status_order,id_office_order,date_created_order&equalTo=" . $_SESSION["admin"]->id_admin . ",Pendiente," . $_SESSION["admin"]->id_office_admin . "," . date("Y-m-d");
$method = "GET";
$fields = array();

$order = CurlController::request($url, $method, $fields);

if ($order->status == 200) {
    $order = $order->results[0];

    /*=============================================
    A draft is priced at today prices, not at the ones of the day
    each product happened to be added
    =============================================*/

    require_once __DIR__ . "/../../../../lib/order.pricing.php";

    OrderPricing::reprice((int) $order->id_order);

    $order = CurlController::request(
    	"orders?linkTo=id_order&equalTo=" . $order->id_order,
    	"GET",
    	array()
    )->results[0];
} else {
    $order = null;
}

?>

<div class="container-fluid py-3 p-lg-4">
    <div class="row">

        <?php if (!empty($modules)): ?>

            <?php foreach ($modules as $key => $value): $module = $value ?>

                <!--=========================================
                Breadcrumb module
                ===========================================-->
                <?php if ($module->type_module == "breadcrumbs"): ?>
                    <?php include "breadcrumbs/breadcrumbs.php" ?>
                <?php endif ?>

                <!--=========================================
                Metric module
                ===========================================-->
                <?php if ($module->type_module == "metrics"): ?>
                    <?php include "metrics/metrics.php" ?>
                <?php endif ?>

                <!--=========================================
                Chart module
                ===========================================-->

                <?php if ($module->type_module == "graphics"): ?>
                    <?php include "graphics/graphics.php" ?>
                <?php endif ?>

                <!--=========================================
                Table module
                ===========================================-->
                <?php if ($module->type_module == "tables"): ?>
                    <?php include "tables/tables.php" ?>
                <?php endif ?>

                <!--=========================================
                Custom module
                ===========================================-->
                <?php if ($module->type_module == "custom"): ?>
                    <?php include "custom/" . str_replace(" ", "_", $module->title_module) . "/" . str_replace(" ", "_", $module->title_module) . ".php" ?>
                <?php endif ?>

            <?php endforeach ?>

        <?php endif ?>

        <?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>
            <div class="text-center <?php if (!empty($routesArray[1]) && $routesArray[1] == "manage"): ?> d-none  <?php endif ?>">
                <button class="btn btn-default bg-white border rounded btn-sm ms-3 menu-text mt-1 py-2 px-3 myModule" idPage="<?php echo $page->results[0]->id_page ?>">Agregar Módulo</button>
            </div>
        <?php endif ?>

    </div>
</div>

<?php if (!isset($_SESSION["admin"]->phone_office)): ?>
    <?php include "views/modules/modals/offices.php"; ?>
<?php endif ?>

<script src="<?php echo View::asset('/views/assets/js/pos/pos.js') ?>"></script>