<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('ADMIN:library.AdminBaseController');
/**
 * 后台访问入口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: ManageController.php 25854 2013-03-25 13:15:25Z xiao.fengx $
 * @package account
 */
 
class ManageController extends AdminBaseController {
	
	private $type;
                                                                                    	
	/**
	 * (non-PHPdoc)
	 * @see AdminBaseController::beforeAction()
	 */
	public function beforeAction($handlerAdapter) {
		$type = $this->getInput('type');
		
		//升级
		if($type == 'upgrade'){
			$typeClasses = array();
			foreach ($this->_getAccountTypeService()->getType() as  $v){
				$typeClasses[$v] = '';
			}
			$this->setOutput($typeClasses,'typeClasses');
			return true;
		}
			
		//账号通
		if(empty($type) || !$this->_getAccountTypeService()->checkType($type)){
			$this->type = 'taobao';
		}else{
			$this->type = trim($type);
		}
		
		$typeClasses = array();
		foreach ($this->_getAccountTypeService()->getType() as  $v){
			$typeClasses[$v] = $this->type == $v ? ' class="current"' : '';
		}
		$this->setOutput($typeClasses,'typeClasses');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$info = $this->_getAccountInfoDs()->get($this->type);
		$this->setOutput($info,'info');	
		$this->setTemplate(strtolower($this->type) . '_run');
	}
	
	public function doSetAction(){
		$param = array(
				'type' => $this->type,
				'status' => $this->getInput('status','post'),
				'appKey' => $this->getInput('appkey','post'),
				'appSecret' => $this->getInput('appsecret','post'),
				'displayOrder' => $this->getInput('display_order','post'),
		);
		$this->_getAccountService()->adminSet($param);
		$this->showMessage('设置成功');
	}
	
	
	public function upgradeAction() {
		$this->setOutput(' class="current"','upgrade');
		$this->setTemplate('upgrade');
	}
	
	/*
	 * 升级数据
	 */
	public function doUpgradeAction(){
		$result = $this->_getAccountService()->upgrade();
		if($result instanceof PwError){
			$this->showError($result->getError());
		}
		
		if($result === true){
			$this->showMessage('升级成功','app/manage/upgrade?app=account');
		}elseif(intval($result) > 0){
			
			$refer = 'app/manage/doUpgrade?type='.$this->type.'&app=account&page='.intval($result);
			$this->showMessage('正在升级...',$refer,true);
		}else{
			$this->showMessage('升级失败','app/manage/upgrade?app=account');
		}
		
	}
	
	
	private function _getAccountService(){
		Wind::import('EXT:account.service.srv.App_Account_Factory');
		return App_Account_Factory::getInstance($this->type);
	}
	
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
	
	private function _getAccountInfoService(){
		return Wekit::load('EXT:account.service.srv.App_Account_InfoService');
	}
	
	private function _getAccountInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_Info');
	}
}

