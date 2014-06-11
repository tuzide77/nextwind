<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('SRV:forum.srv.threadList.do.PwThreadListDoBase');

/**
 * 评分 - 帖子列表
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_ThreadListDo extends PwThreadListDoBase{

    /**
     *
     *钩子执行方法
     */
    
    public function createHtmlAfterSubject($thread){
    	if (!$thread['app_mark']) return false;
    	list($cNum, ) = $this->_getService()->splitStringToArray($thread['app_mark']);
        $cNum && PwHook::template('app_mark_displayThreadListHtml', 'EXT:mark.template.read_mark', true, $cNum);
    }
	
	/**
	 * @return App_Mark_Service
	 */
	private function _getService() {
		return Wekit::load('EXT:mark.service.srv.App_Mark_Service');
	}
    
}