<?php

$uname = posix_uname(); //print_r($uname);exit;
define('IMG', __DIR__ . '/img/');
$conf = array();

$conf['debug'] = true;


$conf['ringingTimeAllowed'] = 5;
$conf['callingTimeAllowed'] = 5;

$conf['sendRingingCommandTime'] = 4;

$conf['viberStates'] = array(
	'NOT RUNNING', 'STARTING', 'RUNNING', 'FOCUSED', 'READY', 'INCOMING',
	'CALLING', 'CALL IN PROGRESS', 'NO DIAL', 'STALE', 'SLEEPING', 'STANDBY', 'KILLED', 'DEACTIVATED', 'CLEANUP'
);
$conf['callTempStates'] = array(
	'DIALED', 'RINGING', 'MIXING', 'HUNG UP', 'NO MIXING',
	'ANSWERING SIP', 'NO SIP CALL', 'SIP CALL ANSWERED'
);
$conf['callFinalStates'] = array('NO ANSWER', 'BUSY', /*'ANSWERED',*/ 'VIBER OUT', /*'FAILED', */ 'VIBER OFFLINE', 'VIBER NO AV', 'WABUSY', 'WABLOCKED', 'GONE', 'TEMPUNAVAIL');
/*
 * This is not a real timeout, this is just the time between 
 * synch calls, to give it a break and check if everything else
 * is still alright
 */
$conf['beanstalkReserveTimeout'] = 300;

$conf['homeDir'] = '';

$conf['controlCenterUrl'] = 'http://cc-api.7eet.net';
