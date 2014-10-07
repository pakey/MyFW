<?php

class themeController extends AdminController {

    public function init() {
        $this->typename = '模版管理';
        parent::init();
    }

    public function indexAction() {
        $config = pt::import(APP_PATH . '/common/config.php');
        $this->defaulttpl = $config['tpl_theme'];
        $this->list = M('theme')->getlist();
        $this->display();
    }

    public function setAction() {
        $key = I('get.tpl', 'str', '');
        cookie('THEME_' . MODULE_NAME, null);
        $this->saveconfig(array('tpl_theme' => $key));
        $this->success('设置默认模版成功');
    }

    public function configAction() {
        $this->actionname = '模版参数设置';
        $key = I('get.tpl', 'str', '');
        $file = TPL_PATH . '/' . $key . '/config.php';
        if (!$this->config = pt::import($file)) {
            $this->config = array();
        }
        if (IS_POST) {
            $data = array();
            foreach ($_POST as $k => $v) {
                if ($v['name'] && $v['key']) {
                    $data[$v['key']] = array(
                        'name' => $v['name'],
                        'value' => $v['value'],
                    );
                }
            }
            F($file, $data);
            $this->success('修改成功');
        }
        $this->tpl = I('get.tpl', 'str');
        $this->display();
    }

    public function delAction() {
        $key = I('get.tpl', 'str', '');
        $id = I('get.id', 'str', '');
        $file = TPL_PATH . '/' . $key . '/config.php';
        $data = pt::import($file);
        if (isset($data[$id])) {
            unset($data[$id]);
            F($file, $data);
            $this->success('删除成功');
        }
        $this->error('没有找到对应的配置项');
    }
}