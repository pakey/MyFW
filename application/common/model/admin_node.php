<?php
class Admin_NodeModel extends Model{

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
        return $this->update($param);
    }

    public function del($id) {
        $this->where(array('id'=>$id))->delete();
    }

    /**
     * 获取当前节点及父节点的信息
     * @return mixed
     */
    public function getMenuInfo() {
        $info=$this->field('name,pid')->where(array('module'=>MODULE_NAME,'controller'=>CONTROLLER_NAME,'action'=>ACTION_NAME))->find();
        $parentinfo=$this->field('name,pid,module,controller,action')->where(array('id'=>$info['pid']))->find();
        if (empty($parentinfo['module'])){
            $res['menu']['name']=$info['name'];
            $res['menu']['url']=U(MODULE_NAME.'.'.CONTROLLER_NAME.'.'.ACTION_NAME);
            $res['submenu']['name']='';
            $res['submenu']['url']='';
        }else{
            $res['menu']['name']=$parentinfo['name'];
            $res['menu']['url']=U($parentinfo['module'].'.'.$parentinfo['controller'].'.'.$parentinfo['action']);
            $res['submenu']['name']=$info['name'];
            $res['submenu']['url']=U(MODULE_NAME.'.'.CONTROLLER_NAME.'.'.ACTION_NAME);
        }
        return $res;
    }
}