<?php 
defined('WEKIT_VERSION') or exit(403);
/**
 * 服务抽象类
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Account_Abstract.php 22630 2012-12-26 04:54:55Z xiao.fengx $
 * @package account
 */

class App_Account_BaseService{
	
	protected $appKey;
	protected $appSecret;
	protected $status;
	protected $displayOrder;
	protected $upgradeConfig = 'EXT:account.conf.App_Account_UpgradeConfig';
	
	public function __construct(){
		$info = $this->_getAccountInfoDs()->get($this->type);
		$this->type = $info['type'];
		$this->appKey = $info['app_key'];
		$this->appSecret = $info['app_secret'];
		$this->displayOrder = $info['display_order'];
		$this->status = $info['status'];
	}
	
	/**
	 * 检查账号通是否开启
	 */
	public  function checkStatus(){
		if(!$this->status) {
			$msg = '已关闭' . $this->_getAccountTypeService()->getTypeName($this->type) . '登录';
			return $msg;
		}else{
			return true;
		}
	}
	
	/**
	 * 后台设置
	 */
	public function adminSet($param){
		if($this->type){
			$dm = new App_Account_InfoDm($param['type']);
			$dm->setAppKey($param['appKey'])
			->setAppSecret($param['appSecret'])
			->setDisplayOrder($param['displayOrder'])
			->setStatus($param['status']);
			$this->_getAccountInfoDs()->update($dm);
		}else{
			$dm = new App_Account_InfoDm();
			$dm->setType($param['type'])
			->setAppKey($param['appKey'])
			->setAppSecret($param['appSecret'])
			->setDisplayOrder($param['displayOrder'])
			->setStatus($param['status']);
			$this->_getAccountInfoDs()->add($dm);
		}		
	}
	
	/**
	 * 获取应用回调地址
	 */
	public function getCallBackUrl($type){
		if(!$this->_getAccountTypeService()->checkType($type)) return false;
		$callBackUrl =  '?app=account&m=app&c=index&a=callback';
		$callBackUrl .= '&type='.$type;
		
		return $this->_getCommonService()->getHost() .  $callBackUrl;
	}
	
	/**
	 * 获取加密字符串,防止伪造请求
	 */
	public function getSignSting(){
		return WindUtility::generateRandStr(10);
	}
	/**
	 * http get请求
	 */
	public function request($url, $timeout = 5) {
		$url = trim($url);
		if(!$url) return false;
		if(!function_exists('curl_init')){
			return file_get_contents($url);
		}
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}
	
	/**
	 * http post请求
	 */
	public function requestByPost($url,$postfields,$timeout = 30){
		if(!function_exists('curl_init')){
			return false;
		}
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, 'phpwind');
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ci, CURLOPT_POST, TRUE);
		curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ci, CURLOPT_URL, $url );
		$response = curl_exec($ci);
		curl_close ( $ci );
		return $response;
	}
	
	/**
	 * 升级数据
	 */
	public function upgrade(){
		Wind::import("WIND:db.WindConnection");
		$configFile = include Wind::getRealPath($this->upgradeConfig);
		$dsn = 'mysql:dbname='.$configFile['old_db_name'].';host=' . $configFile['old_db_host'] . ';port=' . $configFile['old_db_port'];
		

		//分页
		$limit = $configFile['limit'];//每次升级200个
		$page = $_GET['page'];
		$page = $page < 1 ? 1 : intval($page);
		list($offset, $limit) = Pw::page2limit($page, $limit);
		$sql = "SELECT * FROM pw_weibo_bind WHERE weibotype = '$this->type' LIMIT ". max(0, intval($offset)) . " , " . max(1, intval($limit));
		
		try {
			$pdo = new WindConnection($dsn, $configFile['old_db_user'], $configFile['old_db_pass']);
			$result = $pdo->query($sql)->fetchAll();
			
				
		} catch (PDOException $e) {
			$error = $e->getMessage();
			return new PwError($error);
		}
		
		//升级完成
		if(empty($result)){
			return true;
		}
		
		$bindInfo = array();
		foreach($result as $key => $value){
			//解决反序列化字符串编码不一致问题
			$unserialized = $value['info'];
			$unserialized = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $unserialized );
			$info = unserialize($unserialized);
		
			if($value['uid'] && $info['id']){
				$bindInfo[] = array(
						'uid' => intval($value['uid']),
						'type' => $this->type,
						'app_uid' => intval($info['id']),
				);
			}
				
		}
		$this->_getAccountBindDs()->batchAdd($bindInfo);
		return $page + 1;
	}
	
	
	/**
	 * 更新session
	 */
	protected function updateSession($user_id,$nick,$type){
		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		
		$bindInfo = $this->_getAccountBindDs()->getByAppUidAndType($user_id,$type);
		$isBound = $bindInfo ? 1 : 0;
		$bbsUid = intval($bindInfo['uid']) ? intval($bindInfo['uid']) : 0;
		
		if($type != 'alipay'){
			$nick = Pw::convert(trim($nick), Wind::getApp()->getResponse()->getCharset(),'UTF-8');
		}
		$data = array(
				'data' => array(
						'nick'   => $nick,
						'user_id' => $user_id,
						'isBound' => $isBound,
						'bbs_uid' => $bbsUid,
						'sign'    => $this->getSignSting(),
				)
		);
		$this->_getLoginSessionService()->updateLoginSession($sessionId,$data);
	}
	
	protected function _getAccountInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_Info');
	}	
	
	protected function _getLoginSessionService(){
		return Wekit::load('EXT:account.service.srv.App_Account_LoginSessionService');
	}
	
	protected function _getAccountBindDs(){
		return Wekit::load('EXT:account.service.App_Account_Bind');
	}
	
	protected function _getAccountTypeService(){
		return Wekit::load('EXT:account.service.srv.App_Account_TypeService');
	}
	
	protected function _getSiteInfoDs(){
		return Wekit::load('SRV:site.PwBbsinfo');
	}
	
	protected function _getCommonService(){
		return Wekit::load('EXT:account.service.srv.App_Account_CommonService');
	}
}