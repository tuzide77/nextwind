<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * 马甲绑定的DM
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_MajiaDm.php 24332 2013-01-28 11:28:58Z xiaoxia.xuxx $
 * @package majia.service.dm
 */
class App_Majia_MajiaDm extends PwBaseDm {
	protected $id = null;
	
	/**
	 * 获取ID值
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	
	/**
	 * 设置ID值
	 *
	 * @param int $id
	 * @return App_Majia_MajiaDm
	 */
	public function setId($id) {
		$this->id = $id;
		$this->_data['id'] = $id;
		return $this;
	}
	
	/**
	 * 设置用户ID值
	 *
	 * @param int $uid
	 * @return App_Majia_MajiaDm
	 */
	public function setUid($uid) {
		$this->_data['uid'] = $uid;
		return $this;
	}
	
	/**
	 * 设置用户的帐号密码
	 *
	 * @param string $password
	 * @return App_Majia_MajiaDm
	 */
	public function setPassword($password) {
		$this->_data['password'] = trim($password);
		return $this;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		if (0 >= intval($this->getField('uid'))) return new PwError('非法UID');
		//if (!$this->getField('password')) return new PwError('绑定帐号的密码不能为空');
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	 protected function _beforeUpdate() {
	 	if (0 >= intval($this->getField('uid'))) return new PwError('非法UID');
	 	/*if (isset($this->_data['password']) && !$this->_data['password']) {
	 		return new PwError('绑定帐号的密码不能为空');
	 	}*/
		return true;
	}
}

?>