<?php

/**
 * Shown instead of the login form when the url carries a recovery code that is
 * still valid. The code proves the person reached the mailbox; only now may the
 * password change.
 *
 * @var array $resetAccount The account the code belongs to
 * @var string $resetCode   The code itself, carried back in the form
 */

?>

<div class="d-flex flex-wrap justify-content-center align-content-center vh-100 px-3">

	<div class="card border-0 rounded shadow p-4 w-100" style="max-width:420px">

		<form method="POST" class="needs-validation" novalidate>

			<?php echo CsrfGuard::field() ?>

			<input type="hidden" name="resetCode" value="<?php echo View::raw($resetCode) ?>">

			<h3 class="pt-3 text-center">Nueva contraseña</h3>

			<p class="text-center text-muted small mb-4"><?php echo View::raw($resetAccount["email_admin"]) ?></p>

			<div class="form-group mb-3">

				<label for="newPassword" class="form-label">Escribe tu nueva contraseña</label>

				<input
					type="password"
					class="form-control rounded"
					id="newPassword"
					name="newPassword"
					minlength="8"
					required
					placeholder="Mínimo 8 caracteres">

				<div class="invalid-feedback">Debe tener al menos 8 caracteres.</div>

				<?php $passwordRulesFor = "newPassword"; include __DIR__ . "/../../modules/password.rules.php" ?>

			</div>

			<button type="submit" class="btn btn-dark btn-block w-100 rounded mt-3 backColor">Guardar contraseña</button>

			<div class="text-center mt-3">
				<a href="/" class="textColor" style="font-size:12px">Volver a ingresar</a>
			</div>

			<?php

			require_once "controllers/admins.controller.php";

			$reset = new AdminsController();
			$reset->completeReset();

			?>

		</form>

	</div>

</div>
