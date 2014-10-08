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
}