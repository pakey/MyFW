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
            switch ($type) {
                case 'success':
                    $color = 'green';
                    break;
                case 'error':
                    $color = 'red';
                    break;
                case 'warning':
                    $color = "orangered";
                    break;
                case 'info':
                    $color = 'darkblue';
                    break;
                default:
                    $color = $type;
            }
            $line = $line ? '<br/>' . PHP_EOL : '';
            if ($color) {
                echo "<span style='color:{$color}'>{$msg}</span>{$line}";
            } else {
                echo "<span>{$msg}</span>{$line}";
            }
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
}