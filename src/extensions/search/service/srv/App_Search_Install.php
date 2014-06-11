<?php

Wind::import('APPCENTER:service.srv.iPwInstall');

/**
 * 本地搜索清理接口
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Search_Install implements iPwInstall {
	
	/* (non-PHPdoc)
	 * @see AbstractPwAppUninstall::unInstall()
	 */
	public function unInstall($install) {
		$this->_loadConfig()->deleteConfigByName('site', 'search.isopen');
		$this->_loadConfig()->deleteConfig('search');
		Wekit::loadDao('EXT:search.service.dao.App_Search_RecordDao')->alterDeleteLastSearch();
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
		Wekit::loadDao('EXT:search.service.dao.App_Search_RecordDao')->alterAddLastSearch();
		return true;
	}

	/**
	 * @return PwConfig
	 */
	private function _loadConfig() {
		return Wekit::load('config.PwConfig');
	}
}

?>