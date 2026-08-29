<?php

require_once __DIR__ . "/../../../../../../../lib/view.php";

require_once __DIR__ . "/../../../../../../../lib/walkin.client.php";

/*=============================================
Only the clients of the branch being worked in. This used to ask for every
client in the system, so a cashier saw the other branches customers too.
=============================================*/

$currentOffice = (int) $_SESSION["admin"]->id_office_admin;

$url    = "clients?linkTo=id_office_client&equalTo=" . $currentOffice;
$method = "GET";
$fields = array();

$clients = CurlController::request($url, $method, $fields);

if ($clients->status == 200) {
    $clients = $clients->results;
} else {
    $clients = array();
}

/*=============================================
The walk in customer, so a sale can be charged without asking the buyer to
register. Created for this branch the first time it is needed.
=============================================*/

$walkInClient = WalkInClient::idFor($currentOffice, $_SESSION["admin"]->token_admin);

if ($walkInClient !== null && empty(array_filter($clients, fn($c) => (int) $c->id_client === $walkInClient))) {

    $refresh = CurlController::request($url, $method, $fields);
    $clients = $refresh->status == 200 ? $refresh->results : $clients;
}

/*=============================================
Which client the selector starts on: the one already on the order, or the
walk in customer when the order has none yet.
=============================================*/

$selectedClient = !empty($order) && (int) $order->id_client_order > 0
    ? (int) $order->id_client_order
    : $walkInClient;

?>

<div class="row mb-4">
    <div class="col-7">
        <div class="form-group">
            <label class="mb-1" for="clientList">Cliente</label>
            <span class="btn badge badge-default border-0 float-end rounded backColor <?php if (empty($order)): ?> d-none <?php endif ?>" id="addClient">Agregar</span>
            <select class="form-control rounded-start custom-select select2" id="clientList">
                <option value="">Buscar</option>
                <?php if (!empty($clients)): ?>
                    <?php foreach ($clients as $key => $value): ?>
                        <option
                            value="<?php echo $value->id_client ?>"
                            <?php if ((int) $value->id_client === (int) $selectedClient): ?> selected <?php endif ?>><?php echo View::text($value->name_client) . " " . View::text($value->surname_client) . " " . View::text($value->cc_client) ?></option>
                    <?php endforeach ?>
                <?php endif ?>
            </select>
        </div>
    </div>
    <div class="col-5">
        <div class="form-group">
            <label class="mb-1" for="seller">Vendedor</label>
            <div class="input-group">
                <input type="text" readonly class="form-control rounded-start bg-light" id="seller" idAdmin="<?php echo $_SESSION["admin"]->id_admin ?>" value="<?php echo View::text($_SESSION["admin"]->name_admin) ?>">
                <span class="input-group-text rounded-end bg-light"><i class="fas fa-user-tie"></i></span>
            </div>
        </div>
    </div>
</div>
