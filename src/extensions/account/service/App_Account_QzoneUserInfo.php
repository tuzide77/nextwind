<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_QzoneUserInfoDm');
/**
 * App_Account_QzoneUserInfo - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: $
 * @package account
 */
class App_Account_QzoneUserInfo {
	

	public function add(App_Account_QzoneUserInfoDm $dm) {
		if (true !== ($r = $dm->beforeAdd())) return $r;
		return $this->_loadDao()->add($dm->getData());
	}
	
	public function updateByOpenId(App_Account_QzoneUserInfoDm $dm) {
		if (true !== ($r = $dm->beforeUpdate())) return $r;
		return $this->_loadDao()->updateByOpenId($dm->getOpenId(), $dm->getData());
	}
	

	public function get($user_id) {
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		return $this->_loadDao()->get($user_id);
	}
	
	public function getByOpenId($openId){
		return $this->_loadDao()->getByOpenId($openId);
	}
	
	public function delete($user_id) {
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		return $this->_loadDao()->delete($user_id);
	}
	
	/**
	 * @return App_Account_InfoDao
	 */
	private function _loadDao() {
		return Wekit::loadDao('EXT:account.service.dao.App_Account_QzoneUserInfoDao');
	}
}

?>