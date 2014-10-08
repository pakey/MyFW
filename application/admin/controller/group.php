<?php
class GroupController extends AdminController{

    public function init() {
        $this->tableName='admin_group';
        parent::init();
    }

    public function indexAction() {
        $this->list=$this->model->getlist();
        $this->display();
    }

    public function addAction() {
        $tree=new Tree(M('admin_node'));
        $this->menu=$tree->getAuthList(0,'id,name');
        print_r($this->menu);
        $this->display();
    }

    public function editAction() {

    }

    public function delAction() {

    }

    public function multiAction() {

    }
}