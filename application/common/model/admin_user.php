<?php
class Admin_UserModel extends Model{

    // 检查用户是否具有可登录状态
    public function checkUserStatus($userid) {
        return $this->where(array('passport_id'=>$userid))->getfield('status');
    }

    // 设置用户登录状态
    public function setLoginStatus($userid) {
        //设置登录信息
        $_SESSION['admin']['userid']=$userid;
        $_SESSION['admin']['username']=M('passport')->where(array('id'=>$userid))->getField('name');
        // 更新通行证登录时间
        $data['login_ip']=get_ip();
        $data['login_time']=NOW_TIME;
        M('passport')->where(array('id'=>$userid))->update($data);
        // 更新后台表信息
        $data['login_num']=array('exp','`login_num`+1');
        $this->where(array('passport_id'=>$userid))->update($data);
    }

    // 删除用户登录信息
    public function delLoginStatus() {
        $_SESSION['admin']=null;
    }


}