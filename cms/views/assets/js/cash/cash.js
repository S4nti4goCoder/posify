/*=============================================
Till: opening, the count at closing, and the ticket
=============================================*/

var cashData = null;
var cashClosed = false;

function cashPost(action, extra, done) {
  var data = new FormData();
  data.append("action", action);

  for (var key in extra) {
    data.append(key, extra[key]);
  }

  $.ajax({
    url: "/ajax/cash.ajax.php",
    method: "POST",
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    dataType: "json",
    success: function (response) {
      if (response && response.error == "logout") {
        window.location = "/salir";
        return;
      }
      done(response);
    },
    error: function () {
      fncToastr("error", "No se pudo comunicar con el servidor");
    }
  });
}

/*=============================================
Open
=============================================*/

$(document).on("click", "#confirmOpenCash", function () {
  var start = Number($("#startCash").val());

  if (isNaN(start) || start < 0) {
    $("#openCashError").removeClass("d-none").html("Escribe un monto válido");
    return;
  }

  cashPost("open", { start: start }, function (response) {
    if (response.ok) {
      fncToastr("success", "Caja abierta");
      setTimeout(function () { window.location.reload(); }, 800);
      return;
    }

    var message = response.error == "already_open"
      ? "Ya hay una caja abierta en esta sucursal"
      : "No se pudo abrir la caja";

    $("#openCashError").removeClass("d-none").html(message);
  });
});

/*=============================================
Close: pull the day before showing anything
=============================================*/

$("#modalCloseCash").on("show.bs.modal", function () {
  cashPost("summary", {}, function (response) {
    if (!response.ok) {
      fncToastr("error", "No hay una caja abierta");
      $("#modalCloseCash").modal("hide");
      setTimeout(function () { window.location.reload(); }, 900);
      return;
    }

    cashData = response;

    var r = response.report;
    var s = response.summary;

    $("#cashOrders").html(r.orders);
    $("#cashTotal").html("$ " + money(r.total));
    $("#cashDiscounts").html("$ " + money(r.discounts));
    $("#cashByCash").html("$ " + money(r.methods.efectivo));
    $("#cashByCard").html("$ " + money(r.methods.tarjeta));
    $("#cashByTransfer").html("$ " + money(r.methods.transferencia));

    $("#cashStart").html("$ " + money(response.session.start));
    $("#cashIncome").html("$ " + money(s.income));
    $("#cashBills").html("$ " + money(s.bills));
    $("#cashExpected").html("$ " + money(s.expected));

    if (r.top.length) {
      var items = "";
      for (var i = 0; i < r.top.length; i++) {
        items += "<li>" + $("<div>").text(r.top[i].name).html() + "</li>";
      }
      $("#cashTop").html(items);
      $("#cashTopBox").removeClass("d-none");
    } else {
      $("#cashTopBox").addClass("d-none");
    }

    $(".countBill").val(0);
    $(".billLine").html("$ 0");
    $("#manualCount").val("");
    countCash();
  });
});

/*=============================================
The count: bills add up, the manual amount wins
=============================================*/

function countCash() {
  var total = 0;

  $(".countBill").each(function () {
    var qty = Number($(this).val()) || 0;
    var value = Number($(this).attr("data-value"));
    var line = qty * value;

    total += line;
    $(this).closest("div").find(".billLine").html("$ " + money(line));
  });

  $("#cashCounted").html("$ " + money(total));

  var manual = $("#manualCount").val();
  var counted = manual === "" ? total : Number(manual) || 0;

  var expected = cashData ? cashData.summary.expected : 0;
  var gap = counted - expected;

  $("#cashGap").html("$ " + money(gap));

  if (gap === 0) {
    $("#cashGapNote").html("<span class='text-success'>La caja cuadra</span>");
  } else if (gap > 0) {
    $("#cashGapNote").html("<span class='text-primary'>Sobrante</span>");
  } else {
    $("#cashGapNote").html("<span class='text-danger'>Faltante</span>");
  }
}

$(document).on("input", ".countBill, #manualCount", countCash);

/*=============================================
Confirm the close
=============================================*/

$(document).on("click", "#confirmCloseCash", function () {
  var manual = $("#manualCount").val();
  var counted = 0;

  if (manual !== "") {
    counted = Number(manual) || 0;
  } else {
    $(".countBill").each(function () {
      counted += (Number($(this).val()) || 0) * Number($(this).attr("data-value"));
    });
  }

  var button = $(this);
  button.prop("disabled", true);

  cashPost("close", { counted: counted }, function (response) {
    button.prop("disabled", false);

    if (!response.ok) {
      fncToastr("error", "No se pudo cerrar la caja");
      return;
    }

    $("#modalCloseCash").modal("hide");
    renderTicket(response.ticket, response.expected, response.gap);
    $("#modalCashTicket").modal("show");
    cashClosed = true;
  });
});

