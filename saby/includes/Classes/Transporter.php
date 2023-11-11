<?php
class Transporter_New_Ma3llem
{
    public $saby = null;
    public $sabyServer = null;
    public $sabyFullName = null;
    public $waNumber = null;
    public $waName = null;
    public $vpnType = null;
    public $vpnRegion = null;
    public $emulatorPort = null;
    public $androidID = null;
    public $state = null;
    public $stateExtra = null;
    public $apiManager = null;
    public $controlCenterApi = null;
    public $controlCenterApiNew = null;
    public $managerIp = null;
    public $uuid = null;
    public $filterApi = null;
    public $sabyInFilterId = null;
    public $sabyId = null;
    public $behavior_api = null;
    public $prodName = null;

    public function __construct($saby, $sabyFullName, $emulatorPort, $androidId, $newm3allem, $newM3allemIp)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->saby = $saby;
        $this->sabyServer = trim(gethostname());
        $this->sabyFullName = $sabyFullName;
        $this->emulatorPort = $emulatorPort;
        $this->androidID = $androidId;
        $this->apiManager = $newm3allem;
        $this->managerIp = $newM3allemIp;
        $this->controlCenterApi = "http://cc-api.7eet.net";
        $this->controlCenterApiNew = "http://65.108.105.115/MapleVoipSocket/apiManager";
        $this->uuid = file_get_contents('/proc/sys/kernel/random/uuid');
        $this->setBehaviorApi("http://filter.7eet.net/whatsapp_behaviour_api");
        unset($saby, $sabyFullName, $emulatorPort, $androidId, $newm3allem);
    }


    public function __destruct()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        myLog("Destroying Transporter Class");
    }
    public function setProdname($prod_name)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->prodName = $prod_name;
        $this->add_prod_to_whatsapp_behaviour();
    }
    public function setAndroidID($android_id)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->androidID = $android_id;
    }

    public function add_prod_to_whatsapp_behaviour(){

        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->behavior_api . "/update_new_whatsapp_behaviour/" . urlencode($this->sabyFullName)  . '/' . urlencode($this->prodName);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $output, $countryName, $number);
    }
    public function setSabyEmulatorState($state, $stateExtra = null)
    {
        $ch = curl_init();
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $this->state = $state;
        $this->stateExtra = $stateExtra;
        $stateExtra = strtoupper($stateExtra);
        $url = $this->apiManager . "/setSabyEmulatorState/" . urlencode($this->sabyId) . '/' . urlencode($this->state) . '/' . urlencode($this->stateExtra);
        myLog("THE URL REQUESTED --> " . __FUNCTION__ . "  " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        curl_close($ch);
        if ($obj->id != $this->sabyId) {
            $this->setSabyEmulatorState($state, $stateExtra);
        }
        myLog("The result of in " . __FUNCTION__ . " >>>>>>" . $output);
        unset($state, $stateExtra, $ch, $url, $obj, $output);
    }

    public function getSabyFilterId()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        return $this->sabyInFilterId;
    }

    public function getCurrentState()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        return $this->state;
    }

    public function getCurrenExtratState()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        return $this->stateExtra;
    }

    public function disabaleBehaviourCalls()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
//        $url = $this->behavior_api . '/DisallowCalls/' . $this->sabyFullName;
        $url = $this->behavior_api . '/DisallowNewBehaviorCalls/' . $this->sabyFullName;
        myLog("URL REQUESED >>>> " . __FUNCTION__ . "  in " . __CLASS__ . " >>>> " . $url);
        $curl = curl_init();
//        DisallowCalls
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->behavior_api . '/DisallowNewBehaviorCalls/' . $this->sabyFullName,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function enabaleBehaviourCalls()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
//        $url = $this->behavior_api . '/AllowCalls/' . $this->sabyFullName;
        $url = $this->behavior_api . '/AllowNewBehaviorCalls/' . $this->sabyFullName;
        myLog("URL REQUESED >>>> " . __FUNCTION__ . "  in " . __CLASS__ . " >>>> " . $url);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function GetGroupMembers($Group_id, $country_group_id)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
//        $url = $this->behavior_api . '/getGroupMembers/' . $this->sabyFullName . "/" . $Group_id . "/" . $country_group_id;
        $url = $this->behavior_api . '/getGroupMembersNew/' . $this->sabyFullName . "/" . $Group_id . "/" . $country_group_id;
        myLog("URL REQUESED >>>> " . __FUNCTION__ . "  in " . __CLASS__ . " >>>> " .  $url);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function CreateGroup($Group_id, $country_group_id)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
//        $url = $this->behavior_api . '/CreateGroup/' . $this->sabyFullName;
        $url = $this->behavior_api . '/CreateNewBehaviorGroup/' . $this->sabyFullName;
        myLog("URL REQUESED >>>> " . __FUNCTION__ . "  in " . __CLASS__ . " >>>> " .  $url);
        $curl = curl_init();
