<?php

require_once __DIR__ . "/../api/models/connection.php";

// A pending order is a draft, not a quote.
//
// Its lines kept the price of the moment each product was added, so an order
// left open across a price change was charged at prices that no longer exist.
// Opening it prices it again at today's.
//
// Only pending orders: a charged one records what was actually sold and must
// never move.

final class OrderPricing
{
    public static function reprice(int $orderId): void
    {
        $db = Connection::connect();

        $stmt = $db->prepare("SELECT status_order FROM orders WHERE id_order = :id");
        $stmt->execute([":id" => $orderId]);

        if ($stmt->fetchColumn() !== "Pendiente") {

            return;
        }

        /*=============================================
        Each line at the current price of its product, discount included
        =============================================*/

        $db->prepare(
            "UPDATE sales s
               INNER JOIN products p ON p.id_product = s.id_product_sale
               INNER JOIN purchases pu ON pu.id_product_purchase = p.id_product
                SET s.subtotal_sale = ROUND(pu.price_purchase * s.qty_sale),
                    s.discount_sale = p.discount_product
              WHERE s.id_order_sale = :id"
        )->execute([":id" => $orderId]);

        /*=============================================
        And the order from its lines, the same arithmetic the till does
        =============================================*/

        $db->prepare(
            "UPDATE orders o
               INNER JOIN (
                   SELECT id_order_sale,
                          COALESCE(SUM(subtotal_sale), 0) AS base,
                          COALESCE(SUM(subtotal_sale * discount_sale / 100), 0) AS less,
                          COALESCE(SUM((subtotal_sale - (subtotal_sale * discount_sale / 100))
                                       * tax_sale / 100), 0) AS vat
                     FROM sales
                    WHERE id_order_sale = :id
                    GROUP BY id_order_sale
               ) AS line ON line.id_order_sale = o.id_order
                SET o.subtotal_order = ROUND(line.base),
                    o.discount_order = ROUND(line.less),
                    o.tax_order      = ROUND(line.vat),
                    o.total_order    = ROUND(line.base) - ROUND(line.less) + ROUND(line.vat)
              WHERE o.id_order = :id2"
        )->execute([":id" => $orderId, ":id2" => $orderId]);
    }
}
