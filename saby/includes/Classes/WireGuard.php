<?php
class WireGuard
{
    public $emulatorPort = null;
    public $emulatorName = null;
    public $emulatorAlpha = null;
    public $autoObject = null;
    public $vpn_region = null;
    public function __construct($emulatorName, $emulatorAlpha, $emulatorPort, $vpn_region)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->emulatorPort = $emulatorPort;
        $this->emulatorName = $emulatorName;
        $this->emulatorAlpha = $emulatorAlpha;
        $this->vpn_region = $vpn_region;
    }

    public function addPythonUIObject($automator)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->autoObject = $automator;
    }

    public function InstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/wireguard.apk");
        myLog("The Application is Scussefully Installed");
        return true;
    }

    public function GetConfigFile()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $vpnConfig = file_get_contents("http://cc-api.7eet.net/getSabyVpnActivationConfig/" . $this->emulatorName . "/" . $this->vpn_region);
        if (substr_count($vpnConfig, "error") == 0) {
            $configPath = "/home/" . get_current_user() . "/" . $this->emulatorName . ".conf";
            file_put_contents($configPath, $vpnConfig, LOCK_EX);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->emulatorName . ".conf /mnt/sdcard/download/" . $this->emulatorName . ".conf");
            myLog(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->emulatorName . ".conf /mnt/sdcard/download/" . $this->emulatorName . ".conf");
            return true;
        }
        return false;
    }

    public function initSabyVpnAndCheckIsReady()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        myLog("init or check Saby Vpn for: " . $this->emulatorName . " on vpn region" . $this->vpn_region);
        $ch = curl_init();
        $url = "http://cc-api.7eet.net/initSabyVpnActivation/" . $this->emulatorName . "/" . $this->vpn_region;
        myLog($url);
        myLog("URL request:" . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result: " . $output);
        if (substr_count($output, "error") > 0) {
            myLog('KILLED' . '"\t" WIREGUARD VPN ERROR');
            exit(2);
        }
        curl_close($ch);
        return ($obj->{'vpn_status'}) === "ready";
    }

    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.wireguard.android/.activity.MainActivity");
        sleep(3);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'WireguardAddtunnel.png', null, 5)) {

            sleep(2);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'WireguardImportFileButton.png', null, 5)) {
            }
            sleep(2);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 120 190");
            sleep(3);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IMODownloadsFile.png', null, 5)) {

                sleep(2);
                if ($this->autoObject->pythonUI->findAndClick(IMG . 'WiregardConfgFileIcon.png', null, 5) || $this->autoObject->pythonUI->findAndClick(IMG . 'WiregardConfgFileIcon2.png', null, 5)) {
                    $reagonOK = $this->autoObject->pythonUI->createLocation(270, 380);

                    sleep(5);
                    if ($this->autoObject->pythonUI->findAndClick(IMG . 'WireGuardNotConnected.png', null, 5)) {
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . 'WireguardConnectionWaring.png', null, 5)) {
                        if ($this->autoObject->pythonUI->findAndClick(IMG . 'WireguardConnectionWaringConfirmation.png', null, 5)) {
                        }
                    }
                }
            }
            sleep(5);
            if ($this->isVpnConnected()) {
                $this->AlwaysOnVPN();
            }
        } else {
            myLog("The VPN App Not Working Yet");
            return false;
        }
    }

    public function AlwaysOnVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app com.wireguard.android");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 1");
    }

    public function isVpnConnected()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $vpnStatus = shell_exec(ADB . ' -s emulator-' . $this->emulatorPort . ' shell dumpsys connectivity | grep "type: VPN"');
        $vpnConnceted = substr_count($vpnStatus, "type: VPN[], state: CONNECTED/CONNECTED") > 0;
        if ($vpnConnceted) {
            myLog("VPN Connected");
            return true;
        } else {
            myLog("VPN Not Connected");
            return false;
        }
        return $vpnConnceted;
    }

    public function DisconnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        file_get_contents("http://cc-api.7eet.net/destroySabyVpnActivation/" . $this->emulatorName);
        file_get_contents("http://cc-api.7eet.net/destroySabyVpnActivation/" . $this->emulatorAlpha);
    }

    public function UninstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " uninstall com.wireguard.android");
        sleep(2);
        myLog("The VPN is Uninstall the VPN Succesfully");
    }
}
