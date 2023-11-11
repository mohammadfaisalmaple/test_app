<?php
class Checker
{
    public $emulatorPort     = null;
    public $emulatorName     = null;
    public $emulatorAlpha    = null;
    public $application      = null;
    public $androidID        = null;
    public $emulatorID       = null;
    public $automator        = null;
    public $vpnType          = null;
    public $asteriskHost     = null;
    public $_data            = null;
    public $emulatorBaseName = null;

    public function __construct($emulatorPort, $application, $emulatorBaseName, $emulatorID, $emulatorAlpha, $asteriskHost = null)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $emulatorID = $emulatorID == "0" ? "" : $emulatorID;
        $this->emulatorPort = $emulatorPort;
        $this->emulatorName = $emulatorBaseName . $emulatorID;
        $this->application = $application;
        $this->emulatorAlpha = $emulatorAlpha;
        $this->asteriskHost = $asteriskHost;
    }

    public function __destruct()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        //$this->cleanup();
        myLog("Destroying Checker Class");
    }

    public function cleanup()
    {

        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        foreach (get_class_vars(__CLASS__) as $clsVar => $_) {
            unset($this->$clsVar);
        }

        //cleanup all objects inside data array
        if (is_array($this->_data)) {
            foreach ($this->_data as $value) {
                if (is_object($value) && method_exists($value, 'cleanUp')) {
                    $value->cleanUp();
                }
            }
        }
    }

    public function addPythonUIObject($autoObject)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->automator = $autoObject;
    }

    public function AdbStartupPermissions()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        if (!$this->_checkAVDIsBootCompleted()) {
            myLog("AVD is not booted");
            return false;
        }
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global heads_up_notifications_enabled 0");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content insert --uri content://settings/system --bind name:s:show_touches --bind value:i:1");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put system pointer_location 1");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global window_animation_scale 0");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global transition_animation_scale 0");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global animator_duration_scale 0");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put system font_scale 1.3");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global wifi_on 1");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm grant com.android.contacts android.permission.READ_CONTACTS");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm grant com.android.contacts android.permission.WRITE_CONTACTS");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm grant com.android.contacts android.permission.GET_ACCOUNTS");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm grant com.android.contacts android.permission.CALL_PHONE");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm grant com.android.contacts android.permission.READ_PHONE_STATE");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm grant com.android.contacts android.permission.READ_EXTERNAL_STORAGE");
        sleep(1);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell wm density 540");
        sleep(1);
        return true;
    }
    public function checkWhatsappPermissions()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $cmd = ADB . " emulator-" . $this->emulatorPort . " shell dumpsys package com.whatsapp | grep granted=false";
        myLog("THE REQUESTED COMMAND IN " . __FUNCTION__ . " >>>>  IN " . __CLASS__ . " IS : " . $cmd);
        $result = shell_exec($cmd);
        if (!empty(trim($result))) {
            myLog("TRY TO KNOW WHERE IS THE APK FILE IN EMULATOR STORAGE");
            $cmdOfSearchingOfApkInSabyStroage = ADB . " emulator-" . $this->emulatorPort . " shell \"find /data/app -name 'com.whatsapp-*'\"";
            myLog("THE REQUESTED FOR SEARCHING COMMAND IN " . __FUNCTION__ . " >>>>  IN " . __CLASS__ . " IS : " . $cmdOfSearchingOfApkInSabyStroage);
            $resultOfSearchingOfApkInSabyStroage = shell_exec($cmd);
            $resultOfSearchingOfApkInSabyStroage = trim($resultOfSearchingOfApkInSabyStroage);
            $cmdOfPullingData = ADB . " emulator-" . $this->emulatorPort . " pull " . $resultOfSearchingOfApkInSabyStroage . "/base.apk /home/" . get_current_user() . "/Downloads/WA-" . $this->emulatorAlpha . ".apk";
            myLog("THE REQUESTED FOR PULLING COMMAND IN " . __FUNCTION__ . " >>>>  IN " . __CLASS__ . " IS : " . $cmdOfPullingData);
            shell_exec($cmdOfPullingData);
            $cmdForInstallWhatsapp = ADB . " emulator-" . $this->emulatorPort . " install -g -r " . "/home/" . get_current_user() . "/Downloads/WA-" . $this->emulatorAlpha . ".apk";
            myLog("THE REQUESTED FOR INSTALL WHATSAPP COMMAND IN " . __FUNCTION__ . " >>>>  IN " . __CLASS__ . " IS : " . $cmdForInstallWhatsapp);
            shell_exec($cmdForInstallWhatsapp);
            shell_exec($cmdForInstallWhatsapp);
            sleep(5);
        }
    }
    public function getEmulatorAndroidID()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->_checkAVDIsRoot();
        $android_id = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings get secure android_id");
        $this->androidID = trim(preg_replace('/\s+/', '', $android_id));
        myLog($this->emulatorName . ' / ' . $this->emulatorAlpha . ' / Android ID :' . $this->androidID);
        return $this->androidID;
    }

    public function stopAVD()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec('nohup ~/7eet-saby-whatsapp-ubuntu20/scripts/avd_stop.sh &');
    }

    public function startAVD()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec('nohup ~/7eet-saby-whatsapp-ubuntu20/scripts/avd_start_by_name.sh ' . $this->emulatorName . ' & ');
        if (!$this->_checkAVDIsBootCompleted()) {
            myLog("AVD is not booted");
            return false;
        }
    }

    public function isScreenBlack()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        if (!$this->automator->pythonUI->exists(IMG . 'ScreenNotBlack.png', null, 2)) {
            $this->rebootAVD();
        }
        return true;
    }

    public function checkSystemUI()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->_checkAVDIsRoot();
        if ($this->automator->pythonUI->exists(IMG . 'SystemUiNotResponding.png', null, 2)) {
            $this->automator->pythonUI->findAndClick(IMG . 'CloseSystemUiNotRespoding.png', null, 2);
            if (!$this->_checkAVDIsBootCompleted()) {
                myLog("AVD is not booted");
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function rebootAVD()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " reboot");
        if (!$this->_checkAVDIsBootCompleted()) {
            myLog("AVD is not booted");
            return false;
        }
        return true;
    }

    public function checkPythonUI()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function CheckPulseAudio()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        if (($this->_countPulseAudio() != 1) || (!$this->_isPulseAudioOK())) {
            myLog("error: Pulseaudio, we need to restart all..");
            $this->stopAVD();
            //stopLinphone();
            $this->_stopPulseAudio();
            sleep(10);
            $this->startAVD();
            sleep(30);
        } else {
            myLog("Looks Good we have Single Pulseaudio and its working Fine");
        }
    }

    public function CheckLinphone()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $linphone = false;
        $tryCount = 0;
        do {
            myLog('Stopping Linphone');
            $this->_stopLinphone();
            sleep(2);
            $sipStatus = $this->_putSipInReadyState();
            myLog('sipStatus: ' . $sipStatus);
            if (!$sipStatus) {
                myLog('linphone couldnt register, Will Try Again');
                $tryCount++;
            } else {
                myLog('linphone registered succsefully, continue');
                $linphone = true;
            }
        } while (!$linphone && $tryCount < 10);
        return $linphone;
    }

    public function isApplicationInstalled()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $tryCount = 0;
        do {
            $this->_isSystemNotResponding();
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Main");
            myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Main");
            sleep(5);
            $currentScreenActivity = shell_exec(ADB . ' -s emulator-' . $this->emulatorPort . ' shell dumpsys activity activities | grep "mResumedActivity"');
            myLog("Current Activity : " . $currentScreenActivity);
            $isWhatsappActivity = substr_count($currentScreenActivity, $this->application) > 0;
            $tryCount++;
            $this->automator->dumpScreenToFile();
            if ($this->automator->searchStringInDumpScreen("Verify phone number")) {
                list($xCoordinate, $yCoordinate) = $this->automator->SearchCoordinatesByClass("android.widget.ImageButton");
                if ($xCoordinate > 0 && $yCoordinate > 0) {
                    $this->automator->click($xCoordinate, $yCoordinate);
                }
            }
        } while ($tryCount < 5 && !$isWhatsappActivity);
        if (!$isWhatsappActivity) {
            myLog("No " . $this->application . " Found !!");
            return false;
        } else {
            return true;
        }
    }

    public function updateApplicationRequired()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        if ($this->automator->pythonUI->exists(IMG . 'UpdateWhatsapp.png', null, 3) || $this->automator->pythonUI->exists(IMG . 'UpdateWhatsapp2.png', null, 3)) {
            myLog("Update Required Will Do Now...");
            return true;
        } else {
            myLog("No Update Required");
            return false;
        }
    }

    public function isApplicationStillActive()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        do {
            $currentScreenActivity = shell_exec(ADB . ' -s emulator-' . $this->emulatorPort . ' shell dumpsys activity activities | grep "mResumedActivity"');
            $isWhatsappActivity = substr_count($currentScreenActivity, $this->application) > 0;
            if (!$isWhatsappActivity) {
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Main");
            }
            myLog("Request Sceen Dump");
            $onscreen = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell uiautomator dump && " . ADB . " -s emulator-" . $this->emulatorPort . " shell cat /sdcard/window_dump.xml");
        } while (substr_count($onscreen, 'ERROR: could not get idle state.') > 0);
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell uiautomator dump && " . ADB . " -s emulator-" . $this->emulatorPort . " shell cat /sdcard/window_dump.xml");
        //check isDeactivated?
        myLog("Prepare deactivate variables");
        $deactivated4Img = false;
        $deactivated9Img = false;
        $deactivated1 = substr_count($onscreen, "You have a custom ROM installed. Custom ROMs can cause problems with WhatsApp Messenger and are unsupported by our customer service team.") > 0;
        $deactivated2 = substr_count($onscreen, "AGREE AND CONTINUE") > 0;
        $deactivated3 = substr_count($onscreen, "If you want to restore chats history from WhatsApp, make sure you made a backup from your messages, then you can restore it by pressing the button below, then put your number and press (Next).") > 0;
        $deactivated4 = substr_count($onscreen, "detect an SMS") > 0;
        $deactivated5 = substr_count($onscreen, "is banned from using WhatsApp") > 0;
        $deactivated6 = substr_count($onscreen, "Wrong number?") > 0;
        $deactivated7 = substr_count($onscreen, "You have guessed too many times.&#10;&#10;Please check with your mobile provider that you can receive SMS and phone calls.&#10;&#10;Please wait for a new code to be sent.&#10;&#10;Try again after -1 seconds.") > 0;
        $deactivated8 = substr_count($onscreen, "Your phone number is no longer registered with WhatsApp on this phone. This might be because you registered it on another phone.&#10;&#10;If you didn't do this, verify your phone number to log back into your account.") > 0;
        $deactivated9 = substr_count($onscreen, "Please provide your name and an optional profile photo") > 0;
        $deactivated10 = substr_count($onscreen, "This account is not allowed to use WhatsApp due to spam") > 0 || substr_count($onscreen, "Something went wrong. You'll need to verify your account again.");
        $deactivated11 = substr_count($onscreen, "This account is not allowed to use WhatsApp");
        $keepStoping    = substr_count($onscreen, "WhatsApp keeps stopping") > 0;
        $restoredAccount    = substr_count($onscreen, "This account has been restored") > 0;
        $restoredChats    = substr_count($onscreen, "Restore chat history") > 0;
        $bandTemmporarily    = $this->automator->pythonUI->exists(IMG . 'whatsappTemporarilyBanded.png', null, 5);
        if ($this->automator->pythonUI->exists(IMG . 'wa_waiting_sms.png', null, 5) || $this->automator->pythonUI->exists(IMG . 'wa_wrong_number.png', null, 5) || $this->automator->pythonUI->exists(IMG . 'WhatsappTwoStepVarifications.png', null, 5)) {
            $deactivated4Img = true;
        }
        if ($this->automator->pythonUI->exists(IMG . 'WhatsappUnableToConnect.png', null, 5) || $this->automator->pythonUI->exists(IMG . 'wa_your_name.png', null, 5)) {
            $deactivated9Img = true;
        }

        myLog("Checking deactivate variables");
        if ($deactivated9 || $deactivated9Img) {
            myLog($this->application . "Activation Not Completed " . $this->application . " Waiting Name");
            $activationStatus = '3';
        } elseif ($deactivated1 || $deactivated2 || $deactivated3 || $deactivated4 || $deactivated4Img || $deactivated5 || $deactivated6 || $deactivated7) {
            myLog($this->application . "Not Actived or Activation Not Completed");
            $activationStatus = '2';
            $this->_destroySabyVpn();
        } elseif ($deactivated8) {
            myLog($this->application . "Deactivated");
            $activationStatus = '1';
        } elseif ($deactivated10) {
            myLog($this->application . "SPAM ACCOUNT");
            $activationStatus = '4';
            $this->_destroySabyVpn();
        } elseif ($deactivated11) {
            myLog($this->application . "NOT ALLOW");
            $activationStatus = '6';
            $this->_destroySabyVpn();
        } elseif ($keepStoping) {
            myLog($this->application . " WhatsApp keeps stopping");
            $activationStatus = '5';
        } elseif ($restoredAccount) {
            myLog($this->application . " WhatsApp RESTORD ACCOUNT");
            $activationStatus = '7';
        } elseif ($bandTemmporarily) {
            myLog($this->application . " WhatsApp Temporarily Banded");
            $activationStatus = '8';
        } elseif ($restoredChats) {
            myLog($this->application . " WhatsApp Restored Chats");
            $activationStatus = '9';
        } else {
            myLog($this->application . " Still Active");
            $activationStatus = '0';
        }
        unset($currentScreenActivity, $isWhatsappActivity, $onscreen, $deactivated4Img, $deactivated1, $deactivated2, $deactivated3, $deactivated4, $deactivated5, $deactivated6, $deactivated7, $deactivated8, $deactivated9, $deactivated4Img, $deactivated9Img);
        return $activationStatus;
    }

    public function getVpnType()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $vpnTypes = array('com.surfshark.vpnclient.android', 'com.wireguard.android', 'com.expressvpn.vpn', 'com.ixolit.ipvanish', 'hotspotshield.android.vpn', 'com.strongvpn', 'com.gaditek.purevpnics');
        $vpnTypeFound = '';
        $loop = 0;
        while ($loop < count($vpnTypes)) {
            $search = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell find /data/data/" . $vpnTypes[$loop] . " 2>&1 &");
            $NotFound = substr_count($search, "No such file or directory");
            myLog($vpnTypes[$loop] . " : " . $NotFound);
            if ($NotFound == 0) {
                $vpnTypeFound = $vpnTypes[$loop];
                break;
            }
            $loop++;
        }
        $this->vpnType = $vpnTypeFound;
        myLog("This Saby Configured with VPN : " . $this->vpnType);
        switch ($this->vpnType) {
            case 'com.surfshark.vpnclient.android';
                $vpnReturnValue = 'Surfshark';
                break;
            case 'com.wireguard.android';
                $vpnReturnValue = 'WireGuard';
                break;
            case 'com.expressvpn.vpn';
                $vpnReturnValue = 'ExpressVPN';
                break;
            case 'com.ixolit.ipvanish';
                $vpnReturnValue = 'PIA';
                break;
            case 'hotspotshield.android.vpn';
                $vpnReturnValue = 'HotSpot';
                break;
            case 'com.strongvpn';
                $vpnReturnValue = 'Strong';
                break;
            case 'com.gaditek.purevpnics';
                $vpnReturnValue = 'PureVpn';
                break;
        }
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop " . $this->vpnType);
        unset($vpnTypes, $vpnTypeFound, $NotFound);
        return $vpnReturnValue;
    }

    private function _destroySabyVpn()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        switch ($this->vpnType) {
            case 'com.surfshark.vpnclient.android':
                $this->automator->destroySurfshark();
                break;
            case 'com.ixolit.ipvanish':
                $this->automator->destroyPIA();
                break;
            case 'com.wireguard.android':
                $this->automator->destroyWireGuard();
                break;
            case 'com.expressvpn.vpn':
                $this->automator->destroyExpressVpn();
                break;
            case 'hotspotshield.android.vpn':
            case 'com.strongvpn':
            case 'com.gaditek.purevpnics';
            case 'PureVpn':
                //$this->automator->destroyHotSpot();
                break;
        }
    }

    public function ReinstallApp()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " install -r " . "/home/" . get_current_user() . "/Downloads/WAORG.apk");
    }

    public function rebootAVDByUI()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $cmd = dirname(__DIR__) . '/pythonScripts/rebootAvdByUI.py';
        $result = shell_exec($cmd);
    }

    private function _checkAVDIsBootCompleted()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $tryCounter = 0;
        do {
            if (!$this->_checkAVDIsRoot()) {
                $this->rebootAVD();
                sleep(10);
            } else {
                break;
            }
            $tryCounter++;
        } while ($tryCounter <= 5);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " wait-for-device");
        $tryingCount = 0;
        do {
            myLog("Checking if AVD is ready and booted Try " . $tryingCount);
            $boot_completed = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell getprop sys.boot_completed");
            myLog("boot_completed :" . $boot_completed);
            sleep(2);
            $tryingCount++;
        } while (!(substr_count($boot_completed, "1") > 0) && $tryingCount < 75);
        $result = $tryingCount < 75;
        if ($result) {
            sleep(10);
            $this->_checkAVDIsRoot();
        }
        unset($tryingCount, $boot_completed);
        return $result;
    }

    public function detectAirPlaneModeStatus()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $result = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings get global airplane_mode_on");
        $returnValue = $result > 0;
        myLog('Leaving ' . __FUNCTION__ . ' >>> ' . ' >>> ' . $returnValue);
        unset($result);
        return $returnValue;
    }

    private function _checkAVDIsRoot()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $rooted = "";
        $tryingCount = 0;
        myLog("THE COUNTER IN " . __FUNCTION__ . " IS " . $tryingCount);
        do {
            myLog("Checking if AVD is rooted Try " . $tryingCount);
            $rooted = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " root");
            sleep(2);
            myLog("rooted :" . $rooted);
            $tryingCount++;
        } while (!(substr_count($rooted, "adbd is already running as root") > 0) && $tryingCount < 20);
        return substr_count($rooted, "adbd is already running as root");
        unset($rooted, $tryingCount);
    }

    private function _countPulseAudio()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $output = shell_exec('pgrep -u${USER} pulseaudio|wc -l');
        return $output;
    }

    private function _stopPulseAudio()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec('killall -u ${USER} pulseaudio');
    }

    private function _isPulseAudioOK()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $output = shell_exec('pactl list|wc -l');
        myLog("isPulseAudioOK() list: " . $output);
        if ($output <= 1) {
            unset($output);
            return false;
        }
        unset($output);
        return true;
    }

    public function openNextEmulator($totalEmulators)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec('pkill -u ${USER} -9 qemu');
        $totalEmulators = $totalEmulators - 1;
        $emulatorsArray = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        if ($this->emulatorID == $totalEmulators) {
            $this->emulatorID = 0;
            $this->emulatorName = $this->emulatorBaseName;
        } else {
            $this->emulatorID = $this->emulatorID + 1;
            $this->emulatorName = $this->emulatorBaseName . $this->emulatorID;
        }
        $this->emulatorAlpha = $emulatorsArray[$this->emulatorID];
        sleep(10);
        for ($loop = 0; $loop <= $totalEmulators; $loop++) {
            $folderIndex =  $loop == 0 ? '' : $loop;
            exec('rm -f ${HOME}/.android/avd/' . $this->emulatorBaseName . $folderIndex . '.avd/*lock');
        }

        exec("~/Android/Sdk/emulator/emulator @" . $this->emulatorName . " -no-snapshot -no-snapshot-load -no-snapshot-save -no-snapstorage -no-boot-anim -camera-back emulated -camera-front emulated -gpu host -qemu -allow-host-audio > ~/avd.log 2>&1 &");
        sleep(10);
        $this->isAvdOnScreen();
        if (!$this->_checkAVDIsBootCompleted()) {
            myLog("AVD is not booted");
            return false;
        }
        $this->isAvdOnScreen();
        unset($loop, $emulatorsArray, $folderIndex, $totalEmulators);
        return array($this->emulatorAlpha, $this->emulatorPort);
    }

    public  function  start_mix_sip(){
        $mix = '~/7eet-saby-whatsapp-ubuntu20/scripts/audio_mix_sip.sh';
        exec($mix);
    }
    private function _stopLinphone()
    {
        myLog('FIRST MIX');
        $mix = '~/7eet-saby-whatsapp-ubuntu20/scripts/audio_mix_sip.sh';
        shell_exec($mix);
        sleep(1);
        $reset = '~/7eet-saby-whatsapp-ubuntu20/scripts/audio_mix_reset.sh';
        shell_exec($reset);
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec('linphonecsh exit');
        exec('pkill -u ${USER} -9 linphonec');
     
    }

    private function _putSipInReadyState()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $cmd = '~/7eet-saby-whatsapp-ubuntu20/scripts/sipme.sh ' . $this->asteriskHost;
        $output = shell_exec("$cmd 2>&1");
        myLog($output);
        $findme = "identity=sip";
        $pos = strpos($output, $findme);
        if ($pos == false) {
            myLog("error. sip registstration failed, must exit");
            unset($cmd, $output, $findme, $pos);
            return false;
        } else {
            // sip registration worked - found the string identity=sip in the output,
            myLog("sip registstration worked, no errors");
            unset($cmd, $output, $findme, $pos);
            return true;
        }
    }

    public function isVpnConnected()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $vpnStatus = shell_exec(ADB . ' -s emulator-' . $this->emulatorPort . ' shell dumpsys connectivity | grep "type: VPN"');
        $vpnConnceted = substr_count($vpnStatus, "type: VPN[], state: CONNECTED/CONNECTED") > 0;
        if ($vpnConnceted) {
            myLog("VPN Connected");
            unset($vpnStatus, $vpnConnceted);
            return true;
        } else {
            myLog("VPN Not Connected");
            unset($vpnStatus, $vpnConnceted);
            return false;
        }
        return $vpnConnceted;
    }

    public function isWifiConnected()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $wifiStatus = shell_exec(ADB . ' -s emulator-' . $this->emulatorPort . ' shell dumpsys connectivity | grep "type: WIFI"');
        $WifiConnceted = substr_count($wifiStatus, "type: WIFI[], state: CONNECTED/CONNECTED") > 0;
        if ($WifiConnceted) {
            unset($WifiConnceted, $wifiStatus);
            return true;
        } else {
            unset($WifiConnceted, $wifiStatus);
            return false;
        }
    }

    public function isInternetWorking()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
        $url = array('http://www.google.com', 'http://www.whatsapp.com', 'https://www.microsoft.com', 'https://www.yandex.com');
        $internet = true;
        for ($loop = 0; $loop <= 3; $loop++) {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url[$loop] . "'");
            sleep(10);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url[$loop] . "'");
            sleep(10);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url[$loop] . "'");
            sleep(10);
            $opened = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell uiautomator dump && " . ADB . " -s emulator-" . $this->emulatorPort . " shell cat /sdcard/window_dump.xml");
            $notFoundString = substr_count($opened, "Webpage not available");
            myLog("Internet Status : " . $notFoundString);
            if ($notFoundString > 0) {
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
                $internet = false;
                myLog("Internet Status : " . $internet);
            } else {
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
                $internet = true;
            }
        }
        myLog("Final Internet Status : " . $internet);
        unset($url, $opened, $notFoundString);
        return $internet;
    }

    public function _isSystemNotResponding()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->automator->dumpScreenToFile();
        if ($this->automator->searchStringInDumpScreen("System UI isn't responding") || $this->automator->searchStringInDumpScreen("Quickstep isn't responding")) {
            list($xCoordinate, $yCoordinate) = $this->automator->SearchCoordinatesByString('Close app');
            if ($xCoordinate > 0 && $yCoordinate > 0) {
                $this->automator->click($xCoordinate, $yCoordinate);
                $this->rebootAVD();
                return true;
            }
        }
        myLog('Leaving ' . __FUNCTION__ . ' >>> ');
        return false;
    }

    public function isInternetWorkingNew($apiManager)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $internet = false;
        $tryCount = 0;
        do {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm clear org.chromium.webview_shell");
            sleep(2);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d '$apiManager/apk/internetTest.html'");
            $this->_isSystemNotResponding();
            if ($this->automator->pythonUI->exists(IMG . 'InternetWorking.png', null, 50)) {
                $internet = true;
                myLog("Final Internet Status : Working");
            } else {
                myLog("Final Internet Status : NotWorking");
            }
            $tryCount++;
        } while (!$internet && $tryCount < 5);

        if($internet === false){
            $this->automator->dumpScreenToFile();
            list($xCoordinate, $yCoordinate) = $this->automator->SearchCoordinatesByString("INTERNET IS WORKING FINE");
            if ($xCoordinate > 0 && $yCoordinate > 0) {
                myLog("i am in xml check INTERNET");
                $internet=true;
            }
        }
        unset($tryCount);
        return $internet;
    }

    public function lookForVoiceCall()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->_checkAVDIsRoot();
        $call_count_xml = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell cat data/data/com.whatsapp/shared_prefs/com.whatsapp_preferences_light.xml | grep -E 'name=\"call_confirmation_dialog_count\"'| awk '{print $3}'");
        $call_count = (int) filter_var($call_count_xml, FILTER_SANITIZE_NUMBER_INT);
        return ($call_count < 5);
    }

    public function isAvdOnScreen()
    {
        $this->_checkAVDIsRoot();
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $emulatorDetails = trim(shell_exec('~/7eet-saby-whatsapp-ubuntu20/scripts/get_avd_port_name.sh'));
        myLog("THE RESULT OF " . __FUNCTION__ . " >>>>> " . $emulatorDetails);
        // if emulator not on screen or not running script will return 0
        if ($emulatorDetails == '0') {
            myLog("Avd Not On Screen : ");
            unset($emulatorDetails);
            return false;
        }
        $emulatorDetailsArray = explode(',', $emulatorDetails);

        // emulator-port stored in $emulatorDetailsArray[1];
        if ($this->emulatorPort != $emulatorDetailsArray[1]) {
            $this->emulatorPort = $emulatorDetailsArray[1];
            myLog("Avd Port Changed to : " . $this->emulatorPort);
        }
        unset($emulatorDetails, $emulatorDetailsArray);
        return true;
    }

    public function memory_usage()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $mem_usage = memory_get_peak_usage();

        if ($mem_usage < 1024) {
            $return = $mem_usage . " bytes";
        } elseif ($mem_usage < 1048576) {
            $return = round($mem_usage / 1024, 2) . " kilobytes";
        } else {
            $return = round($mem_usage / 1048576, 2) . " megabytes";
        }
        myLog("Memory Usage : " . $return);
        unset($mem_usage, $return);
    }

    public function RejectCall()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $iscall = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell dumpsys notification | grep -E Decline");
        if (strlen($iscall) > 0) {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 162 1477 162 1000");
        }
        unset($iscall);
    }

    public function restartEmulatorWithPhoneNumber($phoneNumber)
    {
        /*myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec('pkill -u ${USER} -9 qemu');
        exec('rm -f ${HOME}/.android/avd/GB.avd/*lock');
        exec('rm -f ${HOME}/.android/avd/GB1.avd/*lock');
        exec('rm -f ${HOME}/.android/avd/GB2.avd/*lock');
        sleep(10);
        exec("~/Android/Sdk/emulator/emulator @" . $this->emulatorName . " -port $this->emulatorPort -no-snapshot -no-snapshot-load -no-snapshot-save -no-snapstorage -no-boot-anim -camera-back emulated -camera-front emulated -gpu host -phone-number " . $phoneNumber . " -qemu -allow-host-audio > ~/avd.log 2>&1 &");
        sleep(10);
        $this->isAvdOnScreen();
        if (!$this->_checkAVDIsBootCompleted()) {
            myLog("AVD is not booted");
            unset($phoneNumber);
            return false;
        }*/
        unset($phoneNumber);
        return $this->emulatorPort;
    }


    public function AdbUpdateGsmProp($mccmnc, $iso, $network)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        if (!$this->_checkAVDIsBootCompleted()) {
            myLog("AVD is not booted");
            unset($mccmnc, $iso, $network);
            return false;
        }
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.sim.operator.alpha '" . $network . "'");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.operator.alpha '" . $network . "'");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.sim.operator.iso-country " . $iso);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.operator.iso-country " . $iso);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.sim.operator.numeric " . $mccmnc);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.operator.numeric " . $mccmnc);

        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.sim.operator.alpha '" . $network . "'");
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.operator.alpha '" . $network . "'");
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.sim.operator.iso-country " . $iso);
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.operator.iso-country " . $iso);
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.sim.operator.numeric " . $mccmnc);
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell setprop gsm.operator.numeric " . $mccmnc);
        unset($mccmnc, $iso, $network);
        return true;
    }

    public function setAvdName($name, $phoneNumber)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $name = str_replace('\ ', ' ', $name);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global device_name '" . $name . "'");
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global device_name '" . $name . "'");
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global phone_number '" . $phoneNumber . "'");
        //myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell settings put global phone_number '" . $phoneNumber . "'");
        unset($name, $phoneNumber);
        return true;
    }

    public function setSabyVPNIp($ENV_IP, $sabyId)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm clear org.chromium.webview_shell");
        sleep(2);
        $url = "http://" . $ENV_IP . "/MapleVoipFilter/apiManager/setsabyVPNIp/" . $sabyId;
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url . "'");
        sleep(2);
        unset($url, $sabyId);
    }

    public function proxmoxIpDetecter($controlCenterApi)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm clear org.chromium.webview_shell");
        sleep(2);
        $url = $controlCenterApi . "/whatIsMyIp";
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url . "'");
        sleep(5);

        $onscreen = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell uiautomator dump && " . ADB . " -s emulator-" . $this->emulatorPort . " shell cat /sdcard/window_dump.xml");
        sleep(5);
        $onscreen = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell uiautomator dump && " . ADB . " -s emulator-" . $this->emulatorPort . " shell cat /sdcard/window_dump.xml");
        if (!empty($onscreen)) {
            $vpnIp = get_between_data($onscreen, '^^^^', '^^^^');
            switch ($vpnIp) {
                case '65.21.233.12':
                case '162.55.137.134':
                case '135.181.183.250':
                case '135.181.222.118':
                case '135.181.209.39':
                case '65.21.76.117':
                case '65.108.123.53':
                    return false;
                    break;
            }
        }
        return true;
    }


    public function check_register_saby_behavior($transporter){

        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.whatsapp");
        sleep(3);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com.whatsapp/.Main");
        sleep(3);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start  -n com.whatsapp/.profile.ProfileInfoActivity");
        sleep(5);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 990 930 990 353 500");
        sleep(5);
        $profileDumpScreen = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " exec-out uiautomator dump /dev/tty");
        /*Find Number in Whatsapp*/
        $findNumber = strpos($profileDumpScreen, '+');
        $WaNumber   = substr($profileDumpScreen, $findNumber, 21);
        //remove anything that doesn't contain a number
        $WaNumber       = preg_replace("/[^0-9]/", "", $WaNumber);
        myLog("My Whatsapp Number is : " . $WaNumber);


        /*Find Name in Whatsapp*/
        $beforeName = 'bounds="[243,721][406,803]" /><node index="1" text="';
        //get the position before the name
        $beforeNamePosition = strpos($profileDumpScreen, $beforeName);
        $nameStartPosition  = strlen($beforeName) + $beforeNamePosition;
        //get the position after the name
        $afterName         = '" resource-id="com.whatsapp:id/profile_settings_row_subtext';
        $afterNamePosition = strpos($profileDumpScreen, $afterName);
        //claculate the Name exact Position
        $nameEndPosition = $afterNamePosition - $nameStartPosition;
        $WaName          = substr($profileDumpScreen, $nameStartPosition, $nameEndPosition);
        $this->waName    = $WaName;
        myLog("My Whatsapp Name is : " . $WaName);
        myLog("Checking Whatsapp Number Details: " . $WaNumber);
        if(strlen($WaNumber) > 5){
            $transporter->setSabyEmulatorActivationInfo('sim_number', $WaNumber);
            $transporter->update_saby_number($WaNumber);
            $transporter->registerSabyInBehvaior($WaName,$WaNumber);
        }else{
            echo "error when get number get number is : ".$WaNumber;
        }
        sleep(2);
        exec(ADB . " -s emulator-" . $auto->emulatorPort . " shell am start -n com.whatsapp/.Main");

    }
}
