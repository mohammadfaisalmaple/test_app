#!/usr/bin/php -q
<?php

require_once 'common.php';
require_once 'Classes/auto.php';
require_once 'Classes/Checker.php';




myLog('Starting Saby..');
$safeWidthPixels = 10;
// ARGV's As Received from the Starting Script and Kept in CAPITAL for Marking
$PROFILE_AVD_NAME = $argv[1];
myLog('Base AVD Name : ' . $PROFILE_AVD_NAME);
sleep(2);
$ENV_HOST_TYPE = $argv[2];
myLog('Running Mode Type : ' . $ENV_HOST_TYPE);
sleep(2);
$ENV_HOST_NAME = $argv[3];
myLog('Running Mode Name : ' . $ENV_HOST_NAME);
sleep(2);
$ENV_HOST_API = $argv[4];
myLog('API Manager Url : ' . $ENV_HOST_API);
$ENV_HOST_API = 'http://' . $ENV_HOST_API;
myLog('API Manager Full Url : ' . $ENV_HOST_API);
sleep(2);
$ENV_HOST_ASTERISK = $argv[5];
myLog('Asterisk Manager Details : ' . $ENV_HOST_ASTERISK);
sleep(2);
$ENV_HOST_BEANSTALK = $argv[6];
myLog('Beanstalk Manager Url : ' . $ENV_HOST_BEANSTALK);
myLog('Beanstalk Manager Full Url : ' . $ENV_HOST_BEANSTALK);
sleep(2);
$EMULATOR_ALPHA = (strlen($argv[7]) != 1 ? 'A' : $argv[7]);
myLog('Saby Emulator Alpha : ' . $EMULATOR_ALPHA);
sleep(2);
$EMULATOR_ID = $argv[8];
myLog('Saby Emulator Suffix ID : ' . $EMULATOR_ID);
sleep(2);
$RUNNING_EMULATOR_PORT = $argv[9];
myLog('Saby Emulator Port : ' . $RUNNING_EMULATOR_PORT);
sleep(2);
$RUNNING_EMULATOR_X = $argv[10];
myLog('Saby Emulator X posision : ' . $RUNNING_EMULATOR_X);
sleep(2);
$RUNNING_EMULATOR_Y = $argv[11];
myLog('Saby Emulator Y posision : ' . $RUNNING_EMULATOR_Y);
sleep(2);
$RUNNING_EMULATOR_WIDTH = $argv[12] + $safeWidthPixels;
myLog('Saby Emulator Width : ' . $RUNNING_EMULATOR_WIDTH);
sleep(2);
$RUNNING_EMULATOR_HEIGHT = $argv[13];
myLog('Saby Emulator Height : ' . $RUNNING_EMULATOR_HEIGHT);
sleep(2);
$PROFILE_ANDROID = $argv[14];
myLog('Saby Emulator Android : ' . $PROFILE_ANDROID);
sleep(2);
$PROFILE_WA_TYPE = $argv[15];
myLog('Saby Emulator Application Short Name: ' . $PROFILE_WA_TYPE);
$PROFILE_WA_TYPE = "whatsapp";
myLog('Saby Emulator Application Full Name: ' . $PROFILE_WA_TYPE);


$sabyName = getSabyName();
myLog("Saby Name : " . $sabyName);
$sabyFullName = $sabyName . "-" . $EMULATOR_ALPHA;
myLog("Saby Full Name: " . $sabyFullName);

//$RUNNING_EMULATOR_X=0;
//$RUNNING_EMULATOR_Y=0;
//$RUNNING_EMULATOR_WIDTH=1240;
//$RUNNING_EMULATOR_HEIGHT=900;
//$RUNNING_EMULATOR_PORT=5544;
//$sabyFullName='test';
//$sabyName='test';

myLog("starting automation");
$autoStartTime = microtime(true);
$auto= new Automate($sabyName, $sabyFullName, $RUNNING_EMULATOR_PORT);
myLog('New Region : ' . $RUNNING_EMULATOR_X . ',' . $RUNNING_EMULATOR_Y . ',' . $RUNNING_EMULATOR_WIDTH . ',' . $RUNNING_EMULATOR_HEIGHT);
$auto->pythonUI->setDefaultRegion($RUNNING_EMULATOR_X, $RUNNING_EMULATOR_Y, $RUNNING_EMULATOR_WIDTH, $RUNNING_EMULATOR_HEIGHT);
myLog("Automate loading time: " . sprintf('%.2f', microtime(true) - $autoStartTime));


myLog("Install IP Location APP");
$auto->installIpLocationApp();

