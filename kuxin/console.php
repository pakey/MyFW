<?php

namespace Kuxin;

class Console
{
    
    protected $params = [];
    
    public function __construct()
    {
        $this->params = Registry::get('cli_params', []);
    }
    
    public function init()
    {
        
    }
    
    /**
     * 终端输出
     *
     * @param $text
     * @param $status
     * @param $line
     * @return mixed
     */
    public function info($text, $status = 'text', $line = true)
    {
        echo Response::terminal($text, $status, $line);
    }
    
    public function param($key, $type = 'int', $default = 0)
    {
        return Input::param($key, $type, $default, $this->params);
    }
}