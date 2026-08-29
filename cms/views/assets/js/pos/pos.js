/*=============================================
JD SLIDER
=============================================*/
$(".jd-slider").jdSlider({
  wrap: ".slide-inner",
  slideShow: 4,
  slideToScroll: 2,
  isLoop: true,
  responsive: [
    {
      viewSize: 768,
      settings: {
        slideShow: 1,
        slideToScroll: 1,
      },
    },
  ],
});

/*=============================================
LOAD MORE PRODUCTS
=============================================*/
$(document).on("click", "#loadPageProducts", function () {
  if (
    Number($("#currentPageProducts").val()) <
    Number($("#totalPagesProducts").val())
  ) {
    var nextPage = Number($("#currentPageProducts").val()) + 1;

    if (Number($("#totalPagesProducts").val()) == nextPage) {
      $("#loadPageProducts").addClass("d-none");
      $("#loadPageProducts").removeClass("d-block");
    }

    $("#currentPageProducts").val(nextPage);

    var limit = Number($("#limitProduct").val());
    var startAt = nextPage * limit - limit;
    var category = $("#filterByCategory").val();
    var search = $("#searchProduct").val();

    loadMoreProducts(limit, startAt, category, search);
  } else {
    $("#loadPageProducts").addClass("d-none");
    $("#loadPageProducts").removeClass("d-block");
  }
});

/*=============================================
FILTER PRODUCTS BY CATEGORY
=============================================*/
$(document).on("click", ".loadCategory", function () {
  var category = $(this).attr("idCategory");
  $("#filterByCategory").val(category);

  var limit = Number($("#limitProduct").val());
  var startAt = 0;
  $("#currentPageProducts").val(1);
  var search = $("#searchProduct").val();

  loadMoreProducts(limit, startAt, category, search);
});

/*=============================================
FILTER PRODUCTS BY SEARCH
=============================================*/
$(document).on("keyup", "#searchProduct", function () {
  var search = $(this).val();

  var limit = Number($("#limitProduct").val());
  var startAt = 0;
  $("#currentPageProducts").val(1);
  var category = $("#filterByCategory").val();

  loadMoreProducts(limit, startAt, category, search);
});

/*=============================================
LOAD MORE PRODUCTS
=============================================*/
function loadMoreProducts(limit, startAt, category, search) {
  if (search == "") {
    fncSweetAlert("loading", "Cargando productos...", "");
  }

  var data = new FormData();
  data.append("limit", limit);
  data.append("startAt", startAt);
  data.append("category", category);
  data.append("search", search);
  data.append("idOffice", $("#idOffice").val());

  $.ajax({
    url: "/ajax/pos.ajax.php",
    method: "POST",
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    success: function (response) {
      var parsedResponse = JSON.parse(response);

      if (parsedResponse.htmlProducts != "") {
        if (startAt == 0) {
          $(".viewProducts").html(parsedResponse.htmlProducts);
        } else {
          $(".viewProducts").append(parsedResponse.htmlProducts);
        }

        if (
          parsedResponse.totalPagesProducts > 1 &&
          $("#currentPageProducts").val() < parsedResponse.totalPagesProducts
        ) {
          $("#loadPageProducts").removeClass("d-none");
          $("#loadPageProducts").addClass("d-block");
        }

        if (
          parsedResponse.totalPagesProducts <= 1 &&
          $("#currentPageProducts").val() == 1
        ) {
          $("#loadPageProducts").addClass("d-none");
          $("#loadPageProducts").removeClass("d-block");
        }
      }

      fncSweetAlert("close", "", "");
    },
  });
}

