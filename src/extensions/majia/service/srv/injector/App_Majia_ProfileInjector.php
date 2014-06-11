<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:majia.service.srv.do.App_Majia_ProfileDo');
/**
 * 构建
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_ProfileInjector.php 23780 2013-01-15 11:20:36Z xiaoxia.xuxx $
 * @package majia.service.srv.injector
 */
class App_Majia_ProfileInjector extends PwBaseHookInjector {
	
	/**
	 * 资料页扩展
	 */
	public function run() {
		return new App_Majia_ProfileDo($this->bp);
	}
}