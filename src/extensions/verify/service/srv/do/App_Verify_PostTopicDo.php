<?php
Wind::import('SRV:forum.srv.post.do.PwPostDoBase');
Wind::import('EXT:verify.service.App_Verify');

/**
 * 实名认证 - 发帖扩展
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Verify_PostTopicDo extends PwPostDoBase { 
	
	protected $loginUser;
	
	public function __construct(PwPost $pwpost) {
		$this->loginUser = $pwpost->user;
	}
	
	public function check($postDm) {
		if (($result = $this->_getService()->checkVerifyRights($this->loginUser->uid, App_Verify::RIGHT_POSTTOPIC)) instanceof PwError) {
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