/*=============================================
CREATE A NEW ORDER
=============================================*/
$(document).on("click", ".newOrder", function () {
  if ($("#orderHeader").attr("mode") == "on") {
    if ($("#clientList").val() == "") {
      fncToastr(
        "error",
        "Antes de crear otra orden, agregue cliente a la orden actual"
      );
      return;
    }
  }
  if ($("#idOffice").val() > 0) {
    var data = new FormData();
    data.append("order", "new");
    data.append("idOffice", $("#idOffice").val());
    data.append("seller", $("#seller").attr("idAdmin"));

    $.ajax({
      url: "/ajax/pos.ajax.php",
      method: "POST",
      data: data,
      contentType: false,
      cache: false,
      processData: false,
      success: function (response) {
        if (response == "current cash error") {
          fncToastr("error", "No hay caja abierta hoy. Ábrela en el módulo Caja para poder vender.");
          return;
        } else if (response == "yesterday cash error") {
          fncToastr("error", "La caja de un día anterior sigue abierta. Ciérrala en el módulo Caja.");
          return;
        } else if (response == "logout") {
          fncSweetAlert(
            "error",
            "Token vencido, debe iniciar sesión nuevamente",
            setTimeout(() => {
              window.location = "/logout";
            }, 1250)
          );
        } else {
          if (JSON.parse(response).type == "new") {
            fncToastr("success", "Orden creada con éxito");
          }

          $(".removeOrder").attr(
            "idOrder",
            JSON.parse(response).transaction_order
          );

          /*=============================================
	   			Build the order header
	   			=============================================*/
          $("#orderHeader").attr("mode", "on");
          $("#orderHeader").attr("idOrder", JSON.parse(response).id_order);
          $("#orderHeader").removeClass("bg-light");
          $("#orderHeader").addClass("backColor");
          $("#orderHeader h6").html(
            "Orden # " + JSON.parse(response).transaction_order
          );

          /*=============================================
	   			Enable the add client option
	   			=============================================*/
          $("#addClient").removeClass("d-none");

          /*=============================================
	   			Enable the added products panel
	   			=============================================*/
          $("#countProduct").removeClass("bg-light");
          $("#countProduct").addClass("backColor");
          $("#cleanListProduct").removeClass("d-none");
          $("#cleanListProduct").attr("idOrder", JSON.parse(response).id_order);
          $("#addProduct").html("");

          /*=============================================
	   			Enable the totals panel
	   			=============================================*/
          $("#granTotal").removeClass("bg-light");
          $("#granTotal").addClass("bg-blue");

          /*=============================================
	   			Enable the payment methods
	   			=============================================*/
          $("#payMethods").show();
        }
      },
    });
  } else {
    fncToastr("error", "Asignar sucursal a esta orden");
  }
});

/*=============================================
Pick a client
=============================================*/

$(document).on("change", "#clientList", function () {
  updateOrder();
});

/*=============================================
Add a new client
=============================================*/
$(document).on("click", "#addClient", function () {
  $("#modalClient").modal("show");
  $("#modalClient").on("shown.bs.modal", function () {
    $(".alertClient").remove();

    /*=============================================
    Client form variables
    =============================================*/
    var name_client = "";
    var surname_client = "";
    var cc_client = "";
    var email_client = "";
    var phone_client = "";
    var address_client = "";

    /*=============================================
    Watch the client form for changes
    =============================================*/
    $(".changeFormClient").change(function () {
      name_client = $("#name_client").val();
      surname_client = $("#surname_client").val();
      cc_client = $("#cc_client").val();
      email_client = $("#email_client").val();
      phone_client = $("#phone_client").val();
      address_client = $("#address_client").val();
    });

    /*=============================================
    Save the client form
    =============================================*/
    $("#btnAddClient").click(function () {
      if (
        name_client != "" &&
        surname_client != "" &&
        cc_client != "" &&
        email_client != "" &&
        phone_client != "" &&
        address_client != ""
      ) {
        var data = new FormData();
        data.append("name_client", name_client);
        data.append("surname_client", surname_client);
        data.append("cc_client", cc_client);
        data.append("email_client", email_client);
        data.append("phone_client", phone_client);
        data.append("address_client", address_client);
        data.append("idOffice", $("#idOffice").val());

        $.ajax({
          url: "/ajax/pos.ajax.php",
          method: "POST",
          data: data,
          contentType: false,
          cache: false,
          processData: false,
          success: function (response) {
            if (response == "logout") {
              fncSweetAlert(
                "error",
                "Token vencido, debe iniciar sesión nuevamente",
                setTimeout(() => {
                  window.location = "/logout";
                }, 1250)
              );
            } else {
              $("#clientList").append(`
                <option value="${response}" selected>${name_client} ${surname_client} ${cc_client}</option>
               `);

              $("#modalClient").modal("hide");

              fncToastr("success", "El cliente se ha agregado con éxito");
              updateOrder();
            }
          },
        });
      } else {
        $(this)
          .parent()
          .parent()
          .before(
            `<div class="alert alert-danger rounded mx-3 alertClient">No pueden ir campos vacíos </div>`
          );
      }
    });
  });
});

