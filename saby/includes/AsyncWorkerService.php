<?php

class AsyncWorkerService extends Thread
{
    public $response = null;

    private $_runMethod;
    public function __construct($method)
    {
        $this->_runMethod = $method;
    }

    public function run()
    {
        $method = $this->_runMethod;
        $response = $method();
    }
}
