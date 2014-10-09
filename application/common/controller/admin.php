<?php

class AdminController extends Controller {

    // 当前操作表
    protected $tableName;
    // 当前表的model类
    protected $model;
    // 过滤列表
    protected $map = array();

    public function __construct() {
        C('tpl_theme', '');
        C('layout', true);
        C('layout_name', '/application/admin/view/public_layout.html');
        $this->skipnode=array('admin.index.index');
    }

    public function init() {
        session_start();
        // 登录状态判断
        if (empty($_SESSION['admin'])) {
            //未登录
            $this->redirect(U('admin.public.login'));
        } else {
            $this->username = $_SESSION['admin']['username'];
            $this->groupname=$_SESSION['admin']['groupname'];
            session_write_close();
        }
        // 设置了表名则自动初始化model
        if ($this->tableName){
            $this->model=M($this->tableName);
        }
        // 当前页面信息
        $this->menuinfo=M('admin_node')->getMenuInfo();
        //判断是否有权限访问当前页面 创始人 访问权限 免验节点 ajax
        if ($_SESSION['admin']['userid']!='1'
            && !in_array($this->menuinfo['nodeid'],explode(',',dc::get('admin_group',$_SESSION['admin']['groupid'],'node')))
            && !in_array(MODULE_NAME.'.'.CONTROLLER_NAME.'.'.ACTION_NAME,$this->skipnode)
            && ACTION_NAME!='ajax'){
            $this->error('您没有权限访问这个页面！',0,0);
        }
        // 其他初始化
        $this->pagestr='';
    }

    // 防止进入空控制器
    public function addAction() {}
    public function editAction() {}
    public function delAction() {
        $id=I('request.id','int',0);
        $this->model->del(array('id'=>$id));
        $this->success('删除成功');
    }
    public function multiAction() {}
    public function ajaxAction() {}
}