/*=============================================
Add a product
=============================================*/
$(document).on("click", ".addProductPos", function () {
  fncSweetAlert("loading", "Cargando producto...", "");

  /*=============================================
	Scroll to the top
	=============================================*/
  $("html, body").animate(
    {
      scrollTop: 0,
    },
    300
  );
  if ($("#orderHeader").attr("mode") == "on") {
    if ($("#clientList").val() == "") {
      fncToastr("error", "Antes de agregar producto elige un cliente");
      return;
    }

    var data = new FormData();
    data.append("idProduct", $(this).attr("idProduct"));
    data.append("idOrder", $("#orderHeader").attr("idOrder"));
    data.append("idClient", $("#clientList").val());
    data.append("seller", $("#seller").attr("idAdmin"));
    data.append("idOffice", $("#idOffice").val());

    $.ajax({
      url: "/ajax/pos.ajax.php",
      method: "POST",
      data: data,
      contentType: false,
      cache: false,
      processData: false,
      success: function (response) {
        fncSweetAlert("close", "", "");
        if (response == "error stock") {
          fncToastr("error", "El producto no posee stock");
        } else if (response == "logout") {
          fncSweetAlert(
            "error",
            "Token vencido, debe iniciar sesión nuevamente",
            setTimeout(() => {
              window.location = "/logout";
            }, 1250)
          );
        } else if (response == "product exist") {
          fncToastr("error", "El producto ya está agregado a la orden");
        } else {
          /*=============================================
	        Render the added product
	        =============================================*/
          $("#addProduct").append(response);

          /*=============================================
	        Order totals
	        =============================================*/
          calculateProducts();
        }
      },
    });
  } else {
    fncToastr("error", "Antes de agregar producto genere una orden");
  }
});

/*=============================================
Change quantity with the buttons
=============================================*/
$(document).on("click", ".btnQty", function () {
  /*=============================================
	Read the product id
	=============================================*/
  var key = $(this).attr("key");

  /*=============================================
	Decrease quantity
	=============================================*/
  if ($(this).attr("type") == "btnMin") {
    if (Number($(".showQuantity_" + key).val()) > 1) {
      $(".showQuantity_" + key).val(
        Number($(".showQuantity_" + key).val()) - 1
      );
    }
  }

  /*=============================================
	Increase quantity
	=============================================*/
  if ($(this).attr("type") == "btnMax") {
    $(".showQuantity_" + key).val(Number($(".showQuantity_" + key).val()) + 1);
  }
  changeQuantity(key);
});

/*=============================================
Change quantity by typing
=============================================*/
$(document).on("change", ".showQuantity", function () {
  if ($(this).val() < 1) {
    $(this).val(1);
    fncToastr("error", "No puede ingresar número inferior a 1");
    return;
  }

  changeQuantity($(this).attr("key"));
});

/*=============================================
Quantity changed
=============================================*/
function changeQuantity(key) {
  /*=============================================
	Read the discount
	=============================================*/
  var discount = Number($(".deleteSale_" + key).attr("discountSale"));

  /*=============================================
	Update the subtotal
	=============================================*/
  var pricePurchase =
    Number($(".pricePurchase_" + key).attr("originalPricePurchase")) *
    $(".showQuantity_" + key).val();
  $(".pricePurchase_" + key).attr("pricePurchase", Math.round(pricePurchase));
  $(".pricePurchase_" + key).html(money(pricePurchase));

  /*=============================================
	Save quantity and subtotal
	=============================================*/
  var data = new FormData();
  data.append("idSaleUpdate", $(".deleteSale_" + key).attr("idSale"));
  data.append("qtySale", $(".showQuantity_" + key).val());
  data.append("subtotalSale", Math.round(pricePurchase));
  $.ajax({
    url: "/ajax/pos.ajax.php",
    method: "POST",
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    success: function (response) {
      if (response == "logout") {
        fncSweetAlert(
          "error",
          "Token vencido, debe iniciar sesión nuevamente",
          setTimeout(() => {
            window.location = "/logout";
          }, 1250)
        );
      } else if (String(response).indexOf("error stock") === 0) {
        /*=============================================
        The server refused the new quantity. Without this the input kept
        the typed number while the database held the old one, so the
        screen showed one total and the charge would have been another.
        =============================================*/
        var disponible = parseInt(String(response).replace(/[^0-9]/g, ""), 10);
        if (isNaN(disponible)) disponible = 0;

        fncToastr("error", "Solo quedan " + disponible + " unidades de este producto");

        if (disponible >= 1) {
          $(".showQuantity_" + key).val(disponible);
          changeQuantity(key);
        } else {
          $(".showQuantity_" + key).val(1);
          calculateProducts();
        }
        /*=============================================
        Product totals
        =============================================*/
        calculateProducts();
      }
    },
  });
}

