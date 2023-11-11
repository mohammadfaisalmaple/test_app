<?php
class CallsManager
{
    public $emulatorPort = null;
    public $applicaion = null;
    public $automator = null; // Auto PythonUI Object
    public $reporter = null; // Transporter Object
    public $callID = null;
    public $callStartTime = null;
    public $callDialTime = null;
    public $callState = null;
    public $callee = null;
    public $flowFlag = null;
    public $voiceCall = false;
    public $saby = null;
    public $addContactRegion = null;
    public $socketObject = null;
    public $dumpScreenFile = null;
    public $videoCallReagon = null;
    public $addContactRegion2 = null;

    public function __construct($emulatorPort, $applicaion, $autoObject, $transporterObject, $callId, $callStartTime, $destinationNumber, $lookForVoiceCall, $sabyName, $addContactRegion, $mainSocket)
    {
        $this->emulatorPort = $emulatorPort;
        $this->applicaion = $applicaion;
        $this->automator = $autoObject;
        $this->reporter = $transporterObject;
        $this->callID = $callId;
        $this->callStartTime = $callStartTime;
        $this->callee = substr($destinationNumber, 2);
        $this->flowFlag = substr($destinationNumber, 0, 2);
        $this->voiceCall = $lookForVoiceCall;
        $this->saby = $sabyName;
        $this->addContactRegion = $addContactRegion;
        $this->socketObject = $mainSocket;
        $this->dumpScreenFile = $_SERVER['HOME'] . '/myLogxml.xml';
        //$this->videoCallReagon = $this->automator->pythonUI->createRegion(325, 500, 50, 100);
    }

    public function __destruct()
    {
        //$this->cleanup();
        myLog("Destroying CallsManagerClass");
    }

    /*  public function cleanup()
    {
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
    }*/

