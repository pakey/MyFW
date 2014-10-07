<?php

class PublicController extends AdminController {

    public function init() {
        C('LAYOUT', false);
        session_start();
    }

    // 登录操作
    public function loginAction() {
        if (IS_POST) {
            $username = I('post.username', 'str', '');
            $password = I('post.password', 'str', '');
            $verify = I('post.verifycode');
            if ($verify == $_SESSION['verify']) {
                if ($userid = M('passport')->checkInfo($username, $password)) {
                    if (M('admin_user')->checkUserStatus($userid)) {
                        M('admin_user')->setLoginStatus($userid);
                        $this->success('登录成功',U('admin.index.index'));
                    } else {
                        $this->error('您没有权限进入后台！');
                    }
                } else {
                    $this->error('帐号和密码输入错误');
                }
            } else {
                $this->error('验证码输入错误');
            }
        }
        $this->display();
    }

    // 退出操作
    public function logoutAction() {
        M('admin_user')->delLoginStatus();
        $this->success('已经成功退出系统',U('Admin.Index.index'));
    }

    /**
     * 验证码
     */
    public function verifyAction() {
        verify::buildImageVerify(6, 1, 'png', 70, 30);
    }
}