/*=============================================
Remove a product from the order
=============================================*/
$(document).on("click", ".deleteSale", function () {
  var idSale = $(this).attr("idSale");
  var elem = $(this);

  fncSweetAlert("confirm", "¿Está seguro de borrar este producto?", "").then(
    (resp) => {
      if (resp) {
        var data = new FormData();
        data.append("idSaleDelete", idSale);
        $.ajax({
          url: "/ajax/pos.ajax.php",
          method: "POST",
          data: data,
          contentType: false,
          cache: false,
          processData: false,
          success: function (response) {
            if (response == "logout") {
              fncSweetAlert(
                "error",
                "Token vencido, debe iniciar sesión nuevamente",
                setTimeout(() => {
                  window.location = "/logout";
                }, 1250)
              );
            } else if (response == "error") {
              fncToastr("error", "El producto no se puede remover");
            } else {
              fncToastr("success", "El producto se ha removido correctamente");
              $(elem).parent().parent().remove();
              calculateProducts();
            }
          },
        });
      }
    }
  );
});

/*=============================================
Clear the added products
=============================================*/
$(document).on("click", "#cleanListProduct", function () {
  if ($("#addProduct tr").length == 0) {
    fncToastr("error", "No hay productos a remover");
    return;
  }

  var idOrderSale = $(this).attr("idOrder");
  fncSweetAlert(
    "confirm",
    "¿Está seguro de borrar estos productos añadidos?",
    ""
  ).then((resp) => {
    if (resp) {
      fncSweetAlert("loading", "Eliminando productos...", "");
      var data = new FormData();
      data.append("idOrderSale", idOrderSale);
      $.ajax({
        url: "/ajax/pos.ajax.php",
        method: "POST",
        data: data,
        contentType: false,
        cache: false,
        processData: false,
        success: function (response) {
          fncSweetAlert("close", "", "");
          if (response == "error") {
            fncToastr("error", "Los productos no se pueden remover");
          } else if (response == "logout") {
            fncSweetAlert(
              "error",
              "Token vencido, debe iniciar sesión nuevamente",
              setTimeout(() => {
                window.location = "/logout";
              }, 1250)
            );
          } else {
            fncToastr("success", "Los productos se han removido correctamente");
            $("#addProduct").html("");
            calculateProducts();
          }
        },
      });
    }
  });
});

/*=============================================
Product totals
=============================================*/
function calculateProducts() {
  /*=============================================
	Count the products
	=============================================*/
  var showQuantity = $(".showQuantity");
  var totalQty = 0;

  showQuantity.each((i) => {
    totalQty += Number($(showQuantity[i]).val());
  });

  $("#countProduct").html(totalQty);

  /*=============================================
	Add up the subtotals
	=============================================*/
  var pricePurchase = $(".pricePurchase");
  var totalPricePurchase = 0;

  pricePurchase.each((i) => {
    totalPricePurchase += Number($(pricePurchase[i]).attr("pricePurchase"));
  });

  /*=============================================
	Subtotal
	=============================================*/
  $("#subtotal").attr("subtotal", Math.round(totalPricePurchase));
  $("#subtotal").html(money(totalPricePurchase));

  /*=============================================
	Add up discounts and taxes
	=============================================*/
  var deleteSale = $(".deleteSale");
  var calculateDiscount = 0;
  var totalPriceDiscount = 0;
  var calculateTax = 0;
  var totalPriceTax = 0;

  deleteSale.each((i) => {
    calculateDiscount =
      Number($(pricePurchase[i]).attr("pricePurchase")) *
      (Number($(deleteSale[i]).attr("discountSale")) / 100);
    totalPriceDiscount += calculateDiscount;

    if (Number($(deleteSale[i]).attr("discountSale")) > 0) {
      calculateTax =
        (Number($(pricePurchase[i]).attr("pricePurchase")) -
          Number(calculateDiscount)) *
        (Number($(deleteSale[i]).attr("taxSale")) / 100);
    } else {
      calculateTax =
        Number($(pricePurchase[i]).attr("pricePurchase")) *
        (Number($(deleteSale[i]).attr("taxSale")) / 100);
    }
    totalPriceTax += calculateTax;
  });

  /*=============================================
	Discount
	=============================================*/
  $("#discount").attr("discount", Math.round(totalPriceDiscount));
  $("#discount").html(money(totalPriceDiscount));

  /*=============================================
	Tax
	=============================================*/
  $("#tax").attr("tax", Math.round(totalPriceTax));
  $("#tax").html(money(totalPriceTax));

  /*=============================================
	Grand total
	=============================================*/
  var total =
    Number($("#subtotal").attr("subtotal")) -
    Number($("#discount").attr("discount")) +
    Number($("#tax").attr("tax"));
  $("#granTotal span").attr("granTotal", Math.round(total));
  $("#granTotal span").html(money(total));

  /*=============================================
	Update the order
	=============================================*/
  updateOrder();
}

