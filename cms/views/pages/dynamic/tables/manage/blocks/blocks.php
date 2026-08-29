<div class="card rounded border-0 shadow mb-3 pb-3">
	
	<div class="card-body">

		<label for="<?php echo $module->columns[$i]->title_column ?>" class="form-label float-start text-capitalize">
			<?php echo $module->columns[$i]->alias_column ?>:
		</label>
		<span class="float-end badge badge-default border small rounded text-muted">
			<?php echo $module->columns[$i]->type_column ?>
		</span>
		<div class="clearfix"></div>

		<?php 
		
		/*=============================================
		Text field
		=============================================*/
		
		include "forms/text.php"; 

		/*=============================================
		Textarea field
		=============================================*/
		
		include "forms/textarea.php"; 

		/*=============================================
		Integer field
		=============================================*/
		
		include "forms/int.php"; 

		/*=============================================
		Decimal field
		=============================================*/
		
		include "forms/double.php"; 

		/*=============================================
		Select field
		=============================================*/
		
		include "forms/select.php"; 

		/*=============================================
		Boolean field
		=============================================*/
		
		include "forms/boolean.php"; 

		/*=============================================
		Array field
		=============================================*/
		
		include "forms/array.php"; 

		/*=============================================
		Object field
		=============================================*/
		
		include "forms/object.php"; 

		/*=============================================
		JSON field
		=============================================*/
		
		include "forms/_json.php"; 

		/*=============================================
		File, image and video field
		=============================================*/
		
		include "forms/file.php"; 

		/*=============================================
		Date field
		=============================================*/
		
		include "forms/date.php"; 

		/*=============================================
		Time field
		=============================================*/
		
		include "forms/time.php"; 

		/*=============================================
		Date and time field
		=============================================*/
		
		include "forms/datetime.php"; 

		/*=============================================
		Automatic timestamp field
		=============================================*/

		include "forms/timestamp.php"; 

		/*=============================================
		Code field
		=============================================*/

		include "forms/code.php"; 

		/*=============================================
		Color field
		=============================================*/

		include "forms/color.php"; 

		/*=============================================
		Password field
		=============================================*/

		include "forms/password.php"; 

		/*=============================================
		Email field
		=============================================*/

		include "forms/email.php"; 

		/*=============================================
		Relations field
		=============================================*/

		include "forms/relations.php";

		/*=============================================
		Relations field
		=============================================*/

		include "forms/chatgpt.php";


		?>

		<div class="valid-feedback">Válido.</div>
		<div class="invalid-feedback">Campo inválido.</div>
	
	</div>

</div>