<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_QzoneUserInfoDao
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package account
 * 
 */
class App_Account_QzoneUserInfoDao extends PwBaseDao {
	
	/**
	 * table name
	 */
	protected $_table = 'app_account_qzone_userinfo';
	/**
	 * primary key
	 */
	protected $_pk = 'user_id';
	
	/**
	 * table fields
	 */
	protected $_dataStruct = array('user_id','open_id','nick_name','avatar','avatar_mid','avatar_big','gender','create_at');
	
	public function add($fields) {
		return $this->_add($fields, true);
	}
	
	public function updateByOpenId($open_id, $fields) {
		$sql = $this->_bindSql("UPDATE %s SET %s WHERE open_id=?",$this->getTable(),$this->sqlSingle($fields));
		return $this->getConnection()->createStatement($sql)->execute(array($open_id));
	}
	
	public function getByOpenId($openId){
		$sql = $this->_bindSql("SELECT * FROM %s WHERE open_id=?",$this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($openId));
	}
	
	public function delete($user_id) {
		return $this->_delete($user_id);
	}
	
	public function get($user_id) {
		return $this->_get($user_id);
	}
}

?>