/*=============================================
Save changes to the order
=============================================*/
function updateOrder() {
  if ($("#orderHeader").attr("mode") == "on") {
    var idOrder = $("#orderHeader").attr("idOrder");
    var idClient = $("#clientList").val();
    var subtotalOrder = $("#subtotal").attr("subtotal");
    var discountOrder = $("#discount").attr("discount");
    var taxOrder = $("#tax").attr("tax");
    var totalOrder = $("#granTotal span").attr("granTotal");

    var data = new FormData();
    data.append("idOrderUpdate", idOrder);
    data.append("idClient", idClient);
    data.append("subtotalOrder", subtotalOrder);
    data.append("discountOrder", discountOrder);
    data.append("taxOrder", taxOrder);
    data.append("totalOrder", totalOrder);

    $.ajax({
      url: "/ajax/pos.ajax.php",
      method: "POST",
      data: data,
      contentType: false,
      cache: false,
      processData: false,
      success: function (response) {
        console.log(response);
        if (response == "logout") {
          fncSweetAlert(
            "error",
            "Token vencido, debe iniciar sesión nuevamente",
            setTimeout(() => {
              window.location = "/logout";
            }, 1250)
          );
        }
      },
    });
  }
}

/*=============================================
DELETE THE ORDER
=============================================*/
$(document).on("click", ".removeOrder", function () {
  var idOrder = $(this).attr("idOrder");
  fncSweetAlert("confirm", "¿Está seguro de remover esta orden?", "").then(
    (resp) => {
      if (resp) {
        fncSweetAlert("loading", "Eliminando Orden...", "");

        var data = new FormData();
        data.append("idOrderDelete", idOrder);
        $.ajax({
          url: "/ajax/pos.ajax.php",
          method: "POST",
          data: data,
          contentType: false,
          cache: false,
          processData: false,
          success: function (response) {
            fncSweetAlert("close", "", "");
            if (response == "error") {
              fncToastr("error", "La orden no se puede remover");
            } else if (response == "logout") {
              fncSweetAlert(
                "error",
                "Token vencido, debe iniciar sesión nuevamente",
                setTimeout(() => {
                  window.location = "/logout";
                }, 1250)
              );
            } else {
              fncSweetAlert(
                "success",
                "La orden se ha removido con éxito", "");
              setTimeout(() => location.reload(), 1250)
            }
          },
        });
      }
    }
  );
});

/*=============================================
Checkout modal

One modal for every method. The old one bound its handlers inside
shown.bs.modal, so each reopen added another copy of them
=============================================*/

function checkoutEscape(value) {
  return $("<div>").text(value == null ? "" : value).html();
}

/*=============================================
What is really being charged: the order total less the manual discount
=============================================*/
function checkoutTotal() {
  var base = Number($("#granTotal span").attr("granTotal")) || 0;
  var extra = Number($("#extraDiscount").val()) || 0;

  if (extra < 0) {
    extra = 0;
  }

  if (extra > base) {
    extra = base;
  }

  return base - extra;
}

