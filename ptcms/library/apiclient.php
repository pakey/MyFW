<?php

class Apiclient
{
	protected $uri;

	/**
	 * @param $apiuri
	 * @param $apiid
	 * @param $apikey
	 */
	public function __construct($apiuri, $apiid, $apikey)
	{
		$this->appid = $apiid;
		$this->appkey = $apikey;
		$this->apiurl = $apiuri;
	}

	public function __call($method, $params = array())
	{
		$params=$params['0'];
		$params['action'] = $method;
		$params['appid'] = $this->appid;
		$params['format'] = 'json';
        $params['datetime']=$_SERVER['REQUEST_TIME'];
		$params['sign'] = $this->sign($params);
		//自动重试5次 防止失败！
		for($i=0;$i<5;$i++){
			$data = json_decode(http::get($this->apiurl, $params),true);
			if (is_array($data) && $data['status']!=0){
				break;
			}
		}
		if (is_array($data)){
			if ($data['status']==1){
				return $data['data'];
			}else{
				exit('采集错误！原因：'.$data['msg']. ' 参数：<pre>' . var_export($params,true).'</pre>');
			}
		}else{
			exit('采集错误！方法' . $method . ' 参数：<pre>' . var_export($params,true).'</pre>');
		}

	}

	public function sign($params)
	{
		asort($params);
		$str='';
		foreach($params as $k=>$v){
			$str.=$k.'='.$v.'&';
		}
        $str=substr($str,0,-1);
		return md5($str.$this->appkey);
	}

}
