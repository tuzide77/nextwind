<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('SRV:user.srv.login.PwUserLoginDoBase');
/**
 * 用户登录之后的操作
 *
 * @author shilong <shilong1987@163.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_Signaturelogin_WelcomeDo extends PwUserLoginDoBase {
	/*
	 * @see PwUserLoginDoBase
	 */
	public function __construct() {}

	public function welcome(PwUserBo $userBo, $ip) {
		$config = Wekit::C('site');
		$info = $userBo->info;
		$moneyType = $config['app.signature.moneytype'];
		$money = $config['app.signature.money'];
		$in_group = strpos($config['app.signature.groups'], ',' . $info['groupid'] . ',') !== false;
		$neverPay = !$info['app_signature_starttime'] && $money && $in_group && $info['credit' . $moneyType] > $money;
		$everPay = $info['app_signature_starttime'] && $info['app_signature_starttime'] != Pw::getTdtime();
		if ($neverPay || $everPay) {
			Wind::import('EXT:signature.service.srv.App_Signature_Pay');
			$srv = new App_Signature_Pay($info, $config);
			$srv->doPay($info['app_signature_starttime'], $info['credit' . $moneyType]);
		}
	}
	
}
?>