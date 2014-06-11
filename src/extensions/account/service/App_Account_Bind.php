<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_BindDm');
/**
 * App_Account_Bind - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_Bind.php 24614 2013-02-01 07:51:55Z xiao.fengx $
 * @package account
 */
class App_Account_Bind {
	

	public function add(App_Account_BindDm $dm) {
		if (true !== ($r = $dm->beforeAdd())) return $r;
		return $this->_loadDao()->add($dm->getData());
	}

	public function batchAdd($fields){
		if(!is_array($fields) || count($fields) < 1) return false;
		return $this->_loadDao()->batchAdd($fields);
	}
	
	public function deleteByUidAndType($uid,$type) {
		$uid = intval($uid);
		if($uid < 1) return false;
		if(!$this->_getAccountTypeService()->checkType($type)) return false;
		return $this->_loadDao()->deleteByUidAndType($uid,$type);
	}
	
	public function getByUid($uid){
		$uid = intval($uid);
		if($uid < 1) return false;
		return $this->_loadDao()->getByUid($uid);
	}
	
	public function getByUidAndType($uid,$type) {
		$uid = intval($uid);
		if($uid < 1) return false;
		if(!$this->_getAccountTypeService()->checkType($type)) return false;
		return $this->_loadDao()->getByUidAndType($uid,$type);
	}
	
	public function fetchByUidAndTypes($uid,$types){
		$uid = intval($uid);
		if($uid < 1) return false;
		if(!is_array($types) || empty($types)) return false;
		return $this->_loadDao()->fetchByUidAndTypes($uid,$types);
	}
	
	public function getByAppUidAndType($app_uid,$type){
		$app_uid = intval($app_uid);
		if($app_uid < 1) return false;
		if(!$this->_getAccountTypeService()->checkType($type)) return false;
		return $this->_loadDao()->getByAppUidAndType($app_uid,$type);
	}
	
	public function deleteByUid($uid){
		$uid = intval($uid);
		if($uid < 1) return false;
		return $this->_loadDao()->deleteByUid($uid);
	}
	
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
	
	/**
	 * @return App_Account_InfoDao
	 */
	private function _loadDao() {
		return Wekit::loadDao('EXT:account.service.dao.App_Account_BindDao');
	}
}

?>