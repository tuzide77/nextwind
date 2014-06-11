<?php
Wind::import('APPS:.profile.service.PwProfileExtendsDoBase');
/**
 * 个人设置 - 实名认证
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Verify_Profile extends PwProfileExtendsDoBase {
	protected $user;
	protected $left;
	
	public function __construct(PwUserProfileExtends $bp = null, $left = null) {
		$this->user = $bp->user;
		$this->left = 'profile_'.$left;
	}
	
	public function createHtml($left, $tab) {
		$verify = $this->_getDs()->getVerify($this->user->uid);
		$types = $this->_getService()->getOpenVerifyType();
		$typeNames = $this->_getService()->getVerifyTypeName();
		$haveVerify = $noVerify = array();
		foreach ($types as $k => $v) {
			if (Pw::getstatus($verify['type'], $k)) {
				$haveVerify[$typeNames[$k]] = $v;
			} else {
				$noVerify[$typeNames[$k]] = $v;
			}
		}

		$conf = Wekit::C('app_verify');
		$rightType = $this->_getService()->getRightType();
		PwHook::template('displayAppProfileVerify', 'EXT:verify.template.index_run', true, $rightType, $types, $haveVerify, $noVerify, $conf);
	}
	
	public function displayFootHtml($current) {
		$this->conf = Wekit::C('app_verify');
		if (!$this->conf['verify.isopen']) {
			return false;
		}
		$openTypes = $this->_getService()->getOpenVerifyType();
		$types = $this->_getService()->getVerifyTypeName();
		$data = array();
		foreach ($openTypes as $k => $v) {
			$data[$types[$k]] = WindUrlHelper::createUrl('app/verify/index/typeTab', array('type' => $types[$k]));
		}
		
		PwHook::template('displayAppProfileVerifyFootHtml', 'EXT:verify.template.index_run', true, $this->left, $data);
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
