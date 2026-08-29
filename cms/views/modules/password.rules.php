<?php

/*=============================================
The rule list under a password field. Set $passwordRulesFor to the field id
before including it. Labels and the common list come from PasswordPolicy, so
the browser and the server cannot drift apart
=============================================*/

require_once __DIR__ . "/../../../lib/password.policy.php";
require_once __DIR__ . "/../../../lib/view.php";

$rulesFor = isset($passwordRulesFor) ? (string) $passwordRulesFor : "";

if ($rulesFor !== ""):

?>

<ul class="passwordRules small list-unstyled ps-0 mt-2 mb-0" data-password-for="<?php echo View::text($rulesFor) ?>">
    <?php foreach (PasswordPolicy::rules() as $key => $label): ?>
        <li data-rule="<?php echo View::text($key) ?>" class="text-muted"><i class="bi bi-circle me-1"></i><?php echo View::text($label) ?></li>
    <?php endforeach ?>
</ul>

<?php

    if (!defined("POS_COMMON_PASSWORDS_SENT")) {

        define("POS_COMMON_PASSWORDS_SENT", true);

        $common = [];

        foreach (file(__DIR__ . "/../../../lib/common.passwords.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {

            $line = trim($line);

            if ($line !== "" && $line[0] !== "#") {
                $common[] = mb_strtolower($line);
            }
        }

        echo '<script>window.posCommonPasswords = ' . View::js($common) . ';</script>';
    }

endif;
