<?php
class FriendlinkBlock extends Block {
    public function exec($param) {
        $num=I('num','int',10,$param);
        $list=M('friendlink')->where(array('status'=>1))->field('name,url,description,logo,isbold,color')->order('ordernum asc')->limit($num)->getlist();
        return $list;

    }
}