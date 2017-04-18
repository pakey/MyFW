<?php

namespace Kuxin\Helper;

class Arr
{
    
    public static function msort($list, $key, $order = 'desc')
    {
        $arr = $new = [];
        foreach ($list as $k => $v) {
            $arr[$k] = $v[$key];
        }
        if ($order == 'asc') {
            asort($arr);
        } else {
            arsort($arr);
        }
        foreach ($arr as $k => $v) {
            $new[] = $list[$k];
        }
        return $new;
    }
}