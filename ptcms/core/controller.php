<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : controller.php
 */
abstract class Controller
{
    protected function getView()
    {
        static $view;
        if (!isset($view)) {
            plugin::call('view_start');
            //实例化view
            $view = new View();
            //初始化模版
            $view->getTheme();
        }
        return $view;
    }

    public function assign($name, $value = null)
    {
        $this->getView()->assign($name, $value);
        return $this; //支持连贯操作
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
    protected function display($tpl = null, $module = null, $theme = null)
    {
        $content = $this->render($tpl, $module, $theme);
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
    protected function show($content, $mimeType = 'text/html')
    {
        pt::show($content, $mimeType);
    }

    protected function render($tpl = null, $module = null, $theme = null)
    {
        if (C('html', null, false)) {
            $rules = C('URL_RULES');
            $key = $_GET['m'] . '.' . $_GET['c'] . '.' . $_GET['a'];
            if (isset($rules[$key])) {
                $param = $_GET;
                unset($param['m'], $param['c'], $param['a'], $param['s'], $param['t']);
                C('is_gen_html', true);
                $content = $this->getView()->render($tpl, $module, $theme);
                $url = empty($this->htmlurl) ? U($key, $param, C('link_ignore', null, array())) : $this->htmlurl;
                html::create($url, $content);
                return $content;
            }
        }
        return $this->getView()->render($tpl, $module, $theme);
    }

    // 实现 $this->name=value 的赋值方法
    public function __set($name, $value)
    {
        $this->getView()->assign($name, $value);
    }

    // 获取 $this->name 的值
    public function __get($name)
    {
        return $this->getView()->getassign($name);
    }

    protected function success($info, $jumpUrl = '', $second = 1)
    {
        $this->dispatchJump($info, 1, $jumpUrl, $second);
    }

    protected function error($info, $jumpUrl = '', $second = 3)
    {
        $this->dispatchJump($info, 0, $jumpUrl, $second);
    }

    protected function dispatchJump($message, $status = 1, $jumpurl = '', $second = 1)
    {
        C('LAYOUT', false);
        if (IS_AJAX or $second === true) {
            $data['status'] = $status;
            $data['info'] = $message;
            $data['url'] = $jumpurl;
            $this->ajax($data);
        } else {
            defined('PT_SITENAME') ? $this->assign('msgname', PT_SITENAME) : $this->assign('msgname', C('SITENAME', null, 'PTCMS'));
            //如果设置了关闭窗口，则提示完毕后自动关闭窗口
            $this->assign('status', $status); // 状态
            $this->assign('waitsecond', $second);
            $this->assign('message', $message); // 提示信息
            $this->assign('msgtitle', $status ? '成功' : '失败');
            if ($status) { //发送成功信息
                $this->assign('msgtype', 'success'); // 提示类型
                // 默认操作成功自动返回操作前页面
                if ($jumpurl) {
                    $this->assign("jumpurl", $jumpurl);
                } elseif (!empty($_SERVER['HTTP_REFERER'])) {
                    $this->assign("jumpurl", $_SERVER["HTTP_REFERER"]);
                } else {
                    $this->assign('jumpurl', $_SERVER['REQUEST_URI']);
                }
            } else {
                $this->assign('msgtype', 'error'); // 提示类型
                // 默认发生错误的话自动返回上页
                if ($jumpurl) {
                    $this->assign("jumpurl", $jumpurl);
                } elseif (!empty($_SERVER['HTTP_REFERER'])) {
                    $this->assign("jumpurl", '#back#');
                } else {
                    $this->assign('jumpurl', $_SERVER['REQUEST_URI']);
                }
            }
            $this->display('message', 'common', C('tpl_theme') ? C('tpl_theme') : 'default');
            exit;
        }
    }

    protected function ajax($data, $type = 'json')
    {
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                pt::show(json_encode($data), 'application/json');
                break;
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : 'ptcms_jsonp';
                pt::show($handler . '(' . json_encode($data) . ');', 'application/json');
                break;
            case 'EVAL' :
                // 返回可执行的js脚本
                pt::show($data);
                break;
            default     :
                // 用于扩展其他返回格式数据
        }
        exit;
    }

    public function redirect($url, $type = 302)
    {
        if ($type == 302) {
            header('HTTP/1.1 302 Moved Temporarily');
            header('Status:302 Moved Temporarily'); // 确保FastCGI模式下正常
        } else {
            header('HTTP/1.1 301 Moved Permanently');
            header('Status:301 Moved Permanently');
        }
        header('Location: ' . $url);
        exit;
    }
}