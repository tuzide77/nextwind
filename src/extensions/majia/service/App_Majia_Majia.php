<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:majia.service.dm.App_Majia_MajiaDm');

/**
 * 马甲绑定DS服务
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_Majia.php 24332 2013-01-28 11:28:58Z xiaoxia.xuxx $
 * @package majia.service
 */
class App_Majia_Majia {
	
	/**
	 * 添加马甲绑定
	 *
	 * @param App_Majia_MajiaDm $dm
	 * @return int
	 */
	public function add(App_Majia_MajiaDm $dm) {
		if (true !== ($r = $dm->beforeAdd())) return $r;
		return $this->_loadDao()->add($dm->getData());
	}
	
	/**
	 * 更新绑定信息
	 *
	 * @param int $oldId
	 * @param int $newId
	 * @return int
	 */
	public function updateId($oldId, $newId) {
		if (0 >= ($oldId = intval($oldId))) return false;
		if (0 >= ($newId = intval($newId))) return false;
		return $this->_loadDao()->updateId($oldId, $newId);
	}
	
	/**
	 * 更具用户ID更新马甲信息
	 *
	 * @param App_Majia_MajiaDm $dm
	 * @return int
	 */
	public function updateByUid(App_Majia_MajiaDm $dm) {
		if (true !== ($r = $dm->beforeUpdate())) return $r;
		return $this->_loadDao()->updateByUid($dm->getField('uid'), $dm->getData());
	}
	
	/**
	 * 根据用户ID批量删除绑定关系
	 *
	 * @param array $uids
	 * @return int
	 */
	public function batchDeleteByUid($uids) {
		if (empty($uids)) return false;
		return $this->_loadDao()->batchDeleteByUid($uids);
	}
	
	/**
	 * 根据用户ID删除绑定信息
	 *
	 * @param int $uid
	 * @return int
	 */
	public function deleteByUid($uid) {
		if (0 >= ($uid = intval($uid))) return false;
		return $this->_loadDao()->deleteByUid($uid);
	}
	
	/**
	 * 根据用户ID获取与该用户绑定的帐号信息
	 *
	 * @param int $uid
	 * @return array
	 */
	public function getByUid($uid) {
		if (0 >= ($uid = intval($uid))) return array();
		return $this->_loadDao()->getByUid($uid);	
	}
	
	/**
	 * 根据用户ID列表批量获取该用户绑定的信息
	 *
	 * @param array $uids
	 * @return array
	 */
	public function fetchByUid($uids) {
		if (empty($uids)) return array();
		return $this->_loadDao()->fetchByUid($uids);
	}
	
	/**
	 * @return App_Majia_MajiaDao
	 */
	private function _loadDao() {
		return Wekit::loadDao('EXT:majia.service.dao.App_Majia_MajiaDao');
	}
}

?>