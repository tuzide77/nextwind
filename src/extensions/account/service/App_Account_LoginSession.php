<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_LoginSessionDm');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');

/**
 * App_Account_Bind - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_LoginSession.php 22911 2012-12-28 10:06:35Z xiao.fengx $
 * @package account
 */
class App_Account_LoginSession {
	

	public function add(App_Account_LoginSessionDm $dm) {
		if (true !== ($r = $dm->beforeAdd())) return $r;
		return $this->_loadDao()->add($dm->getData());
	}
	
	public function update(App_Account_LoginSessionDm $dm) {
		if (true !== ($r = $dm->beforeUpdate())) return $r;
		App_Account_LoginSessionBo::unsetInstance($dm->getSessionId());//todo
		return $this->_loadDao()->update($dm->getSessionId(), $dm->getData());
	}
	
	public function get($sessionid) {
		return $this->_loadDao()->get($sessionid);
	}
	
	public function delete($sessionid) {
		App_Account_LoginSessionBo::unsetInstance($sessionid);
		return $this->_loadDao()->delete($sessionid);
	}
	
	public function deleteByExpire($expire){
		$expire = intval($expire);
		if($expire < 1) return false;
		App_Account_LoginSessionBo::unsetInstance();
		return $this->_loadDao()->deleteByExpire($expire);
	}
	
	/**
	 * @return App_Account_InfoDao
	 */
	private function _loadDao() {
		return Wekit::loadDao('EXT:account.service.dao.App_Account_LoginSessionDao');
	}
}

?>