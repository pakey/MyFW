<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : upload.php
 */
 
class upload{
	public $postfile; //文件
	public $fileName; //自定义文件名
	public $fileDir;  //自定义文件存放路径
	public $fileType = "jpg|png|gif|txt|bmp|doc|xls|jpeg"; //自定义允许文件后缀
	public $fileSize; //自定义文件大小 Kb
	public $fileUrl;
	
	//临时文件
	public function setFile($postfile){
		$this->postfile = $postfile;
	}
	
	/**
	 *设置上传的文件名
	 *
	 */
	public function setName($filename){
		$this->fileName = $filename;
	}
	
	/**
	 *设置上传的文件路径
	 *
	 */
	public function setDir($filedir){
		$this->fileDir = $filedir;
	}
	
	/**
	 *设置上传的文件后缀
	 *
	 */
	public function setType($filetype){
		$this->fileType = $filetype;
	}
	
	/**
	 *设置上传的文件大小
	 *
	 */
	public function setSize($filesize){
		$this->fileSize = $filesize;
	}
	/**
 *设置上传的文件访问路径
 *
 */
	public function seturl($url){
		$this->fileUrl = $url;
	}
	
	/**
	 *检测文件大小
	 *
	 */
	private function check_size(){
		if($this->postfile['size'] <= $this->fileSize*1024){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 *检测文件后缀
	 *
	 */
	private function check_type(){
		$check = false;
		$ext = explode("|",$this->fileType); //以"|"来分解默认扩展名;  
		foreach($ext as $k=>$v){
			if($v == strtolower($this->get_type())) //比较文件扩展名是否与默认允许的扩展名相符;  
            {  
               $check = true; //相符则标记;  
               break;  
            } 
		}
		return $check;
	}
	/**
	 *获取文件后缀
	 */
	private function get_type(){
		return preg_replace('/.*\.(.*[^\.].*)*/iU','\\1',$this->postfile['name']); //取得文件扩展名;  
	}
	
	/**
	 *检测文件上传路径是否存在
	 *
	 */
	private function check_dir(){
	
		if (! file_exists($this->fileDir)){ //检测子目录是否存在;  
            @mkdir($this->fileDir,0777, true); //不存在则创建;  
        }  
	}
	
	/**
	 *检测文件完整路径
	 *
	 */
	private function check_filepath(){
		if(!$this->fileName) $this->fileName = date("YmdHis");
		$this->fileDir .= '/'.$this->fileName.".".$this->get_type();
		$this->fileUrl .= '/'.$this->fileName.".".$this->get_type();
	}
	
	/**
	 * 错误返回
	 **/
	 private function error($info){
		return array('status'=>0,'info'=>$info);
	 }
	/**
	 *上传文件
	 *
	 */
	public function upload(){
		 //检测文件大小
		 if(!$this->check_size()){ 
			return $this->error("上传附件不得超过".$this->fileSize."KB");  
		 }
		 //不符则警告  
        if(!$this->check_type()){  
            return $this->error("正确的扩展名必须为".$this->fileType."其中的一种！");  
        } 
		
		$this->check_dir();
		$this->check_filepath();
		//return $this->postfile['name'].'--'.$this->filePath;
		if($this->postfile['error'] == 0){
			if(!move_uploaded_file($this->postfile['tmp_name'],$this->fileDir)){
				return $this->error("上传失败！");  
			}else
			{
				return array('image'=>$this->fileUrl,'status'=>1,'info'=>'上传成功');
			}
		}else{
			return $this->error("上传错误，代码：".$this->postfile['error']."");  
		}

		
	}
	
	

}