//        getGroupMembers
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->behavior_api . '/getGroupMembersNew/' . $this->sabyFullName . "/" . $Group_id . "/" . $country_group_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function GetSabyId()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/GetSabyId/" . $this->sabyFullName;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        curl_close($ch);
        myLog("The result of in " . __FUNCTION__ . " >>>>>>" . $output);
        $this->sabyId = $obj->id;
        unset($ch, $url, $obj, $output);
    }

    public function getSabyInfo()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        myLog("Get Behavior Info");
        $url = $this->apiManager . "/getSabyInfo/" . $this->sabyId;
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        curl_close($ch);
        unset($ch, $url, $output);
        return $obj;
    }


    public function setSabyState($state, $stateExtra)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $this->setSabyEmulatorState($state, $stateExtra);
        $this->_cpuAvgLoad();
        unset($state, $stateExtra);
    }

    public function setSabyEmulatorCallLimitRemain($remainCalls)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/setSabyEmulatorCallLimitRemain/" . urlencode($this->sabyId) . '/' . $remainCalls;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        unset($remainCalls, $url);
    }

    public function setSabyEmulatorCallLimitOpened()
    {
        $ch = curl_init();
        $url = $this->apiManager . "/setSabyEmulatorCallLimitOpened/" . urlencode($this->sabyId);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        unset($url, $ch);
    }

    public function setCallAnsweredBySaby()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/setCallAnsweredBySaby/" . urlencode($this->sabyId);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    private function _cpuAvgLoad()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $cpuAvgLoad = shell_exec("cat /proc/loadavg | awk '{print $1}'");
        $url = $this->apiManager . "/setSabyCpuLoadAvg/" . urlencode($this->saby) . "/" . $cpuAvgLoad;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($cpuAvgLoad, $url);
    }

    public function getDialType($value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/getConfigByValue/" . $value;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($value, $ch, $url, $output);
        return $obj->{'config_name'};
    }

    public function activationPermissionController()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $maxActivating = $this->getConfig('max_activating');
        $currentActivating = $this->_statusEmulatorsCount('ACTIVATING');
        if ($currentActivating >= $maxActivating) {
            unset($currentActivating, $maxActivating);
            return false;
        } else {
            unset($currentActivating, $maxActivating);
            return true;
        }
    }

    public function setSabyEmulatorOnline()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/setSabyEmulatorOnline/" . urlencode($this->sabyId);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    public function SabyEmulatorSignal($signal, $action)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/SabyEmulatorSignal/" . urlencode($this->sabyFullName) . "/" . urlencode($signal) . "/" . urlencode($action);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url, $signal, $action);
    }

    public function setSabyEmulatorOffline()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/setSabyEmulatorOffline/" . urlencode($this->saby);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }


    public function ServerSabyCheckInState($nextState)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/ServerSabyCheckInState/" . urlencode($nextState);
        $ch = curl_init();
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        myLog("result: " . $result);
        curl_close($ch);
        unset($url, $ch, $nextState);
        return $result ? true :false;
    }

    private function _statusEmulatorsCount($status)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/statusEmulatorsCount/" . $status;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($ch, $status, $url, $output);
        return ($obj->{'count'});
    }

    public function updateHangUpSide($callId,  $value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/updateCallStatus/" . $callId . "/hangup_side/" . $value;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_exec($ch);
        curl_close($ch);
        unset($callId, $ch, $value, $url);
    }

    public function updateCallStatus($callId, $key, $value = true)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("Running " . __FUNCTION__ . " " . $key);
        $ch = curl_init();
        $url = $this->apiManager . "/updateCallStatus/" . $callId . "/" . $key . "/" . $value;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        $result = json_decode($output);
        if ($result->uniqueid != $callId) {
            $this->updateCallStatus($callId, $key, $value);
        }
        curl_close($ch);
        unset($callId, $ch, $url, $key, $value);
    }

    public function getLablesDetails()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/getLablesDetails";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $Lables = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($Lables);
        unset($ch, $url, $Lables);
        return $output;
    }

    public function setSabyLable($id)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/setSabyLable/" . $this->sabyId . "/" . urlencode($id);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($id, $url);
    }

    private function _sabyInfoIndex($sabyInfo, $needle)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        foreach ($sabyInfo as $key => $value) {
            if (substr_count($key, $needle) > 0) {
                unset($sabyInfo, $key, $needle);
                return $value;
            }
        }
        unset($sabyInfo, $key, $needle, $value);
        return false;
    }

    private function _time($timeValue)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $curruntTime = time();
        return date("Y-m-d H:i:s", strtotime("+ " . $timeValue,  $curruntTime));
    }

    public function updateNordVpnConfig($EMULATOR_ALPHA, $vpn_region, $wg_group)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("URL request in " . __FUNCTION__ . " : >>  " . $this->controlCenterApi . "/nordvpnwg.php/getSabyNordvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $wg_group);
        $vpnConfig = file_get_contents($this->controlCenterApi . "/nordvpnwg.php/getSabyNordvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $wg_group);
        if (substr_count($vpnConfig, "ERROR") == 0) {
            $configPath = "/home/" . get_current_user() . "/" . $this->sabyFullName . ".conf";
            file_put_contents($configPath, $vpnConfig, LOCK_EX);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            unset($EMULATOR_ALPHA, $vpn_region, $vpnConfig, $configPath);
            return true;
        }
        unset($EMULATOR_ALPHA, $vpn_region, $vpnConfig, $configPath);
        return false;
    }

    public function RemoveSabyFromBehaviuor()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
//        $url = "http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=UnRegister&machine_id=" . $this->sabyFullName;
        $url = "http://filter.7eet.net/whatsapp_behaviour_api/NewUnRegister/" . $this->sabyFullName;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    public function switchRunningSaby()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/switchRunningSaby/" . urlencode($this->sabyFullName) . '/NewMainStartWithSwitch/default/default/default/default/default';
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    public function switchSabyToNormalMode()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/sabyCmd/" . urlencode($this->sabyFullName) . '/switch_normal/default/default/default/default/default';
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }
    public function RestartSaby()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/sabyCmd/" . urlencode($this->sabyFullName) . '/NewMainStart/default/default/default/default/default';
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    public function switchSabyToSenderMode()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/sabyCmd/" . urlencode($this->sabyFullName) . '/switch_BehaveOnlyModeSender/default/default/default/default/default';
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    public function switchSabyToRecieverMode()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/sabyCmd/" . urlencode($this->sabyFullName) . '/switch_BehaveOnlyModeReciever/default/default/default/default/default';
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    public function switchSabyToMultiEmulatorMode()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/switchRunningSaby/" . urlencode($this->sabyFullName) . '/switchSabyToMultiEmulatorMode/default/default/default/default/default';
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($url);
    }

    public function updateWireXpressVpnConfig($EMULATOR_ALPHA, $vpn_region, $groupId)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("URL request in " . __FUNCTION__ . " : >>  " . $this->controlCenterApi . "/xpressvpnwg.php/getSabyXpressvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        $vpnConfig = file_get_contents($this->controlCenterApi . "/xpressvpnwg.php/getSabyXpressvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        if (substr_count($vpnConfig, "ERROR") == 0) {
            $configPath = "/home/" . get_current_user() . "/" . $this->sabyFullName . ".conf";
            file_put_contents($configPath, $vpnConfig, LOCK_EX);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            myLog(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
            return true;
        }
        unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
        return false;
    }

    public function getSabyHotspotvpnWgConfig($EMULATOR_ALPHA, $vpn_region, $groupId)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("URL request in " . __FUNCTION__ . " : >>  " . $this->controlCenterApi . "/HTZ-hotspotvpnwg.php/getSabyHotspotvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        $vpnConfig = file_get_contents($this->controlCenterApi . "/HTZ-hotspotvpnwg.php/getSabyHotspotvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        if (substr_count($vpnConfig, "ERROR") == 0) {
            $configPath = "/home/" . get_current_user() . "/" . $this->sabyFullName . ".conf";
            file_put_contents($configPath, $vpnConfig, LOCK_EX);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            myLog(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
            return true;
        }
        unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
        return false;
    }

    public function getSabySurfsharkvpnWgConfig($EMULATOR_ALPHA, $vpn_region, $groupId)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("URL request in " . __FUNCTION__ . " : >>  " . $this->controlCenterApi . "/HTZ-surfsharkvpnwg.php/getSabySurfsharkvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        $vpnConfig = file_get_contents($this->controlCenterApi . "/HTZ-surfsharkvpnwg.php/getSabySurfsharkvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        if (substr_count($vpnConfig, "ERROR") == 0) {
            $configPath = "/home/" . get_current_user() . "/" . $this->sabyFullName . ".conf";
            file_put_contents($configPath, $vpnConfig, LOCK_EX);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            myLog(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
            return true;
        }
        unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
        return false;
    }

    public function getSabyprotonvpnWgConfig($EMULATOR_ALPHA, $vpn_region, $groupId)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("URL request in " . __FUNCTION__ . " : >>  " . $this->controlCenterApi . "/HTZ-protonvpnwg.php/getSabyprotonvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        $vpnConfig = file_get_contents($this->controlCenterApi . "/HTZ-protonvpnwg.php/getSabyprotonvpnWgConfig/" . $this->saby . "/" . $EMULATOR_ALPHA . "/" . $vpn_region . "/" . $groupId);
        if (substr_count($vpnConfig, "ERROR") == 0) {
            $configPath = "/home/" . get_current_user() . "/" . $this->sabyFullName . ".conf";
            file_put_contents($configPath, $vpnConfig, LOCK_EX);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            myLog(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->sabyFullName . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
            return true;
        }
        unset($EMULATOR_ALPHA, $vpn_region, $configPath, $vpnConfig);
        return false;
    }

    public function lableChecker()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $lables = $this->getLablesDetails();
        $lablesCounter = count($lables);
        $sabyInfo = $this->getSabyInfo();
        $result = null;
        for ($lablesLoop = 0; $lablesLoop < $lablesCounter; $lablesLoop++) {
            foreach ($lables[$lablesLoop] as $key => $value) {
                if ($value != null) {
                    switch ($key) {
                        case 'last_answer_min':
                        case 'last_attempt_min':
                        case 'last_global_attempt_min':
                        case 'up_time_min':
                        case 'active_min':
                            $result = $this->_time($value) >= ($this->_sabyInfoIndex($sabyInfo, $key));
                            break;
                        case 'Load_avg_min':
                            $result = $this->_time($value) >= ($this->_sabyInfoIndex($sabyInfo, $key));
                            break;
                        case 'last_answer_max':
                        case 'last_attempt_max':
                        case 'last_global_attempt_max':
                        case 'up_time_max':
                        case 'active_max':
                            $result = $this->_time($value) <= ($this->_sabyInfoIndex($sabyInfo, $key));
                            break;
                        case 'Load_avg_max':
                            $result = $value <= ($this->_sabyInfoIndex($sabyInfo, $key));
                            break;
                        case 'vpn_provider':
                        case 'vpn_reagon':
                        case 'state':
                        case 'extra_state':
                            $result = $value == ($this->_sabyInfoIndex($sabyInfo, $key));
                            break;
                        case 'Code_version':
                            $result = $value == $sabyInfo->{'git_rev'};
                            break;
                        case 'profile_code':
                            $result = $value == $sabyInfo->{'profile_code'};
                            break;
                    }
                }
            }
            if (!$result) {
                break;
            } else {
                $result = $lables['id']->{'value'};
                unset($lables);
                return $result;
            }
        }
        myLog("There is no Lable for this saby");
        unset($lables, $lablesCounter, $sabyInfo, $result, $lablesLoop, $value);
        return false;
    }

    public function turnOffSabyInFilter()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $this->updateSabyStateInFilter(0);
    }

    public function turnOnSabyInFilter()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $this->updateSabyStateInFilter(1);
    }

    public function activationDetailsController($vpnProvider, $vpnRegion, $simProvider)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/activationController/" . $vpnProvider . "/" . $vpnRegion . "/" . $simProvider;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($vpnProvider, $vpnRegion, $simProvider, $url, $ch);
        if ($output > 0) {
            return true;
        }
        //Approved
        return false;
    }

    public function updateSabyStateInFilter($status)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/updateSabySocketStatus/" . $this->sabyInFilterId . '/' . $this->sabyId . '/' . $status;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $outPut = curl_exec($ch);
        curl_close($ch);
        myLog("THE OUTPUT " . __FUNCTION__ . " >>>> " . $outPut);
        unset($status, $ch, $url, $outPut);
    }

    public function getConfig($config)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("getConfig: " . $config);
        $ch = curl_init();
        $url = $this->apiManager . "/getConfig/" . $config;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($config, $ch, $url, $outPut);
        return $obj->{'config_value'};
    }

    public function cleanAndInitSabyVpn($vpn_region)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        destroySabyVpn($this->saby);
        return $this->initSabyVpnAndCheckIsReady($vpn_region);
    }

    public function destroySabyVpn()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("URL request in " . __FUNCTION__ . " : >>  " . $this->controlCenterApi . "/destroySabyVpnActivation/" . $this->saby);
        file_get_contents($this->controlCenterApi . "/destroySabyVpnActivation/" . $this->saby);
    }

    public function initSabyVpnAndCheckIsReady($vpn_region)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("init or check Saby Vpn for: " . $this->saby . " on vpn region" . $vpn_region);
        $ch = curl_init();
        $url = $this->controlCenterApi . "/initSabyVpnActivation/" . $this->saby . "/" . $vpn_region;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        if (substr_count($output, "error") > 0) {
            $this->setSabyState('KILLED', 'WIREGUARD VPN ERROR');
            exit(2);
        }
        curl_close($ch);
        return ($obj->{'vpn_status'}) === "ready";
    }

    public function updateVpnConfig($vpn_region)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("URL request in " . __FUNCTION__ . " : >>  " . $this->controlCenterApi . "/getSabyVpnActivationConfig/" . $this->saby . "/" . $vpn_region);
        $vpnConfig = file_get_contents($this->controlCenterApi . "/getSabyVpnActivationConfig/" . $this->saby . "/" . $vpn_region);
        if (substr_count($vpnConfig, "error") == 0) {
            $configPath = "/home/" . get_current_user() . "/" . $this->saby . ".conf";
            file_put_contents($configPath, $vpnConfig, LOCK_EX);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " push ~/" . $this->saby . ".conf /mnt/sdcard/download/" . $this->saby . ".conf");
            unset($vpn_region, $configPath, $vpnConfig);
            return true;
        }
        unset($vpn_region, $configPath, $vpnConfig);
        return false;
    }

    public function GetBehaviorInfo()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/GetBehaviorMode/" . $this->sabyId;
        $ch = curl_init();
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $output = json_decode($result);
        curl_close($ch);
        unset($url, $ch, $result);
        return array($output->{'first_behave'}, $output->{'last_behave'});
    }

    public function getCountryOperatorIsoDetails($value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $value = str_replace(' ', '%20', $value);
        $url   =  $this->apiManager . "/getNumberDetails/" . $value;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output);
        myLog("result: " . $output);
        unset($value, $url, $ch, $output);
        return array($result->{'mccmnc'}, $result->{'iso'}, $result->{'country'}, $result->{'network'});
    }

    public function setSabyEmulatorInfo($key, $value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/setSabyEmulatorActivationInfo/" . urlencode($this->sabyId) . "/" . $key . "/" .  urlencode($value);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        unset($key, $value, $output, $ch, $url);
    }

    public function setSabyEmulatorActivationInfo($key, $value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $this->addSabyActivationDetails($key, $value);
        unset($key, $value);
    }

    public function getAsteriskHangup($callId)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("getAsteriskHangup: " . $callId);
        $ch = curl_init();
        $url = $this->apiManager . "/getAsteriskHangup/" . $callId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($callId, $ch, $url, $output);
        return $obj->{'asterisk_hangup'};
    }

    public function getActivationDetailsbyNumber($simNumber)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = "http://filter.7eet.net/whatsapp_registry/matching_tool.php?number=" . $simNumber;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        $result = file_get_contents($url);
        $url2   = $this->apiManager . "/restoreActivationDate/" . urlencode($this->sabyFullName) . '/' . $result;
        myLog("restoreActivationDate URL : " . $url2);
        httpGetAsync($url2);
        unset($simNumber, $result, $url2, $url);
    }

    public function reportCallTimer($callId, $callState, $time = null)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/updateCall/" . urlencode($callId) . '/' . urlencode($callState);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($callId, $callState, $time, $url);
    }

    public function setSabyBehaveLog($var1, $var2)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        if (!is_numeric($var1)) {
            $url = "http://filter.7eet.net/whatsapp_behaviour/behaviour_log_api.php?src=" . $this->sabyFullName . "&dst=" . $var2 . "&type=" . $var1;
            myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
            $result = file_get_contents($url);
            unset($var1, $var2, $url);
            return $result;
        } else {
            $url = "http://filter.7eet.net/whatsapp_behaviour/behaviour_log_api.php?id=" . $var1 . "&status=" . $var2;
            myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
            $result = file_get_contents($url);
        }
        unset($var1, $var2, $result, $url);
    }

    public function getRandom5SimCountry($vpnProvider)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->controlCenterApi . "/getRandom5SimCountry/" . $vpnProvider . "/whatsapp";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getRandom5SimCountry RESULT: " . $output);
        $obj = json_decode($output);
        unset($vpnProvider, $output, $ch, $url);
        return array($obj->{'Country'}, $obj->{'five_sim_id'});
    }

    public function getCountryByLocation($vpnProvider, $vpnRegion)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->controlCenterApi . "/getCountryByLocation/" . $vpnProvider . "/" . $vpnRegion;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getCountryByLocation RESULT: " . $output);
        $obj = json_decode($output);
        unset($vpnProvider, $vpnRegion, $ch, $url, $output);
        if ($obj != 'false') {
            return array($obj->{'country'}, $obj->{'id'});
        } else {
            return array(false, false);
        }
    }
    public function setSabyBehaviorDetails($sabyName, $sabyNumber)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/setSabyBehaviorDetails/" . $this->sabyId . '/' . urlencode($sabyName) . "/" . $sabyNumber;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . "  in " . __CLASS__ . " RESULT: " . $output);
        unset($sabyName, $sabyNumber, $ch, $url, $output);
    }

    public function get5SimCountryRate($countryId)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->controlCenterApi . "/get5SimCountryRate/" . $countryId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        $obj = json_decode($output);
        unset($ch, $url, $output, $countryId);
        return $obj->{'country_wa_rate'};
    }

    public function addSabyActivationDetails($key, $value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/addSabyActivationDetails/" . urlencode($this->sabyFullName) . "/" . $key . "/" .  urlencode($value);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        unset($ch, $url, $key, $value);
        myLog(__FUNCTION__ . " RESULT: " . $output);
    }

    public function sabyDetail($action, $value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $url = $this->apiManager . "/sabyDetail/" . urlencode($this->sabyFullName) . "/daily_limit/" . $action . "/" . $value;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($action, $value, $url);
    }

    public function getSabyEmulatorActivationInfo($key)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/getSabyEmulatorActivationInfo/" . urlencode($this->sabyId) . "/" . urlencode($key);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        unset($key, $ch, $url);
        return $output;
    }

    public function checkVpnBackbone()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm clear org.chromium.webview_shell");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d 'http://cc-api.7eet.net/checkVpnBackbone'");
        sleep(5);
        $screenResult = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell uiautomator dump && " . ADB . " -s emulator-" . $this->emulatorPort . " shell cat /sdcard/window_dump.xml");
        $screenResult = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell uiautomator dump && " . ADB . " -s emulator-" . $this->emulatorPort . " shell cat /sdcard/window_dump.xml");
        if (substr_count($screenResult, "NordVpn") > 0) {
            $VpnBackbone = "NordVpn";
        } elseif (substr_count($screenResult, "Wire-XpressVpn") > 0) {
            $VpnBackbone = "Wire-XpressVpn";
        } else {
            $VpnBackbone = "WireGuard";
        }
        unset($screenResult);
        return $VpnBackbone;
    }

    public function checkVpnBackboneAndLocation()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.chrome");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell pm clear com.android.chrome");
        sleep(2);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d 'http://cc-api.7eet.net/checkVpnBackboneAndLocation'");
        sleep(5);
        $screenResult = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " exec-out uiautomator dump /dev/tty >" . $_SERVER['HOME'] . '/myLogxml.xml');
        $screenResult = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " exec-out uiautomator dump /dev/tty >" . $_SERVER['HOME'] . '/myLogxml.xml');
        myLog("THE screenResult IN " . __FUNCTION__ . " IS >>>> " . $screenResult);
        $screenResult = file_get_contents($_SERVER['HOME'] . '/myLogxml.xml');
        $firstSubStr = substr($screenResult, strpos($screenResult, '^^^^') + strlen('^^^^'));
        $LastSubString = substr($firstSubStr, 0, strpos($firstSubStr, '^^^^'));
        list($vpn_type, $vpn_reagon) = explode(",", $LastSubString);
        unset($screenResult, $firstSubStr, $LastSubString);
        return array($vpn_type, $vpn_reagon);
    }

    public function registerSabyInFilter($socketPort, $astriskHost)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $applicationId = 1;
        $ch = curl_init();
        $url = $this->apiManager . "/Register/" . urlencode($this->sabyFullName) . '/' . urlencode($socketPort) . '/' .  urlencode($astriskHost) . "/" . $applicationId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        curl_close($ch);
        $this->sabyInFilterId = $obj->{'id'};
        myLog("The sabyInFilterId is : " . $this->sabyInFilterId);
        unset($ch, $url, $output, $obj, $applicationId, $astriskHost);
    }

    public function setSabyCallerCallee($caller, $callee)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $caller = str_replace(' ', '', $caller);
        $callee = str_replace(' ', '', $callee);
        $url = $this->apiManager . "/setSabyCallerCallee/" . urlencode($this->sabyId) . '/' . $caller . '/' . $callee;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog(__FUNCTION__ . " RESULT is : " . $output);
        curl_close($ch);
        unset($ch, $url, $output, $caller, $callee);
    }

    public function addSabyCdrRecord($caller, $callee)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $caller = str_replace(' ', '', $caller);
        $callee = str_replace(' ', '', $callee);
        $url = $this->apiManager . "/addSabyCdrRecord/" . urlencode($this->sabyFullName) . '/' . $caller . '/' . $callee;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $result = json_decode($output);
        myLog(__FUNCTION__ . " RESULT is : " . $output);
        curl_close($ch);
        unset($ch, $url, $caller, $callee);
        return $result->{'id'};
    }

    public function updateSabyCdrRecord($id, $cycleTime)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/updateSabyCdrRecord/"  . $id . '/' . $cycleTime;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog(__FUNCTION__ . " RESULT is : " . $output);
        curl_close($ch);
        unset($ch, $url, $id, $cycleTime, $output);
    }

    public function updateSabyCdrRecordAnswerDuration($id, $answerDuration)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/updateSabyCdrRecordAnswerDuration/"  . $id . '/' . $answerDuration;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog(__FUNCTION__ . " RESULT is : " . $output);
        curl_close($ch);
        unset($ch, $url, $id, $cycleTime, $output);
    }

    public function autoActivateSaby($vpnProvider, $simProvider)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        switch ($simProvider) {
            case '5S':
                list($vpnRegion, $simRegion) = getRandom5SimCountry($vpnProvider);
                break;
            default:
                list($vpnRegion, $simRegion) = getRandom5SimCountry($vpnProvider);
                break;
        }

        switch ($vpnProvider) {
            case 'ExpressVPN':
            case 'HotSpot':
                $sabyCmd = 'DeployMultiEmu';
                break;
            default:
                $sabyCmd = 'CreateMultiEmu';
                break;
        }
        myLog('New sim country id :' . $simRegion);
        myLog('New vpn region : ' . $vpnRegion);

        $url = $this->apiManager . "/sabyCmd/" . urlencode($this->sabyFullName) . '/' . $sabyCmd . '/' . $vpnProvider . '/same/' . $vpnRegion . '/' . $simProvider . '/' . $simRegion;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        httpGetAsync($url);
        unset($vpnProvider, $simProvider, $url, $sabyCmd);
    }

    public function getCurrentStateExtra()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        return $this->stateExtra;
    }

    public function AnalysisLogsExtra($dataType, $value)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $value = str_replace(' ', '%20', $value);
        if ($dataType == 'Act') {
            $url = "http://filter.7eet.net/whatsapp_registry/API_EXTRA.php?machine_id=" . $this->sabyFullName . "\&data_type=" . $dataType . "\&value=" . $value;
            myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.VIEW -d '" . $url . "'");
            sleep(2);
            exec("~/Android/Sdk/platform-tools/adb -s emulator-" . $this->emulatorPort . " shell am force-stop org.chromium.webview_shell");
            sleep(1);
            myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        } else {
            $url = "http://filter.7eet.net/whatsapp_registry/API_EXTRA.php?machine_id=" . $this->sabyFullName . "&data_type=" . $dataType . "&value=" . $value;
            myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
            unset($output);
        }

        unset($dataType, $value, $url);
    }

    public function getCallLimit()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("getCallLimit: " . $this->sabyFullName);
        $ch = curl_init();
        $url = $this->apiManager . "/getSabyEmulatorCallLimit/" . $this->sabyId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($ch, $url, $output);
        return $obj->{'saby_call_limit'};
    }


    public function checkPhoneNumberInActivation($phoneNumber)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/checkPhoneNumberInActivation/" . $this->sabyFullName . '/' . $phoneNumber;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($ch, $url, $output);
        return $obj->{'result'};
    }

    public function getSleepTimer()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $sleepTimeMax = $this->getConfig("sleep_time");
        $sleepType = $this->_getSleepEmulatorType();
        if (isset($sleepType) && $sleepType != "") {
            $sleepTimeMax = $this->getConfig("sleep_time_" . $sleepType);
        }
        $sleepTimeMin = ceil($sleepTimeMax * 0.7);
        $sleepTime = rand($sleepTimeMin, $sleepTimeMax);
        myLog('Sleep Time Min: ' . $sleepTimeMin);
        myLog('Sleep Time Max: ' . $sleepTimeMax);
        myLog('Sleep Time: ' . $sleepTime);
        unset($sleepTimeMax, $sleepTimeMin, $sleepType);
        return $sleepTime;
    }

    private function _getSleepEmulatorType()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        myLog("getSleepType for: " . $this->sabyFullName);
        $ch = curl_init();
        $url = $this->apiManager . "/getSleepEmulatorType/" . $this->sabyId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        $obj = json_decode($output);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($ch, $output, $url);
        return ($obj->{'sleep_type'});
    }

    public function setBehaviorCallattempt()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/setBehaviorCallattempt/" . $this->sabyId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($ch, $url, $output);
    }

    public function setBehaviorCallAnswered()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/setBehaviorCallAnswered/" . $this->sabyId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($ch, $url, $output);
    }

    private function _getIp()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        return getHostByName(php_uname('n'));
    }
    public function setCallFinalState($callId)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
    }

    public function updateSabyCallTimers()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/updateSabyCallTimers/" . $this->sabyId;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        myLog("result of " . __FUNCTION__ . " : " . $output);
        curl_close($ch);
        unset($ch, $url, $output);
    }

    public function resetEmultorPort($newEmultorPort)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $this->emulatorPort = $newEmultorPort;
        unset($newEmultorPort);
    }

    public function updateActivationController($vpnProvider, $vpnRegion, $vpnSubRegion, $status)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->controlCenterApi . "/updateActivationController/" . $vpnProvider . "/" . $vpnRegion . "/" . $vpnSubRegion . "/" . $status;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        unset($ch, $url, $output, $vpnProvider, $vpnRegion, $vpnSubRegion, $status);
    }

    public function RestartSabySwitchBehave()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/sabyCmd/" . urlencode($this->sabyFullName) . '/switch_behave/default/default/default/default/default';
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        unset($ch, $url, $output);
    }

    public function updateEmulatorDetails($sabyFullName, $RUNNING_EMULATOR_PORT)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $this->emulatorPort = $RUNNING_EMULATOR_PORT;
        $this->sabyFullName = $sabyFullName;
        $this->sabyId = $this->GetSabyId($this->sabyFullName);
        unset($sabyFullName, $RUNNING_EMULATOR_PORT);
    }

    public function getEmulatorCountPerSaby()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/getEmulatorCountPerSaby/" . urlencode($this->saby);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog("getEmulatorCountPerSaby RESULT: " . $output);
        $obj = json_decode($output);
        unset($ch, $url, $output);
        return $obj->{'count'};
    }
    public function setSwitchFlagForServer()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/setSwitchFlagForServer";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        $obj = json_decode($output);
        unset($ch, $url, $output);
    }

    function getSipResponses()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch = curl_init();
        $url = $this->apiManager . "/getSabySipResponse";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " Output: " . $output);
        $obj = json_decode($output);
        unset($ch, $url, $output);
        return $obj;
    }

    public function getApplicationLastVersion()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $fileName = "/home/" . get_current_user() . "/Downloads/WAORG.apk";
        unlink($fileName);
        myLog("THE COMMAND REQESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " " . 'wget http://' . $this->managerIp . '/MapleVoipFilter/apk/WAORG.apk -O ' . $fileName);
        sleep(2);
        while (!file_exists($fileName)) {
            exec('wget http://' . $this->managerIp . '/MapleVoipFilter/apk/WAORG.apk -O ' . $fileName);
        }
        unset($fileName);
    }

    public function detectVpnDetails($endPointIp, $endPointPort)
    {
        myLog("RUNNING >>>> " . __FUNCTION__ . " IN >>>> " . __CLASS__);
        $ch  = curl_init();
        $url = $this->controlCenterApi . "/detectVpnDetails/" . urlencode($this->sabyFullName) . '/' . $endPointIp . '/' . $endPointPort;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        $obj = json_decode($output);
        return array($obj->{'vpn_type'}, $obj->{'location'}, $obj->{'country'}, $obj->{'active_code'});
    }
    public function replaceWirexpressActivationCodes()
    {
        myLog("RUNNING >>>> " . __FUNCTION__ . " IN >>>> " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/replaceWirexpressActivationCodes";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        if ($output) {
            $outputArray = explode(',', $output);
        } else {
            $outputArray = [null];
        }
        return $outputArray;
    }

    public function replaceWirexpressVpnType()
    {
        myLog("RUNNING >>>> " . __FUNCTION__ . " IN >>>> " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/replaceWirexpressVpnType";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: " . $output);
        return $output;
    }

    public function setBehaviorApi($behavior_api)
    {
        myLog("RUNNING >>>> " . __FUNCTION__ . " IN >>>> " . __CLASS__);
        $this->behavior_api = $behavior_api;
    }

    public function registerSabyInBehvaior($saby_behvaior_name, $saby_behvaior_number)
    {
        list($countryName, $countryCode) = checkCountry($saby_behvaior_number);
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
//        $url = $this->behavior_api . "/registerBehavior/" . urlencode($this->sabyFullName)  . '/' . $this->androidID . '/' . $saby_behvaior_number . '/' . urlencode($saby_behvaior_name) . '/' . $countryCode;
        $url = $this->behavior_api . "/registerNewBehavior/" . urlencode($this->sabyFullName)  . '/' . $this->androidID . '/' . $saby_behvaior_number . '/' . urlencode($saby_behvaior_name) . '/' . $countryCode;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $output, $countryName, $number);
    }

    public function validateBehavior($saby_behvaior_number)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->behavior_api . "/validateBehavior/" . $saby_behvaior_number;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $output, $saby_behvaior_number, $number);
    }

    public function getGroupBehvaiorId()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
