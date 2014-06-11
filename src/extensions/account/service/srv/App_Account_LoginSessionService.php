<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_LoginSessionDm');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');

/**
 * App_Account_LoginSessionService 
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright 2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_LoginSessionService.php 23613 2013-01-14 02:26:25Z xiao.fengx $
 * @package account
 */
class App_Account_LoginSessionService {
	
	private $cookieName = 'pw_app_account_login';
	private $cookieExpire = 1800;
		
	public function getCookieName(){
		return $this->cookieName;
	}
	
	public function getCooieExpire(){
		return $this->cookieExpire;
	}
	
	public function createLoginSession($sessionData = ''){
		$this->_collectLoginSessionGarbage();
		$sessionId = $this->_generateSessionId();
		$dm = new App_Account_LoginSessionDm();
		$dm->setSessionId($sessionId)
		    ->setExpire($this->getCooieExpire() + Pw::getTime())
			->setSessionData($sessionData);
		$this->_getLoginSessionDs()->add($dm);
		return $sessionId;
	}
	
	public function getLoginSession($sessionId){
		if($sessionId == '') return null;
// 		$time = Pw::getTime();
// 		$this->_collectLoginSessionGarbage();
		$sessionInfo = $this->_getLoginSessionDs()->get($sessionId);
		if(!$sessionInfo) return null;

// 		$dm = new App_Account_LoginSessionDm($sessionId);
// 		$dm->setExpire($this->getCooieExpire() + $time);
// 		$this->_getLoginSessionDs()->update($dm);
		$sessionInfo['sessiondata'] = $sessionInfo['sessiondata'] ?  unserialize($sessionInfo['sessiondata']) : array();
		return $sessionInfo;
	
	}
	
	
	public function updateLoginSession($sessionId, $sessionData){
		if ('' == $sessionId) return false;
		$this->_collectLoginSessionGarbage();
		$sessionInfo = App_Account_LoginSessionBo::getInstance($sessionId)->getSession();
		$sessionInfoOld = $sessionInfo;
		
		
		$sessionInfo['sessiondata'] = is_array($sessionInfo['sessiondata']) ? $sessionInfo['sessiondata'] : array();
		$sessionData = is_array($sessionData) ? $sessionData : array();
		$sessionData = array_merge($sessionInfo['sessiondata'], $sessionData);

		if($sessionInfoOld){
			$dm = new App_Account_LoginSessionDm($sessionId);
			$dm->setSessionData(serialize($sessionData));
			return $this->_getLoginSessionDs()->update($dm);
			
		}else{
			$dm = new App_Account_LoginSessionDm();
			$dm->setSessionId($sessionId)
				->setExpire($this->cookieExpire + Pw::getTime())
				->setSessionData(serialize($sessionData));
			return $this->_getLoginSessionDs()->add($dm);
		}
		
	}
	
	private function _collectLoginSessionGarbage(){
		$gcDivisor = 100;
		$gcProbability = 1;
		$time = Pw::getTime();
		if (rand(1, $gcDivisor) <= $gcProbability) {
			$this->_getLoginSessionDs()->deleteByExpire($time);
		}
	}
	
	
	private function _generateSessionId() {
		$salt = "423^&78fdf*^\tFGFyWEId4\ra&2!cr3s56O1^";
		return md5($salt . uniqid('', true) . mt_rand());
	}
	
	
	private function _getLoginSessionDs(){
		return Wekit::load('EXT:account.service.App_Account_LoginSession');
	}
	

}

?>