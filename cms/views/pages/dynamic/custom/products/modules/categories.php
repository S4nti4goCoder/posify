<?php

require_once __DIR__ . "/../../../../../../../api/models/connection.php";

/*=============================================
A branch only carries what it has stock rows for
=============================================*/

if (!function_exists("countCatalogue")) {

    function countCatalogue(int $officeId, int $categoryId = 0): int
    {
        if ($officeId <= 0) {

            return 0;
        }

        $where = "s.id_office_stock = :office AND p.status_product = 1";
        $args  = [":office" => $officeId];

        if ($categoryId > 0) {

            $where .= " AND p.id_category_product = :category";
            $args[":category"] = $categoryId;
        }

        $stmt = Connection::connect()->prepare(
            "SELECT COUNT(*) FROM products p
               INNER JOIN stocks s ON s.id_product_stock = p.id_product
              WHERE " . $where
        );

        $stmt->execute($args);

        return (int) $stmt->fetchColumn();
    }
}

?>
<?php

require_once __DIR__ . "/../../../../../../../lib/view.php";

/*=============================================
Read the categories
=============================================*/
$url = "categories?linkTo=status_category&equalTo=1";
$method = "GET";
$fields = array();

$categories = CurlController::request($url, $method, $fields);

if ($categories->status == 200) {
    $categories = $categories->results;
} else {
    $categories = array();
}

?>

<!--===================================
JD SLIDER	
=====================================-->
<div class="jd-slider mb-0 pb-0">
    <div class="slide-inner">
        <ul class="slide-area">

            <?php if (!empty($categories)): ?>

                <li>
                    <div class="border-0 rounded text-center bg-white mx-1 p-3 pb-0 loadCategory" idCategory="all">
                        <img src="/views/assets/files/67e742629d30818.png" class="img-fluid mx-auto" style="width:50px; cursor:pointer">
                        <p class="pt-2 mb-0 lead" style="cursor:move"><strong>Todo</strong></p>

                        <?php
                        if ($_SESSION["admin"]->id_office_admin > 0) {
                            $totalProducts = countCatalogue((int) $_SESSION["admin"]->id_office_admin);

                        } else {
                            $totalProducts = 0;
                        }
                        ?>
                        <p class="small pb-3" style="cursor:move"><?php echo $totalProducts ?> items</p>
                    </div>
                </li>

                <?php foreach ($categories as $key => $value): ?>

                    <li>
                        <div class="border-0 rounded text-center bg-white mx-1 p-3 pb-0 loadCategory" idCategory="<?php echo $value->id_category ?>">
                            <img src="<?php echo View::url($value->img_category) ?>" class="img-fluid mx-auto" style="width:50px; cursor:pointer">
                            <p class="pt-2 mb-0 lead" style="cursor:move"><strong><?php echo View::text($value->title_category) ?></strong></p>

                            <?php
                            if ($_SESSION["admin"]->id_office_admin > 0) {
                                $totalProducts = countCatalogue((int) $_SESSION["admin"]->id_office_admin, (int) $value->id_category);

                            } else {
                                $totalProducts = 0;
                            }
                            ?>

                            <p class="small pb-3" style="cursor:move"><?php echo $totalProducts ?> items</p>
                        </div>
                    </li>

                <?php endforeach ?>

            <?php endif ?>

        </ul>
        <a href="#" class="prev ps-1">
            <i class="bi bi-chevron-left"></i>
        </a>
        <a href="#" class="next ps-1">
            <i class="bi bi-chevron-right"></i>
        </a>
    </div>
    <div class="controller d-none">
        <div class="indicate-area"></div>
    </div>

</div>