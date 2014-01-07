<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------

class Page {
    
    // 分页栏每页显示的页数
    public $rollPage = 5;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =    array('header'=>'条记录','prev'=>'上页','next'=>'下页','first'=>'首页','last'=>'尾页','link'=>' <a href="%pageUrl%">&nbsp;%pageNum%&nbsp;</a> ','current'=>' <span class="current">%pageNum%</span> ','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end% ');
    // 默认分页变量名
    protected $varPage;

    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$url='',$parameter='') {
        $this->url=$url;
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   C('VAR_PAGE') ? C('VAR_PAGE') : 'page' ;
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages    =   ceil($this->totalPages/$this->rollPage);
        $this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if($this->nowPage<1){
            $this->nowPage  =   1;
        }elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        $this->config[$name]    =   $value;
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function show($style='') {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);
        // 分析分页参数
        if($this->url==''){
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  $_REQUEST;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
				if (isset($parameter['m'])){
					unset($parameter['m']);
				}
				unset($parameter['c']);
				unset($parameter['a']);
            }
			$parameter[$p]  =   '__PAGE__';
            $url=rtrim(preg_replace("/$p\/\d+\//",'',__SELF__),'/');
			foreach($parameter as $k=>$v){
				$url.='/'.$k.'/'.$v;
			}
            $url.='/';
        }else{
            $url=$this->url;
        }
        if (!isset($this->config['pagelink'])){
            $this->config['pagelink']=$this->config['link'];
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',$upRow,$url),$this->config['prev']),$this->config['pagelink']);
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',$downRow,$url),$this->config['next']),$this->config['pagelink']);
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',$preRow,$url),"上".$this->rollPage."页"),$this->config['pagelink']);
            $theFirst   =   str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',1,$url),$this->config['first']),$this->config['pagelink']);
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage    =   str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',$nextRow,$url),"下".$this->rollPage."页"),$this->config['pagelink']);
            $theEnd   =   str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',$theEndRow,$url),$this->config['last']),$this->config['pagelink']);
        }
        // 1 2 3 4 5
        $linkPage = "";
        if($style='step'){
			$pagestart = $this->nowPage - floor($this->rollPage / 2);
			if($pagestart < 1) $pagestart = 1;
			$pageend = $pagestart + $this->rollPage - 1;
			if($pageend > $this->totalPages) $pageend = $this->totalPages;
        }else{
			$pagestart = ($nowCoolPage-1)*$this->rollPage+1;
			$pageend = $nowCoolPage*$this->rollPage;
        }
        for($i=$pagestart;$i<=$pageend;$i++){
            if($this->nowPage==$i){
                if($this->totalPages != 1){
					$linkPage .= str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',$i,$url),$i),$this->config['current']);
                }
            }else{
                if($this->totalPages>=$i){
                    $linkPage .= str_replace(array('%pageUrl%','%pageNum%'), array(str_replace('__PAGE__',$i,$url),$i),$this->config['link']);
                }else{
                    break;
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
}