<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom d-lg-flex justify-content-lg-between">
	<div>
		<button class="btn btn-default border-0" id="menu-toggle">
			<i class="bi bi-list"></i>
		</button>
	</div>
	<div class="d-flex flex-wrap align-items-center">
		<div class="p-2 d-flex flex-wrap align-items-center">
			<?php

			/*=============================================
			The branch name comes from the session, never from the url.
			Reading it from ?offices= broke on an empty value and let any
			markup in the url land straight in the header.
			=============================================*/

			$officeLabel = (int) $_SESSION["admin"]->id_office_admin > 0
				? (string) ($_SESSION["admin"]->title_office ?? "")
				: "Multi-Sucursal";

			?>

			<?php if (OfficeGuard::canSwitch()): ?>
				<a href="#myOffices" data-bs-toggle="modal" class="badge badge-default backColor small rounded py-2 px-3 text-truncate" style="max-width:55vw"><?php echo htmlspecialchars($officeLabel, ENT_QUOTES, "UTF-8") ?></a>
			<?php else: ?>
				<span class="badge badge-default backColor small rounded py-2 px-3 text-truncate" style="max-width:55vw"><?php echo htmlspecialchars($officeLabel, ENT_QUOTES, "UTF-8") ?></span>
			<?php endif ?>
			<a href="#myProfile" class="ms-2 text-truncate" data-bs-toggle="modal" style="color:inherit; max-width:45vw">
				<i class="bi bi-person-circle"></i>
				<?php echo View::text($_SESSION["admin"]->name_admin) ?>
			</a>
		</div>
		<div class="p-2 mx-2">
			<a href="/logout" class="text-dark">
				<i class="bi bi-box-arrow-right"></i>
			</a>
		</div>
	</div>
</nav>