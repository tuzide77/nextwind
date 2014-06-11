<?php
Wind::import('APPCENTER:service.srv.iPwInstall');
/**
 * 马甲绑定安装注入
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_Install.php 24585 2013-02-01 04:02:37Z jieyin $
 * @package majia.service.srv
 */
class App_Majia_Install implements iPwInstall {
	/* (non-PHPdoc)
	 * @see iPwInstall::install()
	 */
	public function install($install) {
		$defaultConfig = array('isopen' => array('value' => 0), 
			'band.max.num' => array('value' => 5), 
			'band.allow.groups' => array('value' => array()));
		/* @var $service PwConfig */
		$service = Wekit::load('config.PwConfig');
		$service->setConfigs('app_majia', $defaultConfig);
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see iPwInstall::backUp()
	 */
	public function backUp($install) {
		//  Auto-generated method stub
	}
	
	/* (non-PHPdoc)
	 * @see iPwInstall::revert()
	 */
	public function revert($install) {
		//  Auto-generated method stub
	}
	
	/* (non-PHPdoc)
	 * @see iPwInstall::unInstall()
	 */
	public function unInstall($install) {
		/* @var $ds PwConfig */
		$ds = Wekit::load('config.PwConfig');
		$ds->deleteConfig('app_majia');
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see iPwInstall::rollback()
	 */
	public function rollback($install) {
		//  Auto-generated method stub
	}
}