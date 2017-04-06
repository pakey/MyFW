<?php
namespace app\controller\index;

use app\controller\common\common;
use Kuxin\Controller;

class Index extends Controller  {
    
    public function index()
    {
        //ini_set('yaconf.check_delay',100);
        return 'hello world';
    }
}
