<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:account.service.dm.App_Account_InfoDm');
Wind::import('EXT:account.service.srv.bo.App_Account_LoginSessionBo');
Wind::import('EXT:account.service.srv.App_Account_BaseService');
Wind::import('EXT:account.service.dm.App_Account_AlipayUserInfoDm');
Wind::import('EXT:account.service.srv.App_Account_AlipayCoreFunction');
/**
 * App_Account_AlipayService - 数据服务接口 QQ相关
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package account
 */
class App_Account_AlipayService extends App_Account_BaseService{
	protected $type = 'alipay';
	
	/**
	 *支付宝网关地址（新）
	 */
	private $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do';
	
	/**
	 * 支付宝相关设置
	 */
	//目标服务地址
	private $target_service = "user.auth.quick.login";
	//防钓鱼时间戳
	private $anti_phishing_key = "";
	//若要使用请调用类文件submit中的query_timestamp函数
	
	//客户端的IP地址
	private $exter_invoke_ip = "";
	//非局域网的外网IP地址，如：221.0.0.1
	
	//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
	private $transport = 'http';
	
	/**
	 * HTTPS形式消息验证地址
	 */
	private $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
	 * HTTP形式消息验证地址
	 */
	private $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
	

	public function __construct(){
		parent::__construct();
	}
	
	public function getAuthorizeURL($sessionId){
		$result = $this->checkStatus();
		if($result !== true) return new PwError($result);
		if (!$sessionId) return new PwError('登录失败，请重试');

		
		$params = array(
				"service" => "alipay.auth.authorize",
				"partner" => $this->appKey,
				"target_service"	=> $this->target_service,
				"return_url"	=> $this->getCallBackUrl('alipay'),
				"anti_phishing_key"	=> $this->anti_phishing_key,
				"exter_invoke_ip"	=> $this->exter_invoke_ip,
				"_input_charset"	=> strtolower(Wekit::app()->charset),
		);
		
		return $this->alipay_gateway_new . '?' . http_build_query($this->_buildRequestPara($params)); 
		
	}
	
	public function getResponseInfo(){
		$result = $this->checkStatus();
		if($result !== true) return new PwError($result);
		$sessionId = Pw::getCookie($this->_getLoginSessionService()->getCookieName());
		$sessionInfo = App_Account_LoginSessionBo::getInstance($sessionId)->getSession();
		if(!$sessionId || !$sessionInfo) return new PwError('验证会话失败,请重试');
	
				
		//计算得出通知验证结果
		if(!$this->_verifyReturn()) return new PwError('验证会话失败,请重试');
				
		//支付宝用户号
		$user_id = intval($_GET['user_id']);
		//授权令牌
		$token = trim($_GET['token']);
		$real_name = trim($_GET['real_name']);
		if(!$user_id) return new PwError('获取用户信息失败，请重试');
		//更新数据库
		$dm = new App_Account_AlipayUserInfoDm();
		$dm->setUserId($user_id)->setRealName($real_name)->setCreateAt(Pw::getTime());
		$this->_getAlipayUserInfoDs()->replace($dm);
		
		//更新session
		$this->updateSession($user_id, $real_name, 'alipay');
			
		return true;
		
	}
	
