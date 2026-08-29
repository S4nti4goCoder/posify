/*=============================================
The password rules, ticked as they are met.

The same rules run in PHP, which is what actually refuses a weak password.
This only saves the person a round trip to find that out
=============================================*/

(function () {
  function met(value) {
    var list = window.posCommonPasswords || [];

    return {
      length: value.length >= 8 && value.length <= 64,
      upper: /\p{Lu}/u.test(value),
      lower: /\p{Ll}/u.test(value),
      number: /[0-9]/.test(value),
      symbol: /[^\p{L}\p{N}]/u.test(value),
      common: list.indexOf(value.trim().toLowerCase()) === -1
    };
  }

  function paint(field) {
    var rules = document.querySelector('.passwordRules[data-password-for="' + field.id + '"]');

    if (!rules) {
      return;
    }

    var value = field.value;
    var state = met(value);

    rules.querySelectorAll("li[data-rule]").forEach(function (item) {
      var ok = value !== "" && state[item.getAttribute("data-rule")] === true;
      var icon = item.querySelector("i");

      item.classList.toggle("text-green", ok);
      item.classList.toggle("text-muted", !ok);

      if (icon) {
        icon.className = ok ? "bi bi-check-circle-fill me-1" : "bi bi-circle me-1";
      }
    });
  }

  document.addEventListener("input", function (event) {
    if (event.target && event.target.type === "password" && event.target.id) {
      paint(event.target);
    }
  });

  /*=============================================
  A modal opens with whatever the browser autofilled already in it
  =============================================*/
  document.addEventListener("shown.bs.modal", function (event) {
    event.target.querySelectorAll('input[type="password"][id]').forEach(paint);
  });
})();