/*=============================================
The ticket: 80 mm roll, the width a thermal printer feeds
=============================================*/

function ticketRow(label, value, bold) {
  return "<div class='tk-row" + (bold ? " tk-bold" : "") + "'>" +
    "<span>" + label + "</span><span>" + value + "</span></div>";
}

function renderTicket(ticket, expected, gap) {
  var r = ticket.report;
  var safe = function (text) { return $("<div>").text(text).html(); };

  var html =
    "<div class='tk'>" +

      "<div class='tk-center tk-bold'>" + safe(ticket.office || "Cierre de caja") + "</div>" +
      "<div class='tk-center tk-small'>CIERRE DE CAJA</div>" +
      "<div class='tk-center tk-small'>" + safe(ticket.date) + "</div>" +

      "<div class='tk-sep'></div>" +

      "<div class='tk-head'>SESION</div>" +
      ticketRow("Apertura", safe(ticket.since)) +
      ticketRow("Cierre", safe(ticket.closed)) +
      ticketRow("Base inicial", "$ " + money(ticket.start)) +

      "<div class='tk-sep'></div>" +

      "<div class='tk-head'>VENTAS</div>" +
      ticketRow("No. de ventas", r.orders) +
      ticketRow("Efectivo", "$ " + money(r.methods.efectivo)) +
      ticketRow("Tarjeta", "$ " + money(r.methods.tarjeta)) +
      ticketRow("Transferencia", "$ " + money(r.methods.transferencia)) +
      ticketRow("Descuentos", "-$ " + money(r.discounts)) +
      ticketRow("Total ventas", "$ " + money(r.total), true) +

      "<div class='tk-sep'></div>" +

      "<div class='tk-head'>ARQUEO DE EFECTIVO</div>" +
      ticketRow("Esperado", "$ " + money(expected)) +
      ticketRow("Contado", "$ " + money(ticket.counted)) +
      ticketRow("Diferencia", "$ " + money(gap), true) +
      "<div class='tk-center tk-small'>" +
        (gap === 0 ? "La caja cuadra" : (gap > 0 ? "Sobrante" : "Faltante")) +
      "</div>" +

      "<div class='tk-sep'></div>" +
      "<div class='tk-center tk-small'>" + safe(ticket.admin || "") + "</div>" +

    "</div>";

  $("#cashTicket").html(html);
}

$(document).on("click", "#printCashTicket", function () {
  var win = window.open("", "_blank", "width=420,height=640");

  win.document.write(
    "<html><head><title>Cierre de caja</title>" +
    '<link rel="stylesheet" href="/views/assets/css/ticket/ticket.css">' +
    "<style>body{margin:0}</style></head><body>" +
    $("#cashTicket").html() +
    "</body></html>"
  );

  win.document.close();

  /*=============================================
  A page sized to the content, so the roll is not asked for a whole
  sheet. Chromium ignores "size: 80mm auto" and falls back to letter,
  so the height is measured and written in millimetres.
  =============================================*/

  var sizeAndPrint = function () {
    // the body stretches to the window, so measure the ticket itself
    var strip = win.document.querySelector(".tk");
    var px = strip ? strip.offsetHeight : win.document.body.scrollHeight;
    var mm = Math.ceil((px * 25.4) / 96) + 6;

    var page = win.document.createElement("style");
    page.textContent = "@page{size:80mm " + mm + "mm;margin:3mm}";
    win.document.head.appendChild(page);

    win.focus();
    win.print();
  };

  // document.write finishes synchronously, so onload may already be gone
  if (win.document.readyState === "complete") {

    sizeAndPrint();

  } else {

    win.onload = sizeAndPrint;
  }
});

/*=============================================
The table was drawn before the close, so it is stale until reloaded
=============================================*/

$("#modalCashTicket").on("hidden.bs.modal", function () {
  if (cashClosed) {
    window.location.reload();
  }
});
/*=============================================
A till left open on an earlier day: the modal opens by itself, because
the POS refuses to sell until it is closed and a banner is easy to miss
=============================================*/

$(function () {
  if (typeof cashIsStale !== "undefined" && cashIsStale) {
    setTimeout(function () {
      $("#modalCloseCash").modal("show");
    }, 600);
  }
});
