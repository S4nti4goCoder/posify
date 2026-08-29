<?php

require_once __DIR__ . "/../../lib/view.php";
require_once __DIR__ . "/../../lib/money.php";
require_once __DIR__ . "/../../lib/inventory.php";
require_once __DIR__ . "/../../lib/cash.session.php";

require_once __DIR__ . "/../../lib/csrf.guard.php";

CsrfGuard::enforce();

require_once __DIR__ . "/../../lib/office.guard.php";
require_once __DIR__ . "/../../lib/permission.guard.php";
require_once __DIR__ . "/../../lib/walkin.client.php";
require_once "../controllers/curl.controller.php";
require_once "../controllers/template.controller.php";

class DynamicTablesController
{

	/*=============================================
    Delete items
    =============================================*/

	public $idItemDelete;
	public $tableDelete;
	public $suffixDelete;
	public $token;

	public function deleteItems()
	{

		$idItems = explode(",", $this->idItemDelete);
		$countDelete = 0;

		foreach ($idItems as $key => $value) {

			$recordId = base64_decode($value);

			/*=============================================
			The walk in customer is what the POS falls back to on every
			sale, so it stays put
			=============================================*/

			if (WalkInClient::isProtected($this->tableDelete, $recordId)) {

				echo "protected";
				return;
			}

			$url = $this->tableDelete . "?id=" . $recordId . "&nameId=id_" . $this->suffixDelete . "&token=" . $this->token . "&table=admins&suffix=admin";
			$method = "DELETE";
			$fields = array();

			$deleteItem = CurlController::request($url, $method, $fields);

			// 409: the record still has records pointing at it
			if ($deleteItem->status == 409) {

				echo "in_use:" . preg_replace("/[^a-z_]/", "", (string) $deleteItem->results);
				return;
			}

			if ($deleteItem->status == 200) {

				$countDelete++;

				if ($countDelete == count($idItems)) {

					echo 200;
				}
			}
		}
	}

	/*=============================================
    Return the filtered table
    =============================================*/

	public $contentModule;
	public $orderBy;
	public $orderMode;
	public $limit;
	public $page;
	public $rolAdmin;
	public $search;
	public $between1;
	public $between2;
	public $idOffice;

