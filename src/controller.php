<?php
namespace ptcms;

class Controller{
    /**
     * 失败返回
     * success
     *
     * @param        $info
     * @param string $jumpUrl
     * @param int    $second
     * @return mixed
     */
    public function success($info = 'success', $jumpUrl = '', $second = 1)
    {
        if (Response::type()!='html') {
            return ['status' => 1, 'info' => $jumpUrl ? $jumpUrl : 'success', 'data' => $info];
        }
        $this->dispatchJump($info, 1, $jumpUrl, $second);
    }
    
    /**
     * 失败返回
     * success
     *
     * @param        $info
     * @param string $jumpUrl
     * @param int    $second
     * @return mixed
     */
    public static function error($info = 'error', $jumpUrl = '', $second = 3)
    {
        if (Response::type()!='html') {
            return ['status' => 0, 'info' => $info, 'data' => $jumpUrl ? $jumpUrl : []];
        }
        self::dispatchJump($info, 0, $jumpUrl, $second);
    }
    
    
    protected static function dispatchJump($message, $status = 1, $jumpurl = '', $second = 1)
    {
        Config::set('layout', false);
        if (Request::isAjax() or $second === true) {
            $data['status'] = $status;
            $data['info']   = $message;
            $data['url']    = $jumpurl;
            self::ajax($data);
        } else {
            defined('PT_SITENAME') ? View::set('msgname', PT_SITENAME) : View::set('msgname', Config::get('sitename', null, 'PTFrameWork'));
            if (is_array($jumpurl)) {
                if (count($jumpurl) > 1) {
                    $second = $second < 3 ? 3 : $second;
                    View::set('urllist', $jumpurl);
                }
                $first   = current($jumpurl);
                $jumpurl = $first['url'];
            }
            //如果设置了关闭窗口，则提示完毕后自动关闭窗口
            View::set('status', $status); // 状态
            View::set('waitsecond', $second);
            View::set('message', $message); // 提示信息
            View::set('msgtitle', $status ? '成功' : '失败');
            if ($status) { //发送成功信息
                View::set('msgtype', 'success'); // 提示类型
                // 默认操作成功自动返回操作前页面
                if ($jumpurl) {
                    View::set("jumpurl", $jumpurl);
                } elseif (!empty($_SERVER['HTTP_REFERER'])) {
                    View::set("jumpurl", $_SERVER["HTTP_REFERER"]);
                } else {
                    View::set('jumpurl', $_SERVER['REQUEST_URI']);
                }
            } else {
                View::set('msgtype', 'error'); // 提示类型
                // 默认发生错误的话自动返回上页
                if ($jumpurl) {
                    View::set("jumpurl", $jumpurl);
                } elseif (!empty($_SERVER['HTTP_REFERER'])) {
                    View::set("jumpurl", '#back#');
                } else {
                    View::set('jumpurl', $_SERVER['REQUEST_URI']);
                }
            }
            View::make(Config::get('tpl_message','/common/message.html'));
            exit;
        }
    }
    
    public static function ajax($data, $type = 'json')
    {
        Response::type(strtolower($type));
        return $data;
    }
    
    public function redirect($url, $type = 302)
    {
        Response::redirect($url, $type);
    }
}