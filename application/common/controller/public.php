<?php
class PublicController extends Controller{
	public function verifyAction()
	{
		session_start();
		verify::buildImageVerify(6, 1, 'png', 70,30);
	}
}