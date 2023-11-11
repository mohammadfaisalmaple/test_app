<?php
class pythonUI
{
    public $xCoordinate = null;
    public $yCoordinate = null;
    public $hight = null;
    public $width = null;
    public function __construct()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $this->_connect();
    }

    private function _connect()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        exec(dirname(__DIR__) . '/pythonScripts/startPythonUI.py');
    }

    public function setDefaultRegion($RUNNING_EMULATOR_X, $RUNNING_EMULATOR_Y, $RUNNING_EMULATOR_WIDTH, $RUNNING_EMULATOR_HEIGHT)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $this->xCoordinate = $RUNNING_EMULATOR_X;
        $this->yCoordinate = $RUNNING_EMULATOR_Y;
        $this->hight = $RUNNING_EMULATOR_WIDTH;
        $this->width = $RUNNING_EMULATOR_HEIGHT;
    }

    public function createRegion($xCoordinate, $yCoordinate, $hight, $width)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        return $xCoordinate . ',' . $yCoordinate . ',' . $hight . ',' . $width;
    }

    public function exists($imagePath, $searchRegion, $timeOut)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        if (is_null($searchRegion)) {
            $xCoordinate = $this->xCoordinate;
            $yCoordinate = $this->yCoordinate;
            $hight = $this->hight;
            $width = $this->width;
        } else {
            list($xCoordinate, $yCoordinate, $hight, $width) = explode(',', $searchRegion);
        }
        $cmd = dirname(__DIR__) . '/pythonScripts/findImage.py' . " " . $imagePath . " " . $xCoordinate . " " . $yCoordinate . " " . $hight . " " . $width . " " . $timeOut;
        $result = shell_exec($cmd);
        if (!empty($result)) {
            list($resultEvalution, $timeValue) = explode(',', $result);
            if (intval($resultEvalution) == 1) {
                myLog("THE " . __FUNCTION__ . " FOUNDED IMAGE >>>> IN >>>> " . $timeValue);
                $resultEvalution = true;
            } else {
                myLog(__CLASS__ . "." . __FUNCTION__ . " >>>> Time OUT / " . $timeOut . " SEC");
                $resultEvalution = false;
            }
        }
        myLog("THE RESULT OF " . __FUNCTION__ . " IN " . __CLASS__ . " >>>> " . $result . " IMAGE >>>> " . str_replace(IMG, "", $imagePath));
        return  $resultEvalution;
    }

    public function getNumberFromSound(){
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $cmd = dirname(__DIR__) . '/pythonScripts/getNumberFromSound.py';
        $result = shell_exec($cmd);
        myLog('this is result get number from sound : '.$result);
        return $result;
    }

    public function findAndClick($imagePath, $searchRegion, $timeOut)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        if (is_null($searchRegion)) {
            $xCoordinate = $this->xCoordinate;
            $yCoordinate = $this->yCoordinate;
            $hight = $this->hight;
            $width = $this->width;
        } else {
            list($xCoordinate, $yCoordinate, $hight, $width) = explode(',', $searchRegion);
        }

        $cmd = dirname(__DIR__) . '/pythonScripts/findImageAndClick.py' . " " . $imagePath . " " . $xCoordinate . " " . $yCoordinate . " " . $hight . " " . $width . " " . $timeOut;
        $result = shell_exec($cmd);
        if (!empty($result)) {
            list($resultEvalution, $timeValue) = explode(',', $result);
            if (intval($resultEvalution) == 1) {
                myLog("THE " . __FUNCTION__ . " FIND AND CLICK  >>>> IN >>>> " . $timeValue);
                $resultEvalution = true;
            } else {
                myLog(__CLASS__ . "." . __FUNCTION__ . " >>>> Time OUT / " . $timeOut . " SEC");
                $resultEvalution = false;
            }
        }
        myLog("THE RESULT OF " . __FUNCTION__ . " IN " . __CLASS__ . " >>>> " . $result . " IMAGE >>>> " . str_replace(IMG, "", $imagePath));
        return $resultEvalution;
    }

    public function click($clickRegion)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        list($xCoordinate, $yCoordinate) = explode(',', $clickRegion);
        exec(dirname(__DIR__) . '/pythonScripts/click.py' . " " . $xCoordinate . '  ' . $yCoordinate);
    }

    private function _getAVDPid()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $pid = shell_exec('pgrep -u ${USER} -x qemu-system-x86');
        if (!empty($pid)) {
            return trim($pid);
        }
        return false;
    }

    private function _getAVDMainWid($pid)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $wids = shell_exec("xdotool search --pid " . $pid . " 2>&1");
        $wids = explode("\n", $wids);
        foreach ($wids as $wid) {
            $wid = (int) $wid;
            if ($wid) {
                $windowName = shell_exec("xdotool getwindowname " . $wid . " 2>&1");
                if ($this->_isAVDWindow($windowName)) {
                    return $wid;
                }
            }
        }
    }

    private function _isAVDWindow($windowName)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        if (strstr($windowName, 'Android Emulator')) {
            return true;
        }
        return false;
    }

    private function _activateWindow($wid)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $output = shell_exec("xdotool windowactivate $wid 2>&1");
        if (strstr($output, 'failed')) {
            return false;
        }
        return true;
    }

    public function _putAVDInFocus()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $AVDPid = $this->_getAVDPid();
        myLog("Emulator Proccess ID : " . $AVDPid);
        $wid = $this->_getAVDMainWid($AVDPid);
        myLog("Emulator Window ID : " . $wid);
        $isActive = $this->_activateWindow($wid);
        return true;
    }

    public function type($text)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $this->_putAVDInFocus();
        exec(dirname(__DIR__) . "/pythonScripts/writeText.py '" . $text . "'");
    }

    public function keyDown($button)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        exec(dirname(__DIR__) . '/pythonScripts/pressButton.py ' . $button);
    }

    public function searchStringInScreenShot($needString, $searchRegion, $timeOut)
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        if (is_null($searchRegion)) {
            $xCoordinate = 117;
            $yCoordinate = 217;
            $hight = $this->hight;
            $width = $this->width;
        } else {
            list($xCoordinate, $yCoordinate, $hight, $width) = explode(',', $searchRegion);
        }
        $cmd = dirname(__DIR__) . '/pythonScripts/getStringFromScreenShot.py' . " " . $xCoordinate . " " . $yCoordinate . " " . $width . " " . $hight . " " . $timeOut . " " . $needString;
        $result = shell_exec($cmd);
        if (!empty($result)) {
            list($resultEvalution, $timeValue) = explode(',', $result);
            if (intval($resultEvalution) == 1) {
                myLog("THE STRING FOUNDED IN " . __FUNCTION__ . " >>>> IN >>>> " . $timeValue);
                $resultEvalution = true;
            } else {
                myLog(__CLASS__ . "." . __FUNCTION__ . " >>>> Time OUT / " . $timeOut . " SEC");
                $resultEvalution = false;
            }
        }
        myLog("THE RESULT OF " . __FUNCTION__ . " IN " . __CLASS__ . " >>>> " . $resultEvalution);
        return $resultEvalution;
    }

    public function rebootAVDByUI()
    {
        myLog("RUNNING >>>>" . __FUNCTION__ . " IN CLASS >>>>" . __CLASS__);
        $cmd = dirname(__DIR__) . '/pythonScripts/rebootAvdByUI.py';
        $result = shell_exec($cmd);
    }
}
