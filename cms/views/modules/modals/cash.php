<?php

require_once __DIR__ . "/../../../../lib/money.php";

/**
 * Included from tables.php when the module is the till.
 * Opening, the count at closing, and the ticket that comes out of it.
 */

$denominations = [100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100, 50];
?>

<!-- Open the till -->
<div class="modal fade" id="modalOpenCash" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-sm">
		<div class="modal-content rounded">

			<div class="modal-header">
				<div>
					<h5 class="modal-title mb-0">Abrir caja</h5>
					<small class="text-muted">Ingresa el monto inicial</small>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body">

				<?php echo CsrfGuard::field() ?>

				<label class="form-label small fw-bold" for="startCash">Monto de apertura</label>

				<div class="input-group">
					<span class="input-group-text rounded-start">$</span>
					<input type="number" step="1" min="0" value="0" class="form-control rounded-end" id="startCash">
				</div>

				<div class="small text-danger mt-2 d-none" id="openCashError"></div>

			</div>

			<div class="modal-footer d-flex justify-content-between">
				<button type="button" class="btn btn-dark rounded" data-bs-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-default backColor rounded" id="confirmOpenCash">Abrir caja</button>
			</div>

		</div>
	</div>
</div>


<!-- Close the till -->
<div class="modal fade" id="modalCloseCash" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
		<div class="modal-content rounded">

			<div class="modal-header">
				<div>
					<h5 class="modal-title mb-0">Cerrar caja</h5>
					<small class="text-muted">Revisa el resumen del día y cuenta el efectivo</small>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body">
				<div class="row g-3">

					<!-- Summary of the day -->
					<div class="col-lg-6">

						<div class="border rounded p-3 mb-3">
							<div class="small text-muted fw-bold mb-2">RESUMEN DEL DÍA</div>
							<div class="d-flex justify-content-between"><span>Transacciones</span><span id="cashOrders">0</span></div>
							<div class="d-flex justify-content-between"><span>Total ventas</span><span class="fw-bold" id="cashTotal">$ 0</span></div>
							<div class="d-flex justify-content-between"><span>Descuentos</span><span id="cashDiscounts">$ 0</span></div>
						</div>

						<div class="border rounded p-3 mb-3">
							<div class="small text-muted fw-bold mb-2">POR MÉTODO DE PAGO</div>
							<div class="d-flex justify-content-between"><span>Efectivo</span><span id="cashByCash">$ 0</span></div>
							<div class="d-flex justify-content-between"><span>Tarjeta</span><span id="cashByCard">$ 0</span></div>
							<div class="d-flex justify-content-between"><span>Transferencia</span><span id="cashByTransfer">$ 0</span></div>
						</div>

						<div class="border rounded p-3 mb-3 d-none" id="cashTopBox">
							<div class="small text-muted fw-bold mb-2">MÁS VENDIDOS</div>
							<ol class="mb-0 ps-3 small" id="cashTop"></ol>
						</div>

						<div class="border rounded p-3">
							<div class="d-flex justify-content-between"><span>Base inicial</span><span id="cashStart">$ 0</span></div>
							<div class="d-flex justify-content-between"><span>+ Efectivo ventas</span><span id="cashIncome">$ 0</span></div>
							<div class="d-flex justify-content-between"><span>− Gastos</span><span id="cashBills">$ 0</span></div>
							<hr class="my-2">
							<div class="d-flex justify-content-between fw-bold"><span>Esperado en caja</span><span id="cashExpected">$ 0</span></div>
						</div>

					</div>

					<!-- The count -->
					<div class="col-lg-6">

						<div class="border rounded p-3 mb-3">

							<div class="small text-muted fw-bold mb-2">ARQUEO DESGLOSADO</div>

							<div class="row g-2">

								<?php foreach ($denominations as $bill): ?>

									<div class="col-12 col-sm-6">
										<div class="d-flex align-items-center gap-1">
											<span class="small text-nowrap" style="width:64px"><?php echo Money::format($bill) ?></span>
											<input type="number" min="0" step="1" value="0" placeholder="0"
												class="form-control form-control-sm rounded countBill"
												data-value="<?php echo $bill ?>">
											<span class="small text-end text-nowrap billLine" style="width:64px">$ 0</span>
										</div>
									</div>

								<?php endforeach ?>

							</div>

							<hr class="my-2">

							<div class="d-flex justify-content-between fw-bold">
								<span>Total contado</span><span id="cashCounted">$ 0</span>
							</div>

						</div>

						<label class="form-label small fw-bold" for="manualCount">O ingresar monto manual</label>
						<div class="input-group mb-3">
							<span class="input-group-text rounded-start">$</span>
							<input type="number" min="0" step="1" class="form-control rounded-end" id="manualCount" placeholder="0">
						</div>

						<div class="border rounded p-3">
							<div class="d-flex justify-content-between fw-bold">
								<span>Diferencia</span><span id="cashGap">$ 0</span>
							</div>
							<div class="small mt-1" id="cashGapNote"></div>
						</div>

					</div>

				</div>
			</div>

			<div class="modal-footer d-flex justify-content-between">
				<button type="button" class="btn btn-dark rounded" data-bs-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-danger rounded" id="confirmCloseCash">Cerrar caja</button>
			</div>

		</div>
	</div>
</div>


<!-- The ticket -->
<div class="modal fade" id="modalCashTicket" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content rounded">

			<div class="modal-header">
				<div>
					<h5 class="modal-title mb-0">Reporte de cierre</h5>
					<small class="text-muted">Imprímelo o guárdalo como PDF</small>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body">
				<div id="cashTicket" class="bg-white text-dark p-3 rounded"></div>
			</div>

			<div class="modal-footer d-flex justify-content-between">
				<button type="button" class="btn btn-dark rounded" id="printCashTicket">
					<i class="bi bi-printer"></i> Imprimir o guardar PDF
				</button>
				<button type="button" class="btn btn-default backColor rounded" data-bs-dismiss="modal">Cerrar</button>
			</div>

		</div>
	</div>
</div>
