<?php

require_once __DIR__ . "/../../../../../../../../lib/view.php";
require_once __DIR__ . "/../../../../../../../../lib/cash.session.php";

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "datetime"):

	$column = $module->columns[$i]->title_column;
	$value  = !empty($data) ? View::text($data[$column]) : "";

	// the till is timed by the system, so these two are shown but never typed
	$auto = in_array($column, ["date_start_cash", "date_end_cash"], true);

	$placeholder = "YYYY-mm-dd HH:mm";

	if ($auto) {
		$placeholder = $column == "date_start_cash"
			? "Se registra al abrir la caja"
			: "Se registra al cerrar la caja";
	}
	?>

	<div class="input-group">
		
		<input 
		type="text" 
		class="form-control rounded-start<?php echo $auto ? "" : " datetimepicker" ?>" 
		placeholder="<?php echo $placeholder ?>"
		id="<?php echo $column ?>"  
		name="<?php echo $column ?>"
		value="<?php echo $value ?>"
		<?php echo $auto ? "readonly" : "" ?>
		>

		<div class="input-group-text rounded-end">
			<i class="bi bi-calendar-week"></i>
		</div>

	</div>

<?php endif ?>
