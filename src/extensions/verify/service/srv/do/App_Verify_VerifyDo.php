<?php
Wind::import('EXT:verify.service.App_Verify');

/**
 * Enter description here ...
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package file
 */

class App_Verify_VerifyDo {
	
	/**
	 * 实名认证 - 用户头像
	 *
	 * @param int $uid
	 * @return boolean
	 */
	public function uploadAvatar($uid) {
		return $this->_getService()->updateVerifyInfo($uid, App_Verify::VERIFY_AVATAR);
	}
	
	/**
	 * 实名认证 - 真实姓名
	 *
	 * @param int $uid
	 * @return boolean
	 */
	public function uploadRealName($uid) {
		return $this->_getService()->updateVerifyInfo($uid, App_Verify::VERIFY_REALNAME);
	}
	
	/**
	 * @return App_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.App_Verify');
	}
	
	/**
	 * @return App_Verify_Service
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.App_Verify_Service');
	}
}
?>