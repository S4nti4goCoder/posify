/*=============================================
Dashboard interface
=============================================*/
var menuToggle = document.getElementById("menu-toggle");
var sidebar = document.getElementById("sidebar-wrapper");
var menuText = $(".menu-text");
var btnPages = $(".btnPages");
var toogle = 0;

/*=============================================
Bind the button that opens and closes the menu
=============================================*/
menuToggle.addEventListener("click", function () {
  var isMobile = window.innerWidth <= 768;

  if (isMobile) {
    sidebar.classList.toggle("show");
  } else {
    if (toogle == 0) {
      toogle = 1;
      $(".sidebar-heading:first").css({ "min-width": "50px" });
    } else {
      toogle = 0;
      $(".sidebar-heading:first").css({ "min-width": "225px" });
    }

    sidebar.classList.toggle("collapsed");
    $(btnPages).toggle();
  }

  menuText.each((i) => {
    $(menuText[i]).css({ opacity: 0 });

    if (!sidebar.classList.contains("collapsed")) {
      $(menuText[i]).animate({ opacity: 1 }, 500);
    } else {
      $(menuText[i]).animate({ opacity: 0 }, 500);
    }
  });
});

/*=============================================
Close the floating menu on a click anywhere outside it
=============================================*/
document.addEventListener("click", function (event) {
  var isClickInsideMenu =
    sidebar.contains(event.target) || menuToggle.contains(event.target);
  var isMobile = window.innerWidth <= 768;

  // Closes the menu on a click outside it while on mobile
  if (!isClickInsideMenu && sidebar.classList.contains("show") && isMobile) {
    sidebar.classList.remove("show");
  }
});

/*=============================================
Keep the table responsive as the window resizes
=============================================*/
$(window).resize(function () {
  updateWidth();
});

function updateWidth() {
  var width = Number($(window).width()) - 100;

  if (width < 768 && $(".table-responsive").length) {
    var tableResponsive = $(".table-responsive");

    tableResponsive.each((i) => {
      $(tableResponsive[i]).css({ width: width + "px" });
    });
  }
}

updateWidth();

/*=============================================
Clear the icon field
=============================================*/
$(document).on("change", ".cleanIcon", function () {
  if ($(this).val().split('"').length > 0) {
    $(this).val($(this).val().split('"')[1]);
  } else {
    $(this).val($(this).val());
  }
});

/*=============================================
Price format, Colombian pesos
=============================================*/
function money(number) {
  var pesos = Math.round(Number(number) || 0);
  var sign = pesos < 0 ? "-" : "";

  return sign + Math.abs(pesos).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/*=============================================
Confirm before logging out

A cashier with an order open should not lose it to a stray click. The rest
of the system already asks before destructive actions
=============================================*/
$(document).on("click", 'a[href="/logout"]', function (event) {
  event.preventDefault();

  fncSweetAlert("confirm", "¿Seguro que quieres cerrar sesión?", "").then(function (ok) {
    if (ok) {
      window.location = "/logout";
    }
  });
});
