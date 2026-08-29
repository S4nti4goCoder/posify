<?php

require_once __DIR__ . "/../../lib/inventory.php";
require_once __DIR__ . "/../../lib/office.guard.php";
require_once __DIR__ . "/../../lib/view.php";
require_once __DIR__ . "/../../lib/receipt.php";

class OrdersController
{
    /*=============================================
	Manage orders.

	The old version marked the order completed and then fired one request per
	line to mark the sales. Nothing decremented stock, nothing checked it, and
	a failure halfway left a paid order with lines still pending. It is now a
	single transaction in Inventory::checkout().
	=============================================*/

    public function manageOrder()
    {
        if (!isset($_POST["idOrderPay"])) {

            return;
        }

        echo '<script>
				fncMatPreloader("on");
				fncSweetAlert("loading", "Procesando la orden...", "");
			</script>';

        $result = Inventory::checkout(
            (int) $_POST["idOrderPay"],
            (int) OfficeGuard::current(),
            (int) OfficeGuard::currentAdmin(),
            [
                "method"   => $_POST["methodPay"] ?? "",
                "transfer" => $_POST["transferPay"] ?? "",
                "extra"    => $_POST["extraDiscountPay"] ?? 0,
                "cash"     => $_POST["cashPay"] ?? 0,
                "card"     => $_POST["cardPay"] ?? 0,
                "note"     => $_POST["notePay"] ?? "",
            ]
        );

        if ($result["ok"]) {

            /*=============================================
			Open the cash drawer
			=============================================*/

            /*=============================================
			Print the receipt
			=============================================*/

            $receipt = Receipt::html((int) $_POST["idOrderPay"]);

            /*=============================================
			panel.php runs before dynamic.php loads pos.js, so the call waits
			for the document instead of firing into an undefined function
			=============================================*/

            echo '<script>
					    document.addEventListener("DOMContentLoaded", function () {
					        fncMatPreloader("off");
					        fncFormatInputs();
					        fncShowReceipt(' . View::js($receipt) . ', ' . View::js((string) $result["transaction"]) . ');
					    });
				    </script>';

            return;
        }

        echo '<div class="alert alert-danger mt-3 p-3 rounded alertPos">' . $this->message($result) . '</div>
			<script>
				fncMatPreloader("off");
                fncSweetAlert("close", "", "");
				fncFormatInputs();
			</script>';
    }

    /*=============================================
	What went wrong, in words the cashier can act on
	=============================================*/

    private function message(array $result): string
    {
        switch ($result["error"] ?? "") {

            case "insufficient_stock":
                return "No hay existencias suficientes de <strong>" . View::text($result["product"] ?? "") . "</strong>. "
                    . "Quedan " . (int) ($result["available"] ?? 0) . " y la orden pide " . (int) ($result["asked"] ?? 0) . ".";

            case "insufficient_payment":
                return "El pago recibido no cubre el total de la orden.";

            case "already_completed":
                return "Esta orden ya fue cobrada.";

            case "empty_order":
                return "La orden no tiene productos.";

            case "order_not_found":
                return "La orden no existe o no pertenece a esta sucursal.";

            default:
                return "Error al procesar la orden. No se cobró nada, inténtalo de nuevo.";
        }
    }
}
