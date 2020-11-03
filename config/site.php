<?php
// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'tteae_con5');
// define('DB_PASSWORD', 'wrLj9Uu5');
// define('DB_DATABASE', 'tteae_con5');

// define('ENABLE_MARKETPLACE_SUPPORT',1);

// define('BASE_URL', 'https://www.tte.ae');
// define('DIR_REL', 'https://static-tte.s3-accelerate.dualstack.amazonaws.com');

// require 'app_data.php';


if (strpos($_SERVER['HTTP_HOST'],'admin') !== FALSE) {
	define('REDIRECT_TO_BASE_URL', false); 
}
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'pixelfla_tte_usr');
define('DB_PASSWORD', 'S12wpp9YV[*3');
define('DB_DATABASE', 'pixelfla_tte');
require('app_data.php');
