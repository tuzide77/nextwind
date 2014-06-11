<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * 马甲绑定-dao服务
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_MajiaDao.php 23847 2013-01-16 07:11:15Z xiaoxia.xuxx $
 * @package majia.service.dao
 */
class App_Majia_MajiaDao extends PwBaseDao {
	
	protected $_table = 'app_majia';
	protected $_pk = 'id';
	protected $_dataStruct = array('id', 'uid', 'password');
	
	/**
	 * 添加数据
	 *
	 * @param array $fields
	 * @return int
	 */
	public function add($fields) {
		return $this->_add($fields, true);
	}
	
	/**
	 * 更新数据
	 *
	 * @param int $id
	 * @param array $fields
	 * @return int
	 */
	public function update($id, $fields) {
		return $this->_update($id, $fields);
	}
	
	/**
	 * 更新新的绑定信息
	 *
	 * @param int $oldId
	 * @param int $newId
	 * @return int
	 */
	public function updateId($oldId, $newId) {
		$sql = $this->_bindTable("UPDATE %s SET id = ? WHERE id = ?");
		return $this->getConnection()->createStatement($sql)->execute(array($newId, $oldId));
	}
	
	/**
	 * 根据用户ID更新数据
	 *
	 * @param int $id
	 * @param array $fields
	 * @return int
	 */
	public function updateByUid($uid, $fields) {
		$sql = $this->_bindSql("UPDATE %s SET %s WHERE uid=?", $this->getTable(), $this->sqlSingle($fields));
		return $this->getConnection()->createStatement($sql)->execute(array($uid));
	}
	
	/**
	 * 删除数据
	 *
	 * @param int $id
	 * @return int
	 */
	public function delete($id) {
		return $this->_delete($id);
	}
	
	/**
	 * 根据UID列表批量删除
	 *
	 * @param array $uids
	 * @return int
	 */
	public function batchDeleteByUid($uids) {
		$sql = $this->_bindSql("DELETE FROM %s WHERE uid IN %s", $this->getTable(), $this->sqlImplode($uids));
		return $this->getConnection()->query($sql);
	}
	
	/**
	 * 根据用户ID删除马甲信息
	 *
	 * @param int $uid
	 * @return int
	 */
	public function deleteByUid($uid) {
		$sql = $this->_bindTable("DELETE FROM %s WHERE uid=?");
		return $this->getConnection()->createStatement($sql)->execute(array($uid));
	}
	
	/**
	 * 获取绑定信息
	 *
	 * @param int $uid
	 * @return array
	 */
	public function getByUid($uid) {
		$sql = $this->_bindSql('SELECT u2.uid as uid, u2.password as password FROM %s u1 LEFT JOIN %s u2 ON u1.id=u2.id WHERE u1.uid=?', $this->getTable(), $this->getTable());
		return $this->getConnection()->createStatement($sql)->queryAll(array($uid), 'uid');
	}
	
	/**
	 * 根据用户ID列表获取用户的绑定信息
	 *
	 * @param array $uids
	 * @return array
	 */
	public function fetchByUid($uids) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE uid IN %s', $this->getTable(), $this->sqlImplode($uids));
		return $this->getConnection()->query($sql)->fetchAll('uid');
	}
}

?>