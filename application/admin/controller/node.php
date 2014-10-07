<?php
// 菜单管理
class nodeController extends AdminController{

    public function indexAction() {
        $this->list = M('node')->field('id,name,status,ordernum')->order('orderid asc')->select();
        $this->assign('totalnum', count($this->list));
        $this->display();
    }

    public function addAction() {
        $this->display();
    }
}