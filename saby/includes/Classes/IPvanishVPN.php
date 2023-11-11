<?php
class IPVanishVPN
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
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/IPVanishVPN.apk");
        myLog("The Application is Scussefully Installed");
    }

    public function LoginVPN($UserName, $PassWord)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.ixolit.ipvanish/.activity.ActivityMain");
        sleep(3);
        $reagonArr = null;
        $reagonOK = null;
        if ($this->autoObject->pythonUI->exists(IMG . 'IPVanishMainActivity.png', null, 5)) {
            sleep(5);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishUsernameFelid.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                $this->autoObject->pythonUI->type($UserName);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishPasswordFelid.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input text " . $PassWord);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishLoginButton.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
            sleep(3);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishSkipTutorial.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 92 175");
            $reagonArr = null;
            $reagonOK = null;
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishSettingsButton.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                sleep(2);
                if ($this->autoObject->pythonUI->exists(IMG . 'IPVanishAndroidStartUpConnection.png', null, 5)) {
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 1000 685");
                }
                if ($this->autoObject->pythonUI->exists(IMG . 'IPVanishConnectToLastServer.png', null, 5)) {
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 985 1510");
                }
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            }
            myLog("The Login Succfully Done");
            return true;
        } elseif ($this->autoObject->pythonUI->exists(IMG . 'IPVanishLoginSucssefully.png', null, 5)) {
            myLog("The Login Succfully Done");
            return true;
        } elseif ($this->autoObject->pythonUI->exists(IMG . 'IPVanishLoginDone3.png', null, 5)) {
            myLog("The Login Succfully Done");
            return true;
        } else {
            myLog("The Login Not Succfully Done");
            return false;
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

    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.ixolit.ipvanish/.activity.ActivityMain");
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 89 189");
        sleep(2);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishOpenServersMenu.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(3);
            $reagonArr = null;
            $reagonOK = null;
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishMaginifire.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
            exec(ADB . " -s emulator-" . $this->emulatorPort . "shell input tap 829 175");
        }
        myLog($this->vpn_region);
        sleep(3);
        if ($this->vpn_region != 'Unknown') {
            $random_vpn_region = $this->getPiaRandomRegion();
        } else {
            $random_vpn_region = 'USA';
        }
        myLog("THE REAGON" . $random_vpn_region);
        if ($random_vpn_region == '') {
            $random_vpn_region = $this->getPiaRandomRegion();
        }
        $this->autoObject->pythonUI->type($random_vpn_region);
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 570 360");
        sleep(5);
        $reagonArr = null;
        $reagonOK = null;
        if ($this->autoObject->pythonUI->exists(IMG . 'IPVanishConnectionWaring.png', null, 5)) {
            if ($this->autoObject->pythonUI->exists(IMG . 'IPVanishWaring2Connection.png', null, 5, $reagonOK, $reagonArr)) {
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 129 930");
            }
            $reagonArr = null;
            $reagonOK = null;
            sleep(3);
            if ($this->autoObject->pythonUI->findAndClick(IMG . "IPVanishConnectButton.png", null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
        }
        $reagonArr = null;
        $reagonOK = null;
        if ($this->autoObject->pythonUI->exists(IMG . 'IPVanishConnectionWarrnig3.png', null, 5)) {
            $this->autoObject->pythonUI->click(IMG . "IPVanishWaring3Confermation.png");
        }
        $reagonArr = null;
        $reagonOK = null;
        if ($this->autoObject->pythonUI->findAndClick(IMG . "IPVanishConnectionButton.png", null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
        }
        sleep(5);
        if ($this->isVpnConnected()) {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app com.ixolit.ipvanish");
            sleep(2);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 1");
            sleep(3);
        }
    }

    public function DisconnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 450 0 450 1750 1000");
        sleep(3);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishDisconnectButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        }
    }

    public function LogoutVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.ixolit.ipvanish/.activity.ActivityMain");
        sleep(3);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 89 189");
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishSettingsButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(3);
            $reagonArr = null;
            $reagonOK = null;
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishLogoutMenu.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
            $reagonOK2 = $this->autoObject->pythonUI->createLocation(421, 146);
            $this->autoObject->pythonUI->click($reagonOK2);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'IPVanishLogoutButton.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
            $reagonArr = null;
            $reagonOK = null;
            if ($this->autoObject->pythonUI->exists(IMG . 'IPVanishConfirmLogoutWaring.png', null, 5)) {
                $this->autoObject->pythonUI->click(IMG . 'IPVanishLogoutWaringConfirmation.png');
            }
        }
    }

    public function UninstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " uninstall com.ixolit.ipvanish");
        sleep(2);
        myLog("The VPN is Uninstall the VPN Succesfully");
    }

    public function getPiaRandomRegion()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $url = "http://cc-api.7eet.net/getPiaRandomRegion/" . $this->vpn_region;
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
}
