<?php

require_once __DIR__ . "/../../../../../../../../lib/view.php";

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "textarea"): ?>

 	<textarea 
	class="form-control rounded"
	rows="3"
	id="<?php echo $module->columns[$i]->title_column ?>" 
	name="<?php echo $module->columns[$i]->title_column ?>"><?php if (!empty($data)): ?><?php echo View::text($data[$module->columns[$i]->title_column]) ?><?php endif ?></textarea>
 	
<?php endif ?>

