<?php

set_include_path(get_include_path() . ':' . __DIR__ . '/includes/');

//for ADB Commands
define('ADB', '/home/' . get_current_user() . '/Android/Sdk/platform-tools/adb');

require_once 'functions.php';
//none changeable values
define('confirmOrder', "100");
define('Ringing', "180");
define('answered', "200");
define('CallerHangupBeforAnswer', "487");
//changeapleValues
$GLOBALS['SIP_RESPONSES'] = array(
    'Answer' => 200,
    'TimeOut' => 408,
    'NOANSWER' => 408,
    'NoSipCall' => 409,
    'Gone' => 410,
    'WAOut' => 484,
    'InAnotherCall' => 486,
    'Reconnecting' => 500,
    'BusyUser' => 603,
);
require_once 'config.php';
require_once 'countries.php';
require_once 'timezones.php';
require_once 'pheanstalk/pheanstalk_init.php';
