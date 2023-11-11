<?php
$state = null;
function setState($newState, $callId = null, $extra1 = '')
{
    $startTime = microtime(true);

    global $state, $conf;

    if (in_array($newState, $conf['viberStates'], true) || in_array($newState, $conf['callTempStates'], true) || in_array($newState, $conf['callFinalStates'], true)) {
        $state = $newState;
        handleNewState($newState, $callId, $extra1);
        myLog("new state: $newState - " . sprintf('%.5f', microtime(true) - $startTime) . "s \n");
    } else {
        trigger_error("Setting undefined state '$newState' in newState()", E_USER_WARNING);
    }
}

function addSabyPingWatch($uuid, $sipExt)
{
    $url = $GLOBALS['conf']['moraselUrl'] . "/monitor/addSabyPingWatch/" . $sipExt . "/" . $uuid;
    httpGetAsync($url);
}

function confirmSabyPingWatch($uuid, $sipExt, $emulator_port)
{
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n 'com.android.chrome/org.chromium.chrome.browser.ChromeTabbedActivity -d \"http://116.202.81.199/Watcher/PingAPI.php?machine=" . urlencode($sipExt) . "&requestid=" . $uuid . "\"'");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_BACK");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am force-stop com.android.chrome");
    sleep(2);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am clear com.android.chrome");
}

function updateWA($emulator_port)
{
    updateSaby();
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " install -r " . __DIR__ . "/apk/WAORG.apk");
}

function rebootAVD($emulator_port)
{
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " reboot");
    if (!checkAVDIsBootCompleted($emulator_port)) {
        myLog("AVD is not booted");
        exit(2);
    }
    sleep(5);
    checkAVDIsRoot($emulator_port);
}

function isVPNConnected($saby, $emulator_port)
{
    checkAVDIsRoot($emulator_port);
    $vpnStatus = shell_exec('~/Android/Sdk/platform-tools/adb -s emulator-' . $emulator_port . ' shell dumpsys connectivity | grep "type: VPN"');
    $vpnConnceted = substr_count($vpnStatus, "type: VPN[], state: CONNECTED/CONNECTED") > 0;
    if ($vpnConnceted) {
        return true;
    } else {
        return false;
    }
}

function isWIFIConnected($emulator_port)
{
    checkAVDIsRoot($emulator_port);
    $wifiStatus = shell_exec('~/Android/Sdk/platform-tools/adb -s emulator-' . $emulator_port . ' shell dumpsys connectivity | grep "type: WIFI"');
    $WifiConnceted = substr_count($wifiStatus, "type: WIFI[], state: CONNECTED/CONNECTED") > 0;
    if ($WifiConnceted) {
        return true;
    } else {
        return false;
    }
}

function checkVpnType($emulator_port)
{
    $vpnTypes = array('com.surfshark.vpnclient.android', 'com.wireguard.android', 'com.expressvpn.vpn');
    $vpnTypeFound = '';
    for ($loop = 0; $loop < count($vpnTypes); $loop++) {
        $search = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell find /data/data/" . $vpnTypes[$loop] . " 2>&1 &");
        $NotFound = substr_count($search, "No such file or directory");
        myLog($vpnTypes[$loop] . " : " . $NotFound);
        if ($NotFound == 0) {
            $vpnTypeFound = $vpnTypes[$loop];
            break;
        }
    }
    return $vpnTypeFound;
}

function connectVPN($vpnType, $emulator_port)
{
    $mySabyName = getMySipExt();
    list($name, $vpn_region, $activation_timestamp) = getActivationDetials($mySabyName);
    switch ($vpnType) {
        case 'com.surfshark.vpnclient.android':
            exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start  -n com.surfshark.vpnclient.android/.app.feature.onboarding.OnboardingActivity");
            sleep(5);
            exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 400 1640");
            sleep(10);
            exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 1000 170");
            sleep(5);
            $random_vpn_region = getSurfSharkRandomRegion($vpn_region);
            $random_vpn_region = str_replace(" ", "\ ", $random_vpn_region);
            exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $random_vpn_region);
            sleep(10);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 400 500");
            sleep(5);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 875 1525");

            break;
        case 'com.wireguard.android':
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com.wireguard.android/.activity.MainActivity");
            sleep(5);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 910 380");
            sleep(5);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 870 1515");
            break;
        case 'com.expressvpn.vpn':
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com.expressvpn.vpn/.ui.home.HomeActivity");
            sleep(10);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 936 987");
            sleep(10);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 830 170");
            sleep(5);
            $random_vpn_region = getExpressVpnRandomRegion($vpn_region);
            exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $random_vpn_region);
            sleep(10);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 490 380");
            break;
    }
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_HOME");
    sleep(10);
    enableAlwaysVPN($emulator_port);
}

function uninstallVPN($emulator_port)
{
    checkAVDIsRoot($emulator_port);
    myLog('uninstall wireguard');
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " uninstall com.wireguard.android");
    myLog('uninstall expressvpn');
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " uninstall com.expressvpn.vpn");
    myLog("clean download directory");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell rm -rf /mnt/sdcard/Download/*");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " reboot");
    if (!checkAVDIsBootCompleted($emulator_port)) {
        myLog("AVD is not booted");
        exit(2);
    }
}

function registerAndInstallVPN($sipExt, $emulator_port, $android_ver, $vpn_region)
{
    $result = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell find /data/data/com.wireguard.android | wc -l");
    if (intval($result) > 0) {
        return false;
    }

    cleanAndInitSabyVpn($sipExt, $vpn_region);

    $vpnReady = false;
    $tryingCount = 0;
    do {
        sleep(5);
        $tryingCount += 1;
        myLog("Checking Vpn is Ready - try " . $tryingCount);
        $vpnReady = initSabyVpnAndCheckIsReady($sipExt, $vpn_region);
    } while (!$vpnReady && $tryingCount < 5);

    if ($vpnReady) {
        updateVpnConfig($sipExt, $vpn_region);
    }

    enableWireGuard($sipExt, $emulator_port, $android_ver, false);

    return true;
}

function enableWireGuard($sipExt, $emulator_port, $android_ver, $nav_download_folder = true)
{
    uninstallVPN($emulator_port);
    sleep(10);
    checkAVDIsRoot($emulator_port);
    sleep(5);
    myLog('install wireguard');
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " install -g  " . __DIR__ . "/apk/wireguard.apk");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " push ~/" . $sipExt . ".conf /mnt/sdcard/download/" . $sipExt . ".conf");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " reboot");
    if (!checkAVDIsBootCompleted($emulator_port)) {
        myLog("AVD is not booted");
        exit(2);
    }
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com.wireguard.android/.activity.MainActivity");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 930 1600");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 566 1301");
    sleep(5);
    if ($nav_download_folder) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 122 189");
        sleep(5);
        if ($android_ver === "29") {
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 500 750");
        } else {
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 339 563");
        }
        sleep(5);
    }
    if ($android_ver === "27") {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 363 636");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 910 380");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 870 1515");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com.android.settings/com.android.settings.Settings");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 270 195");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text VPN");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 340 430");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 390 1480");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 969 410");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 914 650");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 902 1009");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 800 1227");
    } elseif ($android_ver === "29" || $android_ver === "30") {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input press 300 1100");
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input press 300 1100");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 910 380");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 870 1515");
        sleep(10);
        enableAlwaysVPN($emulator_port);
    }
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_HOME");
}
function loginExpressVPN($emulator_port)
{
    echo "instaling VPN" . "\n";
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " install -g  " . __DIR__ . "/apk/expressvpn-7-9-8.apk");
    sleep(5);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start  -n com.expressvpn.vpn/.ui.user.WelcomeActivity");
    sleep(5);
    list($id, $username, $password) = getExpressVpnAccount();
    $vpnAccountIdFile = "/home/" . get_current_user() . "/Documents/VPN_ID.txt";
    file_put_contents($vpnAccountIdFile, $id);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 550 1616");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text '" . $username . "'");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 162 845");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text '" . $password . "'");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_BACK");
    //////////////////////////////////////////////////////
    usleep(500000);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 540 1150");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 550 1643");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 550 1450");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 866 1520");
    sleep(10);
    updateExpressVpnAccount($id, 'connect');
}

function enableExpressVPN($emulator_port, $vpn_region, $activation_reauest_type)
{
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com.expressvpn.vpn/.ui.home.HomeActivity");
    sleep(10);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 936 987");
    sleep(10);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 830 170");
    sleep(5);
    $random_vpn_region = getExpressVpnRandomRegion($vpn_region);
    $random_vpn_region = str_replace(" ", "\ ", $random_vpn_region);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $random_vpn_region);
    sleep(10);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 490 380");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_HOME");
    sleep(10);
    if ($activation_reauest_type != 'DEPLOY') {
        enableAlwaysVPN($emulator_port);
    }
}
function loginSurfShark($emulator_port)
{
    $username = 'm.othman@mapletele.com';
    $password = 'Moh.2023!@!';
    echo "instaling VPN" . "\n";
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " install -g  " . __DIR__ . "/apk/Surfshark.apk");
    sleep(5);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start  -n com.surfshark.vpnclient.android/.app.feature.onboarding.OnboardingActivity");
    sleep(5);
    ///////////////////ExpressVPN-Account/////////////////
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 540 1500");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 100 450");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text '" . $username . "'");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 100 720");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text '" . $password . "'");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 540 920");
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 925 175");
    sleep(5);
}

function enableSurfShark($emulator_port, $vpn_region)
{
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start  -n com.surfshark.vpnclient.android/.app.feature.onboarding.OnboardingActivity");
    sleep(5);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 400 1640");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 1000 170");
    sleep(5);
    if ($vpn_region != 'Unknown') {
        $random_vpn_region = getSurfSharkRandomRegion($vpn_region);
    } else {
        $random_vpn_region = 'USA';
    }
    $random_vpn_region = str_replace(" ", "\ ", $random_vpn_region);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $random_vpn_region);
    sleep(10);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 400 500");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 875 1525");
    sleep(5);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_HOME");
    sleep(10);
    enableAlwaysVPN($emulator_port);
}

function enableAlwaysVPN($emulator_port)
{
    exec(" ~/Android/Sdk/platform-tools/adb  -s emulator-" . $emulator_port . " shell am start -n com.android.settings/com.android.settings.Settings");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 270 195");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text VPN");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 390 430");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 422 1660");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 422 1660");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 989 420");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 911 650");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 911 970");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 830 1160");
    sleep(10);
    exec(" ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_HOME");
}

function getState()
{
    global $state;
    return $state;
}

function sabySleep($seconds)
{
    while ($seconds > 0) {
        myLog("----------------SLEEPING------------------");
        myLog("inside sabySLeep() ");
        myLog("sleeping for " . $seconds . " seconds");
        myLog("----------------SLEEPING------------------");
        sleep(1);
        $seconds--;
    }
}

function handleNewState($state, $callId = null, $extra1 = '')
{
    static $mySipExtension, $myCodeRevision;
    if (!isset($mySipExtension)) {
        $mySipExtension = getMySipExt();
    }

    //special case to start ringing on asterisk
    /*
    if ($state == 'RINGING') {
    //store time took to get to a dial tone
    reportCallTimer($callId, 'dial_time', $extra1);
    }

    if ($state == 'BUSY') {
    reportCallTimer($callId, 'busy_time', $extra1);
    }

     */
    switch ($state) {

            /*
         * Viber states
         */
        case 'NOT RUNNING':
        case 'STARTING':
        case 'RUNNING':
            //case 'FOCUSED':
        case 'READY':
        case 'INCOMING':
        case 'SLEEPING':
        case 'STANDBY':
        case 'KILLED':
        case 'DEACTIVATED':
        case 'CLEANUP':
            sendSabyState($mySipExtension, $state);
            break;

            /*
         * Special combined state
         */
        case 'CALLING':
            sendSabyState($mySipExtension, $state);
            reportCallPicked($mySipExtension, $callId);
            break;
            /*
         * Call temp states
         */
        case 'RINGING':
            myLog("[callid: $callId] [$state] [$extra1]");
            sendSabyState($mySipExtension, $state);
            break;
        case 'DIALED':
        case 'MIXING':
        case 'ANSWERING SIP':
        case 'SIP CALL ANSWERED':
            myLog("[callid: $callId] [$state] [$extra1]");
            break;

            /*
         * Call states
         */

            //call answered
        case 'CALL IN PROGRESS':
            setCallAnswered($callId);
            sendSabyState($mySipExtension, $state);
            break;

            //call failed
        case 'NO DIAL':
        case 'STALE':
        case 'NO MIXING':
        case 'NO SIP CALL':
        case 'VIBER NO AV':
            setCallFinalState($callId, 'FAILED', $state, $extra1);
            break;

        case 'VIBER OUT':
            //Let asterisk know the call can't be made
            $sipJob = new stdClass();
            $sipJob->data = array(
                'cmd' => 'hangup',
                'reason' => "OUT",
            );
            $beanstalk = getBeanstalk();
            $beanstalk->useTube($callId)
                ->put(json_encode($sipJob));

            setCallFinalState($callId, $state);
            break;

        case 'BUSY':
            //Let asterisk know the call can't be made
            $sipJob = new stdClass();
            $sipJob->data = array(
                'cmd' => 'hangup',
                'reason' => "BUSY",
            );
            $beanstalk = getBeanstalk();
            $beanstalk->useTube($callId)
                ->put(json_encode($sipJob));

            setCallFinalState($callId, $state);
            break;
        case 'WABUSY':
            //Let asterisk know the call can't be made
            $sipJob = new stdClass();
            $sipJob->data = array(
                'cmd' => 'hangup',
                'reason' => "WABUSY",
            );
            $beanstalk = getBeanstalk();
            $beanstalk->useTube($callId)
                ->put(json_encode($sipJob));

            setCallFinalState($callId, $state);
            break;

        case 'WABLOCKED':
            //Let asterisk know the call can't be made
            $sipJob = new stdClass();
            $sipJob->data = array(
                'cmd' => 'hangup',
                'reason' => "WABLOCKED",
            );
            $beanstalk = getBeanstalk();
            $beanstalk->useTube($callId)
                ->put(json_encode($sipJob));

            setCallFinalState($callId, $state);
            break;

        case 'GONE':
            $sipJob = new stdClass();
            $sipJob->data = array(
                'cmd' => 'hangup',
                'reason' => "GONE",
            );
            $beanstalk = getBeanstalk();
            $beanstalk->useTube($callId)->put(json_encode($sipJob));

            setCallFinalState($callId, $state);
            break;

        case 'TEMPUNAVAIL':
            $sipJob = new stdClass();
            $sipJob->data = array(
                'cmd' => 'hangup',
                'reason' => "TEMPUNAVAIL",
            );
            $beanstalk = getBeanstalk();
            $beanstalk->useTube($callId)->put(json_encode($sipJob));

            setCallFinalState($callId, $state);
            break;

        case 'VIBER OFFLINE':
        case 'NO ANSWER':
            //Let asterisk know the call can't be made
            $sipJob = new stdClass();
            $sipJob->data = array(
                'cmd' => 'hangup',
                'reason' => "NOANSWER",
            );
            $beanstalk = getBeanstalk();
            $beanstalk->useTube($callId)
                ->put(json_encode($sipJob));

            setCallFinalState($callId, $state);
            break;

            /*
         * Extra info
         */
        case 'HUNG UP':
            myLog("[callid: $callId] [$state] [$extra1]");
            break;
    }
}
function sendRingingCommand($callId)
{
    //Let asterisk know the call can't be made
    myLog("inside sendRingingCommand()");
    myLog("going to send to asterisk to route call");

    $sipJob = new stdClass();
    $sipJob->data = array(
        'cmd' => 'ring',
    );
    $beanstalk = getBeanstalk();
    myLog("sendRingingCommand(): got bs now will send command");
    $beanstalk->useTube($callId)->put(json_encode($sipJob));
    myLog("sendRingingCommand(): pushed command to bs");
}