	/**
	 * 升级账号通的数据
	 */
	public function upgrade(){
		Wind::import("WIND:db.WindConnection");
		$configFile = include Wind::getRealPath($this->upgradeConfig);
		$dsn = 'mysql:dbname='.$configFile['old_db_name'].';host=' . $configFile['old_db_host'] . ';port=' . $configFile['old_db_port'];
		$siteId = $configFile['siteId'];
		$siteHash = $configFile['siteHash'];
		$appsUrl = 'http://apps.phpwind.com/upgradeweiboapi.php';
	
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
	
		$bbsUids = array();
		$bind = array();
		foreach($result as $key => $value){
			$bbs_uid = intval($value['uid']);
			if($bbs_uid){
				$bbsUids[] = intval($bbs_uid);
			}
		}
	
		$uids = implode($bbsUids,',');
		$param = array('uids'=>$uids,'site_id'=>$siteId,'type'=>$this->type);
		ksort($param);
		$checkSign = md5(http_build_query($param).$siteHash);
		$url = $appsUrl . '?uids='.$uids.'&siteid='.$siteId.'&type='.$this->type.'&sign='. $checkSign;
		$response = $this->request($url);
		unset($param,$checkSign,$url);
		$info = json_decode($response,TRUE);
		if($info[0] === false) return new PwError('接口通信失败');
		$info = $info[1];
		foreach($bbsUids as $key => $value){
			$bind[] = array('uid'=>$value,'type'=>$this->type,'app_uid'=>$info[$value]);
		}
		$this->_getAccountBindDs()->batchAdd($bind);
		unset($bind,$bbsUids);
		return $page + 1;
	}
	
	
	/**
	 * 生成要请求给支付宝的参数数组
	 * @param $para_temp 请求前的参数数组
	 * @return 要请求的参数数组
	 */
	private function _buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = App_Account_AlipayCoreFunction::paraFilter($para_temp);
	
		//对待签名参数数组排序
		$para_sort = App_Account_AlipayCoreFunction::argSort($para_filter);
	
		//生成签名结果
		$mysign = $this->_buildRequestMysign($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = 'MD5';
	
		return $para_sort;
	}
	
	
	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	private function _buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = App_Account_AlipayCoreFunction::createLinkstring($para_sort);
	
		return 	$mysign = App_Account_AlipayCoreFunction::md5Sign($prestr, $this->appSecret);
	}
	
	
	
	/**
	 * 针对return_url验证消息是否是支付宝发出的合法消息
	 * @return 验证结果
	 */
	private function _verifyReturn(){
		if(empty($_GET)) return false;
		//生成签名结果
		$isSign = $this->_getSignVeryfy($_GET, $_GET["sign"]);
		//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
		$responseTxt = 'true';
		if (! empty($_GET["notify_id"])) {$responseTxt = $this->_getResponse($_GET["notify_id"]);}
	
		if (preg_match("/true$/i",$responseTxt) && $isSign) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * 获取远程服务器ATN结果,验证返回URL
	 * @param $notify_id 通知校验ID
	 * @return 服务器ATN结果
	 * 验证结果集：
	 * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
	 * true 返回正确信息
	 * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
	 */
	private function _getResponse($notify_id) {
		$transport = $this->transport;
		$partner = trim($this->appKey);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = App_Account_AlipayCoreFunction::getHttpResponseGET($veryfy_url, $this->_getCacert());
	
		return $responseTxt;
	}

	
	/**
	 * ca证书路径地址，用于curl中ssl校验
	 * 请保证cacert.pem文件在当前文件夹目录中
	 */
	private function _getCacert(){
		$dir = Wind::getRealPath('EXT:account.service.srv',false);
		return $dir . DIRECTORY_SEPARATOR . 'cacert.pem';
	}
	
	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
	 * @return 签名验证结果
	 */
	private function _getSignVeryfy($para_temp, $sign){
		//除去待签名参数数组中的空值和签名参数
		$para_filter = App_Account_AlipayCoreFunction::paraFilter($para_temp);
		$para_filter = $this->_filterGet($para_filter);
		//对待签名参数数组排序
		$para_sort = App_Account_AlipayCoreFunction::argSort($para_filter);
		
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = App_Account_AlipayCoreFunction::createLinkstring($para_sort);
		
		$isSgin = App_Account_AlipayCoreFunction::md5Verify($prestr, $sign, $this->appSecret);
		
		return $isSgin;
	}
	
	/**
	 * 过滤参数
	 */
	private function _filterGet($para){
		unset($para['app'],$para['m'],$para['c'],$para['a'],$para['type']);
		return $para;
	}
	
	public function logout(){
	
	}
	
	private function _getAlipayUserInfoDs(){
		return Wekit::load('EXT:account.service.App_Account_AlipayUserInfo');
	}
	
}
?>