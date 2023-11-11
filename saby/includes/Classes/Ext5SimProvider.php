<?php

class Ext5SimProvider
{
    public $saby = null;
    public $country = null;
    public $phonenumber = null;
    public $phonenumber_time = null;
    public $emulator_port = null;
    public $api_key = null;
    public $ussdcode = null;
    public $ussdcode_time = null;
    public $status_id = null;
    public $response_status = null;
    public $no_balance = false;

    public function __construct($saby, $country, $emulator_port)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->saby = $saby;
        $this->country = $country;
        $this->emulator_port = $emulator_port;
        $this->api_key = '297792eb34cc4eb2a25a7ceca14dd588';
    }

    public function sendPhonenumberRequest()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function getRequestDetail($request_status, $android_id)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $std = new stdClass();
        $std->saby = $this->saby;
        $std->android_id = $android_id;
        $std->provider_name = '5Sim';
        $std->request_code = $this->status_id;
        $std->request_status = $request_status;
        $std->country_id = $this->country->country_id;
        //$std->country_id = $this->country->{'country_id'};
        //$std->country_name = $this->country->{'country_name'};
        //$std->country_rate = $this->country->{'country_wa_rate'};
        $std->phonenumber_time = $this->phonenumber_time;
        $std->phonenumber = $this->phonenumber;
        $std->ussdcode_time = $this->ussdcode_time;
        $std->ussdcode = $this->ussdcode;
        $std->response_status = $this->response_status;
        return $std;
    }

    public function hasBalance()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        return !$this->no_balance;
    }

    public function getCountry()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        return $this->country;
    }

    public function getPhonenumber($country_id)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $tryingCount = 0;
        $resultArray = null;
        //do {
        //if ($random) {
        //    $this->country = getRandomProviderSimCountry('5S', $this->country->{'country_name'}, $tryingCount);
        //    myLog('new sim country :' . $this->country->{'country_name'});
        //}
        $random = true;
        $tryingCount++;
        //myLog('country :' . json_encode($this->country));
        $url = "http://api1.5sim.net/stubs/handler_api.php?api_key=" . $this->api_key . "&action=getNumber&service=whatsapp&country=" . $country_id;
        $result = file_get_contents($url);
        myLog('Request : ' . $url);
        myLog('response : ' . $result);
        $resultArray = explode(":", $result);
        //} while ($resultArray[0] == "NO_NUMBERS");
        if ($resultArray[0] == "NO_NUMBERS") {
            $this->phonenumber = null;
        }
        if ($resultArray[0] == "ACCESS_NUMBER") {
            $this->status_id = trim($resultArray[1]);
            $this->phonenumber = trim($resultArray[2]);
            $this->phonenumber_time = microtime(true);
        }
        if ($resultArray[0] == "NO_BALANCE") {
            $this->no_balance = true;
        }
        return $this->phonenumber;
    }

    public function getUSSDCode()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $result = file_get_contents("http://api1.5sim.net/stubs/handler_api.php?api_key=" . $this->api_key . "&action=getStatus&id=" . $this->status_id);
        myLog('response :' . $result);
        $resultUSSDArray = explode(":", $result);
        if ($resultUSSDArray[0] == "STATUS_OK") {
            $this->ussdcode = trim($resultUSSDArray[1]);
            $this->ussdcode_time = microtime(true);
        } else {
            $this->ussdcode = null;
        }
        return $this->ussdcode;
    }

    public function sendCancelSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->response_status = -1;
        file_get_contents("http://api1.5sim.net/stubs/handler_api.php?api_key=" . $this->api_key . "&action=setStatus&status=" . $this->response_status . "&id=" . $this->status_id);
    }

    public function sendFinishSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->response_status = 6;
        file_get_contents("http://api1.5sim.net/stubs/handler_api.php?api_key=" . $this->api_key . "&action=setStatus&status=" . $this->response_status . "&id=" . $this->status_id);
    }

    public function sendBannedSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->response_status = 8;
        file_get_contents("http://api1.5sim.net/stubs/handler_api.php?api_key=" . $this->api_key . "&action=setStatus&status=" . $this->response_status . "&id=" . $this->status_id);
    }

    public function sendNotActivatedSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }

    public function sendActivatedSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        sendSabyActionSignal($this->emulator_port, $this->saby, "activation", $this->phonenumber, "5sim");
        $url = "http://filter.7eet.net/whatsapp_registry/API.php?machine_id=" . trim($this->saby) . "&activation_number=" . $this->phonenumber . "&action=Activate";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
    }
    public function sendNumberConfirmSignal()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
    }
}