/*=============================================
The ticket preview
=============================================*/
function checkoutTicket(total) {
  var html = "";

  $("#addProduct tr").each(function () {
    var qty = Number($(this).find(".showQuantity").val()) || 0;
    var title = $(this).find("h6 strong").first().text();
    var subtotal = Number($(this).find(".pricePurchase").attr("pricePurchase")) || 0;

    html +=
      '<div class="tk-row"><span>' +
      qty +
      "x " +
      checkoutEscape(title) +
      "</span><span>$ " +
      money(subtotal) +
      "</span></div>";
  });

  html += '<div class="tk-sep"></div>';

  var discount = Number($("#discount").attr("discount")) || 0;
  var tax = Number($("#tax").attr("tax")) || 0;
  var extra = (Number($("#granTotal span").attr("granTotal")) || 0) - total;
  var rows = "";

  if (discount > 0) {
    rows += '<div class="tk-row"><span>Descuento</span><span>-$ ' + money(discount) + "</span></div>";
  }

  if (tax > 0) {
    rows += '<div class="tk-row"><span>Impuesto</span><span>$ ' + money(tax) + "</span></div>";
  }

  if (extra > 0) {
    rows += '<div class="tk-row"><span>Descuento adicional</span><span>-$ ' + money(extra) + "</span></div>";
  }

  if (rows !== "") {
    html += rows + '<div class="tk-sep"></div>';
  }

  html += '<div class="tk-row tk-bold"><span>TOTAL</span><span>$ ' + money(total) + "</span></div>";

  return html;
}

/*=============================================
Redraw the ticket, show the block the chosen method needs, and only let the
cobro through when the money on screen covers the total
=============================================*/
function checkoutRefresh() {
  var total = checkoutTotal();
  var mixto = $("#toggleMixto").attr("on") == "yes";
  var method = $("#payMethodSelect").val();
  var ok = total > 0;

  $("#checkoutLines").html(checkoutTicket(total));
  $("#payMethodSelect").toggle(!mixto);
  $(".payBlock").hide();

  if (mixto) {
    $("#blockMixto").show();

    var sum = (Number($("#mixtoCash").val()) || 0) + (Number($("#mixtoCard").val()) || 0);
    var mixtoShort = sum < total;

    $("#mixtoSum")
      .html("$ " + money(sum) + " / $ " + money(total))
      .removeClass("text-red text-green")
      .addClass(mixtoShort ? "text-red" : "text-green");

    $("#mixtoShort").html(mixtoShort ? "Faltan $ " + money(total - sum) : "");

    ok = ok && !mixtoShort;
  } else if (method == "efectivo") {
    $("#blockCash").show();

    var cash = Number($("#cashReceived").val()) || 0;
    var cashShort = cash < total;

    $("#cashLabel").html(cashShort ? "Faltan" : "Vuelto");

    $("#cashAmount")
      .html("$ " + money(cashShort ? total - cash : cash - total))
      .removeClass("text-red text-green")
      .addClass(cashShort ? "text-red" : "text-green");

    ok = ok && !cashShort;
  } else if (method == "transferencia") {
    $("#blockTransfer").show();

    ok = ok && $.trim($("#idTransferPay").val()) !== "";
  }

  $("#confirmCheckout").prop("disabled", !ok);
}

/*=============================================
The client already chosen in the panel
=============================================*/
function checkoutClientLabel() {
  var name = $.trim($("#clientList option:selected").text());

  $("#checkoutClientLabel").html(
    name !== "" && name !== "Buscar" ? checkoutEscape(name) : "Asignar cliente al pedido"
  );
}

/*=============================================
Open the checkout
=============================================*/
$(document).on("click", "#openCheckout", function () {
  if ($("#addProduct tr").length == 0) {
    fncToastr("error", "No hay productos añadidos");
    return;
  }

  $("#idOrderPay").val($("#orderHeader").attr("idOrder"));
  $("#payMethodSelect").val("efectivo");
  $("#toggleMixto").attr("on", "no").removeClass("backColor");
  $("#extraDiscount, #cashReceived, #mixtoCash, #mixtoCard, #idTransferPay, #checkoutNote").val("");

  checkoutClientLabel();
  checkoutRefresh();

  $("#modalCheckout").modal("show");
});

$(document).on(
  "input",
  "#extraDiscount, #cashReceived, #mixtoCash, #mixtoCard, #idTransferPay",
  function () {
    checkoutRefresh();
  }
);

$(document).on("change", "#payMethodSelect", function () {
  checkoutRefresh();
});

$(document).on("click", "#toggleMixto", function () {
  var on = $(this).attr("on") == "yes";

  $(this)
    .attr("on", on ? "no" : "yes")
    .toggleClass("backColor", !on);

  checkoutRefresh();
});

