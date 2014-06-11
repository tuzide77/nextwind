<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('ADMIN:library.AdminBaseController');
Wind::import('LIB:engine.error.PwError');
Wind::import('SRV:credit.bo.PwCreditBo');

/**
 * 回复奖励 后台设置 
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class ManageController extends AdminBaseController {
		
	public function run(){
		$this->setCurrentTab('run');
		$groups = $this->_getGroupDs()->getAllGroups();
		$gids = array();
		foreach($groups as $v){
			$gids[] = intval($v['gid']);
		}
		$configByGids = $this->_getReplyRewardConfigService()->getReplyRewardConfigByGids($gids);
		$configInfo = array();
		foreach($configByGids as $key => $value){
			$configInfo[$key] = $value;
		}
		
		$this->setOutput($configInfo, 'configInfo');
		$this->setOutput($groups, 'groups');
	}
	
	/**
	 * 回帖奖励功能 提交
	 */
	public function doSetAction(){
		$config = $this->getInput('conf', 'post');
		if(!is_array($config)) 
			$this->showError('非法操作');
		$config = array_map('intval', $config);

		$this->_getReplyRewardConfigService()->setReplyRewardAdminConfig($config);

		$this->showMessage('设置成功');
	}

	
	public function creditAction(){
		$this->setCurrentTab('credit');
		$creditType = PwCreditBo::getInstance()->cType;
		
		$config = $this->_getReplyRewardConfigService()->getReplyRewardCreditType();
		
		$this->setOutput($creditType, 'creditType');
		$this->setOutput($config,'config');
	}
	
	public function creditDoAction(){
		$type = $this->getInput('type');
		$creditType = array();
		foreach($type as $key => $value){
			if(intval($value) != 1) continue;
			$creditType[] = $key;
		}
		$this->_getReplyRewardConfigService()->setReplyRewardCreditType($creditType);
		$this->showMessage('设置成功');
	}
	
	
	/**
	 * 设置当前选项卡被选中
	 *
	 * @param string $action
	 *        	操作名
	 * @return void
	 */
	private function setCurrentTab($action) {
		$headerTab = array(
				'run' => '',
				'credit' => '',
				);
		$headerTab[$action] = 'current';
		$this->setOutput($headerTab, 'currentTabs');
	}
	
	private function _getGroupDs(){
		return Wekit::load('usergroup.PwUserGroups');
	}

	private function _getReplyRewardConfigService(){
		return Wekit::load('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardConfigService');
	}

}