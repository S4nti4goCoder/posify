<?php

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "password"): ?>

 	<input 
	type="password" 
	class="form-control rounded"
	id="<?php echo $module->columns[$i]->title_column ?>"
	name="<?php echo $module->columns[$i]->title_column ?>"
	placeholder="******"
	>
 	
	<?php $passwordRulesFor = $module->columns[$i]->title_column; include __DIR__ . "/../../../../../../modules/password.rules.php" ?>

<?php endif ?>
