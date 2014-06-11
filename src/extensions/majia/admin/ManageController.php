<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('ADMIN:library.AdminBaseController');

/**
 * 马甲后台设置
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: ManageController.php 24344 2013-01-29 03:32:25Z xiaoxia.xuxx $
 * @package majia.admin
 */
class ManageController extends AdminBaseController {
	
	/* (non-PHPdoc)
	 * @see AdminBaseController::beforeAction()
	 */
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
	}
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$service = $this->_loadConfigService();
		$config = $service->getValues('app_majia');
		/* @var $groupDs PwUserGroups */
		$groupDs = Wekit::load('SRV:usergroup.PwUserGroups');
		$groups = $groupDs->getClassifiedGroups();
		$this->setOutput($groupDs->getTypeNames(), 'groupTypes');
		$this->setOutput($config, 'config');
		$this->setOutput($groups, 'groups');
	}
	
	/**
	 * 执行配置
	 */
	public function dorunAction() {
		$config = new PwConfigSet('app_majia');
		$config->set('isopen', $this->getInput('isopen', 'post'))
		->set('band.max.num', abs(intval($this->getInput('maxnum', 'post'))))
		->set('band.allow.groups', $this->getInput('groups', 'post'))
		->flush();
		$this->showMessage('ADMIN:success');
	}
	
	/**
	 * 加载Config DS 服务
	 *
	 * @return PwConfig
	 */
	private function _loadConfigService() {
		return Wekit::load('SRV:config.PwConfig');
	}
}

?>