//        $url = $this->behavior_api . "/getGroupBehvaiorId/" . urlencode($this->sabyFullName);
        $url = $this->behavior_api . "/getGroupNewBehvaiorId/" . urlencode($this->sabyFullName);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $saby_behvaior_number, $number);
        return $output;
    }

    public function unsetBehaveOnlyMode()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
//        $url = $this->behavior_api . "/unsetBehaveOnlyMode/" . urlencode($this->sabyFullName);
        $url = $this->behavior_api . "/unsetNewBehaveOnlyMode/" . urlencode($this->sabyFullName);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $output, $saby_behvaior_number, $number);
    }

    public function setBehaveOnlyMode()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
//        $url = $this->behavior_api . "/setBehaveOnlyMode/" . urlencode($this->sabyFullName) . "/" . $this->prodName;
        $url = $this->behavior_api . "/setNewBehaveOnlyMode/" . urlencode($this->sabyFullName) . "/" . $this->prodName;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $output, $saby_behvaior_number, $number);
    }

    public function disallowBehaviorCalls()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = disallowBehaviorCalls . "/disallowBehaviorCalls/" . urlencode($this->sabyFullName);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $output, $saby_behvaior_number, $number);
    }

    public function allowBehaviorCalls()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->behavior_api  . "/allowBehaviorCalls/" . urlencode($this->sabyFullName);
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        unset($ch, $url, $output, $saby_behvaior_number, $number);
    }

    public function getRandomBehaviorLink()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = "http://65.108.105.115/whatsapp_behaviour/links.php";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        return $output;
    }

    public function getSocketPort()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/getSabyAdbPort/" . get_current_user();
        myLog("detectVpnDetails URL : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        $result = json_decode($output);
        return $result->{'socket_port'};
    }
    public function getSleepCountByServer()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/getSleepCountByServer";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        $result = json_decode($output);
        return $result->{'sleep_count'};
    }

    public function BehvaiorLog($type)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/behaviorLog/" . $this->sabyFullName . '/' . $type;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
    }

    public function checkSenderReceiver()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/isSabySenderReceiverOn/" . $this->sabyFullName;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $result = json_decode($output);
        $result = (intval($result) == 2) ? true : false;
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        return $result;
    }

    public function setNordVpnLocationNotFound($vpnProvider, $vpnReagon)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/setVpnLocationNotFound/" . $this->sabyFullName . '/' . $vpnProvider . '/' . $vpnReagon;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $result = json_decode($output);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
    }

    public function saveSabyVpnHistory($vpnProvider, $vpnReagon)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/saveSabyVpnHistory/" . $this->sabyFullName . '/' . $vpnProvider . '/' . $vpnReagon;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $result = json_decode($output);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
    }

    public function checkReplaceVpnConfig($vpnProvider, $vpnReagon)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->controlCenterApiNew . "/checkReplaceVpnConfig/" . $vpnProvider . '/' . $vpnReagon;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $result = json_decode($output);
        curl_close($ch);
        return array($result->{'saby_alternative_vpn_providor'}, $result->{'saby_alternative_vpn_reagon'});
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
    }

    public function getSabySocketSwitchFlagForServer()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $ch  = curl_init();
        $url = $this->apiManager . "/getSabySocketSwitchFlagForServer";
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        myLog(__FUNCTION__ . " RESULT: >>>> " . $output);
        $result = json_decode($output);
        return $result->{'count'};
    }

    public function getBehaveOnlyMembers()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $curl = curl_init();
        $url = $this->behavior_api . '/GroupNewBehaveOnlyMembers/' . $this->sabyFullName . '/' . $this->prodName;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function MakeTestCall()
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $curl = curl_init();
//        $url = $this->behavior_api . '/MakeTestCall/' . $this->sabyFullName . '/' . $this->prodName;
        $url = $this->behavior_api . '/MakeNewBehaviorTestCall/' . $this->sabyFullName . '/' . $this->prodName;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function ValidateMemeber($number)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $curl = curl_init();
