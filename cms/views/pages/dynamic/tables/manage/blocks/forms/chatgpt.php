<?php

require_once __DIR__ . "/../../../../../../../../lib/view.php";

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "chatgpt"): ?>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

	<div class="input-group mb-3">

		<span class="input-group-text rounded-start">Prompt: </span>

		<textarea 
		class="form-control rounded-end changeChatGPT"
		idPrompt="<?php echo $module->columns[$i]->id_column ?>"
		titlePrompt="<?php echo $module->columns[$i]->title_column ?>"
		><?php echo $module->columns[$i]->matrix_column ?></textarea>

	</div>

	<textarea 
	class="form-control rounded summernote"
	id="<?php echo $module->columns[$i]->title_column ?>" 
	name="<?php echo $module->columns[$i]->title_column ?>"><?php if (!empty($data)): ?><?php echo View::text($data[$module->columns[$i]->title_column]) ?><?php endif ?></textarea>

<?php endif ?>