	public function loadAjaxTable()
	{

		$module = (json_decode($this->contentModule));
		$startAt = ($this->page - 1) * $this->limit;
		$table = array();
		$totalPages = 0;
		$totalData = 0;


		/*=============================================
		Search filter
		=============================================*/

		if ($this->search != "") {

			/*=============================================
			Searchable columns
			=============================================*/

			$linkTo = array();

			foreach ($module->columns as $key => $value) {

				if ($value->visible_column == 1) {

					if (
						$value->type_column == "text" ||
						$value->type_column == "textarea" ||
						$value->type_column == "email" ||
						$value->type_column == "int" ||
						$value->type_column == "double" ||
						$value->type_column == "money" ||
						$value->type_column == "color" ||
						$value->type_column == "link" ||
						$value->type_column == "select" ||
						$value->type_column == "array" ||
						$value->type_column == "date" ||
						$value->type_column == "time" ||
						$value->type_column == "datetime"
					) {

						array_push($linkTo, $value->title_column);
					}
				}
			}

			/*=============================================
			Search loop
			=============================================*/
			foreach ($linkTo as $key => $value) {
				if ($this->idOffice == 0 || !in_array("id_office_" . $module->suffix_module, array_column($module->columns, "title_column"))) {

					$url = $module->title_module . "?linkTo=" . $value . "&search=" . str_replace(" ", "_", $this->search) . "&orderBy=" . $this->orderBy . "&orderMode=" . $this->orderMode . "&startAt=" . $startAt . "&endAt=" . $this->limit;
				} else {

					$url = $module->title_module . "?linkTo=" . $value . ",id_office_" . $module->suffix_module . "&search=" . str_replace(" ", "_", $this->search) . "," . $this->idOffice . "&orderBy=" . $this->orderBy . "&orderMode=" . $this->orderMode . "&startAt=" . $startAt . "&endAt=" . $this->limit;
				}
				$method = "GET";
				$fields = array();

				$table = CurlController::request($url, $method, $fields);

				if ($table->status == 200) {
					$table = $table->results;

					/*=============================================
					A product has one stock per branch now, so the rows carry the
					one for the branch being looked at
					=============================================*/

					if ($module->title_module == "products") {

						$stockMap = Inventory::mapFor((int) OfficeGuard::current());

						foreach ($table as $row) {

							$row->qty_stock = $stockMap[(int) $row->id_product] ?? 0;
						}
					}
					if ($this->idOffice == 0 || !in_array("id_office_" . $module->suffix_module, array_column($module->columns, "title_column"))) {
						$url = $module->title_module . "?linkTo=" . $value . "&search=" . str_replace(" ", "_", $this->search) . "&select=id_" . $module->suffix_module;
					} else {
						$url = $module->title_module . "?linkTo=" . $value . ",id_office_" . $module->suffix_module . "&search=" . str_replace(" ", "_", $this->search) . "," . $this->idOffice . "&select=id_" . $module->suffix_module;
					}
					$totalData = CurlController::request($url, $method, $fields)->total;
					$totalPages = ceil($totalData / $this->limit);

					break;
				} else {

					$table = array();
				}
			}
		} else {
			if ($this->idOffice == 0 || !in_array("id_office_" . $module->suffix_module, array_column($module->columns, "title_column"))) {
				$url = $module->title_module . "?linkTo=date_created_" . $module->suffix_module . "&between1=" . $this->between1 . "&between2=" . $this->between2 . "&orderBy=" . $this->orderBy . "&orderMode=" . $this->orderMode . "&startAt=" . $startAt . "&endAt=" . $this->limit;
			} else {
				$url = $module->title_module . "?linkTo=date_created_" . $module->suffix_module . "&between1=" . $this->between1 . "&between2=" . $this->between2 . "&orderBy=" . $this->orderBy . "&orderMode=" . $this->orderMode . "&startAt=" . $startAt . "&endAt=" . $this->limit . "&filterTo=id_office_" . $module->suffix_module . "&inTo=" . $this->idOffice;
			}

			$method = "GET";
			$fields = array();

			$table = CurlController::request($url, $method, $fields);

			if ($table->status == 200) {

				$table = $table->results;

				/*=============================================
				A product has one stock per branch now, so the rows carry the
				one for the branch being looked at
				=============================================*/

				if ($module->title_module == "products") {

					$stockMap = Inventory::mapFor((int) OfficeGuard::current());

					foreach ($table as $row) {

						$row->qty_stock = $stockMap[(int) $row->id_product] ?? 0;
					}
				}

				/*=============================================
				Read the full table contents
				=============================================*/
				if ($this->idOffice == 0 || !in_array("id_office_" . $module->suffix_module, array_column($module->columns, "title_column"))) {
					$url = $module->title_module . "?linkTo=date_created_" . $module->suffix_module . "&between1=" . $this->between1 . "&between2=" . $this->between2 . "&select=id_" . $module->suffix_module;
				} else {
					$url = $module->title_module . "?linkTo=date_created_" . $module->suffix_module . "&between1=" . $this->between1 . "&between2=" . $this->between2 . "&select=id_" . $module->suffix_module . "&filterTo=id_office_" . $module->suffix_module . "&inTo=" . $this->idOffice;
				}
				$totalData = CurlController::request($url, $method, $fields)->total;
				$totalPages = ceil($totalData / $this->limit);
			} else {

				$table = array();
			}
		}

		/*=============================================
    	Return the table as HTML
    	=============================================*/

		$HTMLTable = "";


		if (!empty($table)) {

			foreach (json_decode(json_encode($table), true) as $key => $value) {

				/*=============================================
				The walk in customer takes no actions at all: the POS
				depends on it staying exactly as it is
				=============================================*/

				$isLocked = $module->suffix_module == "cash";

				$isWalkIn = $module->title_module == "clients"
					&& isset($value["cc_client"])
					&& (string) $value["cc_client"] === WalkInClient::DOCUMENT;

				$HTMLTable .= '<tr>
						<td>' . ($key + 1 + $startAt) . '</td>';

				if ($this->rolAdmin == "superadmin" || $module->editable_module == 1) {

					$HTMLTable .= '<td>';

					if (!$isWalkIn && !$isLocked) {

						$HTMLTable .= '<div class="form-check formCheck">
		    						<input class="form-check-input checkItem" type="checkbox" idItem="' . base64_encode($value["id_" . $module->suffix_module]) . '">
		    					</div>';
					}

					$HTMLTable .= '</td>';
				}

				foreach ($module->columns as $index => $item) {

					if ($item->visible_column == 1) {

						$HTMLTable .= '<td>';

						/*=============================================
								Image content
								=============================================*/

						if ($item->type_column == "image") {

							$HTMLTable .= '<a href="' . View::url($value[$item->title_column]) . '" target="_blank">
										<img src="' . View::url($value[$item->title_column]) . '" class="rounded" style="width:60px; height:60px; object-fit: cover; object-position:center;">
									</a>';

							/*=============================================
								Video content
								=============================================*/
						} else if ($item->type_column == "video") {

							$HTMLTable .= '<a href="' . View::url($value[$item->title_column]) . '" target="_blank">
										<img src="/views/assets/img/video.png" class="rounded" style="width:60px; height:60px; object-fit: cover; object-position:center;">
									</a>';

							/*=============================================
								Other file content
								=============================================*/
						} else if ($item->type_column == "file") {

							$HTMLTable .= '<a href="' . View::url($value[$item->title_column]) . '" target="_blank">
										<img src="/views/assets/img/file.png" class="rounded" style="width:60px; height:60px; object-fit: cover; object-position:center;">
									</a>';


							/*=============================================
								Boolean content
								=============================================*/
						} else if ($item->type_column == "boolean") {


							if ($value[$item->title_column] == 1) {

								$checked = 'checked';
								$label = "ON";
							} else {

								$checked = '';
								$label = "OFF";
							}

							if ($this->rolAdmin == "superadmin" || $module->editable_module == 1) {

								$HTMLTable .= '<div class="form-check form-switch">
										<input class="form-check-input px-3 changeBoolean" type="checkbox" id="mySwtich" ' . $checked . ' idItem="' . base64_encode($value["id_" . $module->suffix_module]) . '" table="' . $module->title_module . '" suffix="' . $module->suffix_module . '" column="' . $item->title_column . '">
										<label class="form-check-label ps-1 align-middle" for="mySwitch">' . $label . '</label>
										</div>';
							} else {

								$HTMLTable .= '<label class="form-check-label ps-1 align-middle" for="mySwitch">' . $label . '</label>';
							}

							/*=============================================
								Array content
								=============================================*/
						} else if ($item->type_column == "array") {

							$typeArray = explode(",", $value[$item->title_column]);

							foreach ($typeArray as $num => $elem) {

								$HTMLTable .= '<span class="badge badge-sm badge-default rounded bg-dark py-1 px-2 mx-1 mt-1 border small">' . TemplateController::reduceText($elem, 25) . '</span>';
							}

							/*=============================================
								Object content
								=============================================*/
						} else if ($item->type_column == "object") {

							$typeJSON = json_decode($value[$item->title_column]);

							foreach ($typeJSON as $num => $elem) {

								$HTMLTable .= '<span class="badge badge-sm badge-default rounded py-1 px-2 mx-1 mt-1 border text-dark text-uppercase small">' . $num . ': ' . $elem . '</span>';
							}

							/*=============================================
								Link content
								=============================================*/
						} else if ($item->type_column == "link") {

							$HTMLTable .= '<a href="' . View::url($value[$item->title_column]) . '" target="_blank" class="badge badge-default border rounded bg-indigo">' . View::raw(TemplateController::reduceText($value[$item->title_column], 20)) . '</a>';

							/*=============================================
								Color content
								=============================================*/
						} else if ($item->type_column == "color") {

							$HTMLTable .= '<div class="rounded" style="width:25px; height:25px; background:' . View::text($value[$item->title_column]) . '"></div>';

							/*=============================================
								Double content
								=============================================*/
						} else if ($item->type_column == "money") {

							$HTMLTable .= '$' . Money::amount($value[$item->title_column]);

							/*=============================================
								Relations content
								=============================================*/
						} else if ($item->type_column == "relations") {

							if ($item->matrix_column != null && $value[$item->title_column] > 0) {

								$url = "relations?rel=modules,pages&type=module,page&linkTo=type_module,title_module&equalTo=tables," . $item->matrix_column . "&select=url_page,suffix_module";
								$method = "GET";
								$fields = array();

								$urlPage = CurlController::request($url, $method, $fields)->results[0]->url_page;
								$suffixModule = CurlController::request($url, $method, $fields)->results[0]->suffix_module;

								$url = $item->matrix_column . '?linkTo=id_' . $suffixModule . "&equalTo=" . $value[$item->title_column];
								$relation = CurlController::request($url, $method, $fields);
								$arrayRelation  = (array)$relation->results[0];

								$HTMLTable .= '<a href="' . $urlPage . '/manage/' . base64_encode($value[$item->title_column]) . '" target="_blank" class="badge badge-default border rounded bg-indigo">' . View::text($arrayRelation[array_keys($arrayRelation)[1]]) . '</a>';
							} else {

								$HTMLTable .= $value[$item->title_column];
							}

							/*=============================================
								Order content
								=============================================*/
						} else if ($item->type_column == "order") {

							$HTMLTable .= '<input type="number" class="form-control form-control-sm rounded changeOrder" value="' . $value[$item->title_column] . '" style="width:55px" idItem="' . base64_encode($value["id_" . $module->suffix_module]) . '" table="' . $module->title_module . '" suffix="' . $module->suffix_module . '" column="' . $item->title_column . '">';
						} else if ($item->type_column == "posify") {
							$HTMLTable .= '<a href="/posify?order=' . View::text($value[$item->title_column]) . '" style="color:inherit">' . View::text($value[$item->title_column]) . '</a>';
						} else if ($item->type_column == "stock") {
							$colorStock = "bg-secondary";

							if ($value[$item->title_column] < 50) {
								$colorStock = "bg-maroon";
							}
							if ($value[$item->title_column] >= 50 && $value[$item->title_column] < 100) {
								$colorStock = "bg-indigo";
							}
							if ($value[$item->title_column] >= 100) {
								$colorStock = "bg-teal";
							}
							$HTMLTable .= '<span class="badge badge-sm badge-default ' . $colorStock . ' rounded py-1 px-3 mx-1 mt-1 text-uppercase small">' . $value[$item->title_column] . '</span>';
						} else {

							$HTMLTable .= View::raw(TemplateController::reduceText($value[$item->title_column], 25));
						}

						$HTMLTable .= '</td>';
					}
				}

				if ($this->rolAdmin == "superadmin" || $module->editable_module == 1) {

					$HTMLTable .= '<td class="text-center">';

					if (!$isWalkIn && !$isLocked) {

						$HTMLTable .= '<a href="/' . $module->url_page . '/manage/' . base64_encode($value["id_" . $module->suffix_module]) . '/copy" class="btn btn-sm text-dark rounded m-0 p-0 border-0">
		    						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">
									  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
									</svg>
		    					</a>
			    					<a href="/' . $module->url_page . '/manage/' . base64_encode($value["id_" . $module->suffix_module]) . '" class="btn btn-sm text-primary rounded m-0 p-0 border-0">
			    						<i class="bi bi-pencil-square"></i>
			    					</a>
			    					<button type="button" class="btn btn-sm text-maroon rounded m-0 p-0 border-0 deleteItem" idItem="' . base64_encode($value["id_" . $module->suffix_module]) . '" table="' . $module->title_module . '" suffix="' . $module->suffix_module . '">
			    						<i class="bi bi-trash"></i>
			    					</button>';

					} else {

						$HTMLTable .= '<span class="badge badge-default border rounded text-muted small">Del sistema</span>';
					}

					$HTMLTable .= '</td>';
				}

				$HTMLTable .= '</tr>';
			}
		}

		$response = array(

			"HTMLTable" => $HTMLTable,
			"totalData" => $totalData,
			"totalPages" => $totalPages
		);

		echo json_encode($response);
	}