//        $url = $this->behavior_api . '/ValidateMemeber/' . $number;
        $url = $this->behavior_api . '/NewValidateMemeber/' . $number;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function GetSendMessage($Group_id, $country_group_id)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $curl = curl_init();
//        $url = $this->behavior_api . '/SendMessage/' . $this->sabyFullName . '/' . $Group_id . '/' . $country_group_id;
        $url = $this->behavior_api . '/SendNewBehaviorMessage/' . $this->sabyFullName . '/' . $Group_id . '/' . $country_group_id;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function GetContactMakeCall($Group_id, $country_group_id)
    {
        $curl = curl_init();
//        $url = $this->behavior_api . '/MakeCall/' . $this->sabyFullName . '/' . $Group_id . '/' . $country_group_id;
        $url = $this->behavior_api . '/MakeNewBehaviorCall/' . $this->sabyFullName . '/' . $Group_id . '/' . $country_group_id;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function check_last_activation(){

        $curl = curl_init();
        $url = $this->apiManager  . '/check_last_activation/' . $this->sabyFullName ;
        myLog(" URL REQUESTED IN " . __FUNCTION__ . " IN " . __CLASS__ . " : " . $url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


    public function WhatsAppChecker($callee,$status,$SabyHaveWhats=1)
    {
        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $this->apiManager .'/InsertWhatsAppChecker',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('saby' => $this->sabyFullName,'phone_number' => $callee,'status' => $status,'is_saby_have_wa' => $SabyHaveWhats),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }
    public function update_saby_number($number){

        myLog("Running >>>> " . __FUNCTION__ . "  in " . __CLASS__);
        $curl = curl_init();
        $url =$this->apiManager .'/update_saby_number';
        myLog($url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('saby' => $this->sabyFullName,'number' => $number),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        myLog("response : ".$response);
    }
}
