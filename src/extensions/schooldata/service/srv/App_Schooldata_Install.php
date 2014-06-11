<?php
Wind::import('APPS:appcenter.service.srv.iPwInstall');

/**
 * 学校数据安装
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Schooldata_Install implements iPwInstall {
	
	/**
	 * 注册主导航
	 */
	public function install($install) {
		return true;
	}
	
	public function backUp($install) {
		return true;
	}
	
	public function revert($install) {
		return true;
	}
	
	public function rollback($install) {
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractPwAppUninstall::unInstall()
	 */
	public function unInstall($install) {
		return true;
	}
}

?>