	/*=============================================
    Toggle a boolean
    =============================================*/
	public $boolChange;
	public $idItemChange;
	public $tableChange;
	public $suffixChange;
	public $columnChange;

	public function changeBooleanItems()
	{

		$idItems = explode(",", $this->idItemChange);
		$countChange = 0;

		foreach ($idItems as $key => $value) {

			if ($this->boolChange == "false" || $this->boolChange == 0) {

				$this->boolChange = 0;
			} else {

				$this->boolChange = 1;
			}

			$url = $this->tableChange . "?id=" . base64_decode($value) . "&nameId=id_" . $this->suffixChange . "&token=" . $this->token . "&table=admins&suffix=admin";
			$method = "PUT";
			$fields = $this->columnChange . "=" . $this->boolChange;

			$closedAt = CashSession::closedAt($this->tableChange, $this->columnChange, $this->boolChange);

			if ($closedAt !== null) {

				$fields .= "&date_end_cash=" . $closedAt;
			}

			$updateItem = CurlController::request($url, $method, $fields);

			if ($updateItem->status == 200) {

				$countChange++;

				if ($countChange == count($idItems)) {

					echo 200;
				}
			}
		}
	}

	/*=============================================
    Change the selection
    =============================================*/
	public $itemSelect;
	public $idItemSelect;
	public $tableSelect;
	public $suffixSelect;
	public $columnSelect;

