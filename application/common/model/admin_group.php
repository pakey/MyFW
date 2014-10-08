<?php
class Admin_GroupModel extends Model{
    // 获取列表
    public function getlist() {
        $list=$this->select();
        foreach($list as &$v){
            $v['create_username']=dc::get('passport',$v['create_user_id'],'name');
            $v['update_username']=dc::get('passport',$v['update_user_id'],'name');
            $v['url_edit']=U('admin.group.edit',array('id'=>$v['id']));
            $v['create_time']=date('Y-m-d H:i',$v['create_time']);
            $v['update_time']=date('Y-m-d H:i',$v['update_time']);
        }
        return $list;
    }
}