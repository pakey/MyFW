<?php
// 菜单管理
class NodeController extends AdminController{

    public function init() {
        $this->tableName='admin_node';
        parent::init();
    }

    public function indexAction() {
        $tree=new Tree($this->model);
        $this->list=$tree->getIconList($tree->getList(0,'id,name,module,controller,action,ordernum,status'));
        $this->assign('totalnum', count($this->list));
        $this->display();
    }

    public function addAction() {
        if(IS_POST){
            $param['name']=I('name','str','');
            $param['pid']=I('pid','int',0);
            $param['module']=I('module','str','');
            $param['controller']=I('controller','str','');
            $param['action']=I('action','str','');
            $param['status']=I('status','int',1);
            $param['ordernum']=I('ordernum','int',1);
            $param['create_user_id']=$_SESSION['admin']['userid'];
            $param['create_time']=NOW_TIME;
            if($this->model->add($param)){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
        $tree=new Tree($this->model);
        $this->parentlist=$tree->getIconList($tree->getList(0,'id,name'));
        $this->display();
    }

    public function editAction() {
        
    }

    public function delAction() {
        
    }

    public function multiAction() {

    }
}