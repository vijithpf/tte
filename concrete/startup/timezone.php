<?php
defined('C5_EXECUTE') or die('Access Denied.');
if (defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE_SERVER', APP_TIMEZONE);
    date_default_timezone_set(APP_TIMEZONE);
} else {
    date_default_timezone_set(@date_default_timezone_get());
}