function registerWithApiServer()
{
    myLog("inside registerWithApiServer()");
    $mySipExtension = getMySipExt();
    $rev = getMySvnRev();
    //$viberNumber = getLocalViberNumber();
    $viberNumber = "0";
    $internalIp = getIp();

    $url = $GLOBALS['conf']['api_server'] . "/registerSaby/" . urlencode($mySipExtension) . '/'
        . urlencode($viberNumber) . '/' . $rev . '/' . urlencode($internalIp);
    myLog("registerWithApiServer() URL: " . $url);
    httpGetAsync($url);

    //if (!$viberNumber) {
    //    setState('DEACTIVATED');
    //    exit;
    //}
}

function setActivationDateTime()
{
    myLog("inside registerWithApiServer()");
    $mySabyName = getMySipExt();
    list($name, $vpn_region, $activation_timestamp, $vpn_provider, $sim_number, $activation_type) = getActivationDetials($mySabyName);

    $url = $GLOBALS['conf']['api_server'] . "/setSabyActivationDate/" . urlencode($mySabyName) . '/'
        . urlencode($activation_timestamp) . '/' . $vpn_region . '/' . $vpn_provider . '/' . $sim_number . '/' . $activation_type;
    myLog("registerWithApiServer() URL: " . $url);
    httpGetAsync($url);
}

function httpGetAsync($url)
{
    $urlEscaped = str_replace(array('?', '&', ' '), array('\\?', '\\&', '%20'), $url);
    $cmd = 'wget -O /dev/null -o /dev/null -qb --no-cache ' . $urlEscaped;
    myLog("executing: " . $cmd . PHP_EOL);
    exec($cmd);
}
/*
function whatsappDialNumber($number, $qemu_token) {
myLog( "inside whatsappDialNumber() number: " . $number);
myLog( "inside whatsappDialNumber() token: " . $qemu_token);
myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));$ch = curl_init();
$dial_url="http://195.201.8.183/test.php?mobile_number=$number&host_name=mohamed&token=$qemu_token&type=1";
myLog( "dial_url : " . $dial_url);
curl_setopt($ch, CURLOPT_URL, "$dial_url");
myLog( "dialing: " . $number);
$output = curl_exec($ch);
curl_close($ch);
}
 */

function whatsappDialNumber($number, $qemu_token, $host = "act")
{
    myLog("inside whatsappDialNumber() number: " . $number);
    myLog("inside whatsappDialNumber() token: " . $qemu_token);
    $url = "http://195.201.8.183/test.php?mobile_number=$number&host_name=$host&token=$qemu_token&type=1";
    myLog("url : " . $url);
    httpGetAsync($url);
}

function getToken($host)
{
    myLog("getToken: " . $host);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['api_server'] . "/getToken/" . $host;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    return $obj->{'token'};
    curl_close($ch);
}

function setSabyActivationRecord($activationRecord)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GLOBALS['conf']['api_server'] . "/setSabyActivationRecord");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($activationRecord));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_exec($ch);
    curl_close($ch);
    myLog("Saby Activation Record: " . json_encode($activationRecord));
}

function updateSabyActivationRecord($androidid, $finalstatus)
{
    $url = $GLOBALS['conf']['api_server'] . "/updateSabyActivationRecord/" . $androidid . '/' . $finalstatus;
    httpGetAsync($url);
}

function getConfig($config)
{
    myLog("getConfig: " . $config);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['api_server'] . "/getConfig/" . $config;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    curl_close($ch);
    return $obj->{'value'};
}

function getCallLimit($sipext)
{
    myLog("getCallLimit: " . $sipext);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['api_server'] . "/getSabyCallLimit/" . $sipext;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    curl_close($ch);
    return $obj->{'call_limit'};
}

function getQueuePort($sipext)
{
    myLog("getQPort for: " . $sipext);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['api_server'] . "/getSabyQueuePort/" . $sipext;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    curl_close($ch);
    return ($obj->{'queue_port'});
}

function getSleepType($sipext)
{
    myLog("getSleepType for: " . $sipext);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['api_server'] . "/getSleepType/" . $sipext;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    curl_close($ch);
    return ($obj->{'sleep_type'});
}

function cleanAndInitSabyVpn($saby, $vpn_region)
{
    destroySabyVpn($saby);
    return initSabyVpnAndCheckIsReady($saby, $vpn_region);
}

function destroySabyVpn($saby)
{
    file_get_contents("http://cc-api.7eet.net/destroySabyVpnActivation/" . $saby);
}

function initSabyVpnAndCheckIsReady($saby, $vpn_region)
{
    myLog("init or check Saby Vpn for: " . $saby . " on vpn region" . $vpn_region);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = "http://cc-api.7eet.net/initSabyVpnActivation/" . $saby . "/" . $vpn_region;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    if (substr_count($output, "error") > 0) {
        sendSabyState($saby, 'VPN ERROR');
        exit(2);
    }
    curl_close($ch);
    return ($obj->{'vpn_status'}) === "ready";
}

function updateVpnConfig($saby, $vpn_region)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $vpnConfig = file_get_contents("http://cc-api.7eet.net/getSabyVpnActivationConfig/" . $saby . "/" . $vpn_region);
    if (substr_count($vpnConfig, "error") == 0) {
        $configPath = "/home/" . get_current_user() . "/" . $saby . ".conf";
        file_put_contents($configPath, $vpnConfig, LOCK_EX);
        return true;
    }
    return false;
}

