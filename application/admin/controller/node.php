<?php
// 菜单管理
class NodeController extends AdminController{

    public function init() {
        $this->tableName='admin_node';
        parent::init();
    }

    public function indexAction() {
        $tree=new Tree($this->model);
        $list=$tree->getIconList($tree->getList(0,'id,name,module,controller,action,ordernum,status'),2);
        foreach($list as &$v){
            $v['url_edit']=U('admin.node.edit',array('id'=>$v['id']));
            $v['url_son']=U('admin.node.add',array('pid'=>$v['id']));
        }
        $this->list=$list;
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
        $id=I('request.id','int',0);
        $info=$this->model->field('id,name,pid,module,controller,action,status,ordernum')->where(array('id'=>$id))->find();
        if (IS_POST){
            if ($info['id']==$_POST['pid']){
                $this->error('不能设置自己为上级节点');
            }
            $param['name']=I('name','str','');
            $param['pid']=I('pid','int',0);
            $param['module']=I('module','str','');
            $param['controller']=I('controller','str','');
            $param['action']=I('action','str','');
            $param['status']=I('status','int',1);
            $param['ordernum']=I('ordernum','int',1);
            $param['update_user_id']=$_SESSION['admin']['userid'];
            $param['update_time']=NOW_TIME;
            $param['id']=$id;
            if ($this->model->edit($param)){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $tree=new Tree($this->model);
        $this->parentlist=$tree->getIconList($tree->getList(0,'id,name'));
        $this->info=$info;
        $this->display();
    }

    public function multiAction() {
        $param['update_user_id']=$_SESSION['admin']['userid'];
        $param['update_time']=NOW_TIME;
        if (isset($_POST['changestatus'])){
            foreach($_POST['id'] as $k=>$v){
                $param['id']=$v;
                $param['status']=$_POST['value'][$k];
                $this->model->edit($param);
            }
            $this->success('修改状态成功');
        }elseif(isset($_POST['reorder'])){
            foreach($_POST['ordernum'] as $k=>$v){
                $param['id']=$k;
                $param['ordernum']=$v;
                $this->model->edit($param);
            }
            $this->success('排序成功');
        }else{
        }
    }
}