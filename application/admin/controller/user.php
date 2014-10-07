<?php
class UserController extends AdminController{

    public function indexAction() {
        $this->list=M('admin_user')->select();
        $this->pagestr ='';
        $this->display();
    }
}