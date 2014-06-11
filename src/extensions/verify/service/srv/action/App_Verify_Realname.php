<?php
Wind::import('EXT:verify.service.srv.action.App_Verify_Action');

class App_Verify_Realname extends App_Verify_Action{
	
	public $unique = true;
	
	public function checkVerify($check) {
		Wind::import('SRV:user.dm.PwUserInfoDm');
		$dm = new PwUserInfoDm($check['uid']);
		$data = $check['data'] ? unserialize($check['data']) : array();
		if (!$data['realname']) return false;
		$dm->setRealname($data['realname']);
		Wekit::load('user.PwUser')->editUser($dm, PwUser::FETCH_MAIN);
		return true;
	}
	
	public function buildDetail($check) {
		$data = $check['data'] ? unserialize($check['data']) : array();
		$check['data'] = $data['realname'];
		return $check;
	}
}