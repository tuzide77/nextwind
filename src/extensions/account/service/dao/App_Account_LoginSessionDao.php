<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_BindDao - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_LoginSessionDao.php 22630 2012-12-26 04:54:55Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_LoginSessionDao extends PwBaseDao {
	
	/**
	 * table name
	 */
	protected $_table = 'app_account_login_session';
	/**
	 * primary key 
	 */
 	protected $_pk = 'sessionid';
	/**
	 * table fields
	 */
	protected $_dataStruct = array('sessionid','expire','sessiondata');
	
	public function add($fields) {
		return $this->_add($fields, true);
	}

	public function delete($sessionid) {
		return $this->_delete($sessionid);
	}
	
	public function update($sessionid,$fields){
		return $this->_update($sessionid,$fields);
	}
	
	public function get($sessionid) {
		return $this->_get($sessionid);
	}
	
	public function deleteByExpire($expire){
		$sql = $this->_bindSql("DELETE FROM %s WHERE expire < ?",$this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->execute(array($expire));
	}
	
}

?>