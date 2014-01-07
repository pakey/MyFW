<?php

/***************************************************************
 *   $Program: MCPHP FarmeWork (A Open PHP Application FarmeWrok) $
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com $
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service/license.html $
 *      $Date: 2013-04-27 23:27:13 +0800 (星期六, 27 四月 2013) $
 *      $File: Controller.class.php $
 *  $Revision: 72 $
 *      $Desc:
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');
 
class Controller{
    public function run()
    {
        //如果有子类Common，调用这个类的init()方法 做权限控制
        if (method_exists($this, "_init")) {
            $this->_init();
        }
        // 根据动作去找对应的方法
        $action = ACTION_NAME.'Action';
        $beforeAction='_before'.ucfirst(ACTION_NAME);
        $afterAction='_after'.ucfirst(ACTION_NAME);
        // 运行前置操作
        if (method_exists($this, $beforeAction)) {
            $this->$beforeAction();
        }
        // 运行当前操作
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
			if (method_exists($this,'__call')){
				$this->$action();
			}elseif (method_exists($this,'__empty')){
				$this->__empty();
			}else{
                if (APP_DEBUG){
                    header('Content-Type:text/html; charset=utf-8');
                    //便于调试输出信息
                    p($_GET);
                    exit('方法'.$action.'不存在');
                }else{
                    App::err404();
                }

			}
            
        }
        // 运行后置操作
        if (method_exists($this, $afterAction)) {
            $this->$afterAction();
        }
    }

    public function _init(){}

    public function _initView()
    {
        static $_view;
        if (!isset($_view)){
            $_view=new View();
            //初始化模版
            $_view->getTheme();
        }
        return $_view;
    }

    /**
     * 视图变量赋值操作
     *
     * @access public
     * @param $keys         视图变量名
     * @param string $value 视图变量值
     * @return void
     */
    public function assign($keys, $value = null)
    {
        $this->_initView()->assign($keys, $value);
    }

    /**
     * 显示当前页面的视图内容
     *
     * @access public
     * @param string $fileName  视图名称
	 * @param array $data  自定义数据输出
     * @return void
     */
    public function display($fileName = null,$data=array())
    {
        $this->_initView()->display($fileName,$data);
    }

    /**
     * 返回当前页面的视图内容
     * @access public
     * @param string $fileName  视图名称
	 * @param array $data  自定义数据输出
     * @return void
     */
    public function fetch($fileName = null,$data=array())
    {
        return $this->_initView()->fetch($fileName,$data);
    }

    /**
     * 输出视图内容
     * @access public
     * @param string $content   输出内容
     * @param string $mimeType  MIME类型
     * @return void
     */
    public function show($content, $mimeType = 'text/html')
    {
        $this->_initView()->show($content, $mimeType);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data,$type='JSON') {
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
        }
    }

    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param integer $time 延时跳转的时间 单位为秒
     * @return void
     */
    protected function redirect($url,$time=0) {
        $url    = $this->_getUrl($url);
        header("refresh:{$time};url={$url}");
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        if(true === $ajax) {// AJAX提交
            $data           =   array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $waitSecond=$ajax;
        // 提示标题
        $this->assign('msgtitle',$status? '操作成功' : '操作失败');
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if(G('close'))    $jumpUrl='javascript:window.close();';
        if($status) { //发送成功信息
            $this->assign('message',$message);// 提示信息
            // 成功操作后默认停留1秒
            if(!isset($waitSecond))
                $waitSecond=1;
            $this->assign('waitsecond',$waitSecond);
            // 默认操作成功自动返回操作前页面
            if(empty($jumpUrl)){
                if (isset($_SERVER["HTTP_REFERER"])){
                    $jumpUrl = $_SERVER['HTTP_REFERER'];
                } else {
                    $jumpUrl = __ACTION__ ;
                }
            }else{
                $jumpUrl=$this->_getUrl($jumpUrl);
            }
            $this->assign("jumpurl",$jumpUrl);
            $this->display('@'.C('TPL_ACTION_SUCCESS'));
            exit;
        }else{
            $this->assign('message',$message);// 提示信息
            //发生错误时候默认停留3秒
            if(!isset($waitSecond))
                $waitSecond=3;
            $this->assign('waitsecond',$waitSecond);
            // 默认发生错误的话自动返回上页
            if(empty($jumpUrl)){
                $jumpUrl='javascript:history.back(-1);';
            }else{
                $jumpUrl=$this->_getUrl($jumpUrl);
            }
            $this->assign("jumpurl",$jumpUrl);
            $this->display('@'.C('TPL_ACTION_ERROR'));
            // 中止执行  避免出错后继续执行
            exit ;
        }
    }

    protected function _getUrl($url)
    {

        // 直接连接模式
        if (strpos($url, '://') || strpos($url,'.php')) {
            return $url;
        }elseif (strpos($url, 'indow.') or substr($url,0,1)=='/') {
            // js脚本形式
            return $url;
        } else {
            //普通形式
            $path = rtrim($url, "/");
            $num=count(explode('/',$path));
            if ($num==2) {
                if (isset($_GET['m'])){
                    $url=__MODULE__.'/'.$path;
                }else{
                    $url = __APP__.'/'.$path;
                }
            }elseif($num==1){
                $url = __URL__ . "/" . $path;
            }else{
                $url = __APP__.'/'.$path;
            }
            return $url;
        }
    }
}