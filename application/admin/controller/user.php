<?php
class UserController extends AdminController{

    public function init() {
        $this->tableName='admin_user';
        parent::init();
    }

    public function indexAction() {
        $this->list=$this->model->getlist();
        $this->display();
    }
}