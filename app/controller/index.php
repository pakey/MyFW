<?php

namespace app\controller;

use app\controller\common\common;
use app\model\User;
use Kuxin\Controller;
use Kuxin\Log;

class Index extends Controller
{
    
    public function index()
    {
        Log::write('test');
        Log::record('debug','debug');
        Log::build();
        //ini_set('yaconf.check_delay',100);
        $usermodel = new User();
        //$usermodel->insert([
        //    'username' => 'aaa',
        //    'password' => 'vbbb',
        //]);
        //$usermodel->insertAll([
        //    [
        //        'username' => '111',
        //        'password' => 'vbbb',
        //    ],
        //    [
        //        'username' => '222',
        //        'password' => 'vbbb',
        //    ],
        //]);
        //$usermodel->update(['username' => time(), 'id' => 3]);
        //
        //$usermodel->where(['id'=>4])->delete();
        //var_dump($usermodel->where(['id'=>3])->getField('username'));
        //var_dump($usermodel->where(['id'=>3])->setField('username',date('Y-m-d H:i:s')));
        //var_dump($usermodel->where(['id'=>3])->find());
        //var_dump($usermodel->find(3));
        //var_dump($usermodel->count());
        //var_dump($usermodel->getError());
        var_dump($usermodel->where(['id'=>['between',[1,5]]])->select());
        var_dump($usermodel->getLastSql());
        return '';
    }
}
