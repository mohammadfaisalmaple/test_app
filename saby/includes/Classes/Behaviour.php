<?php
class Behaviour
{
    public $saby                    = null;
    public $sabyFullName            = null;
    public $waNumber                = null;
    public $waName                  = null;
    public $emulatorPort            = null;
    public $application             = null;
    public $status                  = false;
    public $group                   = null;
    public $Countrygroup            = null;
    public $androidID               = null;
    public $automator               = null;
    public $botMessages             = null;
    public $timeSpan                = null;
    public $countryCode             = null;
    public $CallLimitsFolder        = null;
    public $ContactsFolder          = null;
    public $saby_working_hour_start = null;
    public $saby_working_hour_end   = null;
    public $countryName             = null;
    public $reporter                = null;
    public $_data                   = null;
    public $behaveId                = null;
    public $multiEmuFirstBehavior   = false;
    public $env_host_name   = false;

    public function __construct($saby, $sabyFullName, $application, $emulator_port, $android_id, $autoObject, $transporterObject, $botArray, $TimeSpan, $CallLimitsFolder, $ContactsFolder, $saby_working_hour_start, $saby_working_hour_end)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->saby                    = $saby;
        $this->sabyFullName            = $sabyFullName;
        $this->emulatorPort            = $emulator_port;
        $this->application             = $application;
        $this->androidID               = $android_id;
        $this->automator               = $autoObject;
        $this->reporter                = $transporterObject;
        $this->botMessages             = $botArray;
        $this->timeSpan                = $TimeSpan;
        $this->CallLimitsFolder        = $CallLimitsFolder;
        $this->ContactsFolder          = $ContactsFolder;
        $this->saby_working_hour_start = $saby_working_hour_start;
        $this->saby_working_hour_end   = $saby_working_hour_end;
        $this->reporter->setBehaviorApi("http://filter.7eet.net/whatsapp_behaviour_api");
        unset(
            $saby,
            $sabyFullName,
            $application,
            $emulator_port,
            $android_id,
            $autoObject,
            $transporterObject,
            $botArray,
            $TimeSpan,
            $CallLimitsFolder,
            $ContactsFolder,
            $saby_working_hour_start,
            $saby_working_hour_end
        );
    }
    public function set_env_host_name($host_name){
        $this->env_host_name=$host_name;
    }

    public function __destruct()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        //$this->cleanup();
        myLog("Destroying Behavior Class");
    }

    public function cleanup()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
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
        unset($clsVar, $_, $value);
    }

    public function SetSabyDetails()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        /*$found = 0;
        $url = "http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=Validate&machine_id=" . $this->saby ."&android_id=" . $this->androidID;
        myLog("Saby Details Validation URL : ".$url);
        $found = file_get_contents($url);

        if($found == 0 && $found == '0')
        {*/
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start  -n com." . $this->application . "/.profile.ProfileInfoActivity");
        sleep(5);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 990 930 990 353 500");
        sleep(5);
        $profileDumpScreen = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " exec-out uiautomator dump /dev/tty");
        /*Find Number in Whatsapp*/
        $findNumber = strpos($profileDumpScreen, '+');
        $WaNumber   = substr($profileDumpScreen, $findNumber, 21);
        //remove anything that doesn't contain a number
        $WaNumber       = preg_replace("/[^0-9]/", "", $WaNumber);
        $this->waNumber = $WaNumber;
        myLog("My Whatsapp Number is : " . $WaNumber);
        if(strlen($WaNumber) > 5){
            $this->reporter->setSabyEmulatorActivationInfo('sim_number', $this->waNumber);
        }


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

        myLog("Checking Whatsapp Number Details: " . $this->waNumber);
        list($countryName, $countryCode, $localNumber) = checkCountry($this->waNumber);
        myLog("countryCode : " . $countryCode);
        $this->countryCode = $countryCode;
        myLog("countryCode : " . $countryName);
        $this->countryName = $countryName;
        if(strlen($WaNumber) > 5) {
            $this->reporter->setSabyBehaviorDetails($this->waName, $this->waNumber);
            $this->reporter->registerSabyInBehvaior($this->waName, $this->waNumber);
        }
        //httpGetAsync("http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=Register&machine_id=" . $this->sabyFullName . "&android_id=" . $this->androidID . "&number=" . $this->waNumber . "&name=" . $this->waName . "&country_code=" . $this->countryCode);
        /*}
        else {
        myLog("This Saby Already Defined For Behaviour...");
        }*/
        $this->CannotTakeCall();
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        unset($WaName, $countryCode, $countryName, $beforeNamePosition, $profileDumpScreen, $findNumber, $WaNumber, $afterName, $afterNamePosition, $nameEndPosition, $localNumber, $beforeName, $nameStartPosition);
    }

    public function GetGroupID()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $tryCount = 0;
        do {
            $result = $this->reporter->getGroupBehvaiorId();
            if ($tryCount == 10) {
                break;
            }
            $tryCount++;
        } while ($result == null);
        $result             = json_decode($result);
        $this->group        = $result->{'saby_group'};
        $this->Countrygroup = $result->{'saby_country_group'};
        myLog("Behaviour Membership ID : " . $this->group);
        myLog("Behaviour Country Membership ID : " . $this->Countrygroup);
        unset($url, $result);
    }

    public function GetGroupMembers()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        myLog("Preparing And Updating Member Contacts");
        $GroupMembers = $this->reporter->GetGroupMembers($this->group, $this->Countrygroup);
        if (!empty($GroupMembers)) {
            $results = json_decode($GroupMembers);
            foreach ($results as $result) {

                if(isset($result->{'saby_old_number'}) && !empty($result->{'saby_old_number'})){
                    $contact_id = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content query --uri content://com.android.contacts/data/phones/filter/+" . $result->{'saby_old_number'} . " --projection contact_id");
                    myLog("Check old Contact Result : ");
                    if (!(substr_count($contact_id, 'No result found.') > 0)) {
                        myLog("check delete old contact");
                        $position = strpos($contact_id, "=");
                        if ($position !== false) {
                            myLog("delete old contact");
                            $number = trim(substr($contact_id, $position + 1));
                            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content delete --uri content://com.android.contacts/contacts/".$number);
                        } else {
                            myLog("Number not found");
                        }
                    }
                }

                if(isset($result->{'saby_number'}) && !empty($result->{'saby_number'})) {
                    $isAlreadyAdded = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content query --uri content://com.android.contacts/data/phones/filter/+" . $result->{'saby_number'} . " --projection display_name");
                    myLog("Check Contact Result : " . $isAlreadyAdded);

                    if (substr_count($isAlreadyAdded, $result->{'saby_name'}) > 0) {
                        myLog($result->{'saby_name'} . " Already in Contacts List No Need To Add Again");
                    } else {
                        $command = ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.INSERT -t vnd.android.cursor.dir/contact -e name '" . str_replace(" ", "\ ", $result->{'saby_name'}) . "' -e phone +" . $result->{'saby_number'};
                        do {
                            exec($command);
                            myLog("THE COMMAND IN " . __FUNCTION__ . " => " . $command);
                        } while (!$this->automator->pythonUI->exists(IMG . 'CreateNewContact.png', null, 10));

                        if ($this->automator->pythonUI->findAndClick(IMG . 'SaveContactButton.png', null, 10)) {
                            //exec(ADB." -s emulator-".$this->emulatorPort." shell input tap 980 180");
                        }
                        $this->createContactFile($result->{'saby_number'});
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
                        sleep(1);
                        myLog($result->{'saby_name'} . " / " . $result->{'saby_number'} . " Saved To Contacts Successfully");
                    }
                }
            }
        } else {
            myLog("No Contacts List Found");
        }
        unset($url, $GroupMembers, $results, $result, $isAlreadyAdded, $command, $regionSave, $regionArr);
    }


    public function add_contact_if_not_exist($saby_number,$saby_name){
        if(isset($saby_number) && isset($saby_name)){
            $isAlreadyAdded = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content query --uri content://com.android.contacts/data/phones/filter/+" . $saby_number . " --projection display_name");
            myLog("Check Contact Result : " . $isAlreadyAdded);

            if (substr_count($isAlreadyAdded, $saby_name) > 0) {
                myLog($saby_name . " Already in Contacts List No Need To Add Again");
                return true;
            } else {
                $command = ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.INSERT -t vnd.android.cursor.dir/contact -e name '" . str_replace(" ", "\ ", $saby_name) . "' -e phone +" . $saby_number;
                do {
                    exec($command);
                    myLog("THE COMMAND IN " . __FUNCTION__ . " => " . $command);
                } while (!$this->automator->pythonUI->exists(IMG . 'CreateNewContact.png', null, 10));

                if ($this->automator->pythonUI->findAndClick(IMG . 'SaveContactButton.png', null, 10)) {
                    //exec(ADB." -s emulator-".$this->emulatorPort." shell input tap 980 180");
                    $this->createContactFile($saby_number);
                    sleep(1);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
                    sleep(1);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
                    sleep(1);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
                    sleep(1);
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
                    sleep(1);
                    myLog($saby_number . " / " . $saby_number . " Saved To Contacts Successfully");
                    return true;
                }
            }
        }
        return false;
    }

    public function MakeCall()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->CannotTakeCall();
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "DIALING");
        myLog("Get Random Memeber To Start A Call");
        $CallTry    = 0;
        $callCreated = false;
        while ($CallTry < 3) {

            $MemeberName = $this->GetContactMakeCall();
            if (!empty($MemeberName)) {
                $result = json_decode($MemeberName);

                if(!$this->add_contact_if_not_exist($result->{'saby_number'},$result->{'saby_name'})){
                    myLog("can't add this contact" . $result->{'saby_number'} .' name' . $result->{'saby_name'});
                    break;
                }

                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Main");
                myLog("Checking If Contact is Added Recently By Number : " . $result->{'saby_number'});
                if (!$this->checkContactFile($result->{'saby_number'})) {
                    myLog($result->{'saby_name'} . " Is Added Recently Will Not Call");
                    $CallTry++;
                    continue;
                }

                myLog("Will Call : " . $result->{'saby_name'});
                // Report to Behaving manager in 7eet-filter.net
                $this->behaveId = $this->reporter->setSabyBehaveLog("OutCall", $result->{'saby_number'});
                $command        = ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Conversation -e jid '" . $result->{'saby_number'} . "@s.whatsapp.net'";
                myLog("Openning WhatsApp Contact");
                exec($command);
                $regionDial  = null;
                $regionDial2 = null;
                $regionArr = null;
                $this->automator->putAVDInFocus();
                if ($this->automator->pythonUI->findAndClick(IMG . 'whatsapp_dial_big.png', null, 7)) {
                    $this->automator->pythonUI->findAndClick(IMG . 'startVoiceCall.png', null, 5);
                    $this->reporter->BehvaiorLog(3); //outGoingCall attemps
                    $this->automator->dumpScreenToFile();
                    if($this->automator->searchStringInDumpScreen("Start voice call?")){
                        myLog("\n found Start voice call ? port : \n".$this->emulatorPort);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 840 1010");
                    }else{
                        myLog("\ni can't found Start voice call ? port : ".$this->emulatorPort ."\n");
                        while ($this->automator->pythonUI->exists(IMG . 'start_voice_call.png', null, 5) ||  $this->automator->pythonUI->exists(IMG . 'new_Wa_start_call.png', null, 5)) {
                            $this->automator->putAVDInFocus();
                            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 840 1010");
                        }
                    }


                    $this->reporter->setBehaviorCallattempt();
                    $callCreated = true;
                    break;
                } else {
                    $this->reporter->setSabyEmulatorState($currentState, "SLEEP");
                    $CallTry++;
                    sleep(30);
                }


            } else {
                $CallTry++;
            }
        }

        if ($callCreated) {
            $this->reporter->setSabyBehaveLog($this->behaveId, "Calling");
            if (!$this->isCallBusy()) {
                $this->automator->putAVDInFocus();
                $this->isRinging();
                // this is outging call so check with false value
                if ($this->isCallAnswered(false)) {
                    $this->reporter->BehvaiorLog(4); //outGoingCall Answerd
                    $this->automator->putAVDInFocus();
                    $this->reporter->setBehaviorCallAnswered();
                    $this->CallController();
                }
            }
        }
        $regionHangup = null;
        $this->automator->pythonUI->findAndClick(IMG . 'whatsapp_hangup.png', null, 1);
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        unset(
            $url,
            $GroupMembers,
            $results,
            $result,
            $isAlreadyAdded,
            $command,
            $regionArr,
            $regionHangup,
            $MemeberName,
            $currentState,
            $CallTry,
            $callCreated,
            $MemeberName,
            $regionDial,
            $regionDial2,
            $regionArr,
            $callCreated
        );
    }

    public function AnswerCall()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->CanTakeCall();
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "WAITING INCOMMING CALLS");
        myLog("Checking Behaviour Incomming Calls");
        $this->CloseVideoCall();
        if ($this->automator->pythonUI->exists(IMG . 'WhatsappIncommingCall.png', null, 50)) {
            if (!$this->automator->detectIncommingCallType()) {
                // Report to Behaving manager in 7eet-filter.net
                $this->reporter->BehvaiorLog(5); //InCommingCall attepmts
                $this->behaveId = $this->reporter->setSabyBehaveLog("IncCall", "UnDetected");
                $this->CannotTakeCall();
                myLog("Incomming Call Detected");
                exec(ADB . " -s emulator-" . $this->emulatorPort . " emu avd hostmicon");
                $randomGap = rand(1500000, 3000000);
                usleep($randomGap);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 540 1477 540 1000");
                // this is incomming call so check with true value
                if ($this->isCallAnswered(true)) {
                    $this->reporter->BehvaiorLog(6); //InCommingCall Answerd
                    $this->CallController();
                }
            } else {
                $this->automator->pythonUI->findAndClick(IMG . 'whatsapp_hangup.png', null, 2);
            }
            unset($currentState, $randomGap);
            return true;
        } else {
            unset($currentState, $randomGap);
            return false;
        }
    }

    public function AnswerCallWhileReady()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->CanTakeCall();
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "SHORT WAITING INCOMMING CALLS ");
        myLog("Checking Behaviour Incomming Calls");
        if ($this->automator->pythonUI->exists(IMG . 'WhatsappIncommingCall.png', null, 15)) {
            // Report to Behaving manager in 7eet-filter.net
            $this->reporter->BehvaiorLog(5); //Incomming Call 
            $this->behaveId = $this->reporter->setSabyBehaveLog("IncCall", "UnDetected");
            $this->CannotTakeCall();
            myLog("Incomming Call Detected");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " emu avd hostmicon");
            $randomGap = rand(1500000, 3000000);
            usleep($randomGap);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 540 1477 540 1000");
            // this is incomming call so check with true value
            if ($this->isCallAnswered(true)) {
                $this->reporter->BehvaiorLog(6); //InCommingCall Answerd
                $this->CallController();
            }
            $this->CannotTakeCall();
            unset($currentState, $randomGap);
            return true;
        } else {
            unset($currentState, $randomGap);
            $this->CannotTakeCall();
            return false;
        }
    }

    public function RejectCall()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $iscall = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell dumpsys notification | grep -E Decline");
        if (strlen($iscall) > 0) {
            $this->behaveId = $this->reporter->setSabyBehaveLog("IncCall", "UnDetected");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 162 1477 162 1000");
            $this->reporter->setSabyBehaveLog($this->behaveId, "Rejected");
        }
        unset($iscall);
    }

    public function SendMessage()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "SENDING NEW MESSAGES");
        $GroupMembers = $this->GetSendMessage();
        if (!empty($GroupMembers)) {


            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Main");
            $results = json_decode($GroupMembers);
            foreach ($results as $result) {
                if(!$this->add_contact_if_not_exist($result->{'saby_number'},$result->{'saby_name'})){
                    myLog('cant add this contact'. $result->{'saby_number'} .'name'.$result->{'saby_name'});
                    continue;
                }

                $this->behaveId = $this->reporter->setSabyBehaveLog("SendMsg", $result->{'saby_number'});
                $this->reporter->BehvaiorLog(1); //New Message 
                $msg            = getRandomSentence();
                $this->PrepareAndSendMessage($result->{'saby_number'} . '@s.whatsapp.net', $msg, '0');
                $microSleep = rand(1000000, 2000000);
                usleep($microSleep);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
                $microSleep = rand(1000000, 2000000);
                usleep($microSleep);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
                $microSleep = rand(1000000, 2000000);
                usleep($microSleep);
            }
        }
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        unset($microSleep, $url, $GroupMembers, $results, $result, $currentState, $msg);
    }

    public function ReplyMessage($type)
    {

        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "REPLYING MESSAGES");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Main");
        $cmd_part1 = ADB . " -s emulator-" . $this->emulatorPort . " shell ";
        if ($type == 'All') {
            $cmd_part2 = "'sqlite3 /data/data/com.whatsapp/databases/msgstore.db \"select jid.raw_string from chat join jid on chat.jid_row_id = jid._id where unseen_message_count > 0 \"'";
        } else {
            $cmd_part2 = "'sqlite3 /data/data/com.whatsapp/databases/msgstore.db \"select jid.raw_string from chat join jid on chat.jid_row_id = jid._id where unseen_message_count > 0 limit 3 \"'";
        }

        myLog("To Get Unseen Messsages Will Execute : " . $cmd_part1 . $cmd_part2);
        // get messages Count and Sender Number
        $messages    = shell_exec($cmd_part1 . $cmd_part2);
        $sendersList = explode("\n", $messages);
        foreach ($sendersList as $sender) {
            if ($sender != '') {
                myLog("We have new Message from : " . $sender);
                list($senderNumber, $domain) = explode('@', $sender);
                if (substr_count($senderNumber, '-') > 0) {
                    list($senderNumber, $dontCare) = explode('-', $senderNumber);
                }
                myLog("Lets Check is he GroupMemeber : " . $senderNumber);
                $MemeberName = $this->reporter->ValidateMemeber($senderNumber);
                myLog("Member Validation URL Result: " . var_dump($MemeberName));
                $this->behaveId = $this->reporter->setSabyBehaveLog("ReplyMsg", $senderNumber);
                if (!empty($MemeberName)) {
                    myLog($senderNumber . " Is a GroupMemeber");
                    $msg = getRandomSentence();
                    $this->PrepareAndSendMessage($sender, $msg, '0');
                    $this->reporter->BehvaiorLog(2);
                } elseif (empty($MemeberName)) {
                    myLog($senderNumber . " Is Not a GroupMemeber");
                    $cmd_part1 = ADB . " -s emulator-" . $this->emulatorPort . " shell ";
                    $cmd_part2 = "'sqlite3 /data/data/com.whatsapp/databases/msgstore.db ";
                    if ((int)str_replace('.', '', $this->automator->getApplicationVersion('com.whatsapp')) >= 2221070) {
                        $cmd_part3 = '"' . "select text_data from message where chat_row_id = (SELECT chat._id FROM chat JOIN jid on chat.jid_row_id = jid._id where jid.user = $senderNumber limit 1) and from_me <> 1 and text_data is NOT NULL order by received_timestamp desc limit 1" . "\"'";
                    } else {
                        $cmd_part3 = '"' . "select data from messages where key_remote_jid like " . '\"' . $senderNumber . '\"' . " and data is not null and key_from_me <> 1 order by received_timestamp limit 1" . '"' . "'";
                    }
                    myLog("To Get Unseen Messsages Will Execute : " . $cmd_part1 . $cmd_part2 . $cmd_part3);
                    $NewMessage = trim(shell_exec($cmd_part1 . $cmd_part2 . $cmd_part3));
                    $id         = null;
                    $id         = searchForKeyword($NewMessage, $this->botMessages);
                    $this->reporter->BehvaiorLog(2); // Reply Message
                    if ($id != null) {
                        $msg = $this->botMessages[$id][1];
                        $this->PrepareAndSendMessage($sender, $msg, '1');
                    } else {
                        $msg = get_random_text_unknown_reply();
                        $this->PrepareAndSendMessage($sender, $msg, '1');
                    }
                } else {
                    if (substr_count($sender, "-") == 0) {
                        myLog($senderNumber . " Is a GroupMemeber in a Whatsapp Group");
                        $msg = getRandomSentence();
                        $this->PrepareAndSendMessage($sender, $msg, '0');
                        $this->reporter->BehvaiorLog(2);
                    } else {
                        myLog("This Message from Strangers Whatsapp Group so... will not reply");
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Conversation -e jid '" . $sender . "'");
                        usleep(500000);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_back");
                        usleep(500000);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_back");
                    }
                }
            }
            $microSleep = rand(1000000, 2000000);
            usleep($microSleep);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            $microSleep = rand(1000000, 2000000);
            usleep($microSleep);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
            $microSleep = rand(1000000, 2000000);
            usleep($microSleep);
        }
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        unset($microSleep, $senderNumber, $sender, $msg, $cmd_part1, $cmd_part2, $messages, $currentState, $domain, $dontCare, $url, $cmd_part3, $NewMessage,  $MemeberName, $type);
    }

    public function PrepareAndSendMessage($number, $msg, $type)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $tryCount = 0;
        do {
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Conversation -e jid '" . $number . "'");
            sleep(1);
            $currentScreenActivity  = shell_exec(ADB . ' -s emulator-' . $this->emulatorPort . ' shell dumpsys activity activities | grep "mResumedActivity"');
            $isConversationActivity = substr_count($currentScreenActivity, 'Conversation') > 0;
            $tryCount++;
        } while (!$isConversationActivity && $tryCount < 3);
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        $regionMSG = null;
        $regionArr = null;
        $this->automator->dumpScreenToFile();
        if ($this->automator->searchStringInDumpScreen("Disappearing messages were turned on") || $this->automator->searchStringInDumpScreen("While this setting is on, new messages will disappear from this chat after 7 days.")) {
            list($xCoordinate, $yCoordinate) = $this->automator->SearchCoordinatesByResourceId("com.whatsapp:id/ephemeral_nux_finished");
            if ($xCoordinate > 0 && $yCoordinate > 0) {
                $this->automator->click($xCoordinate, $yCoordinate);
                $this->automator->click($xCoordinate, $yCoordinate);
            } else {
                list($xCoordinate, $yCoordinate) = $this->automator->SearchCoordinatesByString("OK");
                if ($xCoordinate > 0 && $yCoordinate > 0) {
                    $this->automator->click($xCoordinate, $yCoordinate);
                }
            }
        }
        if ($this->automator->pythonUI->findAndClick(IMG . 'WhatsappMessagingArea2.png', null, 10)) {
            $microSleep = rand(1000000, 1500000);
            usleep($microSleep);
            $VoiceOrText = rand(0, 10);
            if (($VoiceOrText >= 0 && $type == '0') || $type == '1') {
                myLog($VoiceOrText . " / " . $type . " Means Text Message");
                $this->WriteText($msg);
                $this->automator->pythonUI->findAndClick(IMG . 'WhatsappSendMessage.png', null, 10);
            } else {
                myLog($VoiceOrText . " / " . $type . " Means Voice Message");
                $this->SendVoiceNote();
            }
        }

        $this->reporter->setSabyBehaveLog($this->behaveId, "Sent");
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_back");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_back");
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        unset($microSleep, $VoiceOrText, $type, $regionArr, $regionSend, $regionMSG, $isConversationActivity, $tryCount);
    }

    public function WriteText($text)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->automator->pythonUI->type($text);
        unset($text);
    }

    public function isCallBusy()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        if ($this->automator->pythonUI->exists(IMG . 'whatsapp_busy_big.png', null, 5)) {
            $this->reporter->setSabyBehaveLog($this->behaveId, "Busy");
            $this->automator->pythonUI->findAndClick(IMG . 'whatsapp_hangup.png', null, 2);
            unset($regionOkHangup, $regionArr, $regionOkHangup);
            return true;
        } else {
            unset($regionOkHangup, $regionArr, $regionOkHangup);
            return false;
        }
    }

    public function isRinging()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        if ($this->automator->pythonUI->exists(IMG . 'WhatsppRinging.png', null, 2)) {
            myLog("Call Is Ringing ...");
            $currentState = $this->reporter->getCurrentState();
            $this->reporter->setSabyEmulatorState($currentState, 'RINGING');
            $this->reporter->setSabyBehaveLog($this->behaveId, "Ringing");
            unset($currentState);
            return true;
            //sendFilterLogs( $filterTimeStamp,'ringing=1',$destinationNumber);
        } else {
            unset($currentState);
            return false;
        }
    }

    public function isCallAnswered($incomming)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        // incomming variable detirmins the incomming and outgoing calls with true / false
        if ($this->automator->pythonUI->exists(IMG . 'whatsapp_add_contact.png', null, 30)) {
            $this->reporter->setSabyBehaveLog($this->behaveId, "Answered");
            $currentState = $this->reporter->getCurrentState();
            myLog("Going to start playback");
            $playFile = rand(1, 17);
            exec("~/7eet-saby-whatsapp-ubuntu20/scripts/audio_fplay.sh " . $playFile . ".mp3 & ");
            myLog("~/7eet-saby-whatsapp-ubuntu20/scripts/audio_fplay.sh " . $playFile . ".mp3 & ");
            $mixingCount = 0;
            myLog("Going to start mixing");
            while ($mixingCount <= 20) {

                $mixResult = startPlaybackMix();
                usleep(200000); //0.1 second
                $mixResult = startPlaybackMix();
                usleep(100000); //0.1 second
                $mixResult = startPlaybackMix();

                myLog("mixResult: " . $mixResult);
                if ($mixResult) {
                    //if we get here, this means call started! YAAAAY
                    break;
                }
                usleep(100000); //0.1 second
                $mixingCount++;
            }
            if ($mixingCount > 20) {
                $this->reporter->setSabyEmulatorState($currentState, "NO MIXING");
                return false;
            }
            if ($incomming) {
                $this->reporter->setSabyEmulatorState($currentState, "INCOMMING ANSWERED");
            } else {
                $this->reporter->setSabyEmulatorState($currentState, "OUTGOING ANSWERED");
            }
            unset($currentState, $incomming, $mixingCount, $playFile);
            return true;
        } else {
            unset($currentState, $incomming, $mixingCount, $playFile);
            return false;
        }
    }

    public function CallController()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $randomCallDuration = rand(90, 300);
        $callStartTimeStamp = microtime(true);
        while ($this->automator->pythonUI->exists(IMG . 'whatsapp_add_contact.png', null, 5)) {
            if ($this->automator->pythonUI->exists(IMG . 'WhatsappReportCall.png', null, 2)) {
                $this->automator->pythonUI->findAndClick(IMG . 'WhatsappReportCallOK.png', null, 2);
            }
            if ($this->automator->pythonUI->findAndClick(IMG . 'whatsapp_microphone_ok_big.png', null, 2)) {
                break;
            }

            if (microtime(true) - $callStartTimeStamp >= $randomCallDuration) {
                if ($this->automator->pythonUI->findAndClick(IMG . 'whatsapp_hangup.png', null, 2)) {
                    break;
                }
            }
            $this->automator->pythonUI->findAndClick(IMG . 'WhatsappCallCancel.png', null, 1);
        }
        exec("~/7eet-saby-whatsapp-ubuntu20/scripts/audio_stop.sh &");
        if ($this->automator->pythonUI->exists(IMG . 'WhatsappReportCall.png', null, 1)) {
            $this->automator->pythonUI->findAndClick(IMG . 'WhatsappReportCallOK.png', null, 1);
        }
        $this->automator->pythonUI->findAndClick(IMG . 'whatsapp_microphone_ok_big.png', null, 1);
        $this->automator->pythonUI->findAndClick(IMG . 'WhatsappCallCancel.png', null, 1);
        $this->automator->pythonUI->findAndClick(IMG . 'WhatsappRateCall.png', null, 1);
        $this->automator->pythonUI->findAndClick(IMG . 'WhatsappRateCallSubmit.png', null, 1);
        unset($regionOkBig, $regionArr, $regionOkHangup, $randomCallDuration, $callStartTimeStamp);
    }

    public function createContactFile($ContactNumber)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $ContactFile = $this->ContactsFolder . "/Contact-" . $this->sabyFullName . '-' . $ContactNumber . ".txt";
        $TimeStamp   = microtime(true);
        file_put_contents($ContactFile, $TimeStamp);
        unset($ContactFile, $ContactNumber, $TimeStamp);
    }

    public function checkContactFile($ContactNumber)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $ContactFile      = $this->ContactsFolder . "/Contact-" . $this->sabyFullName . '-' . $ContactNumber . ".txt";
        $ContactTimestamp = file_get_contents($ContactFile);
        $TimeDiff         = microtime(true) - $ContactTimestamp;
        myLog("Time to Wait is : " . $this->timeSpan);
        myLog("Actual time is : " . $TimeDiff);
        if ($TimeDiff >= $this->timeSpan) {
            unset($ContactFile, $ContactNumber, $ContactTimestamp, $TimeDiff);
            return true;
        } else {
            unset($ContactFile, $ContactNumber, $ContactTimestamp, $TimeDiff);
            return false;
        }
    }

    public function CreateWhatsappGroup()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "CREATING GROUP");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.group.GroupMembersSelector");
        $this->automator->pythonUI->findAndClick(IMG . 'WhastappMagnifier.png', null, 10);
        $first        = true;
        //$url          = "http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=CreateGroup&machine_id=" . $this->sabyFullName . "&GroupID=" . $this->group . "&CountryGroupID=" . $this->Countrygroup;
        $GroupMembers = $this->GetWhatsAppGroupMembers();
        $members      = '';

        if (!empty($GroupMembers)) {
            $results = json_decode($GroupMembers);
            foreach ($results as $result) {
                //$this->WriteText($result->{'saby_number'});
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input text " . $result->{'saby_number'});
                sleep(5);
                if ($first) {
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 400 400");
                    $first = false;
                    $members .= $result->{'saby_number'};
                } else {
                    exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 400 700");
                    $members .= ' , ' . $result->{'saby_number'};
                }
                $Microsleep = rand(500000, 1250000);
                usleep($Microsleep);
            }
            $this->behaveId = $this->reporter->setSabyBehaveLog("CreateWaGroup", $members);
        }

        $this->automator->pythonUI->findAndClick(IMG . 'WhastappGreenArrow.png', null, 10);

        $this->automator->pythonUI->findAndClick(IMG . 'WhatsappAddGroupName.png', null, 10);
        $groupName = get_random_about();
        $this->WriteText($groupName);
        $this->automator->pythonUI->findAndClick(IMG . 'WhatsappGreenTick.png', null, 10);

        if ($this->automator->pythonUI->exists(IMG . 'WhastappGroupCreated.png', null, 10)) {

            if ($this->automator->pythonUI->findAndClick(IMG . 'WhatsappMessagingArea2.png', null, 10)) {
                $msg = getRandomSentence();
                $this->WriteText($msg);
                $this->automator->pythonUI->findAndClick(IMG . 'WhatsappSendMessage.png', null, 10);
                $this->reporter->setSabyBehaveLog($this->behaveId, "Created");
                //$this->SendVoiceNote();
            }
        }
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        $microSleep = rand(1000000, 2000000);
        usleep($microSleep);
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        unset($microSleep, $regionArr, $regionSend, $msg, $regionMSG, $regionArr, $groupName, $regionGroupName, $members, $currentState, $regionArrow, $regionSearch, $first, $url);
    }

    public function CanTakeCall()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        //httpGetAsync("http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=AllowCalls&machine_id=" . $this->sabyFullName);
        $this->reporter->enabaleBehaviourCalls();
    }

    public function CannotTakeCall()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        // httpGetAsync("http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=DisallowCalls&machine_id=" . $this->sabyFullName);
        $this->reporter->disabaleBehaviourCalls();
    }
    public function GetWhatsAppGroupMembers()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        // httpGetAsync("http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=DisallowCalls&machine_id=" . $this->sabyFullName);
        return  $this->reporter->GetGroupMembers($this->group, $this->Countrygroup);
    }

    public function SendVoiceNote()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $playFile = rand(1, 17);
        exec("~/7eet-saby-whatsapp-ubuntu20/scripts/audio_fplay.sh " . $playFile . ".mp3 & ");
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "SEND VOICE NOTE");
        $mixingCount = 0;
        myLog("Going to start mixing");
        while ($mixingCount <= 20) {

            $mixResult = startPlaybackMix();
            usleep(200000); //0.1 second
            $mixResult = startPlaybackMix();
            usleep(100000); //0.1 second
            $mixResult = startPlaybackMix();

            myLog("mixResult: " . $mixResult);
            if ($mixResult) {
                //if we get here, this means call started! YAAAAY
                break;
            }
            usleep(100000); //0.1 second
            $mixingCount++;
        }
        $voiceNote = rand(5000, 30000);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 1000 825 1000 825 " . $voiceNote);
        myLog(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 1000 825 1000 825 " . $voiceNote);
        exec("~/7eet-saby-whatsapp-ubuntu20/scripts/audio_stop.sh &");
        unset($playFile, $voiceNote, $currentState, $mixingCount, $mixResult);
    }

    public function callLimiter($dailyMaxCalls)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);

        $openTime    = $this->reporter->getConfig("limit_open_time");
        $currentTime = date('H:i:s');
        if ($currentTime >= $openTime) {
            $date = date('Y-m-d', strtotime("+1 days"));
            myLog('Time Now is : ' . $currentTime . ' and it is After ' . $openTime . ' so will check in file of :' . $date);
        } else {
            $date = date('Y-m-d');
            myLog('Time Now is : ' . $currentTime . ' and it is Before ' . $openTime . ' so will check in file of :' . $date);
        }

        $dailyMaxCallsFilePath = $this->CallLimitsFolder . "/" . $this->sabyFullName . '-' . $date . "-Calls.conf";
        if (!file_exists($dailyMaxCallsFilePath)) {
            $currentCalls = 0;
            file_put_contents($dailyMaxCallsFilePath, $currentCalls);
            $remainCalls = $dailyMaxCalls - $currentCalls;
            myLog('RemainCalls Now: ' . $remainCalls);
            $this->reporter->setSabyEmulatorCallLimitRemain($remainCalls);
            $this->reporter->setSabyEmulatorCallLimitOpened();
            $this->multiEmuFirstBehavior = true;
            unset($currentCalls, $remainCalls, $dailyMaxCalls, $dailyMaxCallsFilePath, $date, $currentTime, $openTime);
            return false;
        } else {
            myLog('Max Calls Allowed : ' . $dailyMaxCalls);
            $currentCalls = file_get_contents($dailyMaxCallsFilePath);
            myLog('Current Calls : ' . $currentCalls);
            $remainCalls = $dailyMaxCalls - $currentCalls;
            myLog('RemainCalls Now: ' . $remainCalls);
            $this->reporter->setSabyEmulatorCallLimitRemain($remainCalls);
            if ($currentCalls >= $dailyMaxCalls) {
                // exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
                unset($currentCalls, $remainCalls, $dailyMaxCalls, $dailyMaxCallsFilePath, $date, $currentTime, $openTime);
                return true;
            } else {
                unset($currentCalls, $remainCalls, $dailyMaxCalls, $dailyMaxCallsFilePath, $date, $currentTime, $openTime);
                return false;
            }
        }
    }

    public function updateCallsCount($dailyMaxCalls)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $openTime    = $this->reporter->getConfig("limit_open_time");
        $currentTime = date('H:i:s');
        if ($currentTime >= $openTime) {
            $date = date('Y-m-d', strtotime("+1 days"));
            myLog('Time Now is : ' . $currentTime . ' and it is After ' . $openTime . ' so will update file of :' . $date);
        } else {
            $date = date('Y-m-d');
            myLog('Time Now is : ' . $currentTime . ' and it is Before ' . $openTime . ' so will update file of :' . $date);
        }
        $dailyMaxCallsFilePath = $this->CallLimitsFolder . "/" . $this->sabyFullName . '-' . $date . "-Calls.conf";
        $currentCalls          = file_get_contents($dailyMaxCallsFilePath);
        myLog('CurrentCalls Was: ' . $currentCalls);
        $currentCalls += 1;
        myLog('CurrentCalls Now: ' . $currentCalls);
        file_put_contents($dailyMaxCallsFilePath, $currentCalls);
        $remainCalls = $dailyMaxCalls - $currentCalls;
        myLog('RemainCalls Now: ' . $remainCalls);
        $this->reporter->setSabyEmulatorCallLimitRemain($remainCalls);
        unset($currentCalls, $remainCalls, $dailyMaxCalls, $dailyMaxCallsFilePath, $date, $currentTime, $openTime);
    }

    public function DialTimeZone()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $timeNow = $this->_checkTimeZone();
        myLog("TimeNow : $timeNow");
        if ($timeNow >= $this->saby_working_hour_start && $timeNow <= $this->saby_working_hour_end) {
            unset($timeNow);
            return true;
        } else {
            unset($timeNow);
            return false;
        }
    }

    public function behaveWithTimer($sleepTimer, $startTimeStamp, $switch = null)
    {
        $returnValue = false;
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        do {
            $this->AnswerCall();
            $SleepIsNotTimeOut = $sleepTimer > (microtime(true) - $startTimeStamp);

            if($SleepIsNotTimeOut){
                $this->AnswerTestCallWithSleep($this->env_host_name);
            }

            if ($switch == 'autoSwitch') {
                $sleepCountConfig = $this->reporter->getConfig('sleep_count');
                $sleepCountServer = $this->reporter->getSleepCountByServer();
                $switchFlagServer = $this->reporter->getSabySocketSwitchFlagForServer();
                if ($sleepCountServer >= $sleepCountConfig) {
                    $returnValue = true;
                    break;
                } elseif ($switchFlagServer >= 1) {
                    $returnValue = true;
                    break;
                }
            }
        } while ($SleepIsNotTimeOut);
        unset($SleepIsNotTimeOut, $sleepTimer, $startTimeStamp);

        $this->CannotTakeCall();
        return $returnValue;
    }

    public function behave($type)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->CannotTakeCall();
        $this->CloseVideoCall();
        $this->ReplyMessage($type);
        $randBehavior = rand(0, 10);
        myLog("RANDOM VALUE FOR BEHAVIOR IS >>> " . $randBehavior);
        if ($randBehavior > 3) {
            $this->MakeCall();
        }
        if ($randBehavior % 2 == 0) {
            $this->SendMessage();
        }
        $this->CannotTakeCall();
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        unset($type);
    }

    public function behaveOnlyMode($type, $prodName)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->CannotTakeCall();
        $this->CloseVideoCall();
        $this->_setBehaveOnlyMode($prodName);
        $this->_getBehaveOnlyMembers($prodName);
        $this->ReplyMessage($type);
        $this->AnswerCall();
        $testCallResult = $this->MakeTestCall($prodName);

        $this->CannotTakeCall();
        //       exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        return $testCallResult;
    }

    private function _checkTimeZone()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $count = 0;
        while (substr($this->waNumber, 0, strlen($GLOBALS['countryZones'][$count][0])) != $GLOBALS['countryZones'][$count][0]) {
            $count++;
        }
        $timeZone = $GLOBALS['countryZones'][$count][1];
        myLog("Country Code : " . $GLOBALS['countryZones'][$count][0]);
        myLog("TimeZone : $timeZone");
        $time = date('H:i');
        myLog("TimeNowUTC : $time");
        $timeNow = date('H:i', strtotime($time . '+' . $timeZone . 'hour'));
        unset($timeZone, $time, $count);
        return $timeNow;
    }

    public function CloseVideoCall()
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $regionClose = null;
        $regionArr = null;
        if ($this->automator->pythonUI->exists(IMG . 'WhatsappVideoCall.png', null, 2)) {
            $this->automator->pythonUI->findAndClick(IMG . 'WhstappVideoCallOk.png', null, 2);
        }
        unset($regionClose, $regionArr);
    }

    public function MakeTestCall($prodName)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->CannotTakeCall();
        $this->_getBehaveOnlyMembers($prodName);
        $this->CannotTakeCall();
        $isAnswered   = false;
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "DIALING");
        myLog("Get Random Memeber To Start A Call");
        $CallTry     = 0;
        $callCreated = false;
        while ($CallTry < 3) {
            // $url = "http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=MakeTestCall&prod_name=" . $prodName . "&machine_id=" . $this->sabyFullName;
            //myLog("Saby Details Validation URL : " . $url);
            $MemeberName = $this->reporter->MakeTestCall();
            if (!empty($MemeberName)) {
                $regionDial = null;
                $regionDial2 = null;
                $regionArr = null;
                $result = json_decode($MemeberName);
                    if($this->_saveContactBeforeTestCall($result->{'saby_number'}, $result->{'saby_name'})) {
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Main");
                        myLog("Will Call : " . $result->{'saby_name'});
                        // Report to Behaving manager in 7eet-filter.net
                        $this->behaveId = $this->reporter->setSabyBehaveLog("OutCall", $result->{'saby_number'});
                        $command = ADB . " -s emulator-" . $this->emulatorPort . " shell am start -n com." . $this->application . "/.Conversation -e jid '" . $result->{'saby_number'} . "@s.whatsapp.net'";
                        myLog("Openning WhatsApp Contact");
                        exec($command);
                        $this->CannotTakeCall();
                        $this->automator->putAVDInFocus();
                    }else{
                        myLog("can't save this contact".$result->{'saby_number'}.'saby_name'.$result->{'saby_name'});
                        break;
                    }
                    if ($this->automator->pythonUI->findAndClick(IMG . 'whatsapp_dial_big.png', null, 7)) {
                        $this->automator->pythonUI->findAndClick(IMG . 'startVoiceCall.png', null, 5);
                        $this->reporter->BehvaiorLog(3); //outGoingCall attemps
                        $this->CannotTakeCall();
                        $regionArr = null;
                        $this->automator->dumpScreenToFile();
                        sleep(2);
                        if($this->automator->searchStringInDumpScreen("Start voice call?")){
                            myLog("\n found Start voice call ? port : \n".$this->emulatorPort);
                            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 840 1010");
                        }else{
                            while ($this->automator->pythonUI->exists(IMG . 'start_voice_call.png', null, 5)) {
                                $this->automator->putAVDInFocus();
                                exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input tap 840 1010");
                            }
                        }
                        $callCreated = true;
                        break;


                    } else {
                        $this->reporter->setSabyEmulatorState($currentState, "SLEEP");
                        $CallTry++;
                        sleep(30);
                    }
            } else {
                $CallTry++;
            }
        }

        if ($callCreated) {
            $this->reporter->setSabyBehaveLog($this->behaveId, "Calling");
            if (!$this->isCallBusy()) {
                $this->automator->putAVDInFocus();
                $this->isRinging();
                $this->reporter->setBehaviorCallattempt();
                // this is outging call so check with false value
                if ($this->isCallAnswered(false)) {
                    $this->reporter->BehvaiorLog(4); //Answered Outgoing Call 
                    $this->automator->putAVDInFocus();
                    $isAnswered = true;
                    $this->reporter->setBehaviorCallAnswered();
                    $this->CallController();
                }
            }
        }
        $this->automator->pythonUI->findAndClick(IMG . 'whatsapp_hangup.png', null, 1);
        //exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com." . $this->application);
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_BACK");
        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
        return $isAnswered;
    }

    private function _saveContactBeforeTestCall($number, $name)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        myLog(">>>>");
        $isAlreadyAdded = $this->check_if_in_contact($number);
        myLog(">>>>");
        myLog("Check Contact Result : " . $isAlreadyAdded);
        myLog("<<<<");
        myLog("<<<<");
        if (substr_count($isAlreadyAdded, $name) > 0) {
            myLog($name . " Already in Contacts List No Need To Add Again");
            return true;
        } else {
            $command = ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.INSERT -t vnd.android.cursor.dir/contact -e name '" . str_replace(" ", "\ ", $number) . "' -e phone +" .$number ;
            do {
                exec($command);
                myLog("THE COMMAND IN " . __FUNCTION__ . " => " . $command);
            } while (!$this->automator->pythonUI->exists(IMG . 'CreateNewContact.png', null, 10));

            $regionSave = null;
            $regionArr = null;
            $this->automator->pythonUI->findAndClick(IMG . 'SaveContactButton.png', null, 10);
            $this->createContactFile($number);
            sleep(1);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
            sleep(1);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
            sleep(1);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
            sleep(1);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
            sleep(1);
            myLog($number . " / " . $number . " Saved To Contacts Successfully");
            $isAlreadyAdded = $this->check_if_in_contact($number);
            return substr_count($isAlreadyAdded, $name) > 0;

        }
    }

    public function check_if_in_contact($number){
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        return shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content query --uri content://com.android.contacts/data/phones/filter/+" . $number . " --projection display_name");
    }
    public function updateEmulatorDetails($sabyFullName, $emulator_port, $ContactsFolder, $CallLimitsFolder)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->ContactsFolder = $ContactsFolder;
        $this->CallLimitsFolder = $CallLimitsFolder;
        $this->sabyFullName = $sabyFullName;
        $this->emulatorPort = $emulator_port;
        unset($sabyFullName, $emulator_port, $ContactsFolder, $CallLimitsFolder);
    }

    public function AnswerTestCall($prodName)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->_setBehaveOnlyMode($prodName);
        $this->_getBehaveOnlyMembers($prodName);
        $this->CanTakeCall();
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "WAITING INCOMMING CALLS");
        myLog("Checking Behaviour Incomming Calls");
        $this->CloseVideoCall();
        if ($this->automator->pythonUI->exists(IMG . 'WhatsappIncommingCall.png', null, 50)) {
            // Report to Behaving manager in 7eet-filter.net
            $this->behaveId = $this->reporter->setSabyBehaveLog("IncCall", "UnDetected");
            $this->reporter->BehvaiorLog(5); //Incomming Call 
            $this->CannotTakeCall();
            myLog("Incomming Call Detected");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " emu avd hostmicon");
            $randomGap = rand(1500000, 3000000);
            $this->CannotTakeCall();
            usleep($randomGap);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 540 1477 540 1000");
            // this is incomming call so check with true value
            if ($this->isCallAnswered(true)) {
                $this->reporter->BehvaiorLog(6); //Answered Incomming Call 
                $this->CallController();
            }
            return true;
        } else {
            return false;
        }
    }



    public function AnswerTestCallWithSleep($prodName)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        $this->_setBehaveOnlyMode($prodName);
        $this->_getBehaveOnlyMembers($prodName);
        $this->CanTakeCall();
        $currentState = $this->reporter->getCurrentState();
        $this->reporter->setSabyEmulatorState($currentState, "WAITING INCOMMING CALLS");
        myLog("Checking Behaviour Incomming Calls");
        $this->CloseVideoCall();
        if ($this->automator->pythonUI->exists(IMG . 'WhatsappIncommingCall.png', null, 50)) {
            // Report to Behaving manager in 7eet-filter.net
            $this->reporter->setSabyEmulatorState('SLEEP', 'InCall');

            $this->behaveId = $this->reporter->setSabyBehaveLog("IncCall", "UnDetected");
            $this->reporter->BehvaiorLog(5); //Incomming Call
            $this->CannotTakeCall();
            myLog("Incomming Call Detected");
            exec(ADB . " -s emulator-" . $this->emulatorPort . " emu avd hostmicon");
            $randomGap = rand(1500000, 3000000);
            $this->CannotTakeCall();
            usleep($randomGap);
            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input swipe 540 1477 540 1000");
            // this is incomming call so check with true value
            if ($this->isCallAnswered(true)) {
                $this->reporter->BehvaiorLog(6); //Answered Incomming Call
                $this->CallController();
            }
            return true;
        } else {
            return false;
        }
    }

    private function _setBehaveOnlyMode($prodName)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        //httpGetAsync("http://filter.7eet.net/whatsapp_behaviour/behaviour_api.php?Action=setBehaveOnlyMode&prod_name=" . $prodName . "&machine_id=" . $this->sabyFullName);
        $this->reporter->setBehaveOnlyMode();
    }

    private function _getBehaveOnlyMembers($prodName)
    {
        myLog("Running >>>>>> " . __FUNCTION__ . " IN " . __CLASS__);
        myLog("Preparing And Updating Member Contacts");
        $GroupMembers = $this->reporter->getBehaveOnlyMembers();
        if (!empty($GroupMembers)) {
            $results = json_decode($GroupMembers);
            foreach ($results as $result) {


                if (isset($result->{'saby_old_number'}) && !empty($result->{'saby_old_number'})) {
                    $contact_id = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content query --uri content://com.android.contacts/data/phones/filter/+" . $result->{'saby_old_number'} . " --projection contact_id");
                    myLog("Check old Contact Result : ");
                    if (!(substr_count($contact_id, 'No result found.') > 0)) {
                        myLog("check delete old contact");
                        $position = strpos($contact_id, "=");
                        if ($position !== false) {
                            myLog("delete old contact");
                            $number = trim(substr($contact_id, $position + 1));
                            exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content delete --uri content://com.android.contacts/contacts/" . $number);
                        } else {
                            myLog("Number not found");
                        }
                    }
                }

                if (isset($result->{'saby_number'}) && !empty($result->{'saby_number'})) {


                    $isAlreadyAdded = shell_exec(ADB . " -s emulator-" . $this->emulatorPort . " shell content query --uri content://com.android.contacts/data/phones/filter/+" . $result->{'saby_number'} . " --projection display_name");
                    myLog("Check Contact Result : " . $isAlreadyAdded);
                    if (substr_count($isAlreadyAdded, $result->{'saby_name'}) > 0) {
                        myLog($result->{'saby_name'} . " Already in Contacts List No Need To Add Again");
                    } else {
                        $command = ADB . " -s emulator-" . $this->emulatorPort . " shell am start -a android.intent.action.INSERT -t vnd.android.cursor.dir/contact -e name '" . str_replace(" ", "\ ", $result->{'saby_name'}) . "' -e phone +" . $result->{'saby_number'};
                        do {
                            exec($command);
                            myLog("THE COMMAND IN " . __FUNCTION__ . " => " . $command);
                        } while (!$this->automator->pythonUI->exists(IMG . 'CreateNewContact.png', null, 10));

                        $regionSave = null;
                        $regionArr = null;
                        $this->automator->pythonUI->findAndClick(IMG . 'SaveContactButton.png', null, 10);
                        $this->createContactFile($result->{'saby_number'});
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell am force-stop com.android.contacts");
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
                        sleep(1);
                        exec(ADB . " -s emulator-" . $this->emulatorPort . " shell input keyevent KEYCODE_HOME");
                        sleep(1);
                        myLog($result->{'saby_name'} . " / " . $result->{'saby_number'} . " Saved To Contacts Successfully");
                    }
                }
            }
        } else {
            myLog("No Contacts List Found");
        }
    }

    public function unsetBehaveOnlyMode()
    {
        myLog("RUNNING " . __FUNCTION__ . " >>>>> " . __CLASS__);
        $this->reporter->unsetBehaveOnlyMode();
    }

    public function GetSendMessage()
    {
        return $this->reporter->GetSendMessage($this->group, $this->Countrygroup);
    }

    public function GetContactMakeCall()
    {
        return $this->reporter->GetContactMakeCall($this->group, $this->Countrygroup);
    }
}
