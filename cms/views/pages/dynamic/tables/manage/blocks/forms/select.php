<?php

require_once __DIR__ . "/../../../../../../../../lib/view.php";

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "select"): ?>

	<div class="input-group mb-3">
		
		<input 
		type="text"
		class="form-control rounded changeSelectType tags-input"
		idColumn="<?php echo $module->columns[$i]->id_column ?>"
		titleColumn="<?php echo $module->columns[$i]->title_column ?>"
		value="<?php echo $module->columns[$i]->matrix_column ?>"
		preValue="<?php if (!empty($data)): ?><?php echo View::text($data[$module->columns[$i]->title_column])?><?php endif ?>"
		>
	</div>

	<select 
	class="form-select rounded select2"
	name="<?php echo $module->columns[$i]->title_column ?>" 
	id="<?php echo $module->columns[$i]->title_column ?>">

	<?php if ($module->columns[$i]->matrix_column != null): ?>

		<?php foreach (explode(",",$module->columns[$i]->matrix_column) as $index => $item):?>

			<option value="<?php echo $item ?>" <?php if (!empty($data) && $data[$module->columns[$i]->title_column] == $item): ?> selected <?php endif ?>><?php echo $item ?></option>
			
		<?php endforeach ?>
		
	<?php endif ?>

	</select>

<?php endif ?>