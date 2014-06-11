<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase');
/**
 * 帖子内容展示
 *
 * @author shilong <shilong1987@163.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_SignaturePwThreadDisplayDo extends PwThreadDisplayDoBase {

	public function __construct() {}
	
	/*
	 * @see PwThreadDisplayDoBase
	 */
	public function bulidUsers($users) {
		$config = Wekit::C('site');
		foreach ($users as $key => $value) {
			if ($value['bbs_sign']) {
				$moneyType = $config['app.signature.moneytype'];
				$money = $config['app.signature.money'];
				$in_group = strpos($config['app.signature.groups'], ',' . $value['groupid'] . ',') !== false;
				$payCredit = (!$value['app_signature_starttime'] || $value['credit' . $moneyType] < ((Pw::getTdtime() - $value['app_signature_starttime']) / 86400) * $money);
				if ($money && $in_group && $payCredit) {
					$value['bbs_sign'] = '';
				}
			}
			$users[$key] = $value;
		}
		return $users;
	}
}
?>