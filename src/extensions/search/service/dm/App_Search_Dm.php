<?php
Wind::import('SRV:user.dm.PwUserInfoDm');
/**
 * 搜索记录DM
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Search_Dm extends PwUserInfoDm {

	public function setLastSearchTime($last_search_time) {
		$this->_data['last_search_time'] = intval($last_search_time);
		return $this;
	}
}