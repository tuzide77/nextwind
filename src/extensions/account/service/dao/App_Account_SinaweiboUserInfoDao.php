<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * App_Account_SinaweiboUserInfoDao
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package account
 * 
 */
class App_Account_SinaweiboUserInfoDao extends PwBaseDao {
	
	/**
	 * table name
	 */
	protected $_table = 'app_account_sinaweibo_userinfo';
	/**
	 * primary key
	 */
	protected $_pk = 'user_id';
	
	/**
	 * table fields
	 */
	protected $_dataStruct = array('user_id','screen_name','name','province','city','location','description','url','profile_image_url','domain','gender','followers_count','friends_count','statuses_count','favourites_count','created_at','verified','create_time');
	
	public function add($fields) {
		return $this->_add($fields, true);
	}
	
	public function update($user_id, $fields) {
		return $this->_update($user_id, $fields);
	}
	
	public function get($user_id){
		return $this->_get($user_id);
	}
	
	public function delete($user_id) {
		return $this->_delete($user_id);
	}
	
	public function replace($fields){
		if (!$fields = $this->_filterStruct($fields)) {
			return false;
		}
		
		$sql = $this->_bindSql('REPLACE INTO %s SET %s', $this->getTable(), $this->sqlSingle($fields));
		return $this->getConnection()->execute($sql);
		
	}
}

?>