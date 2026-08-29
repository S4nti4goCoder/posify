<?php

// Ends the request. This runs from inside the dashboard template, which still
// had the profile and page modals to render, and every one of them reads the
// session that was just destroyed.

session_unset();
session_destroy();

echo '<script>
window.location = "/";
</script>';

exit;
