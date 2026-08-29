<?php

require_once __DIR__ . "/../../../../../../../../lib/view.php";

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "time"): ?>

	<div class="input-group">
		
		<input 
		type="text" 
		class="form-control rounded-start timepicker" 
		placeholder="HH:mm"
		id="<?php echo $module->columns[$i]->title_column ?>"  
		name="<?php echo $module->columns[$i]->title_column ?>"
		value="<?php if (!empty($data)): ?><?php echo View::text($data[$module->columns[$i]->title_column]) ?><?php endif ?>"
		>

		<div class="input-group-text rounded-end">
			<i class="far fa-clock"></i>
		</div>

	</div>

<?php endif ?>