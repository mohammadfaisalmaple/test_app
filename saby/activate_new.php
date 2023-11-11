#!/usr/bin/php -q
<?php

require_once 'common.php';
require_once 'Classes/auto.php';
require_once 'Classes/Transporter.php';
require_once 'Classes/Checker.php';

$activationFailed = false;
myLog('Starting Saby Activation..');
$safeWidthPixels = 50;
// ARGV's As Received from the Starting Script and Kept in CAPITAL for Marking
$ACTIVATION_SOURCE = $argv[1];
myLog('AVD CREATED BY : ' . $ACTIVATION_SOURCE);

$ENV_HOST_API = $argv[2];
myLog('API Manager Url : ' . $ENV_HOST_API);
$ENV_HOST_API = 'http://' . $ENV_HOST_API;
myLog('API Manager Full Url : ' . $ENV_HOST_API);
$conf['api_server'] = $ENV_HOST_API;
$PROFILE_WA_TYPE    = $argv[3];
myLog('Saby Emulator Application Short Name: ' . $PROFILE_WA_TYPE);
$PROFILE_WA_TYPE = "whatsapp";
myLog('Saby Emulator Application Full Name: ' . $PROFILE_WA_TYPE);

$PROFILE_ANDROID = $argv[4];
myLog('Saby Emulator Android : ' . $PROFILE_ANDROID);

$PROFILE_AVD_NAME = $argv[5];
myLog('Base AVD Name : ' . $PROFILE_AVD_NAME);

$VPN_PROVIDER = $argv[6];
myLog('VPN APP : ' . $VPN_PROVIDER);

$VPN_REGION = $argv[7];
myLog('VPN TARGET REGION : ' . $VPN_REGION);

$SIM_PROVIDER = $argv[8];
myLog('SIM NUMBER SOURCE : ' . $SIM_PROVIDER);

$SIM_REGION = $argv[9];
myLog('SIM NUMBER COUNTRY ID : ' . $SIM_REGION);

if (!is_numeric($SIM_REGION) && ($SIM_PROVIDER == '5S' || $SIM_PROVIDER == 'SmsActivate')) {
    $SIM_REGION = getCountryByName($VPN_PROVIDER, $SIM_PROVIDER, $SIM_REGION);
    myLog('SIM NUMBER COUNTRY ID CORRECTION: ' . $SIM_REGION);
}

$ACTIVATION_TYPE = $argv[10];
myLog('THIS ACTIVATION SCENARO IS : ' . $ACTIVATION_TYPE);

$EMULATOR_ALPHA = $argv[11];
myLog('Saby Emulator Alpha : ' . $EMULATOR_ALPHA);

$EMULATOR_ID = $argv[12];
myLog('Saby Emulator Suffix ID : ' . $EMULATOR_ID);

$RUNNING_EMULATOR_PORT = 5544;
myLog('Saby Emulator Port : ' . $RUNNING_EMULATOR_PORT);

$RUNNING_EMULATOR_X = $argv[14];
myLog('Saby Emulator X posision : ' . $RUNNING_EMULATOR_X);

$RUNNING_EMULATOR_Y = $argv[15];
myLog('Saby Emulator Y posision : ' . $RUNNING_EMULATOR_Y);

$RUNNING_EMULATOR_WIDTH = $argv[16] + $safeWidthPixels;
myLog('Saby Emulator Width : ' . $RUNNING_EMULATOR_WIDTH);

$RUNNING_EMULATOR_HEIGHT = $argv[17];
myLog('Saby Emulator Height : ' . $RUNNING_EMULATOR_HEIGHT);
$ENV_IP = $argv[18];
myLog('M3allem IP : ' . $ENV_IP);

$sabyName = getSabyName();
myLog("Saby Name : " . $sabyName);
$sabyFullName = $sabyName . "-" . $EMULATOR_ALPHA;
myLog("Saby Full Name: " . $sabyFullName);

myLog("starting Checker");
$checkerStartTime = microtime(true);
$checker          = new Checker($RUNNING_EMULATOR_PORT, $PROFILE_WA_TYPE, $PROFILE_AVD_NAME, $EMULATOR_ID, $EMULATOR_ALPHA);
myLog("Checker loading time: " . sprintf('%.2f', microtime(true) - $checkerStartTime));
$androidID = $checker->getEmulatorAndroidID();

if (!$checker->AdbStartupPermissions()) {

    $activationFailed = true;
    myLog("error on permission");
}

myLog("starting automation");
$autoStartTime = microtime(true);
$auto= new Automate($sabyName, $sabyFullName, $RUNNING_EMULATOR_PORT);
myLog('New Region : ' . $RUNNING_EMULATOR_X . ',' . $RUNNING_EMULATOR_Y . ',' . $RUNNING_EMULATOR_WIDTH . ',' . $RUNNING_EMULATOR_HEIGHT);
$auto->pythonUI->setDefaultRegion($RUNNING_EMULATOR_X, $RUNNING_EMULATOR_Y, $RUNNING_EMULATOR_WIDTH, $RUNNING_EMULATOR_HEIGHT);
myLog("Automate loading time: " . sprintf('%.2f', microtime(true) - $autoStartTime));


$transporter = new Transporter_New_Ma3llem($sabyName, $sabyFullName, $RUNNING_EMULATOR_PORT, $androidID, $ENV_HOST_API, $ENV_IP);


$transporter->setAndroidID($androidID);
$VPN_PROVIDER="HTZ-express";
switch ($VPN_PROVIDER) {
        case "HTZ-express":
            $transporter->updateWireXpressVpnConfig($EMULATOR_ALPHA, 60, 2);
            $auto->ConnectWireGuard($PROFILE_ANDROID, 'New');
            break;
        default:
            $activationFailed = true;
}


myLog("Install IP Location APP");
$auto->installIpLocationApp();

