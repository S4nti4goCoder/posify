/*=============================================
Pagination
=============================================*/

function initPagination() {
  var totalPages = $("#pagination").attr("totalPages");

  var defaultOpts = {
    totalPages: totalPages,
    first: '<i class="fas fa-angle-double-left"></i>',
    last: '<i class="fas fa-angle-double-right"></i>',
    prev: '<i class="fas fa-angle-left"></i>',
    next: '<i class="fas fa-angle-right"></i>',
    onPageClick: function (event, page) {
      if (page == 1) {
        $(".page-item.first").css({ color: "#fff !important" });
        $(".page-item.prev").css({ color: "#fff !important" });
      }

      if (page == totalPages) {
        $(".page-item.next").css({ color: "#aaa !important" });
        $(".page-item.last").css({ color: "#aaa !important" });
      }
    },
  };

  $("#pagination")
    .twbsPagination(defaultOpts)
    .on("page", function (event, page) {
      var contentModule = $("#contentModule").val();
      var orderBy = $("#orderByTable").val();
      var orderMode = $("#orderModeTable").val();
      var limit = $("#limitTable").val();
      var page = page;
      var filter = "pagination";
      var search = $("#searchTable").val();
      var between1 = $("#between1").val();
      var between2 = $("#between2").val();

      loadAjaxTable(
        contentModule,
        orderBy,
        orderMode,
        limit,
        page,
        filter,
        search,
        between1,
        between2
      );
    });
}

initPagination();

/*=============================================
Record limit changed
=============================================*/

$(document).on("change", ".changeLimit", function () {
  var contentModule = $("#contentModule").val();
  var orderBy = $("#orderByTable").val();
  var orderMode = $("#orderModeTable").val();
  var limit = $(this).val();
  var page = 1;
  var filter = "limit";
  var search = $("#searchTable").val();
  var between1 = $("#between1").val();
  var between2 = $("#between2").val();

  /*=============================================
	Store the limit in the hidden input
	=============================================*/

  $("#limitTable").val(limit);

  loadAjaxTable(
    contentModule,
    orderBy,
    orderMode,
    limit,
    page,
    filter,
    search,
    between1,
    between2
  );
});

/*=============================================
Record order changed
=============================================*/

$(document).on("click", ".orderFilter", function () {
  var contentModule = $("#contentModule").val();
  var orderBy = $(this).attr("orderBy");
  var orderMode = $(this).attr("orderMode");
  var limit = $("#limitTable").val();
  var page = 1;
  var filter = "order";
  var search = $("#searchTable").val();
  var between1 = $("#between1").val();
  var between2 = $("#between2").val();

  /*=============================================
	Store orderBy and orderMode in the hidden input
	=============================================*/

  $("#orderByTable").val(orderBy);
  $("#orderModeTable").val(orderMode);

  /*=============================================
	Flip the arrow
	=============================================*/

  if (orderMode == "ASC") {
    $(this).attr("orderMode", "DESC");
    $(this).removeClass("bi-arrow-down-short");
    $(this).addClass("bi-arrow-up-short");
  } else {
    $(this).attr("orderMode", "ASC");
    $(this).addClass("bi-arrow-down-short");
    $(this).removeClass("bi-arrow-up-short");
  }

  loadAjaxTable(
    contentModule,
    orderBy,
    orderMode,
    limit,
    page,
    filter,
    search,
    between1,
    between2
  );
});

/*=============================================
Record search
=============================================*/

$(document).on("keyup", "#searchItem", function () {
  var contentModule = $("#contentModule").val();
  var orderBy = $("#orderByTable").val();
  var orderMode = $("#orderModeTable").val();
  var limit = $("#limitTable").val();
  var page = 1;
  var filter = "search";
  var search = $(this).val();
  var between1 = $("#between1").val();
  var between2 = $("#between2").val();

  /*=============================================
	Store the search term in the hidden input
	=============================================*/

  $("#searchTable").val(search);

  loadAjaxTable(
    contentModule,
    orderBy,
    orderMode,
    limit,
    page,
    filter,
    search,
    between1,
    between2
  );
});

/*=============================================
Filter by date
=============================================*/

