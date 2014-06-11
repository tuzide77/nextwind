<?php
/**
 * 签名消耗积分
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Signature_Pay.php 23825 2013-01-16 06:26:54Z long.shi $
 * @package signature
 */
class App_Signature_Pay {
	private $money;
	private $moneyType;
	private $groups;
	private $info;

	public function __construct($info, $config = array()) {
		$config || $config = Wekit::C('site');
		$this->moneyType = $config['app.signature.moneytype'];
		$this->money = $config['app.signature.money'];
		$this->groups = $config['app.signature.groups'];
		$this->info = $info;
	}

	/**
	 * 为了成长付出的代价
	 */
	public function doPay($starttime, $currency) {
		$tdTime = Pw::getTdtime();
		$set_a = array();
		Wind::import('EXT:signature.service.dm.App_Signature_dm');
		$dm = new App_Signature_dm($this->info['uid']);
		if (!$starttime) {
			$set_a = array($tdTime, $this->money);
		} elseif (!$this->money || strpos($this->groups, ',' . $this->info['groupid'] . ',') === false) {
			$dm->setStartTime(0);
			$this->_userDs()->editUser($dm, PwUser::FETCH_DATA);
		} else {
			$days = floor(($tdTime - $starttime) / 86400);
			$cost = $days * $this->money;
			$cost < 0 && $cost = 0;
			if ($currency >= $cost) {
				$set_a = array($tdTime, $cost);
			} else {
				$cost = $currency - $currency % $this->money;
				$cost < 0 && $cost = 0;
				$set_a = array(0, $cost);
			}
		}
		if ($set_a) {
			/* @var $creditBo PwCreditBo */
			$creditBo = PwCreditBo::getInstance();
			$creditBo->set($this->info['uid'], $this->moneyType, -$set_a[1]);
			$dm->setStartTime($set_a[0]);
			$this->_userDs()->editUser($dm, PwUser::FETCH_DATA);
		}
		return true;
	}
	
	/**
	 * @return PwUser
	 */
	private function _userDs() {
		return Wekit::load('user.PwUser');
	}
}

?>