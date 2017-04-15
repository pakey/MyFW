<?php

namespace Kuxin;

class Console extends Controller {
    
    /**
     * 采集进程输出
     *
     * @param        $msg
     * @param string $type
     * @param bool   $line
     */
    public function out($msg, $type = '', $line = true, $rulename = "")
    {
        if (RUN_ENV == 'web') {
            
            ob_flush();
            flush();
        } elseif (RUN_ENV == 'cli') {
            echo Response::terminal($msg, $type, $line);
            Log::collect(date('Y-m-d H:i:s').' '.$msg,$rulename);
        } else {
            Log::collect(date('Y-m-d H:i:s').' '.$msg,$rulename);
        }
        if ($type == 'error') {
            Log::collecterror(date('Y-m-d H:i:s').' '.$msg,$rulename);
        }
    }
    
    /**
     * 终端输出
     *
     * @param $text
     * @param $status
     * @param $line
     * @return mixed
     */
    public function info($text, $status, $line = true)
    {
       return Response::terminal($text,$status,$line);
    }
}