function isValidateEnabled($sipext)
{
    myLog("is Validate Enabled for: " . $sipext);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['api_server'] . "/isValidateEnabled/" . $sipext;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    curl_close($ch);
    return ($obj->{'enable_validate'});
}

function reportCallPicked($mySipExtension, $callid)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $url = $GLOBALS['conf']['api_server'] . "/reportCallPickedBySaby/" . urlencode($mySipExtension) . '/' . urlencode($callid);
    httpGetAsync($url);
}

function reportCallEnd($callId, $callTime)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $url = $GLOBALS['conf']['api_server'] . "/reportCallEnd/" . urlencode($callId) . '/' . urlencode($callTime);
    httpGetAsync($url);
}

function reportCallTimer($callId, $which, $time)
{
    $url = $GLOBALS['conf']['api_server'] . "/reportCallTimer/" . urlencode($callId) . '/' . urlencode($which) . '/' . urlencode($time);
    httpGetAsync($url);
}

function setCallAnswered($callId)
{
    $url = $GLOBALS['conf']['api_server'] . "/setCallAnswered/" . urlencode($callId);
    httpGetAsync($url);
}

function isViberGettingCall($auto)
{
    myLog("Inside isViberGettingCall()");
    $ViberRingingIndex = getViberRingingIndex();
    myLog('Is someone ringing in, ViberRingingIndex: ' . $ViberRingingIndex);
    if ("0" != $ViberRingingIndex) {
        myLog('Someone is ringing in! ViberRingingIndex: ' . $ViberRingingIndex);
        $auto->hangUpIncoming();
        usleep(100000); //.1 second
    }
}

function getRandomVpnRegion()
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getRandomVpnRegion");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getRandomVpnRegion: " . $output);
    $obj = json_decode($output);
    return $obj->{'region_code'};
}

function getRandom5SimCountry($vpn_provider)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getRandom5SimCountry/" . $vpn_provider . '/whatsapp');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getRandom5SimCountry: " . $output);
    $obj = json_decode($output);
    //$vpn_region = $obj->{'Country'};
    //$sim_country = $obj->{'five_sim_id'};
    return array($obj->{'Country'}, $obj->{'five_sim_id'});
}

function getExpressVpnAccount()
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getExpressVpnAccount");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getExpressVpnAccount: " . $output);
    $obj = json_decode($output);
    return array($obj->{'id'}, $obj->{'username'}, $obj->{'password'});
}

function getSipResponses($ENV_IP)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $ENV_IP . "/MapleVoipFilter/apiManager/getSabySipResponse");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog(__FUNCTION__ . " Output: " . $output);
    $obj = json_decode($output);
    return $obj;
}

function getExpressVpnRandomRegion($vpn_region)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getExpressVpnRandomRegion/" . $vpn_region);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getExpressVpnRandomRegion: " . $output);
    $obj = json_decode($output);
    return str_replace('_', '"\ "', $obj->{'region'});
}

function getSurfSharkRandomRegion($vpn_region)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getSurfSharkRandomRegion/" . $vpn_region);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getSurfSharkRandomRegion: " . $output);
    $obj = json_decode($output);
    return $obj->{'region'};
}

function getPiaRandomRegion($vpn_region)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    list($country, $location) = explode(' ', $vpn_region);
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getPiaRandomRegion/" . urlencode($country));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getPiaRandomRegion: " . $output);
    $obj = json_decode($output);
    return $obj->{'region'};
}

function getHotSpotRandomRegion($vpn_region)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getHotSpotRandomRegion/" . $vpn_region);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getHotSpotRandomRegion: " . $output);
    $obj = json_decode($output);
    return $obj->{'region'};
}

function getStrongRandomRegion($vpn_region)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getStrongRandomRegion/" . $vpn_region);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getStrongRandomRegion: " . $output);
    $obj = json_decode($output);
    return $obj->{'region'};
}

function getPureVpnRandomRegion($vpn_region)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getPureVpnRandomRegion/" . $vpn_region);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getPureVpnRandomRegion: " . $output);
    $obj = json_decode($output);
    return $obj->{'region'};
}

function updateExpressVpnAccount($id, $action)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/updateExpressVpnAccount/" . $id . "/" . $action);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
}

function getCountryById($provider, $country_id)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getCountryByProviderAndId/" . $provider . "/" . $country_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getCountryById: " . $output);
    $obj = json_decode($output);
    return $obj;
}

function getCountryByName($vpnProvider, $simProvider, $country)
{
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getCountryByVpnProviderAndName/" . $vpnProvider . "/" . $simProvider . "/" . $country);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getCountryByName: " . $output);
    $obj = json_decode($output);
    return $obj->{'five_sim_id'};
}

function getCountryByVPNId($country_id)
{
    $country = new stdClass();
    $country->{'country_id'} = $country_id;
    return $country;
}

function getRandomProviderSimCountry($provider, $excludeCountry, $try)
{
    if ($excludeCountry == null || empty($excludeCountry) || trim($excludeCountry) === false) {
        $excludeCountry = "default";
    }
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GLOBALS['conf']['api_server'] . "/getRandomProviderSimCountry/" . urlencode($provider) . '/' . urlencode($excludeCountry) . '/' . $try);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("getRandomProviderSimCountry: " . $output);
    $obj = json_decode($output);
    return $obj;
}

function checkPhonenumberIsValid($provider, $countryid, $phonenumber)
{
    if ($phonenumber == null || empty($phonenumber) || trim($phonenumber) === false) {
        $phonenumber = "default";
    }
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GLOBALS['conf']['api_server'] . "/checkPhonenumberIsValid/" . urlencode($provider) . '/' . urlencode($countryid) . '/' . $phonenumber);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    myLog("checkPhonenumberIsValid: " . $output);
    $obj = json_decode($output);
    return $obj->result;
}

function sendSabyState($mySipExtension, $state)
{
    $url = $GLOBALS['conf']['api_server'] . "/setSabyState/" . urlencode($mySipExtension) . '/' . urlencode($state);
    httpGetAsync($url);
}

function setSabyEmulatorState($mySipExtension, $state)
{
    $url = $GLOBALS['conf']['api_server'] . "/setSabyEmulatorState/" . urlencode($mySipExtension) . '/' . urlencode($state);
    httpGetAsync($url);
}

function switchSabyMode($mySipExtension, $portMode, $vpn, $vpnProvider)
{
    $url = $GLOBALS['conf']['api_server'] . "/sabyCmd/" . urlencode($mySipExtension) . '/' . urlencode($portMode) . '/' . $vpnProvider . '/' . $vpn . '/default/default';
    httpGetAsync($url);
}

function startMainSaby($mySipExtension, $vpn, $vpnProvider)
{
    $url = $GLOBALS['conf']['api_server'] . "/sabyCmd/" . urlencode($mySipExtension) . '/main_start/' . $vpnProvider . '/' . $vpn . '/default/default';
    httpGetAsync($url);
}

function startSaby($mySipExtension, $vpn, $vpnProvider)
{
    $url = $GLOBALS['conf']['api_server'] . "/sabyCmd/" . urlencode($mySipExtension) . '/start/' . $vpnProvider . '/' . $vpn . '/default/default';
    httpGetAsync($url);
}

function moveSaby($mySipExtension, $cmd, $vpn, $vpnProvider)
{
    $url = $GLOBALS['conf']['api_server'] . "/sabyCmd/" . urlencode($mySipExtension) . '/final_' . $cmd . '/' . $vpnProvider . '/' . $vpn . '/default/default';
    httpGetAsync($url);
}

function standbySaby($mySipExtension, $vpn, $vpnProvider)
{
    $url = $GLOBALS['conf']['api_server'] . "/sabyCmd/" . urlencode($mySipExtension) . '/standby/' . $vpnProvider . '/' . $vpn . '/default/default';
    httpGetAsync($url);
}

function setCallFinalState($callId, $state, $stateMore = '', $extra = '')
{
    $url = $GLOBALS['conf']['api_server'] . "/setCallFinalState/"
        . urlencode($callId) . '/' . urlencode($state);
    if ($stateMore) {
        $url .= '/' . urlencode($stateMore);
    }

    if ($extra) {
        $url .= '/' . urlencode($extra);
    }

    httpGetAsync($url);
}

function killViber()
{
    exec("killall -9 Viber");
}

function isAnotherMainRunning($mainSaby = 'main7.php')
{
    $cmd = 'pgrep -u ' . get_current_user() . " " . $mainSaby;
    $pids = shell_exec($cmd);
    $num = count(explode(PHP_EOL, $pids));
    if ($num > 2) {
        return true;
    }
    return false;
}

function sabyHasInternet($saby)
{
    $url = $GLOBALS['conf']['api_server'] . "/getInternetStatus/" . $saby;
    exec('~/7eet-saby-whatsapp-ubuntu20/scripts/check_internet.sh');
    return intval(file_get_contents($url)) == 1;
}

function sabyHasInternet2($saby, $emulatorPort)
{
    $pingCMD = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell ping -c 1 www.google.com");
    $pingResult = substr_count($pingCMD, "1 packets transmitted, 1 received, 0% packet loss") > 0;
    if ($pingResult) {
        return true;
    }
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell am force-stop org.chromium.webview_shell");
    $url = array('http://www.google.com', 'http://www.whatsapp.com', 'https://www.microsoft.com', 'https://www.yandex.com');
    $internet = true;
    for ($loop = 0; $loop <= 3; $loop++) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url[$loop] . "'");
        sleep(10);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url[$loop] . "'");
        sleep(10);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url[$loop] . "'");
        sleep(10);
        $opened = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell uiautomator dump && ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell cat /sdcard/window_dump.xml");
        $notFoundString = substr_count($opened, "Webpage not available");
        myLog("Internet Status : " . $notFoundString);
        if ($notFoundString > 0) {
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell am force-stop org.chromium.webview_shell");
            $internet = false;
            myLog("Internet Status : " . $internet);
        } else {
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell am force-stop org.chromium.webview_shell");
            $internet = true;
        }
    }
    myLog("Final Internet Status : " . $internet);
    return $internet;
}

function getAVDPort()
{
    $emulatorPort = trim(shell_exec('~/7eet-saby-whatsapp-ubuntu20/scripts/get_avd_port.sh'));
    return $emulatorPort;
}

function getActiveEmulatorDetails()
{
    $emulatorDetails = shell_exec('~/7eet-saby-whatsapp-ubuntu20/scripts/get_avd_port_name.sh');
    return $emulatorDetails;
}

