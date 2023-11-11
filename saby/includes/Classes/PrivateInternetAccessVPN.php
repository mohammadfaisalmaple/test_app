<?php
class PrivateInternetAccessVPN
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
        $this->emulatorName = $emulatorAlpha;
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
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/PIA.apk");
        myLog("The Application is Scussefully Installed");
    }

    public function LoginVPN($UserName, $PassWord)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.privateinternetaccess.android/.ui.connection.MainActivity");
        sleep(3);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNLoginButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNEnterUserName.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                myLog("Entering The Username");
                $this->autoObject->pythonUI->type($UserName);
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNEnterPassword.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                $this->autoObject->pythonUI->type($PassWord);
                myLog("Entering The Password");
            }
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNLoginConfirmationButton.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
            sleep(5);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 525 1725 525 350 1250");
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNLoginConfirmOK.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNConnectionWarning.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click(IMG . 'PIAVPNConnectionWarningConfirmation.png');
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIALoginDoneIcon.png', null, 5, $reagonOK, $reagonArr)) {
                myLog("The Login Successfully Done");
                return true;
            }
        } elseif ($this->autoObject->pythonUI->findAndClick(IMG . 'PIALoginDoneIcon.png', null, 5, $reagonOK, $reagonArr)) {
            myLog("The Login Successfully Done");
            return true;
        }
    }

    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.privateinternetaccess.android/.ui.connection.MainActivity");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 95 175");
        sleep(2);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNReagionButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(3);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNSearchFiled.png', null, 5, $reagonOK, $reagonArr)) {
                sleep(3);
                if ($this->vpn_region != 'Unknown') {
                    $random_vpn_region = $this->getPrivateVpnRandomRegion();
                } else {
                    $random_vpn_region = 'USA';
                }
                $this->autoObject->pythonUI->type($random_vpn_region);
                sleep(3);
                $profilePicture = $this->autoObject->pythonUI->createLocation(265, 250);
                $this->autoObject->pythonUI->click($profilePicture);
                sleep(2);
                if ($this->isVpnConnected()) {
                    myLog("The Connection Operation Succssfully Done ~~");
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app com.privateinternetaccess.android");
                    sleep(2);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 1");
                }
            }
        }
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
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 450 0 450 1750 1000");
        sleep(3);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNDisConnectButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        }
    }

    public function LogoutVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.privateinternetaccess.android/.ui.connection.MainActivity");
        sleep(3);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 95 175");
        sleep(2);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'PIAVPNLogoutButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(3);
            if ($this->autoObject->pythonUI->exists(IMG . 'PIAVPNLogoutConfirmaion.png', null, 5)) {
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 770 1200");
            }
        }
    }

    public function UninstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " uninstall com.privateinternetaccess.android");
        sleep(2);
        myLog("The VPN is Uninstall the VPN Succesfully");
    }

    public function getPrivateVpnRandomRegion()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $url = "http://cc-api.7eet.net/getPrivateVpnRandomRegion/" . $this->vpn_region;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("The Complete URL: " . $url);
        myLog("getPiaRandomRegion: " . $output);
        $obj = json_decode($output);
        return $obj->{'region'};
    }

    public function AlwaysOnVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app com.privateinternetaccess.android");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 1");
    }
}
