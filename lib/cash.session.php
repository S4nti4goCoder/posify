<?php

require_once __DIR__ . "/../api/models/connection.php";

// A till session is timed by the system, not typed by the cashier.
//
// Both dates used to be free text fields. Left empty they reached the column
// as '' and were stored as 0000-00-00, and nothing stopped a wrong hour being
// typed into the record that a shift is later reconciled against.

final class CashSession
{
    public const SUFFIX = "cash";


    /** The session that is on for this branch, or null. */
    public static function open(int $officeId): ?array
    {
        $stmt = Connection::connect()->prepare(
            "SELECT id_cash, start_cash, date_start_cash, date_created_cash
               FROM cashs
              WHERE id_office_cash = :office AND status_cash = 1
              ORDER BY id_cash DESC
              LIMIT 1"
        );

        $stmt->execute([":office" => $officeId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * What the drawer should hold. Summed in SQL, not walked in PHP.
     *
     * @return array{income:float,orders:int,bills:float,expected:float}
     */
    public static function summary(int $officeId, string $date, float $start): array
    {
        $db = Connection::connect();

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(total_order - card_order), 0) AS total, COUNT(*) AS orders
               FROM orders
              WHERE date_created_order = :date
                AND id_office_order    = :office
                AND method_order       IN ('efectivo', 'mixto')
                AND status_order       = 'Completada'"
        );

        $stmt->execute([":date" => $date, ":office" => $officeId]);
        $sales = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(cost_bill), 0) AS total
               FROM bills
              WHERE date_created_bill = :date AND id_office_bill = :office"
        );

        $stmt->execute([":date" => $date, ":office" => $officeId]);
        $bills = (float) $stmt->fetchColumn();

        $income = (float) $sales["total"];