function deployAVD()
{
    $cmd = '~/7eet-saby-whatsapp-ubuntu20/scripts/avd_deploy.sh';
    return substr(shell_exec($cmd), 0, 4);
}

/*
function checkIsAnotherMainRunning(){
foreach(scandir($x) as $file) if ($file selection) {...}
}
 */

function startAVD()
{
    exec('nohup ~/7eet-saby-whatsapp-ubuntu20/scripts/avd_start.sh & ');
}

function stopAVD()
{
    exec('nohup ~/7eet-saby-whatsapp-ubuntu20/scripts/avd_stop.sh &');
}

function stopLinphone()
{
    myLog("inside stopLinphone()");
    exec('linphonecsh exit');
    exec('pkill -u ${USER} -9 linphonec.orig');
}

function stopPulseAudio()
{
    myLog("inside stopPulseAudio()");
    exec('killall -u${USER} pulseaudio');
}

function countPulseAudio()
{
    myLog("inside countPulseAudio()");
    $output = shell_exec('pgrep -u${USER} pulseaudio|wc -l');
    return $output;
}

function isPulseAudioOK()
{
    myLog("inside isPulseAudioOK()");
    $output = shell_exec('pactl list|wc -l');
    myLog("isPulseAudioOK() list: " . $output);
    if ($output <= 1) {
        return false;
    }
    return true;
}

function updateSaby()
{
    exec('nohup ~/7eet-saby-whatsapp-ubuntu20/scripts/saby_update.sh & ');
}

function isSipInCall()
{
    $output = shell_exec('linphonecsh generic calls');
    return (strstr($output, 'sip') && strstr($output, 'StreamsRunning'));
}

function hangupSipCall()
{
    myLog("inside hangupSipCall()");
    shell_exec('linphonecsh generic terminate');
}

function getViberRecordingIndex()
{
    $cmd = 'pactl list | grep -B 30 "module-stream-restore.id = \"source-output-by-application-name:Chrome input\""|grep "Source Output"|sed "s/Source Output #//"';
    if (!$cmd) {
        $cmd = 'pactl list | grep -B 30 "module-stream-restore.id = \"source-output-by-application-name:ALSA plug-in \[Viber\]\""|grep "Source Output"|sed "s/Source Output #//"';
    }
    return (int) shell_exec($cmd);
}

function getViberPlaybackIndex()
{
    $cmd = 'pactl list |grep -B 30 "module-stream-restore.id = \"sink-input-by-application-name:ViberPC\""|grep "Sink Input"|sed "s/Sink Input #//"';
    if (!$cmd) {
        $cmd = 'pactl list |grep -B 30 "module-stream-restore.id = \"sink-input-by-application-name:ALSA plug-in [Viber]\""|grep "Sink Input"|sed "s/Sink Input #//"';
    }
    return (int) shell_exec($cmd);
}

function getViberRingingIndex()
{
    $cmd = 'pactl list |grep -B 30 "module-stream-restore.id = \"sink-input-by-application-name:QtPulseAudio:.*\""|grep "Sink Input"|sed "s/Sink Input #//"';
    return (int) shell_exec($cmd);
}

function putSipInReadyState()
{
    myLog("inside putSipInReadyState()");
    $cmd = realpath(__DIR__ . '/../../scripts') . '/sipme.sh';
    $output = shell_exec("$cmd 2>&1");
    myLog($output);
    $findme = "identity=sip";
    $pos = strpos($output, $findme);
    if ($pos == false) {
        myLog("error. sip registstration failed, must exit");
        return false;
    } else {
        // sip registration worked - found the string identity=sip in the output,
        myLog("sip registstration worked, no errors");
        return true;
    }
}

function startAudioMix()
{
    $cmd = realpath(__DIR__ . '/../../scripts') . '/audio_mix_sip.sh';
    $output = shell_exec("$cmd 2>&1");

    if (strstr($output, 'You have to specify a source')) {
        return false;
    }
    myLog("AUDIO MIX IS FUCKIN WORKIN BABY! \n\n");
    return true;
}

function startPlaybackMix()
{
    $cmd = realpath(__DIR__ . '/../../scripts') . '/audio_mix_ffplay.sh';
    $output = shell_exec("$cmd 2>&1");

    if (strstr($output, 'You have to specify a source')) {
        return false;
    }
    myLog("AUDIO MIX IS FUCKIN WORKIN BABY! \n\n");
    return true;
}

function getAVDMainWid($pid)
{
    exec("xdotool search --pid $pid 2>&1", $wids);
    foreach ($wids as $wid) {
        $wid = (int) $wid;
        if ($wid) {
            $windowName = shell_exec("xdotool getwindowname $wid 2>&1");
            if (isAVDWindow($windowName)) {
                return $wid;
            }
        }
    }
}

function setAVDLocation($wid)
{
    $output = shell_exec("xdotool windowmove $wid 0 0 2>&1");
    if (strstr($output, 'failed')) {
        return false;
    }
    return true;
}

function activateWindow($wid)
{
    $output = shell_exec("xdotool windowactivate $wid 2>&1");
    if (strstr($output, 'failed')) {
        return false;
    }
    return true;
}

function getViberProcessState()
{
    $output = shell_exec('ps xo stat,command | grep -i "/usr/share/viber/[V]iber$" | awk \'{print $1}\' ');
    return trim($output);
}

function viberPid()
{
    exec("pgrep -x Viber", $pids);

    if (!empty($pids)) {
        return trim($pids[0]);
    }
    return false;
}

function getAVDPid()
{
    exec("pgrep -u " . get_current_user() . " -x qemu-system-x86", $pids);

    if (!empty($pids)) {
        return trim($pids[0]);
    }
    return false;
}

function checkAVDIsRoot($emulatorPort)
{
    $rooted = "";
    $tryingCount = 0;
    do {
        sleep(2);
        myLog("Checking if AVD is rooted Try " . $tryingCount);
        $rooted = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " root");
        myLog("rooted :" . $rooted);
    } while (!(substr_count($rooted, "adbd is already running as root") > 0) && $tryingCount < 100);
}

function checkAVDIsBootCompleted($emulatorPort)
{
    checkAVDIsRoot($emulatorPort);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " wait-for-device");

    $tryingCount = 0;
    do {
        $tryingCount++;
        myLog("Checking if AVD is ready and booted Try " . $tryingCount);
        $boot_completed = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell getprop sys.boot_completed");
        myLog("boot_completed :" . $boot_completed);
        sleep(2);
    } while (!(substr_count($boot_completed, "1") > 0) && $tryingCount < 100);

    $result = $tryingCount < 100;
    if ($result) {
        sleep(10);
    }

    checkAVDIsRoot($emulatorPort);

    return $result;
}

function isViberActive()
{
    $windowName = shell_exec("xdotool getwindowname `xdotool getactivewindow` 2>&1");
    if (isViberWindow($windowName)) {
        if ('FOCUSED' != getState()) {
            setState('FOCUSED');
        }

        return true;
    }

    return false;
}

function isViberWindow($windowName)
{
    if (strstr($windowName, 'Viber') && strstr($windowName, '+')) {
        return true;
    }

    return false;
}

function isAVDActive()
{
    $windowName = shell_exec("xdotool getwindowname `xdotool getactivewindow` 2>&1");
    if (isViberWindow($windowName)) {
        if ('FOCUSED' != getState()) {
            setState('FOCUSED');
        }

        return true;
    }

    return false;
}

function isAVDWindow($windowName)
{
    if (strstr($windowName, 'Android Emulator')) {
        return true;
    }

    return false;
}

function writeCallFile($data)
{
    $file = $GLOBALS['conf']['callFile'];
    return file_put_contents($file, json_encode($data));
}

function getLocalViberNumber()
{
    $matches = glob($GLOBALS['conf']['viberDir'] . '[0-9]*');
    foreach ($matches as $match) {
        if (strlen($match) > 4) {
            return trim(basename($match));
        }
    }

    trigger_error("Couldn't find viber directory for an active number");
    return 0;
}

function getCurrentViberCallId($number, $lastId)
{
    $db = getViberDb();
    $sql = "SELECT EventID FROM EventInfo
			WHERE Direction=1 AND EventType=1 AND Number=\"$number\" ";
    if ($lastId) {
        $sql .= " AND EventID > $lastId ";
    }

    $sql .= " ORDER BY TimeStamp DESC LIMIT 1";

    $result = $db->querySingle($sql);

    return $result;
}

function getLatestViberCallId()
{
    $db = getViberDb();

    $sql = "SELECT Max(EventID) EventID FROM EventInfo
                       WHERE Direction=1 AND EventType=1 ";

    $result = $db->querySingle($sql);
    return $result;
}

function getViberCdrById($viberCallId)
{
    $db = getViberDb();
    $sql = "SELECT * FROM EventInfo WHERE EventId=\"$viberCallId\" LIMIT 1";
    return $db->querySingle($sql, true);
}

function isViberReceivingCall()
{
    $db = getViberDb();
    $sql = "SELECT count(*) as cnt FROM EventInfo
			WHERE Direction=0 AND EventType=1 AND CallStatus=6
				AND (strftime('%s', 'now') - TimeStamp < 300)";

    return (bool) $db->querySingle($sql);
}

function getLastCallIdByNumber($number)
{
    $db = getViberDb();
    $sql = "SELECT EventID FROM EventInfo
			WHERE Direction=1 AND EventType=1 AND Number=\"$number\"
			ORDER BY TimeStamp DESC
			LIMIT 1";

    return $db->querySingle($sql);
}

function getViberCDRByNumber($number)
{

    $number = '+' . $number;

    $db = getViberDb();
    $sql = "SELECT * FROM EventInfo
			WHERE Direction=1 AND EventType=1 AND Number=\"$number\"
				AND (strftime('%s', 'now') - TimeStamp < 10800)
			ORDER BY TimeStamp DESC
			LIMIT 1";

    return $db->querySingle($sql, true);
}

function getViberDb()
{
    global $viberDb;
    if (!$viberDb) {
        $viberDb = new SQLite3(getViberDbPath());
        if (!$viberDb) {
            trigger_error("Could not open viber DB");
        }

        $viberDb->busyTimeout(500);
    }

    return $viberDb;
}

function getBeanstalk($newInstance = false)
{
    global $bs, $conf, $bsQueuePort;
    myLog("inside getBeanstalk()");
    myLog("going to connect to beanstalk server:" . $conf['beanstalk_server']);
    myLog("going to connect to beanstalk port:" . $bsQueuePort);
    if (!$bs && !$newInstance) {
        $bs = new Pheanstalk_Pheanstalk($conf['beanstalk_server'], $bsQueuePort);
    } else {
        return (new Pheanstalk_Pheanstalk($conf['beanstalk_server'], $bsQueuePort));
    }

    return $bs;
}