$("#daterange-btn").daterangepicker(
  {
    locale: {
      format: "YYYY-MM-DD",
      separator: " - ",
      applyLabel: "Aplicar",
      cancelLabel: "Cancelar",
      fromLabel: "Desde",
      toLabel: "Hasta",
      customRangeLabel: "Rango Personalizado",
      daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
      monthNames: [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre",
      ],
      firstDay: 1,
    },
    ranges: {
      Hoy: [moment(), moment()],
      Ayer: [moment().subtract(1, "days"), moment().subtract(1, "days")],
      "Últimos 7 días": [moment().subtract(6, "days"), moment()],
      "Últimos 30 días": [moment().subtract(29, "days"), moment()],
      "Este Mes": [moment().startOf("month"), moment().endOf("month")],
      "Último Mes": [
        moment().subtract(1, "month").startOf("month"),
        moment().subtract(1, "month").endOf("month"),
      ],
      "Este Año": [moment().startOf("year"), moment().endOf("year")],
      "Último Año": [
        moment().subtract(1, "year").startOf("year"),
        moment().subtract(1, "year").endOf("year"),
      ],
    },
    startDate: moment($("#between1").val()),
    endDate: moment($("#between2").val()),
  },
  function (start, end) {
    var contentModule = $("#contentModule").val();
    var orderBy = $("#orderByTable").val();
    var orderMode = $("#orderModeTable").val();
    var limit = $("#limitTable").val();
    var page = 1;
    var filter = "range";
    var search = $("#searchTable").val();
    var between1 = start.format("YYYY-MM-DD");
    var between2 = end.format("YYYY-MM-DD");

    /*=============================================
		Updating the date picker
		=============================================*/

    $("#startDate").html(between1);
    $("#endDate").html(between2);

    /*=============================================
		Updating the dates in the hidden inputs
		=============================================*/

    $("#between1").val(between1);
    $("#between2").val(between2);

    loadAjaxTable(
      contentModule,
      orderBy,
      orderMode,
      limit,
      page,
      filter,
      search,
      between1,
      between2
    );
  }
);

/*=============================================
Load the table over Ajax
=============================================*/

function loadAjaxTable(
  contentModule,
  orderBy,
  orderMode,
  limit,
  page,
  filter,
  search,
  between1,
  between2
) {
  if (filter != "search") {
    fncSweetAlert("loading", "Cargando información...", "");
  }

  var data = new FormData();
  data.append("contentModule", contentModule);
  data.append("orderBy", orderBy);
  data.append("orderMode", orderMode);
  data.append("limit", limit);
  data.append("page", page);
  data.append("rolAdmin", $("#rolAdmin").val());
  data.append("search", search);
  data.append("between1", between1);
  data.append("between2", between2);
  data.append("idOffice", $("#idOffice").val());

  $.ajax({
    url: "/ajax/dynamic-tables.ajax.php",
    method: "POST",
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    success: function (response) {
      if ("filter" != "search") {
        fncSweetAlert("close", "", "");
      }

      /*=============================================
			Clear the item selection
			=============================================*/

      $("#checkItems").val("");
      $(".checkAllItems").attr("mode", "false");

      if (JSON.parse(response).HTMLTable != "") {
        /*=============================================
				Show filters and pagination
				=============================================*/

        $(".blockFooter").show();

        /*=============================================
				Refresh the table
				=============================================*/

        $("#loadTable").html(JSON.parse(response).HTMLTable);

        if (
          filter == "limit" ||
          filter == "order" ||
          filter == "search" ||
          filter == "range"
        ) {
          /*=============================================
					Refresh the pagination
					=============================================*/

          $("#cont-pagination").html(`

						<ul id="pagination" 
						class="pagination pagination-sm rounded" 
						totalPages="${JSON.parse(response).totalPages}">
			        	</ul>

					`);

          initPagination();
        }

        /*=============================================
				Update the records
				=============================================*/

        $("#startItems").html((page - 1) * limit + 1);

        if (
          Number($("#startItems").html()) - 1 + Number(limit) >
          JSON.parse(response).totalData
        ) {
          $("#endItems").html(JSON.parse(response).totalData);
        } else {
          $("#endItems").html(
            Number($("#startItems").html()) - 1 + Number(limit)
          );
        }

        $("#totalItems").html(JSON.parse(response).totalData);
      } else {
        /*=============================================
				Refresh the table
				=============================================*/

        $("#loadTable").html(`

					<tr>
						<td colspan="${
              $("thead th").length
            }" class="text-center py-3">No hay registros disponibles</td>
					</tr>

				 `);

        /*=============================================
				Hide filters and pagination
				=============================================*/

        $(".blockFooter").hide();
      }
    },
  });
}

/*=============================================
Select a single item
=============================================*/

$(document).on("change", ".checkItem", function () {
  var idItem = $(this).attr("idItem");

  if ($("#checkItems").val() == "") {
    var checkItems = [];
  } else {
    var checkItems = $("#checkItems").val().split(",");
  }

  var typeCheck = $(this).prop("checked");

  if (typeCheck) {
    checkItems.push(idItem);
  } else {
    checkItems.forEach((e, i) => {
      if (e == idItem) {
        checkItems.splice(i, 1);
      }
    });
  }

  $("#checkItems").val(checkItems.toString());
});

/*=============================================
Select items in bulk
=============================================*/

