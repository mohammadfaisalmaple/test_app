<?php
class ExpressVPN
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
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/ExpressVPN.apk");
    }
    public function LoginVPN($username, $PassWord)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonOK = null;
        $reagonArr = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.expressvpn.vpn/.ui.home.HomeActivity");
        if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNSignInButton.png", null, 7)) { //for latest version
            sleep(2);
            if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNSignInButton.png", null, 5)) {

                sleep(3);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 130 670");
                sleep(2);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input text " . $username);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
                if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNPassWordFelid.png", null, 5)) {

                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input text " . $PassWord);
                    sleep(2);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
                }
                if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNLoginConfirmation.png", null, 5)) {

                    sleep(2);
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNHelpToImprove.png", null, 5)) {
                        if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNNoThanksButton.png", null, 5)) {
                        }
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressSetupVPN.png", null, 5)) {
                        if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNConfirmSetUpVPNOKButton.png", null, 5)) {
                        }
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNConnectionWarning.png", null, 5)) {
                        $this->autoObject->pythonUI->click(IMG . "ExpressVPNConnectionWaringConfirm.png");
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNLoginSuccess.png", null, 5)) {
                        myLog("The Login Successfully Done");
                        $this->AlwaysOnVPN();
                        return true;
                    }
                }
            }
        } elseif ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNSignIn27.png", null, 5)) { //for the Old version
            if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNSignIn27.png", null, 5)) {

                sleep(1);
                if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNSignInE-mail27.png", null, 5)) {

                    sleep(1);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input text " . $username);
                }
                if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNSignInPassword27.png", null, 5)) {

                    sleep(1);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input text " . $PassWord);
                }
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
                if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNSignIn27LoginConfermation.png", null, 5)) {

                    sleep(2);
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNHelptoImprove27.png", null, 5)) {
                        if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNDeclineImprove27.png", null, 5)) {
                        }
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . "EpressVPNCreateVPN27.png", null, 5)) {
                        if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNConfirmSetupVPN27.png", null, 5)) {
                        }
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNConnectionWaring27.png", null, 5)) {
                        $this->autoObject->pythonUI->click(IMG . "ExpressVPNConnectionWaringConfirmation27.png");
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressLoginSuccssfullyIcon27.png", null, 5)) {
                        myLog("The Login Successfully Done");
                        $this->AlwaysOnVPN();
                        return true;
                    }
                }
            }
        } elseif ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNLoginSuccess.png", null, 5)) {
            myLog("The Login Successfully Done");
            return true;
        } elseif ($this->autoObject->pythonUI->exists(IMG . "ExpressLoginSuccssfullyIcon27.png", null, 5)) {
            myLog("The Login Successfully Done");
            return true;
        } else {
            myLog("The Login Doesn't Successfully Done");
            return false;
        }
    }
    public function AlwaysOnVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app com.expressvpn.vpn");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 1");
    }
    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonOK = null;
        $reagonArr = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.expressvpn.vpn/.ui.home.HomeActivity");
        sleep(2);
        if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNLoginSuccess.png", null, 5)) {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 920 1120");
            if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNMagnifire.png", null, 5)) {
            }
            if ($this->vpn_region != 'Unknown') {
                $random_vpn_region = $this->getExpressVpnRandomRegion();
            } else {
                $random_vpn_region = 'USA';
            }
            myLog("THE REAGON" . $random_vpn_region);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input text " . $random_vpn_region);
            sleep(2);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 530 360");
            sleep(5);
            if ($this->isVpnConnected()) {
                $this->AlwaysOnVPN();
            }
        } elseif ($this->autoObject->pythonUI->exists(IMG . "ExpressLoginSuccssfullyIcon27.png", null, 5)) {
            if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNOpenLocationButton27.png", null, 5)) {

                sleep(2);
                if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNSearchingmagnifireButton27.png", null, 5)) {

                    sleep(2);
                    if ($this->vpn_region != 'Unknown') {
                        $random_vpn_region = $this->getExpressVpnRandomRegion();
                    } else {
                        $random_vpn_region = 'USA';
                    }
                    $this->autoObject->pythonUI->type($random_vpn_region);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 550 370");
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressSetupVPN27.png", null, 5)) {
                        if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNConfirmSetupVPN27.png", null, 5)) {
                        }
                    }
                    if ($this->autoObject->pythonUI->exists(IMG . "ExpressVPNConnectionWaring27.png", null, 5)) {
                        if ($this->autoObject->pythonUI->findAndClick(IMG . "ExpressVPNConnectionWaringConfirmation27.png", null, 5)) {
                        }
                    }
                }
                sleep(5);
                if ($this->isVpnConnected()) {
                    $this->AlwaysOnVPN();
                }
            }
        } else {
            myLog("The Connection Doesn't Successfully Done");
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
    public function getExpressVpnRandomRegion()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getExpressVpnRandomRegion/" . $this->vpn_region);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getExpressVpnRandomRegion: " . $output);
        $obj = json_decode($output);
        return str_replace('_', '"\ "', $obj->{'region'});
    }
    public function DisconnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 450 0 450 1750 1000");
        sleep(3);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'ExpressVPNDisconnectButton.png', null, 5) || $this->autoObject->pythonUI->findAndClick(IMG . 'ExpressVPNDisconnectButton27.png', null, 5)) {

            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        }
    }
    public function LogoutVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonArr = null;
        $reagonOK = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.expressvpn.vpn/.ui.home.HomeActivity");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 90 170");
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'ExpressVPNSignoutButton.png', null, 5)) {

            sleep(2);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 750 1110");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        } elseif ($this->autoObject->pythonUI->findAndClick(IMG . 'ExpressVPNSignoutButton27.png', null, 5)) {

            sleep(2);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 750 1110");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        }
    }
    public function UninstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " uninstall com.expressvpn.vpn");
        myLog("The Application is Scussefully uninstalled");
    }
}
