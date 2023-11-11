<?php
class HotSpotVPN
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
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -g " . dirname(__DIR__) . "/apk/Hotspot.apk");
        myLog("The Application is Scussefully Installed");
    }

    public function UninstallVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " uninstall hotspotshield.android.vpn");
        myLog("The Application is Scussefully uninstalled");
    }

    public function LoginVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonOK = null;
        $reagonArr = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n hotspotshield.android.vpn/com.anchorfree.hotspotshield.ui.HssActivity");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell inpur swipe 550 1400 550 400  1000");
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'HotspotSignInButton.png', null, 5)) {
        }
    }

    public function ConnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonOK = null;
        $reagonArr = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n hotspotshield.android.vpn/com.anchorfree.hotspotshield.ui.HssActivity");
        sleep(3);
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'HotspotOpenMenuButton.png', null, 5)) {

            if ($this->autoObject->pythonUI->findAndClick(IMG . 'HotspotMagnifireButton.png', null, 5)) {

                sleep(2);
                if ($this->vpn_region != 'Unknown') {
                    $random_vpn_region = $this->getHotSpotRandomRegion();
                } else {
                    $random_vpn_region = 'USA';
                }
                $this->autoObject->pythonUI->type($random_vpn_region);
                sleep(3);
                $profilePicture = $this->autoObject->pythonUI->createLocation(257, 210);
                $this->autoObject->pythonUI->click($profilePicture);
                if ($this->isVpnConnected()) {
                    $this->AlwaysOnVPN();
                }
            }
        }
    }

    public function getHotSpotRandomRegion()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://cc-api.7eet.net/getHotSpotRandomRegion/" . $this->vpn_region);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getHotSpotRandomRegion: " . $output);
        $obj = json_decode($output);
        return $obj->{'region'};
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

    public function AlwaysOnVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_app hotspotshield.android.vpn");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put secure always_on_vpn_lockdown 1");
    }

    public function DisconnectVPN()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $reagonOK = null;
        $reagonArr = null;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n hotspotshield.android.vpn/com.anchorfree.hotspotshield.ui.HssActivity");
        if ($this->autoObject->pythonUI->findAndClick(IMG . 'HotspotDisconnectVPN.png', null, 5)) {
        }
    }
}