function getBeanstalkHup($newInstance = false)
{
    global $bshup, $conf, $bsHupQueuePort;
    myLog("inside getBeanstalkHup()");
    myLog("going to connect to beanstalk server:" . $conf['beanstalk_server']);
    myLog("going to connect to beanstalk port:" . $bsHupQueuePort);
    if (!$bshup && !$newInstance) {
        $bshup = new Pheanstalk_Pheanstalk($conf['beanstalk_server'], $bsHupQueuePort);
    } else {
        return (new Pheanstalk_Pheanstalk($conf['beanstalk_server'], $bsHupQueuePort));
    }

    return $bshup;
}

function getViberDbPath()
{
    return $GLOBALS['conf']['viberDir'] . getLocalViberNumber() . '/viber.db';
}

function answerSipCall()
{

    $sleep = 200000; //0.2 second
    $tries = 0;
    while ($tries++ < 15) {
        shell_exec('linphonecsh generic "answer"');
        if (isSipInCall()) {
            return true;
        }

        usleep($sleep);
    }

    return false;
}

function getSabyName()
{
    $USER = get_current_user();
    return $USER . "-" . trim(gethostname());
}

function getMySipExt()
{
    $USER = get_current_user();
    return $USER . "-" . trim(gethostname());
}

function sabyMultiEmulatorName($activeEmulator)
{
    $USER = get_current_user();
    return $USER . "-" . trim(gethostname()) . "-" . $activeEmulator;
}

function getOS()
{

    $os = strtolower(php_uname('s'));
    switch ($os) {
        case 'darwin':
            $_os = 'osx';
            break;

        case 'linux':
            $_os = 'linux';
            break;

        case 'windows nt':
            $_os = 'windows';
            break;

        default:
            $_os = $os;
            break;
    } //end switch

    return $_os;
} //end getOS()

function shutdown()
{
    setState('KILLED');
    exit;
}

function signalHandler($signo)
{
    exit(2);
}

function isKilledOrDeActivated()
{
    return true;
}

function shutdownHandler()
{
    myLog("inside shutdownHandler()");
    $state = getState();
    switch ($state) {
        case 'KILLED':
        case 'DEACTIVATED':
        case 'RE DEPLOY':
            return;
    }

    setState('KILLED');
    myLog("shutting down...");
    exit(2);
    //myLog(debug_string_backtrace());
}

function getMySvnRev()
{
    $path = realpath(__DIR__ . '/../../');
    $rev = substr((string) `git -C $path log -1|head -1| awk '{print $2}'`, 0, 40);
    $branch = shell_exec("git -C " . __DIR__ . " rev-parse --abbrev-ref HEAD");
    return trim(preg_replace('/\s+/', '', $branch)) . "-" . substr($rev, 0, 7);
}

function clearBeanstalkTube($tube)
{

    myLog("inside clearBeanstalkTube()");
    myLog("clearing tube:" . $tube);
    $beanstalk = getBeanstalk();
    try {
        while ($job = $beanstalk->peekReady($tube)) {
            myLog("inside the loop clearBeanstalkTube()");
            $beanstalk->delete($job);
        }
    } catch (Pheanstalk_Exception_ServerException $e) {
    }
}

function clearBeanstalkHupTube($tube)
{

    myLog("inside clearBeanstalkHupTube()");
    myLog("clearing tube:" . $tube);
    $beanstalkHup = getBeanstalkHup();
    myLog("connected to beanstalk hup, will loop now..");
    try {
        while ($job = $beanstalkHup->peekReady($tube)) {
            myLog("inside the loop clearBeanstalkHupTube()");
            $beanstalkHup->delete($job);
        }
    } catch (Pheanstalk_Exception_ServerException $e) {
    }
}

function getIp()
{
    return getHostByName(php_uname('n'));
}

require_once 'KLogger.php';
function myLog($msg, $level = LogLevel::INFO)
{
    global $log;
    static $isSyslogConnectionOpen = false;
    $USER = get_current_user();
    $HOSTNAME = trim(gethostname());
    $EMULATOR_ALPHA = $GLOBALS['EMULATOR_ALPHA'];
    if (!$isSyslogConnectionOpen) {
        openlog("mySabyLog", LOG_PID | LOG_PERROR | LOG_NDELAY, LOG_LOCAL0);
        $isSyslogConnectionOpen = true;
    }

    syslog(LOG_DEBUG, $msg);

    if (empty($log)) {
        $log = new KLogger("/var/log/saby/$USER/$EMULATOR_ALPHA");
    }

    // Do database work that throws an exception
    $log->log($level, "[$HOSTNAME] [$USER] - " . $msg);

    echo $msg . PHP_EOL;
}

function expressvpn_signout($emulator_port, $wa_pg_name)
{
    myLog("start express vpn signout process");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am force-stop com.expressvpn.vpn");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am force-stop com." . $wa_pg_name);
    sleep(10);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com.expressvpn.vpn/.ui.home.HomeActivity");
    sleep(10);
    $result = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell dumpsys activity activities | grep 'mResumedActivity'");
    myLog("Which Activity is this ? : " . $result);
    if (substr_count($result, 'com.expressvpn.vpn/.ui.home.HomeActivity') > 0) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 90 175");
        sleep(10);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 300 1635");
        sleep(10);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 780 1100");
        sleep(5);
        $vpnAccountIdFile = "/home/" . get_current_user() . "/Documents/VPN_ID.txt";
        $id = trim(file_get_contents($vpnAccountIdFile));
        updateExpressVpnAccount($id, 'disconnect');
    }
}

function surfshark_signout($emulator_port, $wa_pg_name)
{
    myLog("start surfShark vpn signout process");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am force-stop com.surfshark.vpnclient.android");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am force-stop com." . $wa_pg_name);
    sleep(10);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com.surfshark.vpnclient.android/.app.feature.main.MainActivity");
    sleep(10);
    $result = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell dumpsys activity activities | grep 'mResumedActivity'");
    myLog("Which Activity is this ? : " . $result);
    if (substr_count($result, 'com.surfshark.vpnclient.android') > 0) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 950 1650");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 550 155");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 850 480");
        sleep(5);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 800 1030");
    }
}

function debug_string_backtrace()
{
    ob_start();
    debug_print_backtrace(0, 10);
    $trace = ob_get_contents();
    ob_end_clean();

    // Remove first item from backtrace as it's this function which
    // is redundant.
    //$trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

    // Renumber backtrace items.
    //$trace = preg_replace ('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);

    return $trace;
}

function getAsteriskHangup($callId)
{
    myLog("getAsteriskHangup: " . $callId);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['api_server'] . "/getAsteriskHangup/" . $callId;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    $obj = json_decode($output);
    myLog("result: " . $output);
    curl_close($ch);
    return $obj->{'asterisk_hangup'};
}

function registerWithControlCenter()
{
    myLog("inside registerWithControlCenter()");
    $mySipExtension = getMySipExt();
    $rev = getMySvnRev();

    $url = $GLOBALS['conf']['controlCenterUrl'] . "/registerSaby/" . urlencode($mySipExtension) . '/' . $rev;
    myLog("registerWithControlCenter() URL: " . $url);
    httpGetAsync($url);
}

function getConfigEnvironment($sipext)
{
    myLog("inside getConfigEnvironment(): " . $sipext);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['controlCenterUrl'] . "/getConfigEnvironment/" . $sipext;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    myLog("result: " . $output);
    curl_close($ch);
    return $output;
}

function getConfigGeoLocation($vpn_region)
{
    myLog("inside getConfigGeoLocation(): " . $vpn_region);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = $GLOBALS['conf']['controlCenterUrl'] . "/getGeoLocation/" . $vpn_region;
    myLog("URL request:" . $url);
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);
    myLog("result: " . $output);
    curl_close($ch);
    return $output;
}


function writeNumberShown($result,$emulator_port){
    myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    $number = preg_replace('/[^0-9]/', '', $result);
    myLog('code is :'.$number);
    if(strlen($number) ==  3){
        $numberStr = (string) $number;
        $adbCommandPrefix = "~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent ";
        for ($i = 0; $i < strlen($numberStr); $i++) {
            $digit = $numberStr[$i];
            $keycode = ord($digit) - ord("0") + 7;  // Calculate the keycode
            $adbCommand = $adbCommandPrefix . $keycode;
            myLog($adbCommand);
            exec($adbCommand);
        }
    }
}
function insert_profile_pic($emulator_port)
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://95.217.198.229/apk/ProfilePic/picture.php',
        CURLOPT_USERAGENT => 'Codular Sample cURL Request',
    ]);
    $resp = curl_exec($curl);
    curl_close($curl);
    exec("wget " . $resp . " -O ~/pic.jpg");
    checkAVDIsRoot($emulator_port);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " push ~/pic.jpg /mnt/sdcard/pic.jpeg");
    rebootAVD($emulator_port);
}

function add_profile_pic($emulator_port, $wa_pg_name = "whatsapp")
{
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start  -n com." . $wa_pg_name . "/.profile.ProfileInfoActivity");
    sleep(1);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 725 807");
    sleep(3);
    $onscreen = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell uiautomator dump && ~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell cat /sdcard/window_dump.xml");
    $remove = substr_count($onscreen, "Remove");
    if ($remove > 0) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 425 1410");
    } else {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 185 1485");
    }
    sleep(3);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 280 500");
    sleep(3);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 196 570");
    sleep(3);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 900 1680");
}

function get_random_name()
{
    $fnames = file(__DIR__ . "/first_name_list.txt");
    $fnameIndex = rand(0, count($fnames) - 1);
    $fname = trim(preg_replace('/\s+/', ' ', $fnames[$fnameIndex]));
    $lnames = file(__DIR__ . "/last_name_list.txt");
    $lnameIndex = rand(0, count($lnames) - 1);
    $lname = trim(preg_replace('/\s+/', ' ', $lnames[$lnameIndex]));
    return $fname . "\ " . $lname;
}

function get_random_expressvpn()
{
    $countriesList = file(__DIR__ . "/countries.txt");
    $countryIndex = rand(0, count($countriesList) - 1);
    $country = trim(preg_replace('/\s+/', ' ', $countriesList[$countryIndex]));
    return $country;
}

