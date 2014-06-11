<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRC:library.engine.hook.PwBaseHookInjector');
Wind::import('EXT:account.service.srv.do.App_Account_ProfileDo');


/**
 * 账号通 注入服务
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_Injector.php 23613 2013-01-14 02:26:25Z xiao.fengx $
 * @package account
 */

class App_Account_Injector extends PwBaseHookInjector {
	
	public function createHtml(){
		$user = Wekit::getLoginUser();
		$bp = new PwUserProfileExtends($user);
		return new App_Account_ProfileDo($bp);
	}
	
}