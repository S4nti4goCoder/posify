<?php

require_once __DIR__ . "/../../lib/csrf.guard.php";
require_once __DIR__ . "/../../lib/money.php";

CsrfGuard::enforce();

require_once __DIR__ . "/../../lib/office.guard.php";
require_once __DIR__ . "/../../lib/view.php";
require_once __DIR__ . "/../../lib/inventory.php";
require_once __DIR__ . "/../../lib/walkin.client.php";
require_once "../controllers/curl.controller.php";
require_once "../controllers/template.controller.php";

date_default_timezone_set("America/Bogota");

class PosController
{

    /*=============================================
	    Load products
	=============================================*/
    public $limit;
    public $startAt;
    public $category;
    public $search;
    public $idOffice;

    /**
     * The catalogue of one branch.
     *
     * The generic relations endpoint cannot reach this any more: every join it
     * builds starts from the first table, and the stock lives in its own now.
     *
     * @return array{0:array,1:int} rows and how many pages they make
     */
    private function catalogue(): array
    {
        $db    = Connection::connect();
        $where = "s.id_office_stock = :office AND p.status_product = 1";
        $args  = [":office" => (int) $this->idOffice];

        if ($this->category != "all") {

            $where .= " AND p.id_category_product = :category";
            $args[":category"] = (int) $this->category;
        }

        if ($this->search != "") {

            $where .= " AND (p.title_product LIKE :title OR p.sku_product LIKE :sku)";
            $args[":title"] = "%" . $this->search . "%";
            $args[":sku"]   = "%" . $this->search . "%";
        }

        $from = " FROM products p
                  INNER JOIN stocks s ON s.id_product_stock = p.id_product
                  LEFT JOIN categories c ON c.id_category = p.id_category_product
                  WHERE " . $where;

        $stmt = $db->prepare("SELECT COUNT(*)" . $from);
        $stmt->execute($args);
        $total = (int) $stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT p.*, c.title_category, s.qty_stock" . $from .
            " ORDER BY p.id_product DESC LIMIT :startAt, :endAt"
        );

        foreach ($args as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(":startAt", (int) $this->startAt, PDO::PARAM_INT);
        $stmt->bindValue(":endAt", (int) $this->limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            $stmt->fetchAll(PDO::FETCH_OBJ),
            $this->limit > 0 ? (int) ceil($total / $this->limit) : 0,
        ];
    }

    public function loadProducts()
    {
        list($products, $totalPageProducts) = $this->catalogue();

        $method = "GET";
        $fields = array();
        $htmlProducts = "";

        if (!empty($products)) {
            foreach ($products as $key => $value) {
                $htmlProducts .= '<div class="col-12 col-lg-6 col-xl-4 p-2 btn addProductPos" idProduct="' . $value->id_product . '">
					<div class="card rounded border-0 position-relative">';
                if ($value->discount_product > 0) {
                    $htmlProducts .= '<div class="position-absolute small bg-red p-1 shadow-sm rounded" style="top:4px; left:4px; font-size:10px">' . $value->discount_product . '% OFF</div>';
                }
                $htmlProducts .= '<div class="position-absolute small bg-white p-1 shadow-sm rounded" style="top:4px; right:4px; font-size:10px">' . $value->sku_product . '</div>
						<img src="' . View::url($value->img_product) . '" class="card-img-top px-5 py-3 mx-auto" style="width:200px !important">
						<div class="card-body">
							<h6 class="font-weight-bold text-gray samll">' . View::text($value->title_category) . '</h6>
							<h6 class="card-title pb-2 font-weight-bold">' . View::text($value->title_product) . '</h6>
							<div class="d-flex justify-content-between">';
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
                $htmlProducts .= '<div class="card-text small h6 badge badge-default pb-0 ' . $colorStock . '" style="font-size:10px; padding-top:6px">
									' . $value->qty_stock . '
								</div>';
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
                if ($value->discount_product > 0) {
                    $htmlProducts .= '<span class="small ms-auto pe-1 h6 mt-1 text-red font-weight-bold" style="font-size:12px"><s>$ ' . Money::amount($price) . '</s></span>
									<div class="small h6 mt-1 textColor font-weight-bold"><strong>$ ' . Money::amount($discount) . '</strong></div>';
                } else {
                    $htmlProducts .= '<div class="small h6 mt-1 textColor font-weight-bold"><strong>$ ' . Money::amount($price) . '</strong></div>';
                }
                $htmlProducts .= '</div>
						</div>
					</div>
				</div>';
            }
        }
        $response = array(
            "htmlProducts" => $htmlProducts,
            "totalPagesProducts" => $totalPageProducts
        );

        echo json_encode($response);
    }

