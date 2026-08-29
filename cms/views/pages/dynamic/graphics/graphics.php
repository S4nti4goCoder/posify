<?php

require_once __DIR__ . "/../../../../../lib/view.php";

$xAxis = array();
$yAxis = array();

$content = json_decode($module->content_module);

$suffix = explode("_", $content->yAxis);
$suffix = end($suffix);

require_once __DIR__ . "/../../../../../api/models/connection.php";

/*=============================================
Grouped and summed by the database. This used to select every row and
then run a loop inside a loop, comparing each row against every label.

Table and columns come from the module settings, so they are proven
against the catalog before reaching the query.
=============================================*/

$table   = (string) $content->table;
$byBranch = $module->title_module == "ventas por sucursal"
	|| $module->title_module == "compras por sucursal";

if ($byBranch) {

	$content->xAxis = "title_office";
}

$xCol = (string) $content->xAxis;
$yCol = (string) $content->yAxis;

$columns = SchemaGuard::tableExists($table) ? SchemaGuard::columnsOf($table) : array();
$officeColumn = "id_office_" . $suffix;

$valid = $columns !== array()
	&& in_array($yCol, $columns, true)
	&& ($byBranch ? in_array($officeColumn, $columns, true) : in_array($xCol, $columns, true));

if ($valid) {

	$from  = "`" . $table . "` t";
	$group = "t.`" . $xCol . "`";

	if ($byBranch) {

		$from  = "`" . $table . "` t INNER JOIN offices o ON o.id_office = t.`" . $officeColumn . "`";
		$group = "o.title_office";

	} else if ($module->title_module == "gráfico de ventas mensuales") {

		$group = "LEFT(t.`" . $xCol . "`, 7)";
	}

	$where = array();
	$args  = array();

	if ($module->title_module == "gráfico de ventas diarias" && $module->id_page_module == 13
		&& in_array("status_order", $columns, true)) {

		$where[] = "t.status_order = 'Completada'";
	}

	if ($_SESSION["admin"]->id_office_admin > 0 && in_array($officeColumn, $columns, true)) {

		$where[] = "t.`" . $officeColumn . "` = :office";
		$args[":office"] = (int) $_SESSION["admin"]->id_office_admin;
	}

	$sql = "SELECT " . $group . " AS label, COALESCE(SUM(t.`" . $yCol . "`), 0) AS amount
	          FROM " . $from;

	if (!empty($where)) {

		$sql .= " WHERE " . implode(" AND ", $where);
	}

	$sql .= " GROUP BY label ORDER BY label";

	$stmt = Connection::connect()->prepare($sql);
	$stmt->execute($args);

	foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

		$label = (string) $row["label"];

		$xAxis[] = $label;
		$yAxis[$label] = $row["amount"] + 0;
	}
}


?>

<div class="<?php if ($module->width_module == "100"): ?> col-lg-12 <?php endif ?><?php if ($module->width_module == "75"): ?> col-lg-9 <?php endif ?><?php if ($module->width_module == "50"): ?> col-lg-6 <?php endif ?><?php if ($module->width_module == "33"): ?> col-lg-4 <?php endif ?><?php if ($module->width_module == "25"): ?> col-lg-3 <?php endif ?> col-12 mb-3 position-relative">

	<?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>

		<div class="position-absolute border rounded bg-white" style="top:0px; right:12px; z-index:100">

			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 myModule" item='<?php echo json_encode($module) ?>' idPage="<?php echo $page->results[0]->id_page ?>">
				<i class="bi bi-pencil-square"></i>
			</button>

			<button type="button" class="btn btn-sm text-muted rounded m-0 px-1 py-0 border-0 deleteModule" idModule=<?php echo base64_encode($module->id_module) ?>>
				<i class="bi bi-trash"></i>
			</button>


		</div>

	<?php endif ?>


	<div class="card rounded">

		<div class="card-header bg-white rounded-top h4 font-weight-bold text-capitalize py-3">
			<?php echo $module->title_module ?>
		</div>

		<div class="card-body p-4">
			<canvas id="chart-<?php echo str_replace(" ", "_", $module->title_module) ?>" height="500"></canvas>
		</div>

	</div>

</div>

<script>
	if ($("#chart-<?php echo str_replace(" ", "_", $module->title_module) ?>").length > 0) {

		var graphicChart = $("#chart-<?php echo str_replace(" ", "_", $module->title_module) ?>");
		var tagsChart = new Chart(graphicChart, {

			type: "<?php echo $content->type ?>",
			data: {
				labels: [

					<?php
					foreach ($xAxis as $index => $item) {
						echo View::js($item) . ",";
					}
					?>

				],
				datasets: [{
					backgroundColor: 'rgba(<?php echo $content->color ?>,.55)',
					borderColor: 'rgb(<?php echo $content->color ?>)',
					data: [

						<?php
						foreach ($xAxis as $index => $item) {
							echo View::js($yAxis[$item]) . ",";
						}
						?>


					]
				}]
			}, //close data
			options: {
				maintainAspectRatio: false,
				tooltips: {
					mode: 'index',
					intersect: true
				},
				hover: {
					mode: 'index',
					intersect: true
				},
				legend: {
					display: false
				},
				scales: {
					yAxes: [{
						display: true,
						gridLines: {
							display: true
						},
						ticks: $.extend({
							beginAtZero: true,
							// Include a dollar sign in the ticks
							callback: function(value) {
								if (value >= 1000) {
									value /= 1000
									value += 'k'
								}

								return value
							}
						}, {
							fontColor: '#495057',
							fontStyle: 'bold'
						})

					}],
					xAxes: [{
						display: true,
						gridLines: {
							display: true
						},
						ticks: {
							fontColor: '#495057',
							fontStyle: 'bold'
						}
					}]

				} //close scales

			} //close options

		})
	}
</script>