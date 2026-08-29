<?php require_once __DIR__ . "/../../../../../../../lib/view.php" ?>

<!-- =======================================
Add client modal
==========================================-->
<div class="modal fade" id="modalClient">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Cliente</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row row-cols-1 row-cols-sm-2  my-3">
                    <div class="col">
                        <div class="form-floating">
                            <input type="text" class="form-control changeFormClient rounded" id="name_client" placeholder="Ingresar Nombre">
                            <label for="name_client">Nombre</label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-floating">
                            <input type="text" class="form-control changeFormClient rounded" id="surname_client" placeholder="Ingresar Apellido">
                            <label for="surname_client">Apellido</label>
                        </div>
                    </div>
                </div>
                <div class="row row-cols-1 row-cols-sm-2  my-3">
                    <div class="col">
                        <div class="form-floating">
                            <input type="text" class="form-control changeFormClient rounded" id="cc_client" placeholder="Ingresar Documento">
                            <label for="cc_client">Documento</label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-floating">
                            <input type="email" class="form-control changeFormClient rounded" id="email_client" placeholder="Ingresar Correo">
                            <label for="email_client">Correo Electrónico</label>
                        </div>
                    </div>
                </div>
                <div class="row row-cols-1 row-cols-sm-2  my-3">
                    <div class="col">
                        <div class="form-floating">
                            <input type="text" class="form-control changeFormClient rounded" id="phone_client" placeholder="Ingresar Teléfono">
                            <label for="phone_client">Teléfono</label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-floating">
                            <input type="text" class="form-control changeFormClient rounded" id="address_client" placeholder="Ingresar Dirección">
                            <label for="address_client">Dirección</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-default border rounded" data-bs-dismiss="modal">Cerrar</button>
                </div>
                <div>
                    <button type="button" class="btn btn-default backColor rounded" id="btnAddClient">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =======================================
Checkout modal
==========================================-->
<div class="modal fade" id="modalCheckout" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded">
            <form method="POST" action="/posify" id="formCheckout">

                <?php echo CsrfGuard::field() ?>

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Cobrar pedido</h5>
                        <small class="text-muted">Confirma el método de pago.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="idOrderPay" name="idOrderPay">
                    <input type="hidden" id="methodPay" name="methodPay">
                    <input type="hidden" id="transferPay" name="transferPay">
                    <input type="hidden" id="extraDiscountPay" name="extraDiscountPay" value="0">
                    <input type="hidden" id="cashPay" name="cashPay" value="0">
                    <input type="hidden" id="cardPay" name="cardPay" value="0">
                    <input type="hidden" id="notePay" name="notePay" value="">

                    <!-- What the cashier is about to charge -->
                    <div class="border rounded bg-light p-3 mb-3">
                        <div class="tk-preview">
                            <div class="tk-center tk-bold mb-2"><?php echo View::text($_SESSION["admin"]->title_office ?? "") ?></div>
                            <div id="checkoutLines"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1" for="extraDiscount">Descuento adicional</label>
                        <input type="number" min="0" step="1" class="form-control rounded" id="extraDiscount" placeholder="0">
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" for="payMethodSelect">Método de pago</label>
                            <button type="button" class="btn btn-sm border rounded" id="toggleMixto">Pago mixto</button>
                        </div>
                        <select class="form-control rounded" id="payMethodSelect">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>

                    <!-- Cash: what was handed over, and the change -->
                    <div class="border rounded bg-light p-3 mb-3 payBlock" id="blockCash">
                        <label class="form-label mb-1" for="cashReceived">Recibido</label>
                        <input type="number" min="0" step="1" class="form-control rounded mb-2" id="cashReceived" placeholder="0">
                        <div class="d-flex flex-wrap gap-1 mb-1">
                            <button type="button" class="btn btn-sm border rounded flex-fill quickCash" amount="5000">$ 5.000</button>
                            <button type="button" class="btn btn-sm border rounded flex-fill quickCash" amount="10000">$ 10.000</button>
                            <button type="button" class="btn btn-sm border rounded flex-fill quickCash" amount="20000">$ 20.000</button>
                            <button type="button" class="btn btn-sm border rounded flex-fill quickCash" amount="50000">$ 50.000</button>
                        </div>
                        <button type="button" class="btn btn-sm border rounded w-100 mb-2" id="exactCash">Exacto</button>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <span class="text-muted" id="cashLabel">Vuelto</span>
                            <span class="h4 mb-0 font-weight-bold" id="cashAmount">$ 0</span>
                        </div>
                    </div>

                    <!-- Split payment -->
                    <div class="border rounded bg-light p-3 mb-3 payBlock" id="blockMixto" style="display:none">
                        <label class="form-label mb-1 small" for="mixtoCash">Efectivo</label>
                        <input type="number" min="0" step="1" class="form-control rounded mb-2" id="mixtoCash" placeholder="0">
                        <label class="form-label mb-1 small" for="mixtoCard">Tarjeta</label>
                        <input type="number" min="0" step="1" class="form-control rounded mb-2" id="mixtoCard" placeholder="0">
                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <span class="text-muted">Suma</span>
                            <span class="font-weight-bold" id="mixtoSum">$ 0 / $ 0</span>
                        </div>
                        <div class="small text-red mt-1" id="mixtoShort"></div>
                    </div>

                    <div class="border rounded bg-light p-3 mb-3 payBlock" id="blockTransfer" style="display:none">
                        <label class="form-label mb-1" for="idTransferPay">ID de la transferencia</label>
                        <input type="text" class="form-control rounded" id="idTransferPay" placeholder="Ingresa el id de la transferencia">
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1" for="checkoutNote">Notas</label>
                        <input type="text" maxlength="255" class="form-control rounded" id="checkoutNote" placeholder="Observaciones...">
                    </div>

                    <button type="button" class="btn border rounded w-100" id="checkoutClient">
                        <i class="bi bi-person"></i> <span id="checkoutClientLabel">Asignar cliente al pedido</span>
                    </button>

                </div>

                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-default border rounded flex-fill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-default backColor rounded flex-fill" id="confirmCheckout">Confirmar cobro</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- =======================================
Order search modal
==========================================-->
<div class="modal fade" id="modalSearchOrder">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded">
            <div class="modal-header">
                <h4 class="modal-title">Buscar Orden</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <?php
                $url = "relations?rel=modules,pages&type=module,page&linkTo=id_module&equalTo=14";
                $method = "GET";
                $fields = array();
                $module = CurlController::request($url, $method, $fields);
                if ($module->status == 200) {
                    $module = $module->results[0];
                    include "views/pages/dynamic/tables/tables.php";
                } else {
                    $module = array();
                }
                ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default border rounded" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- The sale receipt -->
<div class="modal fade" id="modalReceipt" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Venta completada</h5>
                    <small class="text-muted" id="receiptTransaction"></small>
                </div>
                <button type="button" class="btn-close" id="closeReceipt"></button>
            </div>

            <div class="modal-body">
                <div id="saleTicket" class="bg-white text-dark p-3 rounded"></div>
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-dark rounded" id="printReceipt">
                    <i class="bi bi-printer"></i> Imprimir o guardar PDF
                </button>
                <button type="button" class="btn btn-default backColor rounded" id="finishSale">Nueva venta</button>
            </div>

        </div>
    </div>
</div>
