<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : page.php
 */
class Page
{

	public static function show($totalnum, $perpage, $nowpage, $params = array())
	{
		$params = array_merge($_GET,$params, array('page' => '__PAGE__'));
		unset($params['s'], $params['t']);
		$url = U($params['m'].'.'.$params['c'].'.'.$params['a'],$params);
		$totalpage = ceil($totalnum / $perpage);
		if ($totalpage < 6) {
			$start = 1;
			$end = $totalpage;
		} elseif ($nowpage < 3) {
			$start = 1;
			$end = 5;
		} elseif ($totalpage < $nowpage + 2) {
			$start = $totalpage - 4;
			$end = $totalpage;
		} else {
			$start = $nowpage - 2;
			$end = $nowpage + 2;
		}
		if ($totalpage < 1) return '';
		$str = "<i class=\"all\">{$totalnum} 条记录 {$nowpage}/{$totalpage} 页</i>";
		if ($nowpage == 1) {
			$str .= '<a href="javascript:;" onclick="return false;" class="disable">上一页</a> ';
		} else {
			$str .= '<a href="' . str_replace('__PAGE__', 1, $url) . '">首页</a> ';
			$str .= '<a href="' . str_replace('__PAGE__', ($nowpage - 1), $url) . '">上一页</a> ';
		}
		for ($i = $start; $i <= $end; $i++) {
			if ($i == $nowpage) {
				$str .= '<a href="' . str_replace('__PAGE__', $i, $url) . '" class="current">' . $i . '</a> ';
			} else {
				$str .= '<a href="' . str_replace('__PAGE__', $i, $url) . '">' . $i . '</a> ';
			}
		}
		if ($nowpage == $totalpage) {
			$str .= '<a href="javascript:;" onclick="return false;" class="disable">下一页</a> ';
		} else {
			$str .= '<a href="' . str_replace('__PAGE__', ($nowpage + 1), $url) . '" >下一页</a> ';
			$str .= ' <a href="' . str_replace('__PAGE__', $totalpage, $url) . '">尾页</a> ';
		}
		return $str;
	}
}