<?php
Wind::import('EXT:verify.service.srv.action.App_Verify_Action');

class App_Verify_Email extends App_Verify_Action{
	
	public $unique = true;
	
	public function checkVerify($check) {
		
		return true;
	}
	
	public function buildDetail($check) {
		
		return $check;
	}
}