    /*=============================================
	    Create a new order
    =============================================*/
    public $token;
    public $seller;

    public function newOrder()
    {

        /*=============================================
		A cash session must be open for today
		=============================================*/
        $url = "cashs?linkTo=date_created_cash,status_cash,id_office_cash&equalTo=" . date("Y-m-d") . ",1," . $this->idOffice . "&select=status_cash";
        $method = "GET";
        $fields = array();

        $cash = CurlController::request($url, $method, $fields);

        if ($cash->status == 404) {
            echo "current cash error";
            return;
        } else {

            /*====================================================
			Yesterday cash session must be closed
			====================================================*/
            $yesterday = date("Y-m-d", strtotime(date("Y-m-d") . "- 1 days"));

            $url = "cashs?linkTo=date_created_cash,status_cash,id_office_cash&equalTo=" . $yesterday . ",1," . $this->idOffice . "&select=status_cash";
            $method = "GET";
            $fields = array();

            $cash = CurlController::request($url, $method, $fields);

            if ($cash->status == 200) {
                echo "yesterday cash error";
                return;
            }
        }

        /*=============================================
		Build the transaction number
		=============================================*/
        $transaction_order = TemplateController::genNumCode(12);


        /*=============================================
		The transaction number must not repeat
		=============================================*/
        $validate = TemplateController::transValidate($transaction_order);

        if ($validate) {

            /*=============================================
			Create a new order
			=============================================*/
            $url = "orders?&token=" . $this->token . "&table=admins&suffix=admin";
            $method = "POST";
            $fields = array(
                "transaction_order" => $transaction_order,
                "id_admin_order" => $this->seller,
                "id_office_order" => $this->idOffice,
                // the order starts on the walk in customer, which is what the
                // selector shows anyway; leaving it at 0 points at no client
                "id_client_order" => WalkInClient::idFor((int) $this->idOffice, $this->token) ?? 0,
                "status_order" => "Pendiente",
                "date_created_order" => date("Y-m-d")
            );

            $createOrder = CurlController::request($url, $method, $fields);

            if ($createOrder->status == 200) {
                $response = array(
                    "type" => "new",
                    "id_order" => $createOrder->results->lastId,
                    "transaction_order" => $transaction_order
                );
                echo json_encode($response);
            } else {
                echo "logout";
            }
        } else {

            /*=============================================
			Retry
			=============================================*/
            $ajax = new PosController();
            $ajax->token = $this->token;
            $ajax->seller = $this->seller;
            $ajax->idOffice = $this->idOffice;
            $ajax->newOrder();
        }
    }

    /*=============================================
	Update the order
	=============================================*/
    public $idOrder;
    public $idClient;
    public $subtotalOrder;
    public $discountOrder;
    public $taxOrder;
    public $totalOrder;

    public function updateOrder()
    {
        $url = "orders?id=" . $this->idOrder . "&nameId=id_order&token=" . $this->token . "&table=admins&suffix=admin";
        $method = "PUT";
        $fields = array(
            "id_client_order" => $this->idClient,
            "subtotal_order" => $this->subtotalOrder,
            "discount_order" => $this->discountOrder,
            "tax_order" => $this->taxOrder,
            "total_order" => $this->totalOrder
        );
        $fields = http_build_query($fields);

        $updateOrder = CurlController::request($url, $method, $fields);

        if ($updateOrder->status == 200) {
            echo "ok";
        } else {
            echo "logout";
        }
    }

    /*=============================================
	Add a new client
	=============================================*/
    public $name_client;
    public $surname_client;
    public $cc_client;
    public $email_client;
    public $phone_client;
    public $address_client;

    public function newClient()
    {
        $url = "clients?token=" . $this->token . "&table=admins&suffix=admin";
        $method = "POST";
        $fields = array(
            "name_client" => $this->name_client,
            "surname_client" => $this->surname_client,
            "cc_client" => $this->cc_client,
            "email_client" => $this->email_client,
            "phone_client" => $this->phone_client,
            "address_client" => $this->address_client,
            "id_office_client" => $this->idOffice,
            "date_created_client" => date("Y-m-d")
        );

        $addClient = CurlController::request($url, $method, $fields);

        if ($addClient->status == 200) {
            echo $addClient->results->lastId;
        } else {
            echo "logout";
        }
    }

    /*=============================================
	Add a product to the order
	=============================================*/
    public $idProduct;