function getRandomSentence()
{
    $msgType = rand(1, 100);
    if ($msgType <= 10) {
        $sentencesArray = array('Ahlan', 'Anyoung', 'Anyoung haseyo', 'Bonjour', 'Buna ziua', 'Chao', 'Ciao', 'Czesc', 'Dia dhuit', 'Dzien dobry', 'God dag', 'Goede dag', 'Gooan dag', 'Guten tag', 'Habari', 'Hallo', 'He', 'Hei', 'Hej', 'Hello', 'Helo', 'Hey', 'Hola', 'Hug', 'Hujambo', 'Ia ora na', 'Kalimera', 'Konnichiwa', 'Marhaba', 'Merhaba', 'Namaste', 'Ngiyakwemukela', 'Ni hao', 'Nin hao', 'Oi', 'Ola', 'Privet', 'Salam', 'Salut', 'Salve', 'Sawubona', 'Selam', 'Selamat siang', 'Shwmae', 'Suosdei', 'Suostei', 'Xin chao', 'Ya Yo', 'Yasou', 'Zdrasti', 'Zdraveite', 'Zdravstvuyte', 'que tal');
        $sentencesArrayCount = count($sentencesArray) - 1;
        $randomIndex = rand(0, $sentencesArrayCount);
        $sentence = $sentencesArray[$randomIndex];
    } elseif ($msgType <= 20 && $msgType > 10) {
        $sentencesArray = array('Come stai', 'Hoe gaan dit', 'kya haal hai', 'Como voce esta', 'si jeni', 'koj nyob li cas', 'hogy vagy', 'ce mai faci', 'Kak si', 'Com estas', 'eotteohge jinae', 'como estas', 'Ni hao ma', 'Hur mar du', 'jak se mate', 'quid agis', 'Hvordan har du det', 'hoe gaat het met je', 'nasilsin', 'Kamusta ka', 'mita kuuluu', 'ennaneyirikkunnu', 'yak ty', 'Comment allez-vous', 'wie gehts', 'unjani');
        $sentencesArrayCount = count($sentencesArray) - 1;
        $randomIndex = rand(0, $sentencesArrayCount);
        $sentence = $sentencesArray[$randomIndex];
    } else {
        $linesList = file(__DIR__ . "/content.txt");
        $lineIndex = rand(0, count($linesList) - 1);
        myLog("Choosed Line : " . $lineIndex);
        $selectedLine = $linesList[$lineIndex];
        $words = explode(' ', $selectedLine);
        $countWords = count($words) - 1;
        myLog("Words in Choosed Line : " . $countWords);
        $sentenceLength = rand(2, 5);
        myLog("Words in Sentence : " . $sentenceLength);
        $safeOffset = $countWords - $sentenceLength;
        myLog("Sentence Safe Offset : " . $safeOffset);
        $offset = rand(0, $safeOffset);
        myLog("Sentence Start Offset : " . $offset);
        $fullLength = $offset + $sentenceLength;
        myLog("Sentence End Offset : " . $fullLength);
        $sentence = '';
        for ($loop = $offset; $loop < $fullLength; $loop++) {
            myLog("word " . $loop . " : " . $words[$loop]);
            $sentence .= $words[$loop] . " ";
        }
        $sentence = substr($sentence, 0, -1);
    }

    myLog("Sentence : " . $sentence);
    return $sentence;
}

function get_random_phonenumber()
{
    $phonenumbers = file(__DIR__ . "/phonenumbers.txt");
    $phonenumberIndex = rand(0, count($phonenumbers) - 1);
    $phonenumber = trim(preg_replace('/\s+/', ' ', $phonenumbers[$phonenumberIndex]));
    return $phonenumber;
}

function get_random_status()
{
    $sentences = file(__DIR__ . "/sentences.txt");
    $sentenceIndex = rand(0, count($sentences) - 1);
    $sentence = trim(preg_replace('/\s+/', ' ', $sentences[$sentenceIndex]));
    $emojies = file(__DIR__ . "/emojies.txt");
    $emojiIndex = rand(0, count($emojies) - 1);
    $emoji = trim(preg_replace('/\s+/', ' ', $emojies[$emojiIndex]));
    return $sentence . $emoji;
}

function get_random_about()
{
    $sentences = file(__DIR__ . "/short_sentences.txt");
    $sentenceIndex = rand(0, count($sentences) - 1);
    $sentence = trim(preg_replace('/\s+/', ' ', $sentences[$sentenceIndex]));
    return $sentence;
}

function get_random_text_unknown_reply()
{
    $sentences = file(__DIR__ . "/botRandom.txt");
    $sentenceIndex = rand(0, count($sentences) - 1);
    $sentence = trim(preg_replace('/\s+/', ' ', $sentences[$sentenceIndex]));
    return $sentence;
}

function add_profile_status($emulator_port, $wa_pg_name = "whatsapp")
{
    $random_status = get_random_status();
    myLog("random status: " . $random_status);
    checkAVDIsRoot($emulator_port);
    exec('~/Android/Sdk/platform-tools/adb -s emulator-' . $emulator_port . ' shell am start -n com.' . $wa_pg_name . '/.TextStatusComposerActivity --es "android.intent.extra.TEXT" "' . $random_status . '"');
    $rand = rand(1, 10);
    for ($loop = 0; $loop <= $rand; $loop++) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 242 739");
    }
    $rand = rand(1, 10);
    for ($loop = 0; $loop <= $rand; $loop++) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 400 739");
    }
    sleep(3);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 935 777");
    sleep(3);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 866 1280");
}

function add_profile_about($emulator_port, $wa_pg_name = "whatsapp")
{
    $random_about = get_random_about();
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com." . $wa_pg_name . "/.profile.ProfileInfoActivity");
    sleep(1);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 989 1615");
    sleep(1);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 946 589");
    sleep(1);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text '" . $random_about . "'");
    sleep(1);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 930 830");
}

function lookForVoiceCall($emulator_port)
{
    $call_count_xml = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell cat data/data/com.whatsapp/shared_prefs/com.whatsapp_preferences_light.xml | grep -E 'call_confirmation_dialog_count'");
    if (empty($call_count_xml) || trim($call_count_xml) == false) {
        myLog("call count: 0");
        return true;
    }
    $dom = new DOMDocument();
    $dom->loadXML(trim($call_count_xml));
    $xpath = new DOMXpath($dom);
    $call_count = $xpath->evaluate('string(//int/@value)');
    myLog("call count: " . $call_count);
    return intval($call_count) < 5;
}

function sendSabyActionSignal($emulator_port, $saby, $action, $myphonenumber = null, $source = null)
{
    $url = "http://95.217.198.229/mini_morasel/saby_actions.php?saby=" . $saby . "\&action=" . $action;
    if ($action == "activation") {
        $url .= "\&number=" . $myphonenumber . "\&source=" . $source;
    }
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -a android.intent.action.VIEW -d '" . $url . "'");
    sleep(2);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am force-stop org.chromium.webview_shell");
    sleep(1);
}

function add_contact($saby, $emulator_port, $phonenumber)
{
    $contact_count = intval(shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell content query --uri content://com.android.contacts/raw_contacts | wc -l"));
    if ($contact_count == 1) {
        $result = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell content query --uri content://com.android.contacts/raw_contacts");
        if (substr_count($result, "No result found") > 0) {
            $contact_count = 1;
        } else {
            $contact_count += 1;
        }
    } else {
        $contact_count += 1;
    }
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell content insert --uri content://com.android.contacts/raw_contacts --bind account_type:s:SOME_ACCOUNT_TYPE --bind account_name:s:" . $saby);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell content insert --uri content://com.android.contacts/data --bind raw_contact_id:i:" . $contact_count . " --bind mimetype:s:vnd.android.cursor.item/name --bind data1:s:" . $phonenumber . " --bind mimetype:s:vnd.android.cursor.item/phone_v2 --bind data1:s:+" . $phonenumber);
    myLog("add contact: " . $phonenumber);
}

function pass_wa_first_call($emulator_port, $wa_pg_name = "whatsapp", $autoObj = null)
{
    myLog("start pass wa first call process");
    if (isset($emulator_port) && isset($autoObj)) {
        $passed = false;
        do {
            $phonenumber = get_random_phonenumber();
            add_contact("xdev", $emulator_port, $phonenumber);
            sleep(1);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com." . $wa_pg_name . "/.Conversation -e jid '" . $phonenumber . "@s.whatsapp.net'");
            sleep(1);
            if ($autoObj->pythonUI->exists(IMG . 'whatsapp_dial_big.png', null, 7)) {
                exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 812 179");
            }
            if ($autoObj->pythonUI->exists(IMG . 'start_voice_call.png', null, 5)) {
                exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 840 1010");
            } else {
                $passed = true;
            }
            if ($autoObj->pythonUI->exists(IMG . 'whatsapp_hangup.png', null, 5)) {
                exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 540 1250");
            }
        } while (!$passed);
        return $passed;
    }
    return false;
}

function decline_incomming_call($emulator_port)
{
    $iscall = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell dumpsys notification | grep -E Decline");
    if (strlen($iscall) > 0) {
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input swipe 162 1477 162 1000");
    }
}

function Answer_incomming_call($emulator_port)
{
    $mySabyName = getMySipExt();
    $iscall = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell dumpsys notification | grep -E Decline");
    if (strlen($iscall) > 0) {

        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input swipe 540 1477 540 1000");
        exec("~/7eet-saby-whatsapp-ubuntu20/scripts/audio_fplay.sh 1.mp3 & ");
        //Add Incomming Call Log
        AnalysisLogsExtra('CallsIn', null);
        sendSabyState($mySabyName, "IncommingCallAnswered");
        return true;
    } else {
        return false;
    }
}

function isJson($string)
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function add_validation_contacts($amount, $emulator_port)
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $saby = get_current_user() . "-" . trim(gethostname());
    $url = 'http://88.99.62.60/validation/BatchingAPI.php?machine_id=' . $saby . '&country_code=0&amount=' . $amount . '&try=1000';
    myLog('url : ' . $url);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Codular Sample cURL Request',
    ]);

    $resp = curl_exec($curl);
    curl_close($curl);

    $file_apart = explode('/', $resp);
    $file_name_index = count($file_apart) - 1;
    $download_file = $file_apart[$file_name_index];
    myLog('download_file : ' . $download_file);
    exec("wget " . $resp . " -O ~/" . $download_file);

    checkAVDIsRoot($emulator_port);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_HOME");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " push ~/" . $download_file . " mnt/sdcard/download/contacts.vcf");
    exec('~/Android/Sdk/platform-tools/adb -s emulator-' . $emulator_port . ' shell am start -t "text/vcard" -d "file:/mnt/sdcard/download/contacts.vcf" -a android.intent.action.VIEW com.android.contacts');

    sleep(1);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 880 1050");
}

