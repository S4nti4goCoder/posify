<?php

require_once __DIR__ . "/../api/models/connection.php";
require_once __DIR__ . "/money.php";
require_once __DIR__ . "/view.php";

// The sale receipt. orders.controller.php had "Print the receipt" as a comment
// with nothing under it since the project was written.
//
// Same 80 mm shape as the till closing, so both share ticket.css.

final class Receipt
{
    /** Empty string when the order does not exist. */
    public static function html(int $orderId): string
    {
        $db = Connection::connect();

        $stmt = $db->prepare(
            "SELECT o.transaction_order, o.subtotal_order, o.discount_order, o.tax_order,
                    o.total_order, o.method_order, o.date_order, o.extra_discount_order,
                    o.cash_order, o.card_order, o.note_order, o.transfer_order,
                    cl.name_client, cl.surname_client, cl.cc_client,
                    ofc.title_office, ofc.address_office, ofc.phone_office,
                    a.name_admin
               FROM orders o
               LEFT JOIN clients cl ON cl.id_client = o.id_client_order
               LEFT JOIN offices ofc ON ofc.id_office = o.id_office_order
               LEFT JOIN admins a ON a.id_admin = o.id_admin_order
              WHERE o.id_order = :id"
        );

        $stmt->execute([":id" => $orderId]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order === false) {

            return "";
        }

        $stmt = $db->prepare(
            "SELECT p.title_product, s.qty_sale, s.subtotal_sale, s.discount_sale
               FROM sales s
               LEFT JOIN products p ON p.id_product = s.id_product_sale
              WHERE s.id_order_sale = :id"
        );

        $stmt->execute([":id" => $orderId]);

        $lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return self::render($order, $lines);
    }

    private static function render(array $order, array $lines): string
    {
        $client = trim(($order["name_client"] ?? "") . " " . ($order["surname_client"] ?? ""));

        $html  = '<div class="tk">';
        $html .= '<div class="tk-center tk-bold">' . View::text($order["title_office"]) . '</div>';

        if (!empty($order["address_office"])) {
            $html .= '<div class="tk-center tk-small">' . View::text($order["address_office"]) . '</div>';
        }

        if (!empty($order["phone_office"])) {
            $html .= '<div class="tk-center tk-small">Tel. ' . View::text($order["phone_office"]) . '</div>';
        }

        $html .= '<div class="tk-sep"></div>';
        $html .= self::row("Orden", View::text($order["transaction_order"]));
        $html .= self::row("Fecha", View::text($order["date_order"]));
        $html .= self::row("Cliente", View::text($client !== "" ? $client : "Consumidor Final"));
        $html .= self::row("Documento", View::text($order["cc_client"]));
        $html .= self::row("Atendio", View::text($order["name_admin"]));

        $html .= '<div class="tk-sep"></div>';
        $html .= '<div class="tk-head">PRODUCTOS</div>';

        foreach ($lines as $line) {

            $html .= '<div class="tk-item">' . View::text($line["title_product"]) . '</div>';

            $label = (int) $line["qty_sale"] . " x " . Money::format(
                (int) $line["qty_sale"] > 0 ? $line["subtotal_sale"] / (int) $line["qty_sale"] : 0
            );

            if ((float) $line["discount_sale"] > 0) {
                $label .= "  -" . (float) $line["discount_sale"] . "%";
            }

            $html .= self::row($label, Money::format($line["subtotal_sale"]));
        }

        $html .= '<div class="tk-sep"></div>';
        $html .= self::row("Subtotal", Money::format($order["subtotal_order"]));
        $html .= self::row("Descuento", "-" . Money::format($order["discount_order"]));
        $html .= self::row("Impuesto", Money::format($order["tax_order"]));

        if ((float) $order["extra_discount_order"] > 0) {
            $html .= self::row("Descuento adicional", "-" . Money::format($order["extra_discount_order"]));
        }

        $html .= self::row("TOTAL", Money::format($order["total_order"]), true);
        $html .= self::row("Pago", View::text($order["method_order"]));
        $html .= self::payment($order);

        $html .= '<div class="tk-sep"></div>';
        $html .= '<div class="tk-center tk-small">Gracias por su compra</div>';
        $html .= '</div>';

        return $html;
    }

    /** What was handed over, the change, and the cashier note */
    private static function payment(array $order): string
    {
        $method = (string) $order["method_order"];
        $cash   = (float) $order["cash_order"];
        $card   = (float) $order["card_order"];
        $total  = (float) $order["total_order"];
        $html   = "";

        if ($method === "mixto") {

            $html .= self::row("Efectivo", Money::format($cash));
            $html .= self::row("Tarjeta", Money::format($card));

        } elseif ($method === "efectivo") {

            $html .= self::row("Recibido", Money::format($cash));

        } elseif ($method === "transferencia" && trim((string) $order["transfer_order"]) !== "") {

            $html .= self::row("Referencia", View::text($order["transfer_order"]));
        }

        if ($cash + $card > $total) {
            $html .= self::row("Vuelto", Money::format($cash + $card - $total));
        }

        if (trim((string) $order["note_order"]) !== "") {
            $html .= '<div class="tk-item tk-small">Nota: ' . View::text($order["note_order"]) . '</div>';
        }

        return $html;
    }

    private static function row(string $label, string $value, bool $bold = false): string
    {
        return '<div class="tk-row' . ($bold ? " tk-bold" : "") . '">'
             . '<span>' . $label . '</span><span>' . $value . '</span></div>';
    }
}
