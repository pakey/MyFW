<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : index.php
 */
class IndexController extends CommonController
{
	public function indexAction()
	{
		$this->show('Hello World By PT Framework!');
	}
}