$(document).on("click", ".checkAllItems", function () {
  var mode = $(this).attr("mode");
  var checkItem = $(".checkItem");
  var formCheck = $(".formCheck");

  if ($("#checkItems").val() == "") {
    var checkItems = [];
  } else {
    var checkItems = $("#checkItems").val().split(",");
  }

  if (mode == "false") {
    $(this).attr("mode", "true");

    checkItem.each((i) => {
      if (!$(checkItem[i]).prop("checked")) {
        $(checkItem[i]).attr("checked", true);

        checkItems.push($(checkItem[i]).attr("idItem"));

        $("#checkItems").val(checkItems.toString());
      }
    });
  } else {
    $(this).attr("mode", "false");

    checkItem.each((i) => {
      var idItem = $(checkItem[i]).attr("idItem");

      $(checkItem[i]).remove();

      $(formCheck[i]).html(
        `<input class="form-check-input checkItem" type="checkbox" idItem="${idItem}">`
      );

      $(checkItem[i]).attr("checked", false);

      checkItems.forEach((e, f) => {
        if (e == idItem) {
          checkItems.splice(f, 1);
        }
      });

      $("#checkItems").val(checkItems.toString());
    });
  }
});

/*=============================================
Delete a single item
=============================================*/

$(document).on("click", ".deleteItem", function () {
  var idItem = $(this).attr("idItem");
  var table = $(this).attr("table");
  var suffix = $(this).attr("suffix");

  fncSweetAlert("confirm", "¿Está seguro de borrar este registro?", "").then(
    (resp) => {
      if (resp) {
        fncMatPreloader("on");
        fncSweetAlert("loading", "Eliminando registro...", "");

        var data = new FormData();
        data.append("idItemDelete", idItem);
        data.append("tableDelete", table);
        data.append("suffixDelete", suffix);

        $.ajax({
          url: "/ajax/dynamic-tables.ajax.php",
          method: "POST",
          data: data,
          contentType: false,
          cache: false,
          processData: false,
          success: function (response) {
            if (response == 200) {
              fncMatPreloader("off");
              fncSweetAlert(
                "success",
                "El registro ha sido eliminado con éxito", "");
              setTimeout(() => location.reload(), 1250)
            } else if (String(response).indexOf("in_use") === 0) {
              fncMatPreloader("off");
              fncSweetAlert(
                "error",
                fncDependencyMessage(response),
                ""
              );
            } else if (String(response).trim() == "protected") {
              fncMatPreloader("off");
              fncSweetAlert(
                "error",
                "El cliente Consumidor Final no se puede eliminar",
                ""
              );
            } else {
              fncMatPreloader("off");
              fncSweetAlert("error", "No se pudo eliminar el registro", "");
            }
          },
        });
      }
    }
  );
});

/*=============================================
Bulk delete items
=============================================*/

$(document).on("click", ".deleteAllItems", function () {
  var idItems = $("#checkItems").val();

  if (idItems == "") {
    fncToastr("error", "No hay ningún registro seleccionado");
    return;
  }

  var table = $("#checkItems").attr("table");
  var suffix = $("#checkItems").attr("suffix");

  fncSweetAlert("confirm", "¿Está seguro de borrar estos registros?", "").then(
    (resp) => {
      if (resp) {
        fncMatPreloader("on");
        fncSweetAlert("loading", "Eliminando registros...", "");

        var data = new FormData();
        data.append("idItemDelete", idItems);
        data.append("tableDelete", table);
        data.append("suffixDelete", suffix);

        $.ajax({
          url: "/ajax/dynamic-tables.ajax.php",
          method: "POST",
          data: data,
          contentType: false,
          cache: false,
          processData: false,
          success: function (response) {
            if (response == 200) {
              fncMatPreloader("off");
              fncSweetAlert(
                "success",
                "Los registros han sido eliminados con éxito", "");
              setTimeout(() => location.reload(), 1250)
            } else if (String(response).indexOf("in_use") === 0) {
              fncMatPreloader("off");
              fncSweetAlert(
                "error",
                fncDependencyMessage(response),
                ""
              );
            } else if (String(response).trim() == "protected") {
              fncMatPreloader("off");
              fncSweetAlert(
                "error",
                "El cliente Consumidor Final no se puede eliminar",
                ""
              );
            } else {
              fncMatPreloader("off");
              fncSweetAlert("error", "No se pudo eliminar el registro", "");
            }
          },
        });
      }
    }
  );
});

/*=============================================
Toggle a boolean record
=============================================*/

$(document).on("click", ".changeBoolean", function () {
  var bool = $(this).prop("checked");

  if (!bool) {
    $(this).parent().find(".form-check-label").html("OFF");
  } else {
    $(this).parent().find(".form-check-label").html("ON");
  }

  var idItem = $(this).attr("idItem");
  var table = $(this).attr("table");
  var suffix = $(this).attr("suffix");
  var column = $(this).attr("column");

  var data = new FormData();
  data.append("boolChange", bool);
  data.append("idItemChange", idItem);
  data.append("tableChange", table);
  data.append("suffixChange", suffix);
  data.append("columnChange", column);

  $.ajax({
    url: "/ajax/dynamic-tables.ajax.php",
    method: "POST",
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    success: function (response) {
      if (response == 200) {
        fncToastr("success", "El registro ha sido actualizado con éxito");
      }
    },
  });
});

