<?php 
defined('WEKIT_VERSION') or exit(403);
Wind::import('APPS:.profile.service.PwProfileExtendsDoBase');
Wind::import('EXT:account.service.srv.bo.App_Account_InfoBo');

/**
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id:$
 * @package account
 */
class App_Account_ProfileDo extends PwProfileExtendsDoBase{
	
	private $uid;
	private $tag = true;
	private $typeFilter = array(); //获取开启的账号通类型
	public  $bindInfo = array();
	public  $userName;
	public  $avatar;
	
	public function __construct(PwUserProfileExtends $bp = null){
		$this->uid = intval($bp->user->uid);
		$this->userName = $bp->user->username;
				
	}
	
	/**
	 * 个人设置--绑定设置--页面
	 * @see PwProfileExtendsDoBase::createHtml()
	 */
	public function createHtml($left, $tab){
		$this->typeFilter = $this->_getCommonService()->getAccountType();
		if($this->typeFilter === false) return false;
		$res = $this->_getAccountBindDs()->fetchByUidAndTypes($this->uid,$this->typeFilter);
		
		$bindType = array();
		
		foreach($res as $v){
			$bindType[] = $v['type'];
			$this->bindInfo[$v['type']] = array('isopen' => 1,'iconClass' => $this->_getAccountTypeService()->getIconClassByType($v['type']));
		}
		$unbindType = array();
		$unbindType = array_diff($this->typeFilter,$bindType);
		foreach($unbindType as $v){
			$this->bindInfo[$v] = array('isopen' => 0,'iconClass' => $this->_getAccountTypeService()->getIconClassByType($v));
		}
		krsort($this->bindInfo);
		
		$this->avatar = Pw::getAvatar($this->uid,'small');
		PwHook::template('displayAppProfileAccount', 'EXT:account.template.profile', true,$this);
	}
	
	
	public function getBindUrl($type){
		return $this->_getCommonService()->getWindowOpenScriptByType($type,'bind');
	}
		
	private function _getCommonService(){
		return Wekit::load('EXT:account.service.srv.App_Account_CommonService');
	}
	
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
	
	private function _getAccountBindDs(){
		return Wekit::load('EXT:account.service.App_Account_Bind');
	}
	
	private function _getAccountInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_Info');
	}
	
}