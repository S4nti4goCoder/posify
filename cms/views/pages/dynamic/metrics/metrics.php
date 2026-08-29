<?php
require_once __DIR__ . "/../../../../../lib/money.php";

$metric = 0;

$content = json_decode($module->content_module);

$suffix = explode("_", $content->column);
$suffix = end($suffix);

require_once __DIR__ . "/../../../../../api/models/connection.php";

/*=============================================
Counted, summed and averaged by the database. This used to select every
row of the table and add them up in a loop.

The table and column come from the module settings and cannot be bound,
so they are proven against the catalog before reaching the query.
=============================================*/

$table  = (string) $content->table;
$column = (string) $content->column;

$valid = SchemaGuard::tableExists($table)
	&& in_array($column, SchemaGuard::columnsOf($table), true);

if ($valid) {

	$where = array();
	$args  = array();

	if ($module->title_module == "ventas" && $module->id_page_module == 13
		&& in_array("status_order", SchemaGuard::columnsOf($table), true)) {

		$where[] = "status_order = 'Completada'";
	}

	$officeColumn = "id_office_" . $suffix;

	if ($_SESSION["admin"]->id_office_admin > 0
		&& in_array($officeColumn, SchemaGuard::columnsOf($table), true)) {

		$where[] = "`" . $officeColumn . "` = :office";
		$args[":office"] = (int) $_SESSION["admin"]->id_office_admin;
	}

	$sql = "SELECT COUNT(*) AS rows_total,
	               COALESCE(SUM(`" . $column . "`), 0) AS rows_sum,
	               COALESCE(AVG(`" . $column . "`), 0) AS rows_avg
	          FROM `" . $table . "`";

	if (!empty($where)) {

		$sql .= " WHERE " . implode(" AND ", $where);
	}

	$stmt = Connection::connect()->prepare($sql);
	$stmt->execute($args);

	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($content->type == "total") {

		$metric = (int) $row["rows_total"];
	}

	if ($content->type == "add") {

		$metric = (float) $row["rows_sum"];
	}

	if ($content->type == "average") {

		$metric = (float) $row["rows_avg"];
	}
}


?>


<div class="<?php if ($module->width_module == "100"): ?> col-lg-12 <?php endif ?><?php if ($module->width_module == "75"): ?> col-lg-9 <?php endif ?><?php if ($module->width_module == "50"): ?> col-lg-6 <?php endif ?><?php if ($module->width_module == "33"): ?> col-lg-4 <?php endif ?><?php if ($module->width_module == "25"): ?> col-lg-3 <?php endif ?> col-12 mb-3 position-relative">

	<?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>
		<div class="position-absolute border rounded bg-white" style="top:0px; right:10px">
			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 myModule" item='<?php echo json_encode($module) ?>' idPage="<?php echo $page->results[0]->id_page ?>">
				<i class="bi bi-pencil-square"></i>
			</button>
			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 deleteModule" idModule=<?php echo base64_encode($module->id_module) ?>>
				<i class="bi bi-trash"></i>
			</button>
		</div>
	<?php endif ?>

	<div class="rounded text-white" style="background:rgba(<?php echo $content->color ?>, .55) !important">
		<div class="d-flex justify-content-between p-3">
			<div class="inner">
				<h5 class="font-weight-bold text-capitalize"><?php echo $module->title_module ?></h5>
				<?php if (!$valid): ?>

					<h2 class="pt-2" title="La columna configurada no existe en la base">&mdash;</h2>
					<small class="d-block">Configuración desactualizada</small>

				<?php else: ?>

					<?php if ($content->config == "unit"): ?>
					<h2 class="pt-2"><?php echo $metric ?></h1>
					<?php endif ?>
					<?php if ($content->config == "price"): ?>
						<h2 class="pt-2">$<?php echo Money::amount($metric) ?></h1>
						<?php endif ?>

				<?php endif ?>
			</div>
			<div class="display-2 text-center pt-2 pe-2" style="color:rgb(<?php echo $content->color ?>) !important">
				<i class="<?php echo $content->icon ?>"></i>
			</div>
		</div>
		<div class="text-center p-2 rounded-bottom" style="background:rgb(<?php echo $content->color ?>) !important"></div>
	</div>
</div>