function callLimiter()
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $mySabyName = getMySipExt();
    $date = date('Y-m-d');
    $dailyMaxCallsFilePath = "/home/" . get_current_user() . "/Documents/" . $date . "-Calls.conf";
    if (!file_exists($dailyMaxCallsFilePath)) {
        file_put_contents($dailyMaxCallsFilePath, 0);
        return false;
    } else {
        $dailyMaxCalls = getCallLimit($mySabyName);
        myLog('Max Calls Allowed : ' . $dailyMaxCalls);
        $currentCalls = file_get_contents($dailyMaxCallsFilePath);
        myLog('Current Calls : ' . $currentCalls);
        print_r($currentCalls);
        if ($currentCalls >= $dailyMaxCalls) {
            return true;
        } else {
            return false;
        }
    }
}
function updateCallsCount()
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));

    $date = date('Y-m-d');
    $dailyMaxCallsFilePath = "/home/" . get_current_user() . "/Documents/" . $date . "-Calls.conf";
    $currentCalls = file_get_contents($dailyMaxCallsFilePath);
    myLog('CurrentCalls Was: ' . $currentCalls);
    $currentCalls += 1;
    myLog('CurrentCalls Now: ' . $currentCalls);
    file_put_contents($dailyMaxCallsFilePath, $currentCalls);
}

function AnalysisLogs($action)
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $mySabyName = getMySipExt();
    $url = "http://filter.7eet.net/whatsapp_registry/API.php?machine_id=" . $mySabyName . "&action=" . $action;
    myLog("LOGGER >> " . $url);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
}

function AnalysisLogsExtra($dataType, $value)
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $value = str_replace(' ', '%20', $value);
    $mySabyName = getMySipExt();

    if ($dataType == 'Act') {
        $emulator_port = getAVDPort();
        $url = "http://filter.7eet.net/whatsapp_registry/API_EXTRA.php?machine_id=" . $mySabyName . "\&data_type=" . $dataType . "\&value=" . $value;
        myLog("LOGGER >> " . $url);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -a android.intent.action.VIEW -d '" . $url . "'");
        sleep(2);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am force-stop org.chromium.webview_shell");
        sleep(1);
    } else {
        $url = "http://filter.7eet.net/whatsapp_registry/API_EXTRA.php?machine_id=" . $mySabyName . "&data_type=" . $dataType . "&value=" . $value;

        myLog("LOGGER >> " . $url);
        myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
    }
}

function getActivationDetials($mySabyName)
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $url = "http://filter.7eet.net/whatsapp_registry/API_EXTRA.php?machine_id=" . $mySabyName . "&data_type=Get";

    myLog("LOGGER >> " . $url);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    $obj = json_decode($output);
    return array($obj->{'whatsapp_name'}, $obj->{'vpn_region'}, $obj->{'activation_timestamp'}, $obj->{'vpn_provider'}, $obj->{'sim_number'}, $obj->{'activation_type'});
}

function createConversationArray($file)
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ConversationArray = array();
    $sentenseFile = fopen(__DIR__ . "/" . $file, 'r');

    while (!feof($sentenseFile)) {
        $row = fgets($sentenseFile);
        $rowArray = explode(',', $row);
        $ConversationArray[] = $rowArray;
    }

    fclose($sentenseFile);
    return $ConversationArray;
}

function createBotArray()
{
    myLog("Running >>>>>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $botArray = array();
    $sentenseFile = fopen(__DIR__ . "/bot.txt", 'r');

    while (!feof($sentenseFile)) {
        $row = fgets($sentenseFile);
        $rowArray = explode(',', $row);
        $botArray[] = $rowArray;
    }

    fclose($sentenseFile);
    return $botArray;
}

function createBotRandomArray()
{
    $botRandomArray = array();
    $sentenseFile = fopen(__DIR__ . "/botRandom.txt", 'r');

    while (!feof($sentenseFile)) {
        $row = fgets($sentenseFile);
        $rowArray = explode(',', $row);
        $botRandomArray[] = $rowArray;
    }

    fclose($sentenseFile);
    return $botRandomArray;
}

function shuffle_assoc($list)
{
    if (!is_array($list)) {
        return $list;
    }

    $keys = array_keys($list);
    shuffle($keys);
    $random = array();
    foreach ($keys as $key) {
        $random[] = $list[$key];
    }
    return $random;
}

function searchForKeyword($msg, $array)
{
    $array = shuffle_assoc($array);
    foreach ($array as $key => $val) {
        if (substr_count($msg, $val[0]) > 0) {
            return $key;
        }
    }
    return null;
}
function replySender($emulator_port, $wa_pg_name)
{
    $cmd_part1 = "~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell ";
    $cmd_part2 = "'sqlite3 /data/data/com." . $wa_pg_name . "/databases/msgstore.db \"select unseen_message_count , jid.raw_string from chat join jid on chat.jid_row_id = jid._id where unseen_message_count > 0\"'";

    $messages = shell_exec($cmd_part1 . $cmd_part2);
    $rows = explode("\n", $messages);
    $count = count($rows) - 1;
    for ($loop = 0; $loop < $count; $loop++) {
        list($msgCount, $jid) = explode("|", $rows[$loop]);
        $msg = getRandomSentence();
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com." . $wa_pg_name . "/.Conversation -e jid '" . $jid . "'");
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 400 1680");
        $loopIterations = strlen($msg);
        for ($Iterations = 0; $Iterations < $loopIterations; $Iterations++) {

            if ($msg[$Iterations] == ' ') {
                exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_SPACE");
            } elseif ($msg[$Iterations] == "?" || $msg[$Iterations] == "!" || $msg[$Iterations] == ",") {
                $specialCharcter = "'\".$msg[$Iterations]" . "'";
                exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $specialCharcter);
            } else {
                exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $msg[$Iterations]);
            }
            $microSleep = rand(500000, 1000000);
            usleep($microSleep);
        }
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 1000 820");
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_HOME");
    }
}

function replyMessages($botArray, $botRandomArray, $emulator_port, $wa_pg_name)
{
    //exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com." . $wa_pg_name . "/.Main");
    $cmd_part1 = "~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell ";
    $cmd_part2 = "'sqlite3 /data/data/com." . $wa_pg_name . "/databases/msgstore.db \"select unseen_message_count , jid.raw_string from chat join jid on chat.jid_row_id = jid._id where unseen_message_count > 0\"'";

    $messages = shell_exec($cmd_part1 . $cmd_part2);
    $rows = explode("\n", $messages);
    $count = count($rows) - 1;
    for ($loop = 0; $loop < $count; $loop++) {
        //Add Message Reply Counter Log
        AnalysisLogsExtra('Msg', null);
        list($msgCount, $jid) = explode("|", $rows[$loop]);
        $cmd_part1 = "~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell ";
        $cmd_part2 = "'sqlite3 /data/data/com.whatsapp/databases/msgstore.db ";
        $cmd_part3 = '"' . "select data from messages where key_remote_jid like " . '\"' . $jid . '\"' . " and data is not null and key_from_me <> 1 order by received_timestamp desc limit " . $msgCount . '"' . "'";
        $M = trim(shell_exec($cmd_part1 . $cmd_part2 . $cmd_part3));
        $randBotArray = shuffle_assoc($botArray);
        $id = searchForKeyword($M, $randBotArray);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell am start -n com." . $wa_pg_name . "/.Conversation -e jid '" . $jid . "'");
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 400 1680");
        if ($id != null) {

            $microSleep = rand(500000, 2000000);
            usleep($microSleep);
            $reply1 = $randBotArray[$id][1];
            $loopIterations = strlen($reply1);
            for ($Iterations = 0; $Iterations < $loopIterations; $Iterations++) {

                if ($reply1[$Iterations] == ' ') {
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_SPACE");
                } elseif ($reply1[$Iterations] == "?" || $reply1[$Iterations] == "!" || $reply1[$Iterations] == ",") {
                    $specialCharcter = "'\".$reply1[$Iterations]" . "'";
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $specialCharcter);
                } else {
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $reply1[$Iterations]);
                }
                $microSleep = rand(25000, 50000);
                usleep($microSleep);
            }
            $microSleep = rand(500000, 2000000);
            usleep($microSleep);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 1000 820");
            $microSleep = rand(500000, 2000000);
            usleep($microSleep);
            $reply2 = $randBotArray[$id][2];
            $loopIterations = strlen($reply2);
            for ($Iterations = 0; $Iterations < $loopIterations; $Iterations++) {

                if ($reply2[$Iterations] == ' ') {
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_SPACE");
                } elseif ($reply2[$Iterations] == "?" || $reply2[$Iterations] == "!" || $reply2[$Iterations] == ",") {
                    $specialCharcter = "'\".$reply2[$Iterations]" . "'";
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $specialCharcter);
                } else {
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $reply2[$Iterations]);
                }
                $microSleep = rand(250000, 500000);
                usleep($microSleep);
            }
            $microSleep = rand(500000, 2000000);
            usleep($microSleep);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 1000 820");
            $microSleep = rand(500000, 2000000);
            usleep($microSleep);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_BACK");
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_BACK");
        } else {
            $replyStandard = get_random_text_unknown_reply();
            $loopIterations = strlen($replyStandard);
            for ($Iterations = 0; $Iterations < $loopIterations; $Iterations++) {

                if ($replyStandard[$Iterations] == ' ') {
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_SPACE");
                } elseif ($replyStandard[$Iterations] == "?" || $replyStandard[$Iterations] == "!" || $replyStandard[$Iterations] == ",") {
                    $specialCharcter = "'\".$replyStandard[$Iterations]" . "'";
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $specialCharcter);
                } else {
                    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input text " . $replyStandard[$Iterations]);
                }

                $microSleep = rand(250000, 500000);
                usleep($microSleep);
            }
            $microSleep = rand(500000, 2000000);
            usleep($microSleep);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input tap 1000 820");
            $microSleep = rand(500000, 2000000);
            usleep($microSleep);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_BACK");
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulator_port . " shell input keyevent KEYCODE_BACK");
        }
    }
}

