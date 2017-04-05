<?php
namespace app\controller\index;

use app\controller\common\common;
use Kuxin\Controller;

class Index extends Controller  {
    
    public function index()
    {
        //ini_set('yaconf.check_delay',100);
        var_dump(\Yaconf::get('app.aaa',function(){
            return 'a';
        }));
        var_dump(\Yaconf::get('storage'));
        var_dump(\Yaconf::get('storage.children.children'));

        
    }
}
