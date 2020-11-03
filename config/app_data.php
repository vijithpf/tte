<?php
define('EMAIL_DEFAULT_FROM_ADDRESS', 'website@tte.ae');
define('EMAIL_DEFAULT_FROM_NAME', 'Technical & Trading');
define('FORM_BLOCK_SENDER_EMAIL', EMAIL_DEFAULT_FROM_ADDRESS);
define('ENABLE_MARKETPLACE_SUPPORT', false);
define('ENABLE_NEWSFLOW_OVERLAY', false);
define('URL_REWRITING_ALL', true);
define('PAGE_TITLE_FORMAT', '%2$s | %1$s');
define('APP_TIMEZONE', 'Asia/Dubai');
define('CLIENT_ADMIN_GROUP_NAME', 'Client');
define('PAGING_STRING', 'page');
define('TENTWENTY_ORG_URL', 'https://www.tentwenty.me/');
define('COMPRESS_ASSETS', false);
define('ASYNC_JS', false);

// Date Formats
define('DATE_APP_GENERIC_MDYT_FULL', 'd F Y \a\t g:i A');
define('DATE_APP_GENERIC_MDYT_FULL_SECONDS', 'd F Y \a\t g:i:s A');
define('DATE_APP_GENERIC_MDYT', 'j-n-Y \a\t g:i A');
define('DATE_APP_GENERIC_MDY', 'j-n-Y');
define('DATE_APP_GENERIC_MDY_FULL', 'j F Y');
define('DATE_APP_GENERIC_T', 'g:i A');
define('DATE_APP_GENERIC_TS', 'g:i:s A');
define('DATE_APP_DATE_PICKER', 'd-m-yy');

// URL Definition
if (!defined('BASE_URL')) { 
   if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) {
      define('BASE_URL_SSL', 'https://' . $_SERVER['HTTP_HOST']);
   } else {
      define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST']);
   }
}

// CDN URL Overide
$CDN_URL = 'https://static-tte.s3-accelerate.dualstack.amazonaws.com';
// define('REL_DIR_FILES_UPLOADED', $CDN_URL . '/files');
// define('ASSETS_URL', $CDN_URL);
// define('ASSETS_URL_CSS', $CDN_URL . '/concrete/css');
// define('ASSETS_URL_JAVASCRIPT', $CDN_URL . '/concrete/js');
// define('ASSETS_URL_IMAGES', $CDN_URL . '/concrete/images');