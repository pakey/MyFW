<?php
class FriendlinkModel extends Model{
    /**
     * 插入数据
     * @param $param
     * @return mixed
     */
    public function add($param) {
        return $this->insert($param);
    }

    /**
     * 修改
     * @param $param
     * @return mixed
     */
    public function edit($param) {
        //更新缓存
        return $this->update($param);
    }

    /**
     * 删除数据
     * @param $where
     */
    public function del($where) {
        $this->where($where)->delete();
    }

    //获取列表
    public function getlist() {
        $list=(array)$this->select();
        foreach($list as &$v){
            $v['showname']=$this->getshowname($v);
            if (isset($v['create_user_id'])){
                //后台
                $v['create_username']=dc::get('passport',$v['create_user_id'],'name');
                $v['update_username']=dc::get('passport',$v['update_user_id'],'name');
                $v['url_edit']=U('friendlink.manage.edit',array('id'=>$v['id']));
                $v['create_time']=$v['create_time']?date('Y-m-d H:i',$v['create_time']):'';
                $v['update_time']=$v['update_time']?date('Y-m-d H:i',$v['update_time']):'';
            }
        }
        return $list;
    }

    //获取展示的链接名
    public function getshowname($v) {
        $v['showname']=$v['name'];
        if ($v['isbold']){
            $v['showname']='<b>'.$v['showname'].'</b>';
        }
        if ($v['color']!==''){
            $v['showname']="<font color={$v['color']}>{$v['showname']}</font>";
        }
        return $v['showname'];
    }
}