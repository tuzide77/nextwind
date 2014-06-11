<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * App_Account_QzoneUserInfoDm - 数据模型
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_TaobaoUserInfoDm.php 22630 2012-12-26 04:54:55Z xiao.fengx $
 * @package account
 * 
 */
class App_Account_QzoneUserInfoDm extends PwBaseDm {
	
	private $open_id;
	
	public function __construct($open_id = ''){
		$open_id = trim($open_id);
		if(!$open_id) return;
		$this->open_id = $open_id;
	}

	public function setOpenId($open_id) {
		$this->_data['open_id'] = $open_id;
		return $this;
	}
	
	public function setNickName($nick_name){
		$this->_data['nick_name'] = Pw::convert(trim($nick_name), Wind::getApp()->getResponse()->getCharset(),'UTF-8');		
		return $this;
	}
	
	public function setAvatar($avatar){
		$this->_data['avatar'] = $avatar;
		return $this;
	}
	
	public function setAvatarMid($avatar_mid){
		$this->_data['avatar_mid'] = $avatar_mid;
		return $this;
	}
	
	public function setAvatarBig($avatar_big){
		$this->_data['avatar_big'] = $avatar_big;
		return $this;
	}
	
	public function setGender($gender){
		$this->_data['gender'] = Pw::convert(trim($gender), Wind::getApp()->getResponse()->getCharset(),'UTF-8');
		return $this;
	}
	
	public function setCreateAt($create_at){
		$this->_data['create_at'] = intval($create_at);
		return $this;
	}
	
	public function getOpenId(){
		return $this->open_id;
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
		if(empty($this->_data['open_id'])) return new PwError('非法数据');
		//if(empty($this->_data['nick_name'])) return new PwError('非法数据');
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	 protected function _beforeUpdate() {
		if(empty($this->open_id)) return new PwError('非法数据');
		return true;
	}
}

?>