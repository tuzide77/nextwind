<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_BindDm');

/**
 * App_Account_BindService - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_BindService.php 23980 2013-01-17 12:10:34Z xiao.fengx $
 * @package account
 */
class App_Account_BindService {
		
	public function __construct(){

	}
	
	public function bind($uid,$app_uid,$type){
		$name = $this->_getAccountTypeService()->getTypeName($type);
		if($this->_getAccountBindDs()->getByAppUidAndType($app_uid,$type)){
			$msg = '该' . $name . '账号已绑定，不能重复绑定';
			return new PwError($msg);
		}
		
		if(!$this->_getAccountTypeService()->checkType($type)) return new PwError('绑定类型错误，请重试');
		
		if($this->_getAccountBindDs()->getByUidAndType($uid,$type)) return new PwError('请勿重复绑定');
		
		$dm = new App_Account_BindDm();
		$dm->setUid($uid)
		    ->setType($type)
		    ->setAppUid($app_uid);
		return $this->_getAccountBindDs()->add($dm);
	}
	
	public function unbind($uid,$type){
		if(!$this->_getAccountTypeService()->checkType($type)) return new PwError('解绑类型错误，请重试');
		if(!$this->_getAccountBindDs()->getByUidAndType($uid,$type)) return new PwError('请勿重复解绑');
		
		return $this->_getAccountBindDs()->deleteByUidAndType($uid,$type);
		
	}
		
		
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
	
	private function _getAccountBindDs(){
		return Wekit::load('EXT:account.service.App_Account_Bind');
	}
	
}

?>