/*=============================================
Toggle booleans in bulk
=============================================*/

$(document).on("click", ".myBooleans", function () {
  var idItems = $("#checkItems").val();

  if (idItems == "") {
    fncToastr("error", "No hay ningún registro seleccionado");
    return;
  }

  var table = $("#checkItems").attr("table");
  var suffix = $("#checkItems").attr("suffix");
  var column = $(this).attr("column");

  $("#myBooleans").modal("show");

  $("#myBooleans").on("shown.bs.modal", function () {
    $(document).on("click", ".changeBooleans", function () {
      var bool = $("#valueBoolean").val();

      fncMatPreloader("on");
      fncSweetAlert("loading", "Cambiando registros...", "");

      var data = new FormData();
      data.append("boolChange", bool);
      data.append("idItemChange", idItems);
      data.append("tableChange", table);
      data.append("suffixChange", suffix);
      data.append("columnChange", column);

      $.ajax({
        url: "/ajax/dynamic-tables.ajax.php",
        method: "POST",
        data: data,
        contentType: false,
        cache: false,
        processData: false,
        success: function (response) {
          if (response == 200) {
            fncSweetAlert(
              "success",
              "los registros han sido actualizado con éxito", "");
            setTimeout(() => location.reload(), 1250)
          }
        },
      });
    });
  });
});

/*=============================================
Change the bulk selection
=============================================*/

$(document).on("click", ".mySelects", function () {
  var idItems = $("#checkItems").val();

  if (idItems == "") {
    fncToastr("error", "No hay ningún registro seleccionado");
    return;
  }

  var table = $("#checkItems").attr("table");
  var suffix = $("#checkItems").attr("suffix");
  var column = $(this).attr("column");
  var matrix = $(this).attr("matrix").split(",");

  $("#mySelects").modal("show");

  $("#mySelects").on("shown.bs.modal", function () {
    matrix.forEach((e, i) => {
      $("#valueSelect").append(`<option value="${e}">${e}</option>`);
    });

    $(document).on("click", ".changeSelects", function () {
      var select = $("#valueSelect").val();

      fncMatPreloader("on");
      fncSweetAlert("loading", "Cambiando registros...", "");

      var data = new FormData();
      data.append("itemSelect", select);
      data.append("idItemSelect", idItems);
      data.append("tableSelect", table);
      data.append("suffixSelect", suffix);
      data.append("columnSelect", column);

      $.ajax({
        url: "/ajax/dynamic-tables.ajax.php",
        method: "POST",
        data: data,
        contentType: false,
        cache: false,
        processData: false,
        success: function (response) {
          if (response == 200) {
            fncSweetAlert(
              "success",
              "los registros han sido actualizado con éxito", "");
            setTimeout(() => location.reload(), 1250)
          }
        },
      });
    });
  });
});

/*=============================================
Reorder a record
=============================================*/

$(document).on("change", ".changeOrder", function () {
  var num = $(this).val();
  var idItem = $(this).attr("idItem");
  var table = $(this).attr("table");
  var suffix = $(this).attr("suffix");
  var column = $(this).attr("column");

  var data = new FormData();
  data.append("numOrder", num);
  data.append("idItemOrder", idItem);
  data.append("tableOrder", table);
  data.append("suffixOrder", suffix);
  data.append("columnOrder", column);

  $.ajax({
    url: "/ajax/dynamic-tables.ajax.php",
    method: "POST",
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    success: function (response) {
      if (response == 200) {
        fncToastr("success", "El registro ha sido actualizado con éxito");
      }
    },
  });
});

/*=============================================
Why a record cannot be deleted, and what to do instead
=============================================*/

function fncDependencyMessage(response) {
  var child = String(response).split(":")[1] || "";

  var reasons = {
    sales:
      "Este producto tiene ventas registradas. Apágalo con el interruptor de Estado para retirarlo del punto de venta.",
    purchases:
      "Este producto tiene compras registradas. Apágalo con el interruptor de Estado en vez de borrarlo.",
    stock_movements:
      "Este producto tiene movimientos de inventario. Apágalo con el interruptor de Estado en vez de borrarlo.",
    products:
      "Esta categoría tiene productos asignados. Muévelos a otra categoría o apaga la categoría.",
    orders:
      "Este cliente tiene órdenes registradas. No se puede borrar sin perder ese historial."
  };

  return reasons[child] || "Hay registros que dependen de este.";
}
