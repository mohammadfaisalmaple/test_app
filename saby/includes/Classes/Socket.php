<?php
class SocketClass
{
    public $IPAddress = null;
    public $socketPort = null;
    public $incommingSocket = null;
    public $mainSocket = null;
    public $sentralSocket = null;
    public $callId = null;

    public function __construct($socketPort)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->socketPort = $socketPort;
        unset($socketPort);
    }

    public function __destruct()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->CloseIncommingSocket();
        $this->CloseMainSocket();
        myLog("Destroying Socket Class");
    }

    public function createMainSocket()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->IPAddress = $this->_getIp();
        // create socket
        $this->mainSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or myLog("Saby Can't Create Socket");
        // bind socket to port
        $bindResult = socket_bind($this->mainSocket, $this->IPAddress, $this->socketPort) or myLog("Saby Can't Bind Socket");
        myLog("The IP is :" . $this->IPAddress);
        myLog("The Port is :" . $this->socketPort);
        return $bindResult;
    }

    public function acceptIncommingSocket($time_limit)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $start_time = time();

        //socket_set_nonblock($this->mainSocket);
        socket_set_option($this->mainSocket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $time_limit, "usec" => 500));
        $listenResult = socket_listen($this->mainSocket, 1);
        myLog("The connection Succsessfully Done");
        myLog("IAM WAITING TO ACCEPT");
        do {
            $this->incommingSocket = socket_accept($this->mainSocket);
            $timePassed = microtime(true) - $start_time;
            myLog("IAM WAITING TO ACCEPT REMAIN TIME : " . $timePassed);
        } while ($timePassed < $time_limit && !$this->incommingSocket);
        unset($start_time, $listenResult, $timePassed, $time_limit);
        return $this->incommingSocket;
    }

    public function socketRead()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $time_limit_reading = 1;
        $data = null;
        $timeStart = time();
        do {
            $data = socket_read($this->incommingSocket, 2048);
            $limiter = (time() - $timeStart) < $time_limit_reading;
        } while (empty($data) && $limiter);
        socket_shutdown($this->mainSocket, 0);
        myLog("The Input Form Socket is =" . $data);
        $data = str_replace("\n", '', $data);
        unset($time_limit_reading, $timeStart, $limiter);
        return $data;
    }

    public function socketWrite($massage)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        myLog("Socket Writing " . $massage);
        socket_write($this->incommingSocket, $massage, strlen($massage));
        unset($massage);
    }

    public function CloseMainSocket()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        myLog("Closeing Main Socket");
        if ($this->incommingSocket) {
            socket_shutdown($this->mainSocket, 2);
            socket_close($this->mainSocket);
        }
    }

    public function CloseIncommingSocket()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        myLog("Closeing Incoming Socket");
        if ($this->incommingSocket) {
            socket_shutdown($this->incommingSocket, 2);
            socket_close($this->incommingSocket);
        }
    }

    private function _getIp()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        return trim(shell_exec("hostname -I | cut -d ' ' -f 1"));
    }

    public function socketReply($response)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->socketWrite($response);
        unset($response);
    }

    /*
    For Future Usage in case we create double socket Server 
    
    */
    /* 
    public function sendReceivedOrder()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $this->socketWrite("100");
    }

    public function sendHangUpCommand($reason)
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $massage =  $reason;
        $this->socketWrite($massage);
    }

    public function sendRingingCommand()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $massage = $this->callId . '180';
        $this->socketWrite($massage);
    }

    public function sendBusyCommand()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $massage = $this->callId . '486';
        $this->socketWrite($massage);
    }

    public function sendGoneCommand()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $massage = $this->callId . '410';
        $this->socketWrite($massage);
    }

    public function sendAnsweringCommand()
    {
        myLog('RUNNING >>> ' . __FUNCTION__ . " >>>>  IN " . __CLASS__);
        $massage = $this->callId . '200';
        $this->socketWrite($massage);
    }

    public function setCallData($callId)
    {
        $this->callId = $callId;
    }

    public function sendHangUpByCaller()
    {
        $massage = $this->callId . '|HangupSide|caller';
        $this->socketWrite($massage);
    }

    public function sendHangUpByCallee()
    {
        $massage = $this->callId . '|HangupSide|callee';
        $this->socketWrite($massage);
    }
 */
}
