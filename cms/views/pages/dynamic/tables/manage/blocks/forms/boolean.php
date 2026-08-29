<?php

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "boolean"): ?>

<select 
class="form-select rounded"
name="<?php echo $module->columns[$i]->title_column ?>" 
id="<?php echo $module->columns[$i]->title_column ?>">

	<option value="1" 
	<?php if (!empty($data) && $data[$module->columns[$i]->title_column] == 1 ): ?>
	selected	
	<?php endif ?>>True</option>
	<option value="0"
	<?php if (!empty($data) && $data[$module->columns[$i]->title_column] == 0 ): ?>
	selected	
	<?php endif ?>>False</option>

</select>


<?php endif ?>