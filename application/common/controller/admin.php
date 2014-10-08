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
    }

    public function init() {
        session_start();
        // 登录状态判断
        if (empty($_SESSION['admin'])) {
            //未登录
            $this->redirect(U('admin.public.login'));
        } else {
            $this->username = $_SESSION['admin']['username'];
            session_write_close();
        }
        // 设置了表名则自动初始化model
        if ($this->tableName){
            $this->model=M($this->tableName);
        }
        // 当前页面信息
        $this->menuinfo=M('admin_node')->getMenuInfo();
        // 其他初始化
        $this->pagestr='';
    }
}