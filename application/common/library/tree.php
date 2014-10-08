<?php

class tree {

    // 模型
    protected $model;
    // pid的字段名
    protected $pidkey;

    protected $icon = array('　│ ', '　├─', '　└─', '　　');

    public function __construct($model, $pidkey = 'pid') {
        $this->model = $model;
        $this->pidkey = $pidkey;
    }

    public function getList($pid = 0, $field = '*', $where = array(), $order = 'ordernum asc', $level = 1) {
        $where[$this->pidkey] = $pid;
        $data = $this->model->field($field)->where($where)->order($order)->select();
        if ($data === null) return array();
        $list = array();
        foreach ($data as $v) {
            $v['level'] = $level;
            $list[] = $v;
            $sons = $this->getlist($v[$this->model->getPk()], $field, $where, $order, $level + 1);
            $list = array_merge($list, $sons);
        }
        return $list;
    }

    public function getIconList($list, $startlevel = 1) {
        if ($startlevel>1){
            $preicon[]=array_fill(0,$startlevel-1,$this->icon['3']);
        }else{
            $preicon=array();
        }

        foreach ($list as $k => &$v) {
            $v['showname'] = $v['name'];
            if ($v['level'] >= $startlevel) {
                $icon = $this->icon['2'];
                $preicon[$v['level']-$startlevel]=$this->icon['3'];
                foreach (array_slice($list, $k + 1) as $n) {
                    if ($n['level'] < $v['level']) {
                        //后面没有同级的
                        $preicon[$v['level']-$startlevel]=$this->icon['3'];
                        break;
                    } elseif ($n['level'] == $v['level']) {
                        //后面没有同级的
                        $icon = $this->icon['1'];
                        $preicon[$v['level']-$startlevel]=$this->icon['0'];
                        break;
                    }
                }
                $v['showname'] = implode('',array_slice($preicon,0,$v['level']-$startlevel)) . $icon . $v['name'];
            }
        }
        return $list;
    }

    public function getSonList($pid = 0, $field = '*', $where = array(), $order = 'ordernum asc', $level = 1) {
        $where[$this->pidkey] = $pid;
        $data = $this->model->field($field)->where($where)->order($order)->select();
        if ($data === null) return array();
        $list = array();
        foreach ($data as $v) {
            $v['level'] = $level;
            if (isset($v['module'])) {
                $v['url'] = ($v['module'] == '') ? '' : U($v['module'] . '.' . $v['controller'] . '.' . $v['action']);
                unset($v['module'], $v['controller'], $v['action']);
            }
            if ($level < 3) {
                $v['sons'] = $this->getSonList($v[$this->model->getPk()], $field, $where, $order, $level + 1);
            }
            $list[] = $v;
        }
        return $list;
    }
}