<?php
class indexController extends AdminController{
    public function init() {
        $this->tableName='friendlink';
        parent::init();
    }

    public function indexAction() {
        $this->list=$this->model->getlist();
        $this->display();
    }

    public function addAction() {
        if (IS_POST){
            $param['name']=I('name','str','');
            if (!$param['name']){
                $this->error('请输入链接名称');
            }
            $param['url']=I('url','url','');
            if (!$param['url']){
                $this->error('请输入链接地址');
            }
            $param['logo']=I('logo','str','');
            $param['description']=I('description','str','');
            $param['color']=I('color','str','');
            $param['ordernum']=I('ordernum','int',50);
            $param['status']=I('status','int',50);
            $param['isblod']=I('isblod','int',50);
            $param['create_user_id']=$_SESSION['admin']['userid'];
            $param['create_time']=NOW_TIME;
            if($this->model->add($param)){
                $this->success('添加成功',U('index'));
            }else{
                $this->error('添加失败');
            }
        }
        $this->display();
    }

    public function editAction() {
        $id=I('request.id','int',0);
        $info=$this->model->where(array('id'=>$id))->find();
        if (IS_POST){
            $param['name']=I('name','str','');
            if (!$param['name']){
                $this->error('请输入链接名称');
            }
            $param['url']=I('url','url','');
            if (!$param['url']){
                $this->error('请输入链接地址');
            }
            $param['logo']=I('logo','str','');
            $param['description']=I('description','str','');
            $param['color']=I('color','str','');
            $param['ordernum']=I('ordernum','int',50);
            $param['status']=I('status','int',50);
            $param['isblod']=I('isblod','int',50);
            $param['id']=$id;
            if ($this->model->edit($param)){
                $this->success('修改成功',U('index'));
            }else{
                $this->error('修改失败');
            }
        }
        $this->info=$info;
        $this->display();
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