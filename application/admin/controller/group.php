<?php
class GroupController extends AdminController{

    public function init() {
        $this->tableName='admin_group';
        parent::init();
    }

    public function indexAction() {
        $this->list=$this->model->order('id asc')->getlist();
        $this->display();
    }

    public function addAction() {
        if (IS_POST){
            $param['name']=I('name','str');
            $param['intro']=I('intro','str');
            $param['node']=implode(',',M('admin_node')->toNodeAuth(I('node','arr',array())));
            $param['create_user_id']=$_SESSION['admin']['userid'];
            $param['create_time']=NOW_TIME;
            if($this->model->add($param)){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
        $tree=new Tree(M('admin_node'));
        $this->menu=$tree->getAuthList(0,'id,name');
        $this->display();
    }

    public function editAction() {
        $id=I('request.id','int',0);
        $info=$this->model->field('id,name,node,intro')->where(array('id'=>$id))->find();
        if (IS_POST){
            $param['name']=I('name','str');
            $param['intro']=I('intro','str');
            $param['node']=implode(',',M('admin_node')->toNodeAuth(I('node','arr',array())));
            $param['update_user_id']=$_SESSION['admin']['userid'];
            $param['update_time']=NOW_TIME;
            $param['id']=$id;
            if ($this->model->edit($param)){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $info['node']=explode(',',$info['node']);
        $tree=new Tree(M('admin_node'));
        $this->menu=$tree->getAuthList(0,'id,name');
        $this->info=$info;
        $this->display();
    }

}