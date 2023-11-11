<?php

class GatewayProvider
{
    public $saby = null;
    public $country = null;
    public $beanstalk = null;
    public $phonenumber = null;
    public $phonenumber_time = null;
    public $emulator_port = null;
    public $ussdcode = null;
    public $ussdcode_time = null;
    public $uuid = null;

    public function __construct($saby, $country, $emulator_port)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->saby = $saby;
        $this->country = $country;
        $this->emulator_port = $emulator_port;
        $this->uuid = $this->getKernelRandomUUID();

//        $this->beanstalk = new Pheanstalk_Pheanstalk('beanstalkd-whatsapp.7eet.net', 11301);
//        $this->beanstalk->useTube($this->saby);
//        $this->beanstalk->watch($this->saby);
//        try {
//            while ($job = $this->beanstalk->reserve(0)) {
//                $this->beanstalk->delete($job);
//            }
//        } catch (Pheanstalk_Exception_ServerException $e) {
//        }
    }

    public function getKernelRandomUUID() {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        // Run the shell command to get the UUID
        $command = 'cat /proc/sys/kernel/random/uuid';
        $output = shell_exec($command);

        // Remove any leading/trailing whitespace or line breaks
        $uuid = trim($output);
        myLog('uuid ' .$uuid);
        return $uuid;
    }


    public function sendPhonenumberRequest()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
//        httpGetAsync("http://maple-ms.com/registery/registery/request.php?uuid=" . urlencode($this->uuid) . "&machine_id=" . urlencode($this->saby));
    }

    public function getRequestDetail($request_status, $android_id)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $std = new stdClass();
        $std->saby = $this->saby;
        $std->android_id = $android_id;
        $std->provider_name = 'Gateway';
        $std->request_status = $request_status;
        $std->phonenumber_time = $this->phonenumber_time;
        $std->phonenumber = $this->phonenumber;
        $std->ussdcode_time = $this->ussdcode_time;
        $std->ussdcode = $this->ussdcode;
        return $std;
    }

    public function getCountry()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        return null;
    }

    public function getPhonenumber($random = false)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);

        $url = 'http://maple-ms.com/registery/request.php?uuid='.urlencode($this->uuid).'&machine_id=' . urlencode($this->saby);
        $result = file_get_contents($url);
        myLog('Request : ' . $url);
        myLog('response : ' . $result);

        if($result){
            $result = json_decode($result);
            myLog('result->number : ' .$result->number);
            $this->phonenumber = $result->number;
            $this->phonenumber_time = microtime(true);
        }
        return $this->phonenumber;
    }

    public function getUSSDCode()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $url="http://maple-ms.com/registery/getCode.php?uuid=".urlencode($this->uuid);
        $result = file_get_contents("http://maple-ms.com/registery/getCode.php?uuid=".urlencode($this->uuid));
        myLog('url :'.$url);
        myLog('response :' . $result);
        $this->ussdcode = trim(preg_replace("/[^0-9]/", "", $result));
        return $this->ussdcode;
    }

    public function sendFinishSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function sendBannedSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function sendNotActivatedSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function sendActivatedSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
//        httpGetAsync("http://maple-ms.com/registery/status.php?uuid=" . urlencode($this->uuid));
//        sendSabyActionSignal($this->emulator_port, $this->saby, "activation", $this->phonenumber, "gateway");
        $url = "http://filter.7eet.net/whatsapp_registry/API.php?machine_id=" . trim($this->saby) . "&activation_number=" . $this->phonenumber . "&action=Activate";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
    }

    public function sendCancelSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function sendNumberConfirmSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }
}
