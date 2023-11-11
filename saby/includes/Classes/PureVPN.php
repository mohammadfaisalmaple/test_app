<?php
class PureVPN
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
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/PureVPN.apk");
        myLog("The Application is Sccussefully Installed");
        return true;
    }

    public function UninstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " uninstall com.gaditek.purevpnics");
        myLog("The Application is Sccussefully Installed");
        return true;
    }

    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.gaditek.purevpnics/com.purevpn.ui.dashboard.DashboardActivity");
        sleep(3);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'PureVPNOpenLocationMenu.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(3);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PureVPNOpenLocationMenu.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                sleep(3);
                if ($this->vpn_region != 'Unknown') {
                    $random_vpn_region = $this->getPureVpnRandomRegion();
                } else {
                    $random_vpn_region = 'USA';
                }
                $this->autoObject->pythonUI->type($random_vpn_region);
                sleep(3);
                $reagonOK = $this->autoObject->pythonUI->createLocation(272, 203);
                $this->autoObject->pythonUI->click($reagonOK);
                sleep(3);
                if ($this->isVpnConnected()) {
                    $this->AlwaysOnVPN();
                }
            }
        }
    }

    public function getPureVpnRandomRegion()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getPureVpnRandomRegion/" . $this->vpn_region);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getPureVpnRandomRegion: " . $output);
        $obj = json_decode($output);
        return $obj->{'region'};
    }

    public function DisconnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function LoginVPN($username, $password)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.gaditek.purevpnics/com.purevpn.ui.auth.AuthActivity");
        sleep(2);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'PureVPNLoginButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(2);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PureVPNEnterUsername.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                sleep(2);
                $this->autoObject->pythonUI->type($username);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PureVPNPasswordFelied.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                sleep(2);
                $this->autoObject->pythonUI->type($password);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'PureVPNSignUpButton.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
        }
    }

    public function AlwaysOnVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app com.privateinternetaccess.android");
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
}
