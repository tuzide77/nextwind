<?php
Wind::import('SRV:message.srv.do.PwMessageDoBase');
Wind::import('EXT:verify.service.App_Verify');

/**
 * 实名认证 - 发消息扩展
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Verify_MessageDo extends PwMessageDoBase { 
	
	/* (non-PHPdoc)
	 * @see PwMessageDoBase::addMessage()
	 */
	public function check($fromUid, $content, $uid = 0) {
		if (($result = $this->_getService()->checkVerifyRights($fromUid, App_Verify::RIGHT_MESSAGE)) instanceof PwError) {
			return $result;
		}
		return true;
	}
	
	/**
	 * @return App_Verify_Service
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.App_Verify_Service');
	}
}
?>