    public function addProductPos()
    {
        $url = "relations?rel=purchases,products&type=purchase,product&linkTo=id_product&equalTo=" . $this->idProduct;
        $method = "GET";
        $fields = array();

        $getProduct = CurlController::request($url, $method, $fields);

        if ($getProduct->status == 200) {
            $product = $getProduct->results[0];
            if (Inventory::available((int) $this->idProduct, (int) $this->idOffice) < 1) {
                echo "error stock";
                return;
            } else {

                /*=============================================
				The product must not already be in the order
				=============================================*/
                $url = "sales?linkTo=id_order_sale,id_product_sale&equalTo=" . $this->idOrder . "," . $this->idProduct . "&select=id_sale";
                $method = "GET";
                $fields = array();

                $getSale = CurlController::request($url, $method, $fields);

                if ($getSale->status == 200) {
                    echo "product exist";
                    return;
                }

                /*=============================================
				Add to sales
				=============================================*/
                if ($product->discount_product > 0) {
                    $price_purchase = $product->price_purchase - ($product->price_purchase * ($product->discount_product / 100));
                } else {
                    $price_purchase = $product->price_purchase;
                }

                $url = "sales?token=" . $this->token . "&table=admins&suffix=admin";
                $method = "POST";
                $fields = array(
                    "id_order_sale" => $this->idOrder,
                    "id_product_sale" => $this->idProduct,
                    "tax_type_sale" => explode("_", $product->tax_product)[0],
                    "tax_sale" => explode("_", $product->tax_product)[1],
                    "discount_sale" => $product->discount_product,
                    "qty_sale" => 1,
                    "subtotal_sale" => $product->price_purchase,
                    "status_sale" => "Pendiente",
                    "id_admin_sale" => $this->seller,
                    "id_client_sale" => $this->idClient,
                    "id_office_sale" => $this->idOffice,
                    "date_created_sale" => date("Y-m-d")
                );

                $createSale = CurlController::request($url, $method, $fields);

                if ($createSale->status == 200) {
                    /*=============================================
					Return the HTML
					=============================================*/
                    $html = '<tr>			
								<td>
									<div>
										<img src="' . View::url($product->img_product) . '" class="me-auto rounded mt-2 float-start"style="width:60px !important; height:60px !important">
										<div class="ms-2 float-start">
											<span class="badge badge-default backColor rounded" style="font-size:10px">' . View::text($product->sku_product) . '</span>';
                    if ($product->discount_product > 0) {
                        $html .= '<span class="badge badge-default bg-red rounded ms-1" style="font-size:10px">' . $product->discount_product . '%</span>
												<h6 class="font-weight-bold  mb-0 text-muted"><strong>' . View::text($product->title_product) . '</strong></h6>
												<small>$ ' . Money::amount($price_purchase) . ' <span class="ms-1 text-red" style="font-size:12px"><s>$ ' . Money::amount($product->price_purchase) . ' </s></span></small>';
                    } else {
                        $html .= '<h6 class="font-weight-bold  mb-0 text-muted"><strong>' . View::text($product->title_product) . '</strong></h6>
												<small>$ ' . Money::amount($product->price_purchase) . '</small>';
                    }
                    $html .= '</div>
									</div>
								</td>
								<td class="text-center">
									<div class="d-flex justify-content-center">										
										<div class="input-group mb-3 mt-2" style="width:160px">										
											<span class="input-group-text rounded-start bg-light btnQty" type="btnMin" style="cursor:pointer" key="' . $product->id_product . '">
												<i class="bi bi-dash-lg"></i>
											</span>
											<input type="number" class="form-control text-center showQuantity showQuantity_' . $product->id_product . '" value="1" key="' . $product->id_product . '" style="font-size:12px">
											<span class="input-group-text rounded-end bg-light btnQty" type="btnMax" style="cursor:pointer" key="' . $product->id_product . '">
												<i class="bi bi-plus-lg"></i>
											</span>
										</div>
									</div>							
								</td>
								<td>
									<h6 class="text-center my-3 pricePurchase pricePurchase_' . $product->id_product . '" pricePurchase="' . (int) round((float) $product->price_purchase) . '" originalPricePurchase="' . (int) round((float) $product->price_purchase) . '">$ ' . Money::amount($product->price_purchase) . '</h6>
								</td>
								<td class="text-center">
									<button type="button" class="btn btn-sm rounded ms-1 mt-2 py-2 px-3 bg-red deleteSale deleteSale_' . $product->id_product . '" idSale="' . $createSale->results->lastId . '" taxSale="' . explode("_", $product->tax_product)[1] . '" discountSale="' . $product->discount_product . '">
										<i class="bi bi-trash"></i>
									</button>
								</td>
							</tr>';
                    echo $html;
                } else {
                    echo "logout";
                }
            }
        }
    }

