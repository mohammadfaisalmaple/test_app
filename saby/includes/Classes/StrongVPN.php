<?php
class StrongVpn
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

    public function LoginVPN($username, $password)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $regionOK = null;
        $regionArr = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.strongvpn/.app.presentation.features.login.LoginActivity");
        sleep(3);
        if ($this->autoObject->pythonUI->exists(IMG . 'StrongVPNWelcomeIcon.png', null, 5)) {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 340 1000");
            $this->autoObject->pythonUI->type($username);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            if ($this->pythonUI->findAndClick(IMG . 'StrongPasswordfelid.png', null, 1, $regionOK, $regionArr)) {
                $this->autoObject->pythonUI->click($regionOK);
                sleep(2);
                $this->autoObject->pythonUI->type($password);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            }
            if ($this->autoObject->pythonUI->findAndClick(IMG . 'StrongLoginButton.png', null, 1, $regionOK, $regionArr)) {
                $this->autoObject->pythonUI->click($regionOK);
            }
        }
    }

    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function addPythonUIObject($automator)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->autoObject = $automator;
    }

    public function InstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/StrongVPN.apk");
        myLog("The Application is Scussefully Installed");
        return true;
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

    public function getStrongRandomRegion()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getStrongRandomRegion/" . $this->vpn_region);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getStrongRandomRegion: " . $output);
        $obj = json_decode($output);
        return $obj->{'region'};
    }
}
