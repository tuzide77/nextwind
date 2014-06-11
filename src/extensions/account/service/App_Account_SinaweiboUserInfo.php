<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_SinaweiboUserInfoDm');
/**
 * App_Account_SinaweiboUserInfo - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id:$
 * @package account
 */
class App_Account_SinaweiboUserInfo {

	public function add(App_Account_SinaweiboUserInfoDm $dm) {
		if (true !== ($r = $dm->beforeAdd())) return $r;
		return $this->_loadDao()->add($dm->getData());
	}
	
	public function update(App_Account_SinaweiboUserInfoDm $dm) {
		if (true !== ($r = $dm->beforeUpdate())) return $r;
		return $this->_loadDao()->update($dm->getUserId(), $dm->getData());
	}

	public function get($user_id) {
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		return $this->_loadDao()->get($user_id);
	}
	
	
	public function delete($user_id) {
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		return $this->_loadDao()->delete($user_id);
	}
	
	public function replace(App_Account_SinaweiboUserInfoDm $dm){
		if (!$data = $dm->getData()) return false;
		return $this->_loadDao()->replace($data);
	}
	
	/**
	 * @return App_Account_InfoDao
	 */
	private function _loadDao() {
		return Wekit::loadDao('EXT:account.service.dao.App_Account_SinaweiboUserInfoDao');
	}
}

?>