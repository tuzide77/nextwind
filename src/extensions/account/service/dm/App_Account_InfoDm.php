<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * App_Account_InfoDm - 数据模型
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_InfoDm.php 25854 2013-03-25 13:15:25Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_InfoDm extends PwBaseDm {
	
	private $type;
	
	public function __construct($type = null){
		if($type == null) return;
		$this->type = $type;
	}
	
	/**
	 * set table field value
	 *
	 * @param mixed $field_value
	 */
	public function setType($type) {
		$this->_data['type'] = $type;
		return $this;
	}

	public function setAppKey($app_key) {
		$this->_data['app_key'] = trim($app_key);
		return $this;
	}
	
	public function setAppSecret($app_secret) {
		$this->_data['app_secret'] = trim($app_secret);
		return $this;
	}
	
	public function setDisplayOrder($display_order) {
		$this->_data['display_order'] = intval($display_order);
		return $this;
	}

	public function setStatus($status) {
		$data = array(0,1);
		$status = intval($status);
		if(in_array($status,$data)){
			$this->_data['status'] = $status;
		}
		return $this;
	}
	
	public function getType(){
		return $this->type;
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
		if(empty($this->_data['type'])) return new PwError('非法数据');
		if(empty($this->_data['app_key'])) return new PwError('非法数据');
		if(empty($this->_data['app_secret'])) return new PwError('非法数据');
		if($this->_data['status'] != 0 && $this->_data['status'] != 1) return new PwError('非法数据');
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	 protected function _beforeUpdate() {
		if($this->type == null) return new PwError('非法数据');
		if(empty($this->_data['app_key'])) return new PwError('非法数据');
		if(empty($this->_data['app_secret'])) return new PwError('非法数据');
		if($this->_data['status'] !=0 && $this->_data['status'] != 1) return new PwError('非法数据');
		return true;
	}
}

?>