	public function changeSelectItems()
	{

		$idItems = explode(",", $this->idItemSelect);
		$countSelect = 0;

		foreach ($idItems as $key => $value) {

			$url = $this->tableSelect . "?id=" . base64_decode($value) . "&nameId=id_" . $this->suffixSelect . "&token=" . $this->token . "&table=admins&suffix=admin";
			$method = "PUT";
			$fields = $this->columnSelect . "=" . $this->itemSelect;

			$updateItem = CurlController::request($url, $method, $fields);

			if ($updateItem->status == 200) {

				$countSelect++;

				if ($countSelect == count($idItems)) {

					echo 200;
				}
			}
		}
	}

	/*=============================================
    Change the order
    =============================================*/

	public $numOrder;
	public $idItemOrder;
	public $tableOrder;
	public $suffixOrder;
	public $columnOrder;

	public function changeOrderItems()
	{

		$url = $this->tableOrder . "?id=" . base64_decode($this->idItemOrder) . "&nameId=id_" . $this->suffixOrder . "&token=" . $this->token . "&table=admins&suffix=admin";
		$method = "PUT";
		$fields = $this->columnOrder . "=" . $this->numOrder;

		$updateItem = CurlController::request($url, $method, $fields);

		if ($updateItem->status == 200) {

			echo 200;
		}
	}
}

