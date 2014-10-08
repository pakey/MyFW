<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : index.php
 */
class IndexController extends AdminController {

    public function init() {
        C('LAYOUT', false);
        parent::init();
    }

    // 框架页
    public function indexAction() {
        $tree=new Tree(M('admin_node'));
        $this->menu=$tree->getSonList(0,'id,name,module,controller,action');
        $this->display();
    }

    //欢迎页
    public function welcomeAction() {
        $tips = array();
        // success info warning danger
        if (C('appid') == 'test') {
            $tips[] = array('type' => 'danger', 'content' => '您当前使用的APPID为test，请抓紧时间申请正式APPID，否则您可能会无法使用我们的API服务！<a href="' . U('admin.set.api') . '">点击这里更换</a>');
        }
        if (C('adminpath') == 'admin') {
            $tips[] = array('type' => 'warning', 'content' => '您后台目录为默认的admin，为安全考虑，请您更改目录地址！<a href="' . U('admin.set.base') . '">点击这里更换</a>');
        }
        $usernum = M('user')->getNum();
        $ads = include DATA_PATH . '/ad.php';
        $this->sitenum = 1;
        $this->usernum = $usernum;
        $this->adnum = count($ads);
        $this->friendlinknum = count(C('friendlink'));
        $this->tips = $tips;
        $this->display();
    }
}