<?php
Wind::import('APPS:.profile.service.PwUserProfileExtends');
Wind::import('EXT:verify.service.srv.do.App_Verify_Profile');

/**
 * 帖子发布 - 话题相关
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Verify_Profile_Injector extends PwBaseHookInjector {
	
	public function createHtml() {
		$user = Wekit::getLoginUser();
		$bp = new PwUserProfileExtends($user);
		return new App_Verify_Profile($bp);
	}
	
	public function displayFootHtml() {
		$left = $this->getInput('_tab', 'get');
		$left = $left ? $left : 'profile';
		$user = Wekit::getLoginUser();
		$bp = new PwUserProfileExtends($user);
		return new App_Verify_Profile($bp, $left);
	}

}