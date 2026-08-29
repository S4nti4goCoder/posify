<?php

require_once __DIR__ . "/../../lib/csrf.guard.php";

CsrfGuard::enforce();

require_once __DIR__ . "/../../lib/office.guard.php";
require_once __DIR__ . "/../../lib/cash.session.php";
require_once __DIR__ . "/../../lib/money.php";

date_default_timezone_set("America/Bogota");

OfficeGuard::start();

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["admin"])) {

    echo json_encode(["ok" => false, "error" => "logout"]);
    exit;
}

$office = (int) OfficeGuard::current();

$stmt = Connection::connect()->prepare("SELECT title_office FROM offices WHERE id_office = :id");
$stmt->execute([":id" => $office]);
$officeName = (string) $stmt->fetchColumn();
$admin  = (int) $_SESSION["admin"]->id_admin;
$action = isset($_POST["action"]) ? (string) $_POST["action"] : "";

switch ($action) {

    /*=============================================
    What the drawer should hold right now
    =============================================*/

    case "summary":

        $session = CashSession::open($office);

        if ($session === null) {

            echo json_encode(["ok" => false, "error" => "not_open"]);
            break;
        }

        $date    = (string) $session["date_created_cash"];
        $start   = (float) $session["start_cash"];
        $summary = CashSession::summary($office, $date, $start);
        $report  = CashSession::report($office, $date);

        echo json_encode([
            "ok"      => true,
            "office"  => $officeName,
            "session" => [
                "id"    => (int) $session["id_cash"],
                "start" => $start,
                "since" => (string) $session["date_start_cash"],
                "date"  => $date,
            ],
            "summary" => $summary,
            "report"  => $report,
        ]);

        break;

    case "open":

        echo json_encode(
            CashSession::openTill($office, $admin, (float) ($_POST["start"] ?? 0))
        );

        break;

    case "close":

        $session = CashSession::open($office);

        if ($session === null) {

            echo json_encode(["ok" => false, "error" => "not_open"]);
            break;
        }

        $date   = (string) $session["date_created_cash"];
        $start  = (float) $session["start_cash"];
        $since  = (string) $session["date_start_cash"];
        $report = CashSession::report($office, $date);

        $result = CashSession::closeTill($office, $admin, (float) ($_POST["counted"] ?? 0));

        if ($result["ok"]) {

            $who = trim((string) ($_SESSION["admin"]->name_admin ?? ""));

            $result["ticket"] = [
                "office"  => $officeName,
                "closed"  => date("Y-m-d H:i:s"),
                "admin"   => $who !== "" ? $who : (string) $_SESSION["admin"]->email_admin,
                "start"   => $start,
                "since"   => $since,
                "date"    => $date,
                "counted" => (float) ($_POST["counted"] ?? 0),
                "report"  => $report,
            ];
        }

        echo json_encode($result);

        break;

    /*=============================================
    A till left open on an earlier day blocks selling
    =============================================*/

    case "pending":

        $session = CashSession::open($office);

        $stale = $session !== null
              && (string) $session["date_created_cash"] < date("Y-m-d");

        echo json_encode([
            "ok"    => true,
            "open"  => $session !== null,
            "stale" => $stale,
            "date"  => $session === null ? "" : (string) $session["date_created_cash"],
        ]);

        break;

    default:

        echo json_encode(["ok" => false, "error" => "unknown_action"]);
}
