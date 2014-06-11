<?php
Wind::import('SRV:forum.dm.PwReplyDm');

class App_Mark_ReplyDm extends PwReplyDm {
	
	public function __construct($pid=0) {
		parent::__construct($pid);
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