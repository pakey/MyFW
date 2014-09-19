<?php
/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : login.php
 */

class image{
	//原始图片信息
	public $srcImgInfo;
	
	//原始图片路径
	public $srcImg;
	
	//目标图片名 默认原始名
	public $dstImgName;
	
	//设置是否开启等比例缩放,默认开启
	public $isScale = true;
	
	//被处理的图片的宽
	public $srcWidth;

	//被处理的图片的高
	public $srcHeight;

	//处理前创建的图像源
	public $srcSource;

	//处理后创建的图像源
	public $dstSource;

	//处理后的宽
	public $dstWidth;

	//处理后的高
	public $dstHeight;

	//处理后的前缀名
	public $prefix;

	//被处理图像的类型
	public $srcType;
	
	//裁切坐标-x
	public $coordinateX = 0;
	
	//裁切坐标-y
	public $coordinateY = 0;
	
	/**
	 *设置处理的图片路径
	 *
	 */
	public function setSrcImg($src){
		$this->srcImg = $src;
	}
	/**
	 *设置目标图片名
	 *
	 */
	public function setDstImgName($dst){
		$this->dstImgName = $dst;
	}
	/**
	 *设置目标图片宽高
	 *
	 */
	public function setDstImgWidthHeight($w = 40,$h = 40){
		$this->dstWidth = $w;
		$this->dstHeight = $h;
	}
	
	/**
	 *设置前缀
	 *
	 */
	public function setPrefix($prefix){
		$this->prefix = $prefix;
	}
	
	/**
	 *设置坐标 array(0,0,0,0);
	 *
	 */
	public function setCoordinate($str){
		$arr = explode(',' , $str);
		$this->coordinateX = $arr[0];
		$this->coordinateY = $arr[1];
		$this->srcWidth = $arr[2];
		$this->srcHeight = $arr[3];
	}
	
	/**
	 *创建图片源
	 *
	 */
	private function setSrcSource(){
		//原图像的类型
		$this->srcType = $this->srcImgInfo[2];

		//根据图片类型创建图像源
		switch($this->srcType){
			case 1:	//gif
				$this->srcSource = imagecreatefromgif($this->srcImg);	//创建源图像
				break;
			case 2:	//jpg
				$this->srcSource = imagecreatefromjpeg($this->srcImg);	//创建源图像
				break;
			case 3:	//png
				$this->srcSource = imagecreatefrompng($this->srcImg);	//创建源图像
				break;
			default:
				return $this->error('图片类型错误');
		}
	}
	/**
	 *创建目标图片源
	 *
	 */
	private function setDstSource(){
		$this->dstSource = imagecreatetruecolor($this->dstWidth,$this->dstHeight);
	}
	
	/**
	 *处理--裁切
	 *
	 */
	public function cut(){
		$this->srcImgInfo = getimagesize($this->srcImg);
		$this->setSrcSource();
		$this->setDstSource();
		
		//裁切
		
		imagecopyresized($this->dstSource,$this->srcSource,0,0,$this->coordinateX,$this->coordinateY,$this->dstWidth,$this->dstHeight,$this->srcWidth,$this->srcHeight);
		
		//生成
		//取出源图像的目录路径
		$path = pathinfo($this->srcImg);
			
		//如果设置了压缩文件名字
		if($this->dstImgName){
			$this->dstImgName = $this->dstImgName.'.'.$path['extension'];
		}else{
			$this->dstImgName = $path['basename'];
		}
		if($this->prefix){
			$outImg = $path['dirname'].'/'.$this->prefix.$this->dstImgName;
		}else{
			$outImg = $path['dirname'].'/'.$this->dstImgName;
		}
		
		
		//根据类型输出图像
		switch($this->srcType){
			case 1:	//gif
				imagegif($this->dstSource,$outImg);
				break;
			case 2:	//jpg
				imagejpeg($this->dstSource,$outImg);
				break;
			case 3:	//png
				imagepng($this->dstSource,$outImg);
				break;
		}
		//var_dump('0,0'.','.$this->coordinateX.','.$this->coordinateY.','.$this->dstWidth.','.$this->dstHeight.','.$this->srcWidth.','.$this->srcHeight);
		imagedestroy($this->srcSource);
		imagedestroy($this->dstSource);
		
	}
	/**
	 *错误提示
	 *
	 */
	private function error($info){
		return array('status'=>0,'info'=>$info);
	}
}