<?php
Wind::import('SRV:user.dm.PwUserInfoDm');
/**
 * 用户数据dm
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Signature_dm.php 23825 2013-01-16 06:26:54Z long.shi $
 * @package signature
 */
class App_Signature_dm extends PwUserInfoDm {
	/**
	 * 设置签名档扣积分时间
	 *
	 * @param int $time
	 */
	public function setStartTime($time) {
		$this->_data['app_signature_starttime'] = $time;
		return $this;
	}
}

?>