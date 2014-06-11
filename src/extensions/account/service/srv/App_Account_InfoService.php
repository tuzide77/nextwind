<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_InfoService - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_InfoService.php 22630 2012-12-26 04:54:55Z xiao.fengx $
 * @package account
 */
class App_Account_InfoService {
	
// 	public function getAccountInfoByType($type){
// 		if($this->_getAccountTypeService()->checkType($type) === false) return false;
// 		return $this->_getAccountInfoDs()->get($type);
// 	}
		
	
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
	
	private function _getAccountInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_Info');
	}
	
}

?>