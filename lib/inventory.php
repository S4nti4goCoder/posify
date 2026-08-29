<?php

require_once __DIR__ . "/../api/models/connection.php";
require_once __DIR__ . "/money.php";

// Stock as a ledger, not a counter.
//
// Every movement is written once and never edited; stocks.qty_stock holds the
// running balance per branch and is kept in step with it. Before this, nothing decremented
// on a sale at all: the balance was recomputed from scratch on every page load
// by asking for each product's purchases and sales, which meant two cashiers
// selling at the same time simply overwrote each other.
//
// The checkout runs as one database transaction with the stock rows locked, so
// a sale either lands whole or not at all, and two tills cannot both take the
// last unit.

final class Inventory
{
    public const SALE       = "sale";
    public const PURCHASE   = "purchase";
    public const RETURN_    = "return";
    public const ADJUSTMENT = "adjustment";
    public const TRANSFER   = "transfer";

    /** Units on hand in one branch. */
    public static function available(int $productId, int $officeId): int
    {
        $stmt = Connection::connect()->prepare(
            "SELECT qty_stock FROM stocks
              WHERE id_product_stock = :id AND id_office_stock = :office"
        );

        $stmt->execute([":id" => $productId, ":office" => $officeId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? 0 : (int) $row["qty_stock"];
    }


    /**
     * Stock of one branch keyed by product.
     *
     * The products table shows a Stock column again, and a product now has one
     * figure per branch, so the rows are enriched with the one that applies.
     */
    public static function mapFor(int $officeId): array
    {
        if ($officeId > 0) {

            $stmt = Connection::connect()->prepare(
                "SELECT id_product_stock, qty_stock FROM stocks WHERE id_office_stock = :office"
            );

            $stmt->execute([":office" => $officeId]);

        } else {

            /*=============================================
            Zero means every branch, so the figure is the sum of them
            =============================================*/

            $stmt = Connection::connect()->prepare(
                "SELECT id_product_stock, SUM(qty_stock) AS qty_stock
                   FROM stocks GROUP BY id_product_stock"
            );

            $stmt->execute();
        }

        $map = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

            $map[(int) $row["id_product_stock"]] = (int) $row["qty_stock"];
        }

        return $map;
    }

    /** What a branch holds of one product, set by hand from the product form. */
    public static function setFor(int $productId, int $officeId, int $qty): void
    {
        Connection::connect()->prepare(
            "INSERT INTO stocks (id_product_stock, id_office_stock, qty_stock, date_created_stock)
             VALUES (:id, :office, :qty, CURDATE())
             ON DUPLICATE KEY UPDATE qty_stock = :qty2"
        )->execute([
            ":id"     => $productId,
            ":office" => $officeId,
            ":qty"    => $qty,
            ":qty2"   => $qty,
        ]);
    }

    /**
     * Completes an order: checks stock, writes the movements, moves the
     * balance and marks the order and its lines, all in one transaction.
     *
     * @return array{ok:bool,error?:string,product?:string,available?:int,asked?:int}
     */
    public static function checkout(int $orderId, int $officeId, int $adminId, array $payment): array
    {
        $db = Connection::connect();

        try {

            $db->beginTransaction();

            /*=============================================
            Lock the order first, so a second checkout of the same order
            waits here instead of running alongside this one
            =============================================*/

            $order = self::lockedOrder($db, $orderId, $officeId);

            if ($order === null) {

                $db->rollBack();

                return ["ok" => false, "error" => "order_not_found"];
            }

            if ($order["status_order"] === "Completada") {

                $db->rollBack();

                return ["ok" => false, "error" => "already_completed"];
            }

            $lines = self::orderLines($db, $orderId);

            if ($lines === []) {

                $db->rollBack();

                return ["ok" => false, "error" => "empty_order"];
            }

            /*=============================================
            Lock every product involved and check the whole basket before
            touching anything, so a shortage on the last line does not
            leave the earlier ones already discounted
            =============================================*/

            foreach ($lines as $line) {

                $stock = self::lockedStock($db, (int) $line["id_product_sale"], $officeId);

                if ($stock === null || $stock < (int) $line["qty_sale"]) {

                    $db->rollBack();

                    return [
                        "ok"        => false,
                        "error"     => "insufficient_stock",
                        "product"   => (string) $line["title_product"],
                        "available" => (int) ($stock ?? 0),
                        "asked"     => (int) $line["qty_sale"],
                    ];
                }
            }

            foreach ($lines as $line) {

                self::record(
                    $db,
                    (int) $line["id_product_sale"],
                    $officeId,
                    self::SALE,
                    -(int) $line["qty_sale"],
                    $orderId,
                    $adminId
                );

                self::moveBalance($db, (int) $line["id_product_sale"], $officeId, -(int) $line["qty_sale"]);

                $db->prepare("UPDATE sales SET status_sale = 'Completada' WHERE id_sale = :id")
                    ->execute([":id" => (int) $line["id_sale"]]);
            }

            /*=============================================
            The discount typed at checkout. Clamped here: the browser is free
            to post anything
            =============================================*/

            $base  = Money::round($order["total_order"]);
            $extra = Money::round($payment["extra"] ?? 0);

            if ($extra < 0) {
                $extra = 0.0;
            }

            if ($extra > $base) {
                $extra = $base;
            }

            $total  = $base - $extra;
            $method = (string) ($payment["method"] ?? "");
            $cash   = Money::round($payment["cash"] ?? 0);
            $card   = Money::round($payment["card"] ?? 0);

            if ($method !== "efectivo" && $method !== "mixto") {
                $cash = 0.0;
                $card = 0.0;
            }

            if (($method === "efectivo" || $method === "mixto") && $cash + $card < $total) {

                $db->rollBack();

                return ["ok" => false, "error" => "insufficient_payment"];
            }

            $db->prepare(
                "UPDATE orders
                    SET method_order = :method, transfer_order = :transfer,
                        extra_discount_order = :extra, total_order = :total,
                        cash_order = :cash, card_order = :card, note_order = :note,
                        status_order = 'Completada'
                  WHERE id_order = :id"
            )->execute([
                ":method"   => $method,
                ":transfer" => (string) ($payment["transfer"] ?? ""),
                ":extra"    => $extra,
                ":total"    => $total,
                ":cash"     => $cash,
                ":card"     => $card,
                ":note"     => mb_substr(trim((string) ($payment["note"] ?? "")), 0, 255),
                ":id"       => $orderId,
            ]);

            $db->commit();

            return ["ok" => true, "transaction" => (string) $order["transaction_order"]];

        } catch (Throwable $e) {

            if ($db->inTransaction()) {
                $db->rollBack();
            }

            error_log("Checkout failed for order $orderId: " . $e->getMessage());

            return ["ok" => false, "error" => "failed"];
        }
    }

    /** Writes one line in the ledger. Quantity is signed: negative leaves. */
    public static function record(PDO $db, int $productId, int $officeId, string $type, int $quantity, ?int $referenceId, int $adminId, string $note = ""): void
    {
        $db->prepare(
            "INSERT INTO stock_movements
                (id_product_movement, id_office_movement, id_admin_movement, type_movement,
                 qty_movement, reference_movement, note_movement, date_movement, date_created_movement)
             VALUES (:product, :office, :admin, :type, :quantity, :reference, :note, NOW(), CURDATE())"
        )->execute([
            ":product"   => $productId,
            ":office"    => $officeId,
            ":type"      => $type,
            ":quantity"  => $quantity,
            ":reference" => $referenceId,
            ":admin"     => $adminId,
            ":note"      => $note,
        ]);
    }

    /** Creates the row when a branch touches a product for the first time. */
    private static function moveBalance(PDO $db, int $productId, int $officeId, int $delta): void
    {
        $db->prepare(
            "INSERT INTO stocks (id_product_stock, id_office_stock, qty_stock, date_created_stock)
             VALUES (:id, :office, :delta, CURDATE())
             ON DUPLICATE KEY UPDATE qty_stock = qty_stock + :delta2"
        )->execute([
            ":id"     => $productId,
            ":office" => $officeId,
            ":delta"  => $delta,
            ":delta2" => $delta,
        ]);
    }

    private static function lockedOrder(PDO $db, int $orderId, int $officeId): ?array
    {
        $stmt = $db->prepare(
            "SELECT id_order, status_order, transaction_order, total_order
               FROM orders
              WHERE id_order = :id AND id_office_order = :office
              FOR UPDATE"
        );

        $stmt->execute([":id" => $orderId, ":office" => $officeId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    private static function lockedStock(PDO $db, int $productId, int $officeId): ?int
    {
        $stmt = $db->prepare(
            "SELECT qty_stock FROM stocks
              WHERE id_product_stock = :id AND id_office_stock = :office
              FOR UPDATE"
        );

        $stmt->execute([":id" => $productId, ":office" => $officeId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : (int) $row["qty_stock"];
    }

    private static function orderLines(PDO $db, int $orderId): array
    {
        $stmt = $db->prepare(
            "SELECT s.id_sale, s.id_product_sale, s.qty_sale, p.title_product
               FROM sales s
               LEFT JOIN products p ON p.id_product = s.id_product_sale
              WHERE s.id_order_sale = :order"
        );

        $stmt->execute([":order" => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
