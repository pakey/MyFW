<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : controller.php
 */

class PT_Controller extends PT_Base {
    public function getView() {
        static $view;
        if (!isset($view)) {
            $this->plugin->call('view_start');
            //实例化view
            $view = new PT_View();
            //初始化模版
            $view->getTheme();
        }
        return $view;
    }

    /**
     * 显示当前页面的视图内容
     *
     * @access public
     * @param string $tpl    视图模板
     * @param string $module 所属模块
     * @param string $theme  所属模版
     * @return void
     */
    public function display($tpl = null, $module = null, $theme = null) {
        $content = $this->fetch($tpl, $module, $theme);
        $this->show($content);
    }

    /**
     * 输出视图内容
     *
     * @access public
     * @param string $content  输出内容
     * @param string $mimeType MIME类型
     * @return void
     */
    protected function show($content, $mimeType = 'text/html') {
        $this->response->setBody($content, $mimeType);
    }

    protected function fetch($tpl = null, $module = null, $theme = null) {
        return $this->view->fetch($tpl, $module, $theme);
    }

    protected function render($var){
        if ($var===true){
            $this->response->enableRender();
        }elseif($var===false){
            $this->response->disableRender();
        }else{
            $this->view->setFile($var);
        }
    }

    public function success($info, $jumpUrl = '', $second = 1) {
        $this->dispatchJump($info, 1, $jumpUrl, $second);
    }

    public function error($info, $jumpUrl = '', $second = 3) {
        $this->dispatchJump($info, 0, $jumpUrl, $second);
    }

    protected function dispatchJump($message, $status = 1, $jumpurl = '', $second = 1) {
        $this->config->set('layout', false);
        if ($this->request->isAjax() or $second === true) {
            $data['status'] = $status;
            $data['info']   = $message;
            $data['url']    = $jumpurl;
            $this->ajax($data);
        } else {
            defined('PT_SITENAME') ? $this->view->set('msgname', PT_SITENAME) : $this->view->set('msgname', $this->config->get('sitename', null, 'PTFrameWork'));
            //如果设置了关闭窗口，则提示完毕后自动关闭窗口
            $this->view->set('status', $status); // 状态
            $this->view->set('waitsecond', $second);
            $this->view->set('message', $message); // 提示信息
            $this->view->set('msgtitle', $status ? '成功' : '失败');
            if ($status) { //发送成功信息
                $this->view->set('msgtype', 'success'); // 提示类型
                // 默认操作成功自动返回操作前页面
                if ($jumpurl) {
                    $this->view->set("jumpurl", $jumpurl);
                } elseif (!empty($_SERVER['HTTP_REFERER'])) {
                    $this->view->set("jumpurl", $_SERVER["HTTP_REFERER"]);
                } else {
                    $this->view->set('jumpurl', $_SERVER['REQUEST_URI']);
                }
            } else {
                $this->view->set('msgtype', 'error'); // 提示类型
                // 默认发生错误的话自动返回上页
                if ($jumpurl) {
                    $this->view->set("jumpurl", $jumpurl);
                } elseif (!empty($_SERVER['HTTP_REFERER'])) {
                    $this->view->set("jumpurl", '#back#');
                } else {
                    $this->view->set('jumpurl', $_SERVER['REQUEST_URI']);
                }
            }
            $this->display('message', 'common', $this->config->get('tpl_theme') ? $this->config->get('tpl_theme') : 'default');
            exit;
        }
    }

    public function ajax($data, $type = 'json') {
        // 跨域
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:accept, content-type');

        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                $data=$this->response->jsonEncode($data);
                break;
            case 'JSONP':
                // 返回JSONP数据格式到客户端 包含状态信息
                $data=$this->response->jsonpEncode($data);
                break;
            case 'EVAL' :
                // 返回可执行的js脚本
                break;
            default     :
        }
        $this->response->setBody($data, 'application/json');
        exit;
    }

    public function redirect($url, $type = 302) {
        $this->response->redirect($url, $type);
    }

}