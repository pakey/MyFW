<?php
class UserController extends AdminController{

    public function init() {
        $this->tableName='admin_user';
        parent::init();
    }

    public function indexAction() {
        $this->list=$this->model->getlist();
        $this->display();
    }

    public function addAction() {
        if (IS_POST){
            $param['passport_id']=M('passport')->where(array('name'=>I('name','username','')))->getfield('id');
            $param['intro']=I('intro','str');
            $param['group_id']=I('groupid','int',0);
            $param['status']=I('status','int',0);
            $param['create_user_id']=$_SESSION['admin']['userid'];
            $param['create_time']=NOW_TIME;
            $param['login_num']=0;
            if($this->model->add($param)){
                $this->success('添加成功',U('index'));
            }else{
                $this->error('添加失败');
            }
        }
        $this->grouplist=M('admin_group')->field('id,name')->select();
        $this->display();
    }

    public function editAction() {
        $id=I('request.id','int',0);
        $info=$this->model->field('id,passport_id,group_id,intro,status')->where(array('id'=>$id))->find();
        if (IS_POST){
            $param['intro']=I('intro','str');
            $param['group_id']=I('groupid','int',0);
            $param['status']=I('status','int',0);
            $param['update_user_id']=$_SESSION['admin']['userid'];
            $param['update_time']=NOW_TIME;
            $param['id']=$id;
            if ($this->model->edit($param)){
                $this->success('修改成功',U('index'));
            }else{
                $this->error('修改失败');
            }
        }
        $this->grouplist=M('admin_group')->field('id,name')->select();
        $info['name']=dc::get('passport',$info['passport_id'],'name');
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
        }
    }

    public function ajaxAction() {
        $id=I('request.id','int',0);
        $value=I('param','username','');
        if ($value){
            if ($passport_id=M('passport')->where(array('name'=>$value))->getfield('id')){
                $oid=$this->model->where(array('passport_id'=>$passport_id))->getfield('id');
                if ($oid && $oid!=$id){
                    $data=array('status'=>'n','info'=>'您输入的用户名已经使用了');
                }else{
                    $data=array('status'=>'y','info'=>'帐号可以使用');
                }
            }else{
                $data=array('status'=>'n','info'=>'您输入的用户名不存在');
            }
        }else{
            $data=array('status'=>'n','info'=>'输入的用户名有误');
        }
        $this->ajax($data);
    }
}