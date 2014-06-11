<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_BindDao - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_BindDao.php 24614 2013-02-01 07:51:55Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_BindDao extends PwBaseDao {
	
	/**
	 * table name
	 */
	protected $_table = 'app_account_bind';
	/**
	 * primary key 自增主键
	 */
 	protected $_pk = 'id';
	/**
	 * table fields
	 */
	protected $_dataStruct = array('uid','type','app_uid');
	
	public function add($fields) {
		return $this->_add($fields, true);
	}
	
	public function batchAdd($fields){
		$sql = $this->_bindSql("REPLACE INTO %s (`uid`,`type`,`app_uid`) VALUES %s",$this->getTable(), $this->sqlMulti($fields));
		return $this->getConnection()->execute($sql);
	}

	public function deleteByUidAndType($uid,$type) {
		$sql = $this->_bindSql("DELETE FROM %s WHERE uid=? AND type=?",$this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->execute(array($uid,$type));
	}
	
	public function getByUid($uid){
		$sql = $this->_bindSql("SELECT * FROM %s WHERE uid=?",$this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid));
	}
	
	public function getByUidAndType($uid,$type) {
		$sql = $this->_bindSql("SELECT * FROM %s WHERE uid=? AND type=?",$this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($uid,$type));
	}
	
	public function fetchByUidAndTypes($uid,$types){
		$sql = $this->_bindSql("SELECT * FROM %s WHERE uid=? AND type IN %s",$this->getTable(),$this->sqlImplode($types));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid));
	}
	
	public function getByAppUidAndType($app_uid,$type){
		$sql = $this->_bindSql("SELECT * FROM %s WHERE app_uid=? AND type=?",$this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($app_uid,$type));
	}
	
	public function deleteByUid($uid){
		$sql = $this->_bindSql("DELETE FROM %s WHERE uid=?",$this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->execute(array($uid));
	}
	
}

?>