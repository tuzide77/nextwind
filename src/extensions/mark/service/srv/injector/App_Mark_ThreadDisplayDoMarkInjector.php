<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 评分显示
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_ThreadDisplayDoMarkInjector extends PwBaseHookInjector {
	
	public function run() {
		Wind::import('EXT:mark.service.srv.do.App_Mark_ThreadDisplayDoMark');
		return new App_Mark_ThreadDisplayDoMark();
	}
	
	public function runThreadListHtmlContent() {
		Wind::import('EXT:mark.service.srv.do.App_Mark_ThreadListDo');
		return new App_Mark_ThreadListDo();
	}
}