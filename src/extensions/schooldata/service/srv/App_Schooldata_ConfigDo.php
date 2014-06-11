<?php
/**
 * 配置类扩展
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Schooldata_ConfigDo {
	
	public function getAdminMenu($config) {
		$config += array(
			'app_schooldata' => array('学校数据', 'app/manage/*?app=schooldata', '', '', 'appcenter'),
			);
		return $config;
	}
}

?>