        return [
            "income"   => $income,
            "orders"   => (int) $sales["orders"],
            "bills"    => $bills,
            "expected" => $start + $income - $bills,
        ];
    }


    /**
     * Everything the closing ticket prints, in three grouped queries.
     *
     * @return array{orders:int,total:float,discounts:float,methods:array,top:array}
     */
    public static function report(int $officeId, string $date): array
    {
        $db   = Connection::connect();
        $args = [":date" => $date, ":office" => $officeId];

        $stmt = $db->prepare(
            "SELECT COUNT(*) AS orders,
                    COALESCE(SUM(total_order), 0)    AS total,
                    COALESCE(SUM(discount_order), 0) AS discounts
               FROM orders
              WHERE date_created_order = :date
                AND id_office_order    = :office
                AND status_order       = 'Completada'"
        );

        $stmt->execute($args);
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);

        /*=============================================
        A mixed sale lands on both lines: its cash leg under efectivo and its
        card leg under tarjeta, so the three still add up to the day
        =============================================*/

        $stmt = $db->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN method_order IN ('efectivo', 'mixto')
                                  THEN total_order - card_order ELSE 0 END), 0) AS efectivo,
                COALESCE(SUM(CASE WHEN method_order = 'tarjeta' THEN total_order
                                  WHEN method_order = 'mixto'   THEN card_order
                                  ELSE 0 END), 0) AS tarjeta,
                COALESCE(SUM(CASE WHEN method_order = 'transferencia'
                                  THEN total_order ELSE 0 END), 0) AS transferencia
               FROM orders
              WHERE date_created_order = :date
                AND id_office_order    = :office
                AND status_order       = 'Completada'"
        );

        $stmt->execute($args);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $methods = [
            "efectivo"      => (float) $row["efectivo"],
            "tarjeta"       => (float) $row["tarjeta"],
            "transferencia" => (float) $row["transferencia"],
        ];

        $stmt = $db->prepare(
            "SELECT p.title_product AS name, SUM(s.qty_sale) AS qty
               FROM sales s
               LEFT JOIN products p ON p.id_product = s.id_product_sale
              WHERE s.date_created_sale = :date
                AND s.id_office_sale    = :office
                AND s.status_sale       = 'Completada'
              GROUP BY s.id_product_sale, p.title_product
              ORDER BY qty DESC
              LIMIT 3"
        );

        $stmt->execute($args);

        $top = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

            $top[] = [
                "name" => (string) $row["name"],
                "qty"  => (int) $row["qty"],
            ];
        }

        return [
            "orders"    => (int) $totals["orders"],
            "total"     => (float) $totals["total"],
            "discounts" => (float) $totals["discounts"],
            "methods"   => $methods,
            "top"       => $top,
        ];
    }
    /** @return array{ok:bool,error?:string,id?:int} */
    public static function openTill(int $officeId, int $adminId, float $start): array
    {
        if (self::open($officeId) !== null) {

            return ["ok" => false, "error" => "already_open"];
        }

        if ($start < 0) {

            return ["ok" => false, "error" => "negative_start"];
        }

        $db = Connection::connect();

        $stmt = $db->prepare(
            "INSERT INTO cashs
                (start_cash, status_cash, date_start_cash, id_admin_cash, id_office_cash, date_created_cash)
             VALUES (:start, 1, NOW(), :admin, :office, CURDATE())"
        );

        $stmt->execute([":start" => $start, ":admin" => $adminId, ":office" => $officeId]);

        return ["ok" => true, "id" => (int) $db->lastInsertId()];
    }

    /**
     * Closes the till: stores what was counted next to what was expected, so
     * the gap stays on the record instead of being recomputed later.
     *
     * @return array{ok:bool,error?:string,expected?:float,gap?:float}
     */
    public static function closeTill(int $officeId, int $adminId, float $counted): array
    {
        $session = self::open($officeId);

        if ($session === null) {

            return ["ok" => false, "error" => "not_open"];
        }

        $date    = (string) $session["date_created_cash"];
        $start   = (float) $session["start_cash"];
        $summary = self::summary($officeId, $date, $start);

        $gap = $counted - $summary["expected"];

        $stmt = Connection::connect()->prepare(
            "UPDATE cashs
                SET money_cash    = :income,
                    bills_cash    = :bills,
                    diff_cash     = :expected,
                    end_cash      = :counted,
                    gap_cash      = :gap,
                    date_end_cash = NOW(),
                    status_cash   = 0
              WHERE id_cash = :id AND id_office_cash = :office"
        );

        $stmt->execute([
            ":income"   => $summary["income"],
            ":bills"    => -$summary["bills"],
            ":expected" => $summary["expected"],
            ":counted"  => $counted,
            ":gap"      => $gap,
            ":id"       => (int) $session["id_cash"],
            ":office"   => $officeId,
        ]);

        if ($stmt->rowCount() === 0) {

            return ["ok" => false, "error" => "not_saved"];
        }

        return ["ok" => true, "expected" => $summary["expected"], "gap" => $gap];
    }
    /**
     * The Estado switch is what actually closes a till: the POS refuses to
     * open an order unless a session is on for today. Returns the moment of
     * closing, or null when this switch is not that.
     */
    public static function closedAt(string $table, string $column, $value): ?string
    {
        if ($table !== "cashs" || $column !== "status_cash") {
            return null;
        }

        return (int) $value === 0 ? date("Y-m-d H:i:s") : null;
    }

    /** Fills the dates in $_POST so the generic save path carries them. */
    public static function stampDates(string $suffix, bool $isEdit): void
    {
        if ($suffix !== self::SUFFIX) {
            return;
        }

        $now = date("Y-m-d H:i:s");

        if (!$isEdit) {

            $_POST["date_start_cash"] = $now;
            $_POST["date_end_cash"]   = "";

            return;
        }

        /*=============================================
        The closing amount is what marks the till as closed
        =============================================*/

        $closing = trim((string) ($_POST["end_cash"] ?? ""));
        $closed  = trim((string) ($_POST["date_end_cash"] ?? ""));

        if ($closing !== "" && (float) $closing != 0.0 && $closed === "") {

            $_POST["date_end_cash"] = $now;
        }
    }
}
