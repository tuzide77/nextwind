<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_TaobaoUserInfoDao - 数据服务接口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_TaobaoUserInfoDao.php 23515 2013-01-10 08:03:57Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_TaobaoUserInfoDao extends PwBaseDao {
	
	/**
	 * table name
	 */
	protected $_table = 'app_account_taobao_userinfo';
	/**
	 * primary key
	 */
	protected $_pk = 'user_id';
	
	/**
	 * table fields
	 * user_id 淘宝用户id 唯一键
	 */
	protected $_dataStruct = array('user_id','nick','create_at');
	
	public function add($fields) {
		return $this->_add($fields, true);
	}
	
	public function update($user_id, $fields) {
		return $this->_update($user_id, $fields);
	}
	
	public function delete($user_id) {
		return $this->_delete($user_id);
	}
	
	public function get($user_id) {
		return $this->_get($user_id);
	}
}

?>