<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * App_Account_BindDm - 数据模型
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_BindDm.php 24614 2013-02-01 07:51:55Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_BindDm extends PwBaseDm {
	
	
	/**
	 * set table field value
	 *
	 * @param mixed $field_value
	 */
	public function setUid($uid) {
		$this->_data['uid'] = intval($uid);
		return $this;
	}

	public function setType($type) {
		$this->_data['type'] = $type;
		return $this;
	}
	
	public function setAppUid($app_uid){
		$this->_data['app_uid'] = intval($app_uid);
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		if(empty($this->_data['uid'])) return new PwError('非法数据');
		if(empty($this->_data['type'])) return new PwError('非法数据');
		if(empty($this->_data['app_uid'])) return new PwError('非法数据');
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		return false;	
	}
}

?>