    /*=============================================
	Update quantity
	=============================================*/
    public $idSaleUpdate;
    public $qtySale;
    public $subtotalSale;

    public function updateSale()
    {

        /*=============================================
        A quantity above what is on hand used to go straight through
        =============================================*/

        $sale = CurlController::request("sales?linkTo=id_sale&equalTo=" . $this->idSaleUpdate . "&select=id_product_sale", "GET", array());

        if (!empty($sale) && $sale->status == 200) {

            $available = Inventory::available((int) $sale->results[0]->id_product_sale, (int) OfficeGuard::current());

            if ((int) $this->qtySale > $available) {

                echo "error stock " . $available;
                return;
            }
        }

        $url = "sales?id=" . $this->idSaleUpdate . "&nameId=id_sale&token=" . $this->token . "&table=admins&suffix=admin";
        $method = "PUT";
        $fields = array(
            "qty_sale" => $this->qtySale,
            "subtotal_sale" => round($this->subtotalSale, 2)
        );

        $fields = http_build_query($fields);

        $updateSale = CurlController::request($url, $method, $fields);

        if ($updateSale->status == 200) {
            echo "ok";
        } else {
            echo "logout";
        }
    }

    /*=============================================
	Remove a sale
	=============================================*/
    public $idSaleDelete;
    public function deleteSale()
    {
        /*=============================================
		The sale must not be completed
		=============================================*/
        $url = "sales?linkTo=id_sale,status_sale&equalTo=" . $this->idSaleDelete . ",Completada";
        $method = "GET";
        $fields = array();

        $getSale = CurlController::request($url, $method, $fields);
        if ($getSale->status == 200) {
            echo "error";
            return;
        } else {
            /*=============================================
			Delete a sale
			=============================================*/
            $url = "sales?id=" . $this->idSaleDelete . "&nameId=id_sale&token=" . $this->token . "&table=admins&suffix=admin";
            $method = "DELETE";
            $fields = array();

            $deleteSale = CurlController::request($url, $method, $fields);
            if ($deleteSale->status == 200) {
                echo "ok";
            } else {
                echo "logout";
            }
        }
    }

    /*=============================================
	Remove every sale
	=============================================*/
    public $idOrderSale;
    public function deleteAllSale()
    {
        /*=============================================
		The sale must not be completed
		=============================================*/
        $url = "sales?linkTo=id_order_sale,status_sale&equalTo=" . $this->idOrderSale . ",Pendiente";
        $method = "GET";
        $fields = array();

        $getSale = CurlController::request($url, $method, $fields);
        if ($getSale->status == 200) {
            $countDeleteSale = 0;
            foreach ($getSale->results as $key => $value) {
                /*=============================================
				Delete a sale
				=============================================*/
                $url = "sales?id=" . $value->id_sale . "&nameId=id_sale&token=" . $this->token . "&table=admins&suffix=admin";
                $method = "DELETE";
                $fields = array();

                $deleteSale = CurlController::request($url, $method, $fields);
                if ($deleteSale->status == 200) {
                    $countDeleteSale++;
                    if ($countDeleteSale == count($getSale->results)) {
                        echo "ok";
                    }
                }
            }
        } else {
            echo "error";
        }
    }

    /*=============================================
	Remove the order
	=============================================*/
    public $idOrderDelete;
    public function deleteOrder()
    {
        /*=============================================
		The order must not be completed
		=============================================*/
        $url = "orders?linkTo=id_order,status_order&equalTo=" . $this->idOrderDelete . ",Completada";
        $method = "GET";
        $fields = array();

        $getOrder = CurlController::request($url, $method, $fields);
        if ($getOrder->status == 200) {
            echo "error";
        } else {

            /*=============================================
			Delete the order
			=============================================*/
            $url = "orders?id=" . $this->idOrderDelete . "&nameId=id_order&token=" . $this->token . "&table=admins&suffix=admin";
            $method = "DELETE";
            $fields = array();

            $deleteOrder = CurlController::request($url, $method, $fields);
            if ($deleteOrder->status == 200) {
                $url = "sales?linkTo=id_order_sale&equalTo=" . $this->idOrderDelete;
                $method = "GET";
                $fields = array();

                $getSales = CurlController::request($url, $method, $fields);
                if ($getSales->status == 200) {
                    $countDeleteSales = 0;
                    foreach ($getSales->results as $key => $value) {

                        /*=============================================
						Delete a sale
						=============================================*/
                        $url = "sales?id=" . $value->id_sale . "&nameId=id_sale&token=" . $this->token . "&table=admins&suffix=admin";
                        $method = "DELETE";
                        $fields = array();

                        $deleteSale = CurlController::request($url, $method, $fields);
                        if ($deleteSale->status == 200) {
                            $countDeleteSales++;
                            if ($countDeleteSales == count($getSales->results)) {
                                echo "ok";
                            }
                        }
                    }
                }
            }
        }
    }
}

