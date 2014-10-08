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
        $_SESSION['admin']['groupid']=$this->where(array('passport_id'=>$userid))->getField('group_id');
        $_SESSION['admin']['groupname']=dc::get('admin_group',$_SESSION['admin']['groupid'],'name');
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

    // 获取列表
    public function getlist() {
        $list=$this->select();
        foreach($list as &$v){
            $v['username']=dc::get('passport',$v['passport_id'],'name');
            $v['groupname']=dc::get('admin_group',$v['passport_id'],'name');
            $v['create_username']=dc::get('passport',$v['create_user_id'],'name');
            $v['update_username']=dc::get('passport',$v['update_user_id'],'name');
            $v['url_edit']=U('admin.user.edit',array('id'=>$v['id']));
            $v['create_time']=date('Y-m-d H:i',$v['create_time']);
            $v['update_time']=date('Y-m-d H:i',$v['update_time']);
            $v['login_time']=date('Y-m-d H:i',$v['login_time']);
        }
        return $list;
    }
}