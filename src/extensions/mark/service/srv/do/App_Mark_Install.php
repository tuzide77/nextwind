<?php

Wind::import('APPCENTER:service.srv.iPwInstall');

/**
 * 评分安装服务
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_Install implements iPwInstall {
	
	/* (non-PHPdoc)
	 * @see AbstractPwAppUninstall::unInstall()
	 */
	public function unInstall($install) {
		Wekit::C()->deleteConfig('app_mark');
		$this->_getMark()->alterDropThreadMark();
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
	
	public function install($install) {
		//$this->_getMark()->alterAddThreadMark();
		return true;
	}
	
	/**
	 * @return App_Mark_Record
	 */
	private function _getMark() {
		return Wekit::load('EXT:mark.service.App_Mark_Record');
	}
}
?>