function filterLogInit($mySabyName, $destinationNumber, $flowFlag)
{
    myLog("Running >>>> " . __FUNCTION__ . "  in " . basename(__FILE__));
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    $url = "http://filter.7eet.net/Saby_logs/logger.php?callee=" . $destinationNumber . "&saby=" . $mySabyName . "&saby_techprefix=" . $flowFlag;
    myLog("LOGGER >> " . $url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = str_replace("\n", "", str_replace("\r", "", $output));
    return $output;
}

function sendFilterLogs($filterTimeStamp, $action, $destinationNumber)
{
    myLog("Running >>>> " . __FUNCTION__ . "  in " . basename(__FILE__));
    $mySabyName = getMySipExt();
    $url = "http://filter.7eet.net/Saby_logs/logger.php?callee=" . $destinationNumber . "&saby=" . $mySabyName . "&inserted_at=" . $filterTimeStamp . "&" . $action;
    myLog("LOGGER >> " . $url);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
}

function checkCountry($phoneNumber)
{
    myLog("Running >>>> " . __FUNCTION__ . "  in " . basename(__FILE__));
    $count = 0;
    while (substr($phoneNumber, 0, strlen($GLOBALS['countryCodes'][$count][1])) != $GLOBALS['countryCodes'][$count][1]) {
        $count++;
    }
    $countryName = $GLOBALS['countryCodes'][$count][0];
    $countryCode = $GLOBALS['countryCodes'][$count][1];
    $number = substr_replace($phoneNumber, '', 0, strlen($GLOBALS['countryCodes'][$count][1]));
    unset($count);

    return array($countryName, $countryCode, $number);
}

// Sender Receiver
function sendTestLog($mySabyName, $phoneNumber, $action, $destinationNumber)
{
    $url = "http://filter.7eet.net/Saby_logs/logger.php?callee=" . $destinationNumber . "&saby=" . $mySabyName . "&number=" . $phoneNumber . "&" . $action;
    myLog("sendFilterLogs: " . $url);
    httpGetAsync($url);
}

function deleteSabySenderReceiver($mySabyName)
{
    $url = "http://filter.7eet.net/MapleSystemsAPI/RegisterSaby/RegisterSaby.php?machine_id=" . $mySabyName . "&action=Delete";
    myLog("sendFilterLogs: " . $url);
    httpGetAsync($url);
}

function addSabySenderReceiver($mySabyName, $applicationName, $emulatorNumber, $emulatorName, $Type, $myState)
{
    //replace any space with %20
    $emulatorName = str_replace(' ', '%20', $emulatorName);
    //Initialize CURL session
    $url = "http://filter.7eet.net/MapleSystemsAPI/RegisterSaby/RegisterSaby.php?machine_id=" . $mySabyName . "&application=" . $applicationName . "&phone_number=" . $emulatorNumber . "&phone_name=" . $emulatorName . "&type=" . $Type . "&state=" . $myState . "&action=Add";
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    //URL to fetch
    curl_setopt($ch, CURLOPT_URL, $url);
    //if it is set to true, data is returned as string instead of outputting it.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // save result
    $result = curl_exec($ch);
    //close curl session
    curl_close($ch);
    myLog($url);
    return $result; //created time
}

function getWaContacts($applicationName, $Type, $mySabyName, $emulatorPort)
{

    $Type = $Type;
    //Initialize CURL session
    $url = "http://filter.7eet.net/MapleSystemsAPI/RegisterSaby/RegisterSaby.php?application=" . $applicationName . "&type=" . $Type . "&action=List";
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    //URL to fetch
    curl_setopt($ch, CURLOPT_URL, $url);
    //if it is set to true, data is returned as string instead of outputting it.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // save result
    $resultUrl = curl_exec($ch);
    //close curl session
    curl_close($ch);
    myLog($url);
    //return $url;
    exec("wget " . $resultUrl . " -O " . $mySabyName . ".vcf");
    myLog("****************************************done!");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " push ~/" . $mySabyName . ".vcf /mnt/sdcard/download/" . $mySabyName . ".vcf");
    sleep(5);
    checkAVDIsRoot($emulatorPort);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell input keyevent KEYCODE_HOME");
    exec('~/Android/Sdk/platform-tools/adb -s emulator-' . $emulatorPort . ' shell am start -t "text/vcard" -d "file:/mnt/sdcard/download/' . $mySabyName . '.vcf" -a android.intent.action.VIEW com.android.contacts');
    sleep(10);
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell input tap 880 1050");
}

function updateSabySenderReceiver($mySabyName, $myState)
{
    $url = "http://filter.7eet.net/MapleSystemsAPI/RegisterSaby/RegisterSaby.php?machine_id=" . $mySabyName . "&state=" . $myState . "&action=Update";
    myLog("sendFilterLogs: " . $url);
    httpGetAsync($url);
}

function getLastTestCallDetails($mySabyName, $Type)
{
    $url = "http://filter.7eet.net/MapleSystemsAPI/RegisterSaby/RegisterSaby.php?machine_id=" . $mySabyName . "&type=" . $Type . "&action=LastCall";
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    //URL to fetch
    curl_setopt($ch, CURLOPT_URL, $url);
    //if it is set to true, data is returned as string instead of outputting it.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // save result
    $resultUrl = curl_exec($ch);
    //close curl session
    curl_close($ch);
    myLog($url);
    return $resultUrl;
}

function getEmulatorNameSuffix($activeEmulator)
{
    switch ($activeEmulator) {
        case 'A':
            $emulatorNameSuffix = '';
            break;
        case 'B':
            $emulatorNameSuffix = '1';
            break;
        case 'C':
            $emulatorNameSuffix = '2';
            break;
        case 'D':
            $emulatorNameSuffix = '3';
            break;
        case 'E':
            $emulatorNameSuffix = '4';
            break;
        case 'F':
            $emulatorNameSuffix = '5';
            break;
        case 'G':
            $emulatorNameSuffix = '6';
            break;
        case 'h':
            $emulatorNameSuffix = '7';
            break;
        case 'I':
            $emulatorNameSuffix = '8';
            break;
        case 'J':
            $emulatorNameSuffix = '9';
            break;
    }
    return $emulatorNameSuffix;
}

function AdbStartupPermissions($emulatorPort)
{
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell settings put global heads_up_notifications_enabled 0");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell pm grant com.android.contacts android.permission.READ_CONTACTS");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell pm grant com.android.contacts android.permission.WRITE_CONTACTS");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell pm grant com.android.contacts android.permission.GET_ACCOUNTS");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell pm grant com.android.contacts android.permission.CALL_PHONE");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell pm grant com.android.contacts android.permission.READ_PHONE_STATE");
    exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell pm grant com.android.contacts android.permission.READ_EXTERNAL_STORAGE");
}

function getEmulatorAndroidID($emulatorPort)
{
    $android_id = shell_exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $emulatorPort . " shell settings get secure android_id");
    $androidID = trim(preg_replace('/\s+/', '', $android_id));
    return $androidID;
}

/////////////////////////////////// NEW FUNCTIONS //////////////////////////////////

function defineStatesCounterFildersOnStart()
{
    //Create Limit Saby Status Proccessing -- Queuing
    if (!file_exists("/tmp/GlobalStatus")) {
        mkdir("/tmp/GlobalStatus", 0777, true);
        exec("chmod 0777 /tmp/GlobalStatus");
    }

    // Define behaviourStateCounterFile and create it with 0 value if not exist
    $behaviourStateCounterFile = "/tmp/GlobalStatus/behaviourStateCounter.txt";
    if (!file_exists($behaviourStateCounterFile)) {
        file_put_contents($behaviourStateCounterFile, 0);
        exec("chmod 0777 /tmp/GlobalStatus/*");
    }

    // Define activationStateCounterFile and create it with 0 value if not exist
    $activationStateCounterFile = "/tmp/GlobalStatus/activationStateCounter.txt";
    if (!file_exists($activationStateCounterFile)) {
        file_put_contents($activationStateCounterFile, 0);
        exec("chmod 0777 /tmp/GlobalStatus/*");
    }

    // Define startingStateCounterFile and create it with 0 value if not exist
    $startingStateCounterFile = "/tmp/GlobalStatus/startingStateCounter.txt";
    if (!file_exists($startingStateCounterFile)) {
        file_put_contents($startingStateCounterFile, 0);
        exec("chmod 0777 /tmp/GlobalStatus/*");
    }

    // Define prepairingStateCounterFile and create it with 0 value if not exist
    $prepairingStateCounterFile = "/tmp/GlobalStatus/prepairingStateCounter.txt";
    if (!file_exists($prepairingStateCounterFile)) {
        file_put_contents($prepairingStateCounterFile, 0);
        exec("chmod 0777 /tmp/GlobalStatus/*");
    }

    return array($behaviourStateCounterFile, $activationStateCounterFile, $startingStateCounterFile, $prepairingStateCounterFile);
}

function logInState($stateFolder, $maxCount)
{
    $currentCounter = file_get_contents($stateFolder);
    if ($currentCounter < $maxCount) {
        $currentCounter++;
        file_put_contents($stateFolder, $currentCounter);
        return true;
    } else {
        return false;
    }
}
function logOutState($stateFolder)
{
    $currentCounter = file_get_contents($stateFolder);
    $currentCounter--;
    file_put_contents($stateFolder, $currentCounter);
}

// *********************************************** WATCHER FUNCTION *********************************************** //
function getSabyCurrentState($ENV_HOST_API, $sabyFullName)
{
    $url = $ENV_HOST_API . "/getSabyCurrentState/" . urlencode($sabyFullName);

    myLog("SABY WATCHER LOGGER >> " . $url);
    myLog("RUNNING >>> " . __FUNCTION__ . " IN " . basename(__FILE__));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    $obj = json_decode($output);
    return array($obj->{'state'}, $obj->{'value'}, $obj->{'last_update'});
}

function getSocketPort($sabyFullName)
{
    $sabyFullNameArray = explode('-', $sabyFullName);
    $sabyFullNameArray[0] = trim($sabyFullNameArray[0], 'xdev');
    if (strlen($sabyFullNameArray[0]) == 1) {
        $sabyFullNameArray[0] = "0" . $sabyFullNameArray[0];
    }
    $socketPort = $sabyFullNameArray[2] . $sabyFullNameArray[0];
    $socketPort = (substr($socketPort, 0, 1) == 0) ? substr($socketPort, 1) : $socketPort;
    return ($socketPort);
}

function setSabyScreenResolution()
{
    myLog("Inside setSabyScreenResolution()");
    $cmd = '~/7eet-saby-whatsapp-ubuntu20/scripts/screen_get_set_resolution.sh';
    shell_exec($cmd);
}

function get_between_data($string, $start, $end)
{
    $pos_string = stripos($string, $start);
    $substr_data = substr($string, $pos_string);
    $string_two = substr($substr_data, strlen($start));
    $second_pos = stripos($string_two, $end);
    $string_three = substr($string_two, 0, $second_pos);
    // remove whitespaces from result
    $result_unit = trim($string_three);
    // return result_unit
    return $result_unit;
}

function getCodeVersion()
{
    myLog("RUNNING >>>" . __FUNCTION__);
    $cmd = '~/7eet-saby-whatsapp-ubuntu20/scripts';
    $codeVersion = shell_exec($cmd);
    unset($cmd);
    return $codeVersion;
}
