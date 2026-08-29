<?php

require_once __DIR__ . "/view.php";

// The dashboard look: the sidebar icon and the typeface.
//
// Both columns used to hold raw HTML that the views printed unescaped, so the
// superadmin had to paste an <i> tag and a whole Google Fonts <link> block by
// hand. The symbol is printed on the login screen too, which anyone can open,
// so a script tag stored there ran for every visitor.
//
// Now the columns hold a name and the markup is built here from these lists.
// Values saved the old way still work: the name is read back out of them.

final class Theme
{
    public const DEFAULT_ICON = "cart-check-fill";
    public const DEFAULT_FONT = "Nunito";

    /** Bootstrap Icons, without the "bi-" prefix. */
    public const ICONS = [
        "cart-check-fill", "cart4", "basket3-fill", "bag-check-fill",
        "shop", "shop-window", "house-door-fill", "briefcase-fill",
        "receipt", "receipt-cutoff", "printer-fill", "upc-scan",
        "cash-coin", "cash-stack", "coin", "credit-card-fill",
        "box-seam", "tags-fill", "clipboard-data-fill", "graph-up-arrow",
        "gear-fill", "key-fill", "star-fill", "lightning-charge-fill",
    ];

    /** Family => the weights Google Fonts actually serves for it. */
    public const FONTS = [
        "Nunito"     => "Nunito:wght@300;400;600;700",
        "Inter"      => "Inter:wght@300;400;600;700",
        "Poppins"    => "Poppins:wght@300;400;600;700",
        "Roboto"     => "Roboto:wght@300;400;500;700",
        "Montserrat" => "Montserrat:wght@300;400;600;700",
        "Lato"       => "Lato:wght@300;400;700",
        "Open Sans"  => "Open+Sans:wght@300;400;600;700",
        "Work Sans"  => "Work+Sans:wght@300;400;600;700",
    ];

    /** The icon name, whatever shape the column holds. */
    public static function iconName($stored): string
    {
        $value = trim((string) $stored);

        if (in_array($value, self::ICONS, true)) {

            return $value;
        }

        // saved the old way: <i class="bi bi-cart-check-fill"></i>
        if (preg_match('/bi-([a-z0-9-]+)/i', $value, $found) && in_array($found[1], self::ICONS, true)) {

            return $found[1];
        }

        return self::DEFAULT_ICON;
    }

    public static function icon($stored): string
    {
        return '<i class="bi bi-' . self::iconName($stored) . '"></i>';
    }

    /** The family name, whatever shape the column holds. */
    public static function fontName($stored): string
    {
        $value = trim((string) $stored);

        if (isset(self::FONTS[$value])) {

            return $value;
        }

        // saved the old way: the whole <link> block
        if (preg_match('/family=([^:&"\'>]+)/i', $value, $found)) {

            $family = str_replace("+", " ", urldecode($found[1]));

            if (isset(self::FONTS[$family])) {

                return $family;
            }
        }

        return self::DEFAULT_FONT;
    }

    public static function fontLink($stored): string
    {
        $spec = self::FONTS[self::fontName($stored)];

        return '<link rel="preconnect" href="https://fonts.googleapis.com">'
             . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
             . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=' . $spec . '&display=swap">';
    }

    /** Safe to drop straight into a font-family declaration. */
    public static function fontFamilyCss($stored): string
    {
        return '"' . self::fontName($stored) . '"';
    }
}
