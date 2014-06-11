<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * App_Account_LoginSessionDm - 数据模型
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_LoginSessionDm.php 22913 2012-12-28 10:13:15Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_LoginSessionDm extends PwBaseDm {
	
	private $sessionid;
	
	public function __construct($sessionid = NULL){
		if($sessionid == null) return;
		$this->sessionid = $sessionid;
	}
	
	/**
	 * set table field value
	 *
	 * @param mixed $field_value
	 */
	public function setSessionId($sessionid) {
		$this->_data['sessionid'] = $sessionid;
		return $this;
	}

	public function setExpire($expire) {
		$this->_data['expire'] = intval($expire);
		return $this;
	}
	
	public function setSessionData($sessiondata) {
		$this->_data['sessiondata'] = $sessiondata;
		return $this;
	}
	
	
	public function getSessionId(){
		return $this->sessionid;
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
		if(empty($this->_data['sessionid'])) return new PwError('非法数据');
		if(empty($this->_data['expire'])) return new PwError('非法数据');
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	 protected function _beforeUpdate() {
		if($this->sessionid == null) return new PwError('非法数据');
		return true;
	}
}

?>