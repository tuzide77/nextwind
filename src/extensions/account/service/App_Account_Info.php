<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_InfoDm');
/**
 * App_Account_Info - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_Info.php 22911 2012-12-28 10:06:35Z xiao.fengx $
 * @package account
 */
class App_Account_Info {
	

	public function add(App_Account_InfoDm $dm) {
		if (true !== ($r = $dm->beforeAdd())) return $r;
		return $this->_loadDao()->add($dm->getData());
	}
	
	public function update(App_Account_InfoDm $dm) {
		if (true !== ($r = $dm->beforeUpdate())) return $r;
		return $this->_loadDao()->update($dm->getType(), $dm->getData());
	}
	
	public function fetch($type){
		if(!is_array($type) || empty($type)) return false;
		return $this->_loadDao()->fetch($type);
	}
	
	public function get($type) {
		if(!$this->_getAccountTypeService()->checkType($type)) return false;
		return $this->_loadDao()->get($type);
	}
	
	public function delete($type) {
		if(!$this->_getAccountTypeService()->checkType($type)) return false;
		return $this->_loadDao()->delete($type);
	}
	
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
	
	/**
	 * @return App_Account_InfoDao
	 */
	private function _loadDao() {
		return Wekit::loadDao('EXT:account.service.dao.App_Account_InfoDao');
	}
}

?>