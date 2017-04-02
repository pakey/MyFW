<?php
namespace app\controller\index;

use app\controller\common\common;
use Kuxin\Controller;

class Index extends Controller  {
    
    public function index()
    {
        phpinfo();
        //ini_set('yaconf.check_delay',100);
        var_dump(\Yaconf::get('app'));
        var_dump(\Yaconf::get('storage'));
        var_dump(\Yaconf::get('storage.children.children'));

        
    }
}