/*=============================================
Branch and administrator come from the session, never from the request
=============================================*/

OfficeGuard::start();

$sessionOffice = OfficeGuard::current();
$sessionAdmin  = OfficeGuard::currentAdmin();

if ($sessionOffice === null || $sessionAdmin === null) {

    echo "logout";
    exit;
}

/*=============================================
	Load products
=============================================*/
if (isset($_POST["limit"])) {
    $ajax = new PosController();
    $ajax->limit = $_POST["limit"];
    $ajax->startAt = $_POST["startAt"];
    $ajax->category = $_POST["category"];
    $ajax->search = $_POST["search"];
    $ajax->idOffice = $sessionOffice;
    $ajax->loadProducts();
}

/*=============================================
	Create a new order
=============================================*/
if (isset($_POST["order"])) {
    $ajax = new PosController();
    $ajax->token = Session::token();
    $ajax->seller = $sessionAdmin;
    $ajax->idOffice = $sessionOffice;
    $ajax->newOrder();
}

/*=============================================
Update the order
=============================================*/
if (isset($_POST["idOrderUpdate"])) {
    $ajax = new PosController();
    $ajax->token = Session::token();
    $ajax->idOrder = $_POST["idOrderUpdate"];
    $ajax->idClient = $_POST["idClient"];
    $ajax->subtotalOrder = $_POST["subtotalOrder"];
    $ajax->discountOrder = $_POST["discountOrder"];
    $ajax->taxOrder = $_POST["taxOrder"];
    $ajax->totalOrder = $_POST["totalOrder"];
    $ajax->updateOrder();
}

/*=============================================
Add a new client
=============================================*/
if (isset($_POST["name_client"])) {
    $ajax = new PosController();
    $ajax->name_client = $_POST["name_client"];
    $ajax->surname_client = $_POST["surname_client"];
    $ajax->cc_client = $_POST["cc_client"];
    $ajax->email_client = $_POST["email_client"];
    $ajax->phone_client = $_POST["phone_client"];
    $ajax->address_client = $_POST["address_client"];
    $ajax->idOffice = $sessionOffice;
    $ajax->token = Session::token();
    $ajax->newClient();
}

/*=============================================
Add a product to the order
=============================================*/
if (isset($_POST["idProduct"])) {
    $ajax = new PosController();
    $ajax->idProduct = $_POST["idProduct"];
    $ajax->idOrder = $_POST["idOrder"];
    $ajax->idClient = $_POST["idClient"];
    $ajax->seller = $sessionAdmin;
    $ajax->idOffice = $sessionOffice;
    $ajax->token = Session::token();
    $ajax->addProductPos();
}

/*=============================================
Update quantity
=============================================*/
if (isset($_POST["idSaleUpdate"])) {
    $ajax = new PosController();
    $ajax->idSaleUpdate = $_POST["idSaleUpdate"];
    $ajax->qtySale = $_POST["qtySale"];
    $ajax->subtotalSale = $_POST["subtotalSale"];
    $ajax->token = Session::token();
    $ajax->updateSale();
}

/*=============================================
Remove a sale
=============================================*/
if (isset($_POST["idSaleDelete"])) {
    $ajax = new PosController();
    $ajax->idSaleDelete = $_POST["idSaleDelete"];
    $ajax->token = Session::token();
    $ajax->deleteSale();
}

/*=============================================
Remove every sale
=============================================*/
if (isset($_POST["idOrderSale"])) {
    $ajax = new PosController();
    $ajax->idOrderSale = $_POST["idOrderSale"];
    $ajax->token = Session::token();
    $ajax->deleteAllSale();
}

/*=============================================
Remove the order
=============================================*/
if (isset($_POST["idOrderDelete"])) {
    $ajax = new PosController();
    $ajax->idOrderDelete = $_POST["idOrderDelete"];
    $ajax->token = Session::token();
    $ajax->deleteOrder();
}
