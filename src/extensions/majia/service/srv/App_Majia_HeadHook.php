<?php

/**
 * 马甲头部的引入
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_HeadHook.php 25423 2013-03-15 06:29:21Z xiaoxia.xuxx $
 * @package majia.service.srv
 */
class App_Majia_HeadHook {
	
	/**
	 * 构建头部的引入
	 */
	public function createHtmlForUserHead() {
		echo '<li><a href="#" id="J_head_majia" data-uri="' . WindUrlHelper::createUrl('app/majia/my/run') . '" title="马甲切换"><em style="background:url(' . Wekit::url()->extres . '/majia/images/cutover.png) no-repeat;margin-top:5px;"></em>马甲切换</a></li>';
	}

	/**
	 * 创建底部的js引入
	 */
	public function createHtmlForFooter() {
		echo '<script>Wind.ready("global.js", function(){Wind.js("' . Wekit::url()->extres . '/majia/js/majia.js")})</script>';
	}
}