<?php
namespace app\controller;

use app\controller\common\common;
use app\model\User;
use Kuxin\Controller;

class Index extends Controller  {
    
    public function index()
    {
        //ini_set('yaconf.check_delay',100);
        $usermodel=new User();
        var_dump($usermodel->getTableField());
        $usermodel->insert(['name'=>'aaa','password'=>'vbbb']);
        exit;
    }
}
