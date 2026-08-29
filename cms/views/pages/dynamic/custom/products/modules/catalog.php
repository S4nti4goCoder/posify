<?php

require_once __DIR__ . "/../../../../../../../lib/view.php";
require_once __DIR__ . "/../../../../../../../lib/money.php";

$limit = 6;

/*=============================================
Stock lives in its own table now, and the relations endpoint builds every
join from the first table, so this one is asked for directly
=============================================*/

require_once __DIR__ . "/../../../../../../../api/models/connection.php";

$db = Connection::connect();

$from = " FROM products p
          INNER JOIN stocks s ON s.id_product_stock = p.id_product
          LEFT JOIN categories c ON c.id_category = p.id_category_product
          WHERE s.id_office_stock = :office AND p.status_product = 1";

$stmt = $db->prepare("SELECT COUNT(*)" . $from);
$stmt->execute([":office" => (int) $_SESSION["admin"]->id_office_admin]);

$totalPageProducts = ceil((int) $stmt->fetchColumn() / $limit);

$stmt = $db->prepare(
    "SELECT p.*, c.title_category, s.qty_stock" . $from .
    " ORDER BY p.id_product DESC LIMIT 0, :endAt"
);

$stmt->bindValue(":office", (int) $_SESSION["admin"]->id_office_admin, PDO::PARAM_INT);
$stmt->bindValue(":endAt", $limit, PDO::PARAM_INT);
$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<?php if (!empty($products)): ?>

    <div class="row p-2 viewProducts">

        <?php foreach ($products as $key => $value): ?>

            <div class="col-12 col-lg-6 col-xl-4 p-2 btn addProductPos" idProduct="<?php echo $value->id_product ?>">
                <div class=" card rounded border-0 position-relative">

                <?php if ($value->discount_product > 0): ?>
                    <div class="position-absolute small bg-red p-1 shadow-sm rounded" style="top:4px; left:4px; font-size:10px"><?php echo $value->discount_product ?>% OFF</div>
                <?php endif ?>

                <div class="position-absolute small bg-white p-1 shadow-sm rounded" style="top:4px; right:4px; font-size:10px"><?php echo $value->sku_product ?></div>
                <img src="<?php echo View::url($value->img_product) ?>" class="card-img-top px-5 py-3 mx-auto" style="width:200px !important">
                <div class="card-body">
                    <h6 class="font-weight-bold text-gray samll"><?php echo View::text($value->title_category) ?></h6>
                    <h6 class="card-title pb-2 font-weight-bold"><?php echo View::text($value->title_product) ?></h6>
                    <div class="d-flex justify-content-between">

                        <?php
                        $colorStock = "bg-secondary";

                        if ($value->qty_stock < 50) {
                            $colorStock = "bg-maroon";
                        }
                        if ($value->qty_stock >= 50 && $value->qty_stock < 100) {
                            $colorStock = "bg-indigo";
                        }
                        if ($value->qty_stock >= 100) {
                            $colorStock = "bg-teal";
                        }
                        ?>

                        <div class="card-text small h6 badge badge-default pb-0 <?php echo $colorStock  ?>" style="font-size:10px; padding-top:6px">
                            <?php echo $value->qty_stock ?>
                        </div>

                        <?php
                        $url = "purchases?linkTo=id_product_purchase&equalTo=" . $value->id_product . "&select=price_purchase";
                        $price = CurlController::request($url, $method, $fields);
                        if ($price->status == 200) {
                            $price = $price->results[0]->price_purchase;
                            if ($value->discount_product > 0) {
                                $discount = $price - ($price * ($value->discount_product / 100));
                            }
                        } else {
                            $price = 0;
                        }
                        ?>

                        <?php if ($value->discount_product > 0): ?>
                            <span class="small ms-auto pe-1 h6 mt-1 text-red font-weight-bold" style="font-size:12px"><s>$ <?php echo Money::amount($price) ?></s></span>
                            <div class="small h6 mt-1 textColor font-weight-bold"><strong>$ <?php echo Money::amount($discount) ?></strong></div>
                        <?php else: ?>
                            <div class="small h6 mt-1 textColor font-weight-bold"><strong>$ <?php echo Money::amount($price) ?></strong></div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
    </div>

<?php endforeach ?>

</div>

<?php if ($totalPageProducts > 1): ?>
    <div id="loadPageProducts" class="d-flex justify-content-center mb-5">
        <div><button class="btn btn-sm rounded bg-blue px-3 py-2">Cargar más productos</button></div>
    </div>
<?php endif ?>

<input type="hidden" id="totalPagesProducts" value="<?php echo $totalPageProducts ?>">
<input type="hidden" id="currentPageProducts" value="1">
<input type="hidden" id="limitProduct" value="<?php echo $limit ?>">
<input type="hidden" id="idOffice" value="<?php echo $_SESSION["admin"]->id_office_admin ?>">
<input type="hidden" id="filterByCategory" value="all">

<?php else: ?>

    <div class="row p-2 my-5 text-center">
        <?php include "svg.php" ?>
        <?php if ((int) $_SESSION["admin"]->id_office_admin === 0): ?>

            <p>Elige una sucursal para vender. En Multi-Sucursal se consultan los datos de todas, pero no se puede facturar.</p>

        <?php else: ?>

            <p>No hay productos agregados a esta Sucursal</p>

        <?php endif ?>
    </div>

<?php endif ?>