$(document).on("click", ".quickCash", function () {
  $("#cashReceived").val($(this).attr("amount"));
  checkoutRefresh();
});

$(document).on("click", "#exactCash", function () {
  $("#cashReceived").val(checkoutTotal());
  checkoutRefresh();
});

/*=============================================
The client is picked in the panel, so the button sends the cashier there and
brings the checkout back when that modal closes
=============================================*/
$(document).on("click", "#checkoutClient", function () {
  $("#modalCheckout").modal("hide");

  $("#modalClient").one("hidden.bs.modal", function () {
    checkoutClientLabel();
    checkoutRefresh();
    $("#modalCheckout").modal("show");
  });

  $("#addClient").trigger("click");
});

/*=============================================
Hand the payment to the form. The server clamps the discount and checks the
amounts again, this only keeps an unpayable order from being sent
=============================================*/
$(document).on("submit", "#formCheckout", function (event) {
  var total = checkoutTotal();
  var mixto = $("#toggleMixto").attr("on") == "yes";
  var method = mixto ? "mixto" : $("#payMethodSelect").val();
  var cash = 0;
  var card = 0;

  if (mixto) {
    cash = Number($("#mixtoCash").val()) || 0;
    card = Number($("#mixtoCard").val()) || 0;
  } else if (method == "efectivo") {
    cash = Number($("#cashReceived").val()) || 0;
  }

  if (total <= 0) {
    event.preventDefault();
    fncToastr("error", "La orden no tiene productos");
    return;
  }

  if ((mixto || method == "efectivo") && cash + card < total) {
    event.preventDefault();
    fncToastr("error", "El pago no cubre el total");
    return;
  }

  if (method == "transferencia" && $.trim($("#idTransferPay").val()) === "") {
    event.preventDefault();
    fncToastr("error", "Ingresa el id de la transferencia");
    return;
  }

  $("#methodPay").val(method);
  $("#extraDiscountPay").val((Number($("#granTotal span").attr("granTotal")) || 0) - total);
  $("#notePay").val($("#checkoutNote").val());
  $("#transferPay").val(method == "transferencia" ? $("#idTransferPay").val() : "");
  $("#cashPay").val(cash);
  $("#cardPay").val(card);
});

/*=============================================
Clear the POS alerts
=============================================*/
if ($(".alertPos").length > 0) {
  setTimeout(() => {
    $(".alertPos").remove();
  }, 10000);
}

/*=============================================
The sale receipt. Called from the checkout response, which builds the
ticket server side and hands it over already rendered
=============================================*/

function fncShowReceipt(html, transaction) {
  if (!html) {
    window.location = "/posify";
    return;
  }

  fncSweetAlert("close", "", "");

  $("#saleTicket").html(html);
  $("#receiptTransaction").html("Orden # " + $("<div>").text(transaction || "").html());
  $("#modalReceipt").modal("show");
}

/*=============================================
A charged order cannot be edited, so leaving the receipt starts a new sale
=============================================*/

$(document).on("click", "#finishSale, #closeReceipt", function () {
  window.location = "/posify";
});

$(document).on("click", "#printReceipt", function () {
  printTicket($("#saleTicket").html(), "Recibo de venta");
});

/*=============================================
Sized to the content: Chromium ignores "size: 80mm auto" and falls back
to letter, so the height is measured and written in millimetres
=============================================*/

function printTicket(html, title) {
  var win = window.open("", "_blank", "width=420,height=640");

  win.document.write(
    "<html><head><title>" + title + "</title>" +
    '<link rel="stylesheet" href="/views/assets/css/ticket/ticket.css">' +
    "<style>body{margin:0}</style></head><body>" + html + "</body></html>"
  );

  win.document.close();

  var sizeAndPrint = function () {
    var strip = win.document.querySelector(".tk");
    var px = strip ? strip.offsetHeight : win.document.body.scrollHeight;
    var mm = Math.ceil((px * 25.4) / 96) + 6;

    var page = win.document.createElement("style");
    page.textContent = "@page{size:80mm " + mm + "mm;margin:3mm}";
    win.document.head.appendChild(page);

    win.focus();
    win.print();
  };

  if (win.document.readyState === "complete") {
    setTimeout(sizeAndPrint, 200);
  } else {
    win.onload = function () { setTimeout(sizeAndPrint, 200); };
  }
}
