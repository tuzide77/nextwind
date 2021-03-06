<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * App_Account_AlipayUserInfoDm - 数据模型
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_TaobaoUserInfoDm.php 23515 2013-01-10 08:03:57Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_AlipayUserInfoDm extends PwBaseDm {
	
	private $user_id;
	
	public function __construct($user_id = 0){
		$user_id = intval($user_id);
		if($user_id < 1) return;
		$this->user_id = intval($user_id);
	}
	
	/**
	 * set table field value
	 *
	 * @param mixed $field_value
	 */
	public function setUserId($user_id) {
		$this->_data['user_id'] = intval($user_id);
		return $this;
	}

	public function setRealName($real_name) {
		//$this->_data['real_name'] = Pw::convert(trim($real_name), Wind::getApp()->getResponse()->getCharset(),'UTF-8');
		$this->_data['real_name'] = trim($real_name);
		return $this;
	}
	
	public function setCreateAt($create_at){
		$this->_data['create_at'] = intval($create_at);
		return $this;
	}
	
	
	public function getUserId(){
		return $this->user_id;
	}
	
	public function checkData(){
		if(empty($this->_data)){
			return new PwError('数据为空');
		}
		return true;
	}
	
	
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		if(empty($this->_data['user_id'])) return new PwError('非法数据');
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	 protected function _beforeUpdate() {
		if($this->user_id < 1) return new PwError('非法数据');
		return true;
	}
}

?>