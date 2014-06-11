<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('SRC:library.Pw');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');

/**
 * 前台入口
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IndexController.php 28793 2013-05-24 03:55:18Z jieyin $
 * @package account
 * 
 */
class IndexController extends PwBaseController {
	private $type;
	private $uid;
	private $hostInfo;
	private $urlReferer;
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		$type = $this->getInput('type');
		if(empty($type)){
			$this->type = 'taobao';
		}else{
			$this->type = trim($type);
		}
		
		if(!$this->_getAccountTypeService()->checkType($this->type)){
			$this->showError('登录类型错误，请重试');
		}
		$this->uid = intval($this->loginUser->uid);
		$this->hostInfo = $this->_getCommonService()->getHost();
		$this->urlReferer = $this->getRequest()->getUrlReferer();
	}
	
	/**
	 * 默认首页
	 * (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$this->forwardAction('profile/extends/run',array('_left' => 'app_account'), true);
	}
	
	
	/**
	 * 个人设置 账号绑定
	 */
	public function bindAction(){
		if(!$this->uid) $this->showError('未登录，请先登录');

		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		if (!$sessionId || !App_Account_LoginSessionBo::getInstance($sessionId)->getSession()) {
			$sessionId = $this->_getLoginSessionService()->createLoginSession();
		}
		$this->_getLoginSessionService()->updateLoginSession($sessionId,array('httpReferer' => $this->urlReferer,'type' => $this->type,'action'=>'bind'));
		
		Pw::setCookie($this->_getLoginSessionService()->getCookieName(),$sessionId,$this->_getLoginSessionService()->getCooieExpire());
		
		$url = $this->_getAccountService()->getAuthorizeURL($sessionId);
		if($url instanceof PwError){
			$this->showError($url->getError());
		}
		$this->setOutput($url,'url');
		$this->setTemplate('login');
	}
	
	/**
	 * 个人设置 账号解绑
	 */
	public function unBindAction(){
		if(!$this->uid) $this->showError('未登录，请先登录');
		
		$result = $this->_getAccountBindService()->unbind($this->uid,$this->type);
		if($result instanceof PwError){
			$this->showError($result->getError());
		}
		$this->forwardAction('profile/extends/run',array('_left' => 'app_account'), true);
	}
	
	
	/**
	 * 登录入口
	 */
	public function loginAction(){
		if($this->uid) $this->showError('已登录，请不要重复登录');
		
		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		if (!$sessionId || !App_Account_LoginSessionBo::getInstance($sessionId)->getSession()) {
			$sessionId = $this->_getLoginSessionService()->createLoginSession();
		}
		$this->_getLoginSessionService()->updateLoginSession($sessionId,array('httpReferer' => $this->urlReferer,'type' => $this->type,'action'=>'login'));
				
		Pw::setCookie($this->_getLoginSessionService()->getCookieName(),$sessionId,$this->_getLoginSessionService()->getCooieExpire());
		
		$url = $this->_getAccountService()->getAuthorizeURL($sessionId);
		if($url instanceof PwError){
			$this->showError($url->getError());
		}	
		$this->setOutput($url,'url');
		$this->setTemplate('login');
	}
	

	/**
	 * 第三方回调地址
	 */
	public function callBackAction(){
		$result = $this->_getAccountService()->getResponseInfo();
		if($result instanceof PwError){
			$this->showError($result->getError());
		}

		$this->forwardAction('app/index/route',array('app' => 'account'), true);
		
	}
	
	/**
	 * 结果分析路由
	 */
	public function routeAction(){
		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		$sessionInfo = App_Account_LoginSessionBo::getInstance($sessionId)->getSession();
		$sessionData = $sessionInfo['sessiondata'];
		
		$refer = $sessionData['httpReferer'] ? $sessionData['httpReferer'] : $this->hostInfo;
		
		if(!$this->_getAccountTypeService()->checkType($sessionData['type'])){
			$this->showError('登录类型错误，请重试');
		}
		
		if (!$sessionId || !$sessionInfo || !$sessionData['data']['user_id'] || !$sessionData['action']) {
			$this->showError('验证失败，请重试');
		}
		
		$url =  $refer ? $refer : $this->hostInfo . Wind::getComponent('request')->getScriptUrl();
		
		$type_name = $this->_getAccountTypeService()->getTypeName($sessionData['type']);
		$msg_info = '使用' . $type_name . '账号认证通过（窗口将自动关闭）';
		
		
		
		if($sessionData['action'] == 'bind'){
			//绑定流程
			$result = $this->_getAccountBindService()->bind($this->uid,$sessionData['data']['user_id'],$sessionData['type']);
			if($result instanceof PwError){
				$this->showError($result->getError());
			}
			
		}elseif($sessionData['data']['isBound'] == 0 && $sessionData['action'] == 'login'){
			$sign = $sessionData['data']['sign'];
			
			//没有绑定社区账号 注册或者绑定
			$config = Wekit::C()->getValues('register');
			if($config['type'] == 0){
				//关闭注册，跳转到绑定设置页面
				$url = WindUrlHelper::createUrl('app/login/run',array('app'=>'account','sign'=>$sign));
			}else{
				$url = WindUrlHelper::createUrl('app/register/run',array('app' => 'account','sign' => $sign));
			}

		}elseif($sessionData['data']['isBound'] == 1 && $sessionData['action'] == 'login'){
			//进入登录 用户校验
			
			$uid = intval($sessionData['data']['bbs_uid']);

			Wind::import('SRV:user.bo.PwUserBo');
			$userBo = PwUserBo::getInstance($uid);
			if(!$userBo->isExists()){
				//用户不存在
				$this->_getAccountBindDs()->deleteByUid($uid);
				$this->showError('绑定用户在站点已删除，请重试');
			}

			$pattern = '/m=u&c=login/i';
			if(preg_match($pattern,$url)){
				$url = $this->_getCommonService()->getHost();
			}
			
			$userService = Wekit::load('user.srv.PwUserService');
			$userService->createIdentity($userBo->uid, $userBo->info['password']);			
		}
		
		$this->setOutput($msg_info,'msg_info');
		$this->setOutput($url,'jumpurl');
		$this->setOutput(Wekit::app()->charset, 'charset');
		$this->setTemplate('login_notice');
	}
	
	
	/**
	 * 第三方logout回调地址
	 */
	public function logoutAction(){
		echo "phpwind";
		exit();		
	}

	
	private function _getAccountInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_Info');
	}
	
	private function _getAccountBindService(){
		return Wekit::load('EXT:account.service.srv.App_Account_BindService');
	}
	
	private function _getAccountService(){
		Wind::import('EXT:account.service.srv.App_Account_Factory');
		return App_Account_Factory::getInstance($this->type);
	}
	
	private function _getAccountInfoService(){
		return Wekit::load('EXT:account.service.srv.App_Account_InfoService');
	}
	
	private function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}

	private function _getLoginSessionService(){
		return Wekit::load('EXT:account.service.srv.App_Account_LoginSessionService');
	}
	
	private function _getCommonService(){
		return Wekit::load('EXT:account.service.srv.App_Account_CommonService');
	}
	
	private function _getAccountBindDs(){
		return Wekit::load('EXT:account.service.App_Account_Bind');
	}
}

