<?php

// Copy to config.local.php and fill in. Never commit config.local.php.
// Any key can be overridden with a POS_<KEY> environment variable.

return [
    'db_host'     => 'localhost',
    'db_name'     => 'posify_db',
    'db_user'     => '',            // the user you granted on posify_db
    'db_password' => '',

    'api_key'    => '',             // a long random string
    'jwt_secret' => '',             // a different long random string

    'api_base_url' => 'http://api.posify.com/',
    'cms_origin'   => 'http://cms.posify.com',

    'debug' => false,
];
