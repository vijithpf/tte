<?php
set_time_limit(500);

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
            $_SERVER['HTTPS']='on';
}

require 'vendor/autoload.php';
require 'concrete/dispatcher.php';
