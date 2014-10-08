<?php
class UserController extends AdminController{

    public function indexAction() {
        $this->list=M('admin_user')->getlist();
        $this->pagestr ='';
        $this->display();
    }
}