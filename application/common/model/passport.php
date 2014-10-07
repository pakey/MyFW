<?php
class PassportModel extends Model{

    public function checkInfo($username,$password) {
        if ($info=$this->where(array('name'=>$username))->field('id,password,salt')->find()){
            if ($info['password']==md5(md5($password).$info['salt'])){
                return $info['id'];
            }
        }
        return false;
    }
}