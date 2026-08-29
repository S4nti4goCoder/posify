<?php

/**
 * Included from blocks/blocks.php, which defines the variables below.
 *
 * @var object $module Table module being rendered
 * @var int    $i      Index of the column inside $module->columns
 * @var array  $data   The record when editing, empty when creating
 */

if ($module->columns[$i]->type_column == "relations"): ?>

	<?php 

	/*=============================================
	Read every table
	=============================================*/

	require_once "controllers/install.controller.php";
	$tables = InstallController::getTables();

	?>

	<select 
	class="form-select rounded mb-3 select2 changeRelations"
	idColumn="<?php echo $module->columns[$i]->id_column ?>">

		<?php if ($module->columns[$i]->matrix_column != null): ?>

			<option value="<?php echo $module->columns[$i]->matrix_column ?>"><?php echo $module->columns[$i]->matrix_column ?></option>

		<?php else: ?>

			<option value="">Seleccione Tabla</option>


		<?php endif	?>

			<?php foreach ($tables as $index => $item): ?>

				<option value="<?php echo $item ?>" <?php if (!empty($data) && $module->columns[$i]->matrix_column == $item): ?> selected <?php endif ?> ><?php echo $item ?></option>
				
			<?php endforeach ?>


	</select>

	<div class="mb-3"></div>

	<select 
	class="form-select rounded select2 selectRelations"
	name="<?php echo $module->columns[$i]->title_column ?>" 
	id="<?php echo $module->columns[$i]->title_column ?>">

	<?php if ($module->columns[$i]->matrix_column != null): ?>

		<?php 

			$url = $module->columns[$i]->matrix_column;
			$method = "GET";
			$fields = array();

			$columnsTable = CurlController::request($url,$method,$fields);

			if($columnsTable->status == 200){

				$columnsTable = $columnsTable->results;

			}else{

				$columnsTable = array();
			}

		?>

		<?php if (!empty($columnsTable)): ?>

			<?php foreach ($columnsTable as $index => $item): ?>

				<?php

				$row         = json_decode(json_encode($item), true);
				$optionValue = $row[array_keys((array) $item)[0]];
				$optionLabel = $row[array_keys((array) $item)[1]];

				/*=============================================
				When editing, keep the stored value. When creating a branch
				column, start on the branch being worked in instead of on
				whichever branch happens to come first in the list.
				=============================================*/

				if (!empty($data)) {

					$isSelected = $optionValue == $data[$module->columns[$i]->title_column];

				} else {

					$isSelected = strpos($module->columns[$i]->title_column, "id_office_") === 0
						&& (int) $optionValue === (int) OfficeGuard::current();
				}

				?>

				<option value="<?php echo $optionValue ?>" <?php if ($isSelected): ?> selected <?php endif ?>><?php echo $optionValue ?> - <?php echo $optionLabel ?></option>

			<?php endforeach ?>

		<?php endif ?>

	<?php endif ?>
		

	</select>

<?php endif ?>