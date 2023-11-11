<?php
class SurfSharkVPN
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
        myLog($this->emulatorPort);
    }

    public function addPythonUIObject($automator)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->autoObject = $automator;
    }

    public function InstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/Surfshark.apk");
    }

    public function LoginVPN($UserName, $PassWord)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.surfshark.vpnclient.android/.app.feature.onboarding.OnboardingActivity");
        sleep(3);
        $reagonArr = null;
        $reagonOK = null;
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkLoginButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(5);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkEmailLoginFelid.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                $this->autoObject->pythonUI->type($UserName);
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkLoginPasswordFelid.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                $this->autoObject->pythonUI->type($PassWord);
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkLoginConfirmButton.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
            }
        }
        if ($this->autoObject->pythonUI->exists(IMG . 'SurfsharkLoginSucssefully.png', null, 5)) {
            myLog("The Login Successfully Done");
            return true;
        } else {
            return false;
        }
    }

    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkOpenLocationMenu.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
        }
        sleep(2);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkMagnifireButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
        }
        sleep(3);
        if ($this->vpn_region != 'Unknown') {
            $random_vpn_region = $this->getSurfSharkRandomRegion($this->vpn_region);
        } else {
            $random_vpn_region = 'USA';
        }
        $this->autoObject->pythonUI->type($random_vpn_region);
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 563 482");
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkConnectionWaring.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click(IMG . 'SurfsharkWaringConfermation.png');
        }
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfSharkConnectionSucssefully.png', null, 5, $reagonOK, $reagonArr)) {
            myLog("The Connection With VPN Sucssefully Done");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app com.surfshark.vpnclient.android");
            sleep(2);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 1");
        }
    }

    public function UninstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " uninstall com.surfshark.vpnclient.android");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 0");
        myLog("The VPN is Uninstall the VPN Succesfully");
    }

    public function DisconnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.surfshark.vpnclient.android/.app.feature.main.MainActivity");
        sleep(3);
        $reagonArr = null;
        $reagonOK = null;
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfsharkDisConnectButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
        }
    }

    public function LogoutVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.surfshark.vpnclient.android/.app.feature.main.MainActivity");
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfSharkSettingsButton.png', null, 5, $reagonOK, $reagonArr)) {
            $this->autoObject->pythonUI->click($reagonOK);
            sleep(3);
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfSharkAccountButton.png', null, 5, $reagonOK, $reagonArr)) {
                $this->autoObject->pythonUI->click($reagonOK);
                sleep(3);
                if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfSharkLogoutButton.png', null, 5, $reagonOK, $reagonArr)) {
                    $this->autoObject->pythonUI->click($reagonOK);
                    if ($this->autoObject->pythonUI->findAndClick(IMG . 'SurfSharkLogoutWarning.png', null, 5, $reagonOK, $reagonArr)) {
                        $this->autoObject->pythonUI->click(IMG . 'SurfsharkLogoutWarningConfirmation.png');
                    }
                }
            }
        }
    }

    public function isVpnConnected()
    {
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

    public function getSurfSharkRandomRegion($vpn_region)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getSurfSharkRandomRegion/" . $vpn_region);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getSurfSharkRandomRegion: " . $output);
        $obj = json_decode($output);
        return $obj->{'region'};
    }
}