/*=============================================
POST variables
=============================================*/


/*=============================================
Branch and administrator come from the session, never from the request
=============================================*/

OfficeGuard::start();

$sessionOffice = OfficeGuard::current();
$sessionAdmin  = OfficeGuard::currentAdmin();

if ($sessionOffice === null || $sessionAdmin === null) {

    echo "logout";
    exit;
}

if (isset($_POST["idItemDelete"])) {

	$ajax = new DynamicTablesController();
	$ajax->idItemDelete = $_POST["idItemDelete"];
	PermissionGuard::enforce($_POST["tableDelete"]);

	$ajax->tableDelete = $_POST["tableDelete"];
	$ajax->suffixDelete = $_POST["suffixDelete"];
	$ajax->token = Session::token();
	$ajax->deleteItems();
}

/*=============================================
Return the filtered table
=============================================*/

if (isset($_POST["contentModule"])) {

	$ajax = new DynamicTablesController();
	// the module json names the table, so it is checked like the rest
	$askedFor = json_decode($_POST["contentModule"]);

	PermissionGuard::enforce((string) ($askedFor->title_module ?? ""));

	$ajax->contentModule = $_POST["contentModule"];
	$ajax->orderBy = $_POST["orderBy"];
	$ajax->orderMode = $_POST["orderMode"];
	$ajax->limit = $_POST["limit"];
	$ajax->page = $_POST["page"];
	$ajax->rolAdmin = (string) ($_SESSION["admin"]->rol_admin ?? "");
	$ajax->search = $_POST["search"];
	$ajax->between1 = $_POST["between1"];
	$ajax->between2 = $_POST["between2"];
	$ajax->idOffice = $sessionOffice;
	$ajax->loadAjaxTable();
}


/*=============================================
Toggle a boolean
=============================================*/

if (isset($_POST["tableChange"])) {

	$ajax = new DynamicTablesController();
	$ajax->boolChange = $_POST["boolChange"];
	$ajax->idItemChange = $_POST["idItemChange"];
	PermissionGuard::enforce($_POST["tableChange"]);

	$ajax->tableChange = $_POST["tableChange"];
	$ajax->suffixChange = $_POST["suffixChange"];
	$ajax->columnChange = $_POST["columnChange"];
	$ajax->token = Session::token();
	$ajax->changeBooleanItems();
}

/*=============================================
Change the selection
=============================================*/

if (isset($_POST["tableSelect"])) {

	$ajax = new DynamicTablesController();
	$ajax->itemSelect = $_POST["itemSelect"];
	$ajax->idItemSelect = $_POST["idItemSelect"];
	PermissionGuard::enforce($_POST["tableSelect"]);

	$ajax->tableSelect = $_POST["tableSelect"];
	$ajax->suffixSelect = $_POST["suffixSelect"];
	$ajax->columnSelect = $_POST["columnSelect"];
	$ajax->token = Session::token();
	$ajax->changeSelectItems();
}

/*=============================================
Change the order
=============================================*/

if (isset($_POST["tableOrder"])) {

	$ajax = new DynamicTablesController();
	$ajax->numOrder = $_POST["numOrder"];
	$ajax->idItemOrder = $_POST["idItemOrder"];
	PermissionGuard::enforce($_POST["tableOrder"]);

	$ajax->tableOrder = $_POST["tableOrder"];
	$ajax->suffixOrder = $_POST["suffixOrder"];
	$ajax->columnOrder = $_POST["columnOrder"];
	$ajax->token = Session::token();
	$ajax->changeOrderItems();
}
