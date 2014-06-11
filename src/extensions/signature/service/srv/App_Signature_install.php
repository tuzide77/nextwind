<?php
Wind::import('APPCENTER:service.srv.iPwInstall');
/**
 * 签名档应用安装卸载接口
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Signature_install.php 24585 2013-02-01 04:02:37Z jieyin $
 * @package signature
 */
class App_Signature_install implements iPwInstall {
	/*
	 * (non-PHPdoc) @see iPwInstall::install()
	 */
	public function install($install) {
		// TODO Auto-generated method stub
	}
	
	/*
	 * (non-PHPdoc) @see iPwInstall::backUp()
	 */
	public function backUp($install) {
		// TODO Auto-generated method stub
	}
	
	/*
	 * (non-PHPdoc) @see iPwInstall::revert()
	 */
	public function revert($install) {
		// TODO Auto-generated method stub
	}
	
	/*
	 * (non-PHPdoc) @see iPwInstall::unInstall()
	 */
	public function unInstall($install) {
		/* @var $db WindConnection */
		$db = Wind::getComponent('db');
		$prefix = $db->getTablePrefix();
		try {
			$db->execute(sprintf('ALTER TABLE %s DROP app_signature_starttime', $prefix . 'user_data'));
		} catch (WindDbException $e) {
		}
		/* @var $ds PwConfig */
		$ds = Wekit::C();
		$ds->deleteConfigByName('site', 'app.signature.isopen');
		$ds->deleteConfigByName('site', 'app.signature.money');
		$ds->deleteConfigByName('site', 'app.signature.moneytype');
		$ds->deleteConfigByName('site', 'app.signature.groups');
		return true;
	}
	
	/*
	 * (non-PHPdoc) @see iPwInstall::rollback()
	 */
	public function rollback($install) {
		// TODO Auto-generated method stub
	}
}

?>