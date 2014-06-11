<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase');

/**
 * 评分显示
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_ThreadDisplayDoMark extends PwThreadDisplayDoBase {

	public function createHtmlAfterContent($read) {
		if (!$read['app_mark']) return true; 
		list($cnum, $count, $credits, $ids) = $this->_getService()->splitStringToArray($read['app_mark']);
		if (!$count) return true;
		$credits && $credits = $this->_getService()->buildMarkCredit($credits);
		$page = 1;
		$perpage = 10;
		list($start, $limit) = Pw::page2limit($page, $perpage);
		$list = $this->_getDs()->fetchRecord($ids);
		krsort($list);
		PwHook::template('app_mark_displayMarkHtmlAfterContent', 'EXT:mark.template.read_mark', true, $list, $credits, $page, $perpage, $count, array('tid' => $read['tid'], 'pid' => $read['pid']));
	}
	
	public function createHtmlContentBottom($read) {
		if ($read['pid']) return true;
		$read['app_mark'] && list(, $count) = $this->_getService()->splitStringToArray($read['app_mark']);
		PwHook::template('app_mark_displayMarkHtmlContentBottom', 'EXT:mark.template.read_mark', true, $read, (int)$count);
	}
	
	public function createHtmlForThreadButton($read) {
		if (!$read['pid']) return true;
		$read['app_mark'] && list(, $count) = $this->_getService()->splitStringToArray($read['app_mark']);
		PwHook::template('app_mark_displayMarkHtmlForThreadButton', 'EXT:mark.template.read_mark', true, $read, (int)$count);
	}
	
	/**
	 * @return App_Mark_Record
	 */
	private function _getDs() {
		return Wekit::load('EXT:mark.service.App_Mark_Record');
	}
	
	/**
	 * @return App_Mark_Service
	 */
	private function _getService() {
		return Wekit::load('EXT:mark.service.srv.App_Mark_Service');
	}
}
?>