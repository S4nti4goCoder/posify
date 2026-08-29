<?php

/*=============================================
The sidebar icon and the typeface. Set $themeSymbol and $themeFont to the
stored values before including it; both may still hold the old raw HTML
=============================================*/

require_once __DIR__ . "/../../../lib/theme.php";
require_once __DIR__ . "/../../../lib/view.php";

$currentIcon = Theme::iconName($themeSymbol ?? "");
$currentFont = Theme::fontName($themeFont ?? "");

?>

<div class="form-group mb-3">

    <label class="form-label">Símbolo del Dashboard <sup>*</sup></label>

    <input type="hidden" id="symbol_admin" name="symbol_admin" value="<?php echo View::text($currentIcon) ?>">

    <div class="border rounded p-2 iconPicker" data-target="symbol_admin">
        <?php foreach (Theme::ICONS as $icon): ?>
            <button type="button"
                class="btn btn-sm border rounded m-1 pickIcon <?php echo $icon === $currentIcon ? "backColor" : "" ?>"
                data-icon="<?php echo View::text($icon) ?>"
                title="<?php echo View::text($icon) ?>">
                <i class="bi bi-<?php echo View::text($icon) ?>"></i>
            </button>
        <?php endforeach ?>
    </div>

</div>

<div class="form-group mb-3">

    <label class="form-label" for="font_admin">Tipografía del Dashboard</label>

    <select class="form-select rounded" id="font_admin" name="font_admin">
        <?php foreach (array_keys(Theme::FONTS) as $family): ?>
            <option value="<?php echo View::text($family) ?>" <?php echo $family === $currentFont ? "selected" : "" ?>><?php echo View::text($family) ?></option>
        <?php endforeach ?>
    </select>

</div>

<?php if (!defined("POS_ICON_PICKER_SENT")): define("POS_ICON_PICKER_SENT", true) ?>

<script>
    document.addEventListener("click", function (event) {
        var button = event.target.closest(".pickIcon");

        if (!button) {
            return;
        }

        var picker = button.closest(".iconPicker");
        var field = document.getElementById(picker.getAttribute("data-target"));

        picker.querySelectorAll(".pickIcon").forEach(function (other) {
            other.classList.remove("backColor");
        });

        button.classList.add("backColor");
        field.value = button.getAttribute("data-icon");
    });
</script>

<?php endif ?>
