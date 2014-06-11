<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_InfoDao - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_InfoDao.php 25854 2013-03-25 13:15:25Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_InfoDao extends PwBaseDao {
	
	/**
	 * table name
	 */
	protected $_table = 'app_account_info';
	/**
	 * primary key
	 * 账号通类型 微博 qq 淘宝 支付宝等
	 */
	protected $_pk = 'type';
	/**
	 * table fields
	 */
	protected $_dataStruct = array('type','app_key','app_secret','display_order','status');
	
	public function add($fields) {
		return $this->_add($fields, true);
	}
	
	public function update($type, $fields) {
		return $this->_update($type, $fields);
	}
	
	public function delete($type) {
		return $this->_delete($type);
	}
	
	public function get($type) {
		return $this->_get($type);
	}
	
	public function fetch($type){
		return $this->_fetch($type);
	}
		
	
}

?>