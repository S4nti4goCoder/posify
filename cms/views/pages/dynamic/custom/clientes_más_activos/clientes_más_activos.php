<?php

require_once __DIR__ . "/../../../../../../lib/view.php";

require_once __DIR__ . "/../../../../../../api/models/connection.php";

/*=============================================
Summed and ranked by the database, and each client comes back with the
same query instead of one round trip per row
=============================================*/

$where = "s.status_sale = 'Completada'";
$args  = [];

if ($_SESSION["admin"]->id_office_admin > 0) {

	$where .= " AND s.id_office_sale = :office";
	$args[":office"] = (int) $_SESSION["admin"]->id_office_admin;
}

$stmt = Connection::connect()->prepare(
	"SELECT cl.name_client, cl.surname_client, cl.email_client, cl.phone_client,
	        o.title_office, SUM(s.qty_sale) AS qty
	   FROM sales s
	   INNER JOIN clients cl ON cl.id_client = s.id_client_sale
	   LEFT JOIN offices o ON o.id_office = cl.id_office_client
	  WHERE " . $where . "
	  GROUP BY cl.id_client, cl.name_client, cl.surname_client, cl.email_client, cl.phone_client, o.title_office
	  ORDER BY qty DESC
	  LIMIT 5"
);

$stmt->execute($args);

$topClients = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<!--==============================
Custom
================================-->
<div class="<?php if ($module->width_module == "100"): ?> col-lg-12 <?php endif ?><?php if ($module->width_module == "75"): ?> col-lg-9 <?php endif ?><?php if ($module->width_module == "50"): ?> col-lg-6 <?php endif ?><?php if ($module->width_module == "33"): ?> col-lg-4 <?php endif ?><?php if ($module->width_module == "25"): ?> col-lg-3 <?php endif ?> col-12 mb-3 position-relative">

	<?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>
		<div class="position-absolute border rounded" style="top:0px; right:12px; z-index:100">
			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 myModule" item='<?php echo json_encode($module) ?>' idPage="<?php echo $page->results[0]->id_page ?>">
				<i class="bi bi-pencil-square"></i>
			</button>
			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 deleteModule" idModule=<?php echo base64_encode($module->id_module) ?>>
				<i class="bi bi-trash"></i>
			</button>
		</div>
	<?php endif ?>

	<!--==============================
   	Start Custom
  	================================-->

	<div class="card rounded">
		<div class="card-header">
			<h3 class="card-title">Clientes más activos</h3>
		</div>
		<div class="card-body">

			<?php if (!empty($topClients)): ?>
				<div class="table-responsive"><ul class="list-group">
					<?php foreach ($topClients as $listClients): ?>
						<li class="list-group-item">
							<div class="d-flex border-bottom">
								<div class="flex-fill w-100 text-center">
									<span class="badge badge-default backColor rounded small mt-2"><?php echo TemplateController::reduceText(View::text($listClients->title_office), 12) ?></span>
								</div>
								<div class="flex-fill w-100 text-center">
									<p class="mt-2"><?php echo TemplateController::reduceText(View::text($listClients->name_client) . " " . View::text($listClients->surname_client), 10) ?></p>
								</div>
								<div class="flex-fill w-100 text-center">
									<p class="mt-2"><?php echo TemplateController::reduceText(View::text($listClients->email_client), 10) ?></p>
								</div>
								<div class="flex-fill w-100 text-center">
									<p class="mt-2"><?php echo View::text($listClients->phone_client) ?></p>
								</div>
								<div class="flex-fill w-100 text-center">
									<span class="badge badge-default bg-teal rounded small mt-2"><?php echo (int) $listClients->qty ?></span>
								</div>
							</div>
						</li>
					<?php endforeach ?>
				</ul></div>
			<?php endif ?>

		</div>
	</div>
</div>