<?php
namespace app\controller\index;

use app\controller\common\common;
use Kuxin\Controller;

class Index extends Controller  {
    
    public function index()
    {
        var_dump(get_included_files());
        return 'hello world!';
    }
}