    public function Dial()
    {
        myLog("Callee Number : " . $this->callee);
        myLog("Dial Flag : " . $this->flowFlag);

        // Check Call Proccess Stages and conditions by Dialling Type Profix (flowFlag)
        $dialType = $this->reporter->getDialType($this->flowFlag);
        myLog("Dial Type : " . $dialType);

        // Report Time od start Proccessing in the PMA Calls Table
        myLog("Will now tell " . $this->applicaion . " to Open Conversation Page of : " . $this->callee);
        $appDialTime = microtime(true) - $this->callStartTime;
        myLog("apidialTime: " . $appDialTime);

        // Using Transporter Call Function
        $this->reporter->reportCallTimer($this->callID, 'api_dial_time', $appDialTime);

        // Open Conversation Activity of the Callee Number Using Whatsapp JID
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->applicaion . "/.Conversation -e jid '" . $this->callee . "@s.whatsapp.net'");
        myLog("Inside Conversation Page of : " . $this->callee);
        // Take Desicion about the predialling Checkup
        switch ($dialType) {
            case 'rbt_img':
            case 'test_route':
                myLog("Checking Callee Image by avatar to check if he has Whatsapp or Not ???");
                if ($this->_CheckWhatsappCalleeImage()) {
                    myLog("Callee Image Found -- Will Proceed");
                } else {
                    myLog("No Image Found -- Will Hangup the Call");
                    $this->socketObject->socketReply($GLOBALS['SIP_RESPONSES']['WAOut']);
                    $this->reporter->setSabyEmulatorState('IN CALL', 'WA OUT');
                    $this->reporter->setCallFinalState($this->callID);
                    $waOutTime = microtime(true) - $this->callStartTime;
                    myLog("waOutTime: " . $waOutTime);
                    $this->reporter->reportCallTimer($this->callID, 'wa_out_time', $waOutTime);
                    $this->hangupSipCall();
                    $this->hangupSipCall();
                    $this->reporter->updateHangUpSide($this->callID, 'callee');
                    $this->endTheCall();
                    return false;
                }
                break;
            case 'rbt_wa':
                myLog("Callee Sent by Whatsapp Flag");
                break;
        }
        // Proceed with Dialling
        if ($this->automator->pythonUI->findAndClick(IMG . 'whatsapp_dial_big.png', null, 7)) {;
            myLog("Found Dial Button -- Click Now");
            if ($this->voiceCall) {
                myLog("Start Voice Call is Enabled");
                if ($this->automator->pythonUI->exists(IMG . 'wa_start_call.png', null, 3) || $this->automator->pythonUI->exists(IMG . 'new_Wa_start_call.png', null, 3) || $this->automator->pythonUI->exists(IMG . 'new_voice_call.png', null, 3) ) {
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 840 1010");
                    myLog("Start Voice Call Clicked");
                    $voicecallTime = microtime(true) - $this->callStartTime;
                    myLog("voicecallTime: " . $voicecallTime);
                    $this->reporter->reportCallTimer($this->callID, 'voicecall_time', $voicecallTime);
                } else {
                    myLog("Start Voice Call is Enabled But Didnt Find it -- Will Hangup the Call");
                    $this->reporter->setSabyEmulatorState('IN CALL', 'GONE');
                    $this->reporter->setCallFinalState($this->callID);
                    $this->socketObject->socketReply($GLOBALS['SIP_RESPONSES']['Gone']);
                    return false;
                }
            }
            myLog("Bigdial Disappeared: will Send Ringing Now");
            $ringingTime = microtime(true) - $this->callStartTime;
            myLog("ringingTime: " . $ringingTime);
            $this->reporter->reportCallTimer($this->callID, 'ringing_time', $ringingTime);
            $this->callDialTime = microtime(true);
            $this->socketObject->socketReply(Ringing);
            $tryCount = 0;
            // Waiting for Sip Call From M3allem to Linphone
            do {
                usleep(100000);
                if ($this->_detectIcommingSipCall()) {
                    $this->reporter->updateCallStatus($this->callID, 'dial_time');
                    break;
                }
                $tryCount++;
            } while ($tryCount <= 20);
            // if call not received
            if (!$this->_detectIcommingSipCall()) {
                $this->reporter->updateCallStatus($this->callID, 'call_status', $GLOBALS['SIP_RESPONSES']['NoSipCall']);
                $this->reporter->updateHangUpSide($this->callID, 'caller');
                myLog("No Incommming Sip Call");
            }
            return true;
        } else {
            myLog("Didnt Find the Dial Button -- Click Now");
            $this->reporter->setSabyEmulatorState('IN CALL', 'GONE');
            $this->reporter->setCallFinalState($this->callID);
            //$this->reporter->sendHangUpCommand($this->callID, 'GONE');
            $this->socketObject->socketReply($GLOBALS['SIP_RESPONSES']['Gone']);
            //$this->reporter->sendHangUpCommand($this->callID);
            return false;
        }
    }

    public function isCallerHangupAfterAnswer()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        //if ($this->reporter->getAsteriskHangup($this->callID) === 'true') {
        if (!$this->_isSipInCall()) {
            myLog("Caller Droped the Call ...");
            $this->reporter->setSabyEmulatorState('IN CALL', 'CALLER HANGUP');
            $this->reporter->setCallFinalState($this->callID);
            $callEndTime = microtime(true) - $this->callDialTime;
            $this->reporter->updateHangUpSide($this->callID, 'caller');
            myLog("Call ENDED");
            //$this->reporter->reportCallEnd($this->callID, $callEndTime);
            $this->endTheCall();
            //sendFilterLogs($filterTimeStamp,'caller_hangup=1',$destinationNumber);
            //sendFilterLogs($filterTimeStamp,'hangup_reason=CallerHangup',$destinationNumber);
            return true;
        } else {
            return false;
        }
    }

    public function isBusy()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        if ($this->automator->pythonUI->searchStringInScreenShot('another', null, 1)) {
            myLog("Callee is In Another Call ...");
            $this->reporter->setSabyEmulatorState('IN CALL', 'BUSY');
            $this->reporter->setCallFinalState($this->callID);
            $this->reporter->updateCallStatus($this->callID, 'call_status', $GLOBALS['SIP_RESPONSES']['InAnotherCall']);
            $this->reporter->updateHangUpSide($this->callID, 'callee');
            return true;
            //sendFilterLogs($filterTimeStamp,'hangup_reason=OnAnotherCall',$destinationNumber);
        } else {
            return false;
        }
    }

    public function isRinging()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        if ($this->automator->pythonUI->exists(IMG . 'WhatsppRinging.png', null, 1)) {
            myLog("Call Is Ringing ...");
            $this->reporter->setSabyEmulatorState('IN CALL', 'RINGING');
            $this->socketObject->socketReply(Ringing);
            return true;
            //sendFilterLogs($filterTimeStamp, 'ringing=1', $destinationNumber);
        } else {
            return false;
        }
    }

    public function isCalling($SearchDuration = 1)
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        //$this->videoCallReagon
        /*  if ($this->automator->pythonUI->exists(IMG . 'WhatsappVideoCallButton.png', null, 1)) {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 290 1490 270 1200 500");
        } */
        if ($this->automator->pythonUI->exists(IMG . 'whatsapp_hangup.png', null, $SearchDuration)) {
            return true;
        }
        $this->reporter->updateHangUpSide($this->callID, 'callee');
        $this->reporter->setSabyEmulatorState('IN CALL', 'CALL ENDED');
        return false;
    }

    public function isReconnecting()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        if ($this->automator->pythonUI->exists(IMG . 'whatsapp_connecting_big.png', null, 1)) {
            return true;
        }
        return false;
    }

    public function isAnswered()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        ## Update By Moayad to Check the Image2 of add Contact IMG
        $this->addContactRegion2 = $this->automator->pythonUI->createRegion(120, 450, 300, 100);
        if ($this->automator->pythonUI->exists(IMG . 'whatsapp_add_contact.png', $this->addContactRegion, 1) || $this->automator->pythonUI->exists(IMG . 'whatsapp_add_contact2.png', $this->addContactRegion2, 1)) {
            $startSipAnswerTime = microtime(true);
            $answerTime = microtime(true) - $this->callStartTime;
            myLog("answerTime: " . $answerTime);
            reportCallTimer($this->callID, 'answer_time', $answerTime);
            while (!$this->_isSipInCall()) {
                $sipAnswerState = $this->_answerSipCall();
                $sipAnswerTime = microtime(true) - $startSipAnswerTime;
                if ($sipAnswerTime > 6) {
                    $this->reporter->setSabyEmulatorState('IN CALL', 'NO SIP CALL');
                    //$this->reporter->reportCallEnd($this->callID, $sipAnswerTime);
                    $this->endTheCall();
                    return false;
                }
                myLog("Answering SIP Call: $sipAnswerState. Timer [$sipAnswerTime] ");
            }
            //$this->reporter->sendAnsweringCommand($this->callID);
            $this->reporter->setSabyEmulatorState('IN CALL', 'MIXING');
            myLog("Going to start mixing");
            $mixingCount = 0;
            $mixResult = false;
            while (true) {
                $mixCounter = 0;
                do {
                    $mixResult = $this->_startAudioMix();
                    usleep(200000); //0.2 second
                    $mixCounter++;
                } while ($mixCounter <= 3 && !$mixResult);
                myLog("mixResult: " . $mixResult);

                if ($mixResult) {
                    $this->reporter->setSabyEmulatorState('IN ANSWERED CALL', 'CALL IN PROGRESS');
                    $this->callStartTime = time();
                    $this->reporter->setCallAnsweredBySaby();
                    return true;
                } else {
                    //check if SIP was dropped
                    if (!$this->_isSipInCall()) {
                        $this->reporter->setSabyEmulatorState('IN CALL', 'HUNG UP');
                        $this->reporter->setCallFinalState($this->callID);
                        $this->endTheCall();
                        return false;
                    }

                    //try to mix for ~2 seconds
                    if ($mixingCount > 20) {
                        $this->reporter->setSabyEmulatorState('IN CALL', 'NO MIXING');
                        $this->reporter->setCallFinalState($this->callID);
                        $this->endTheCall();
                        return false;
                    }

                    usleep(100000); //0.1 second
                    $mixingCount++;
                }
            }
        } else {
            return false;
        }
    }

    public function isCallerHangupBeforeAnswer()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        //if ($this->reporter->getAsteriskHangup($this->callID) === 'true') {
        if (!$this->_detectIcommingSipCall()) {
            myLog("Caller Droped the Call ...");
            //API tell abput the caller hang up
            $this->reporter->setSabyEmulatorState('IN CALL', 'CALLER HANGUP');
            $this->reporter->setCallFinalState($this->callID);
            $callEndTime = microtime(true) - $this->callDialTime;
            myLog("Call ENDED");
            $this->reporter->updateHangUpSide($this->callID, 'caller');
            //$this->reporter->reportCallEnd($this->callID, $callEndTime);
            $this->endTheCall();
            //sendFilterLogs($filterTimeStamp, 'caller_hangup=1', $destinationNumber);
            //sendFilterLogs($filterTimeStamp, 'hangup_reason=CallerHangup', $destinationNumber);
            return true;
        } else {
            return false;
        }
    }

    public function endTheCall()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        if ($this->automator->pythonUI->findAndClick(IMG . 'whatsapp_hangup.png', null, 1)) {
            $this->hangupSipCall();
        }
    }

    private function _CheckWhatsappCalleeImage()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        $foundAvatar = false;
        $tryingCount = 0;
        do {
            usleep(250000);
            $tryingCount += 1;
            myLog("Search for whatsapp avatar - try " . $tryingCount);
            $result = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell find /data/data/com." . $this->applicaion . "/files/Avatars/" . $this->callee . "@s.whatsapp.net.j 2>&1 &");
            myLog("Search for whatsapp avatar- try " . $tryingCount . ": " . $result);
            $foundAvatar = (substr_count($result, "find:") == 0);
        } while (!$foundAvatar && $tryingCount < 6);
        myLog("LEAVING >>>> " . __FUNCTION__ . "");
        return $foundAvatar;
    }

    private function _isSipInCall()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $output = shell_exec('linphonecsh generic calls');
        return (strstr($output, 'sip') && strstr($output, 'StreamsRunning'));
    }

    private function _detectIcommingSipCall()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        $output = shell_exec('linphonecsh status hook');
        myLog("LEAVING >>>> " . __FUNCTION__ . "");
        return substr_count($output, 'Incoming call from') > 0;
    }

    private function _answerSipCall()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $sleep = 200000; //0.2 second
        $tries = 0;
        while ($tries++ < 15) {
            $answerResult = shell_exec('linphonecsh generic "answer"');
            myLog('answer attepts ' . $tries . " With reply " . $answerResult);
            if ($this->_isSipInCall()) {
                return true;
            }
            usleep($sleep);
        }
        return false;
    }

    private function _startAudioMix()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        $cmd = '~/7eet-saby-whatsapp-ubuntu20/scripts/audio_mix_sip.sh';
        $output = shell_exec("$cmd 2>&1");

        if (strstr($output, 'You have to specify a source')) {
            myLog("LEAVING >>>> " . __FUNCTION__ . "");
            return false;
        }
        myLog("AUDIO MIX IS FUCKIN WORKIN BABY! \n\n");

        return true;
    }

    public function hangupSipCall()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        myLog("inside hangupSipCall()");
        shell_exec('linphonecsh generic terminate');
        myLog("LEAVING >>>> " . __FUNCTION__ . "");
    }

    public function closeCalligScreen()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        myLog("LEAVING >>>> " . __FUNCTION__ . "");
    }

    public function isMicOK()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        if ($this->automator->pythonUI->findAndClick(IMG . 'whatsapp_microphone_ok_big.png', null, 1)) {
            myLog("LEAVING >>>> " . __FUNCTION__ . "");
            return false;
        }
        myLog("LEAVING >>>> " . __FUNCTION__ . "");
        return true;
    }

    public function getCurrentActivity()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        $currentScreenActivity = shell_exec(ADB . ' -s emulator-' . $this->emulatorPort . ' shell dumpsys activity activities | grep "mResumedActivity" | awk ' . "'{print $4}'");
        $currentScreenActivity = str_replace("\n", "", str_replace("\r", "", $currentScreenActivity));
        myLog("The Result of " . __FUNCTION__ . " : " . $currentScreenActivity);
        myLog("LEAVING >>>> " . __FUNCTION__ . "");
        return  $currentScreenActivity;
    }

    public function dumpScreenToFile()
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        $cmd = ADB . ' -s emulator-' . $this->emulatorPort  . ' exec-out uiautomator dump /dev/tty > ' . $this->dumpScreenFile;
        mylog('Excuting ' . __FUNCTION__ . ' >>> ' . $cmd);
        exec($cmd);
        mylog('Leaving ' . __FUNCTION__ . ' >>> ');
        unset($cmd);
    }

    public function searchStringInDumpScreen($string)
    {
        myLog("Running >>>> " . __FUNCTION__ . "");
        $xmlFile = file_get_contents($this->dumpScreenFile);
        if (substr_count($xmlFile, $string) > 0) {
            // Found
            $returnValue = true;
            mylog('Result ' . __FUNCTION__ . ' >>> Find ' . $string);
        } else {
            // Found
            $returnValue = false;
            mylog('Result ' . __FUNCTION__ . ' >>> Didnt Find ' . $string);
        }
        mylog('Leaving ' . __FUNCTION__ . ' >>> ' . $string . ' >>> ' . $returnValue);
        unset($xmlFile, $string);
        return $returnValue;
    }
}
