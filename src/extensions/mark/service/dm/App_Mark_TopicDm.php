<?php
Wind::import('SRV:forum.dm.PwTopicDm');

/**
 * 评分记录DM
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */

class App_Mark_TopicDm extends PwTopicDm {
	
	public function __construct($tid=0) {
		parent::__construct($tid);
	}
	
	/** 
	 * 设置app_mark_credit
	 *
	 * @param string $app_mark_credit
	 * @return App_Mark_RecordDm
	 */
	public function addMarkCredit($app_mark_credit) {
		$this->_increaseData['app_mark_num'] = intval($app_mark_credit);
		return $this; 
	}
	
	/** 
	 * 设置app_mark
	 *
	 * @param string $app_mark
	 * @return App_Mark_RecordDm
	 */
	public function setMark($app_mark) {
		$this->_data['app_mark'] = trim($app_mark);
		return $this; 
	}
}