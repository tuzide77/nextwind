<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 后台菜单添加
 *
 * @author 肖肖 <x_824@sina.com>
 * @copyright http://www.cnblogs.com/xiaoyaoxia
 * @license http://www.cnblogs.com/xiaoyaoxia
 */
class App_Majia_ConfigDo {
	
	/**
	 * 获取马甲绑定后台菜单
	 *
	 * @param array $config
	 * @return array 
	 */
	public function getAdminMenu($config) {
		$config += array(
			'app_majia' => array('马甲绑定', 'app/manage/*?app=majia', '', '', 'appcenter'),
		);
		return $config;
	}
	
	/**
	 * 前台的用户资料扩展s_profile_menus
	 *
	 * @param array $config
	 * @return array
	 */
	public function registProfile($config) {
		$config['profile_tabs'] += array(
			'app_majia' => array('title' => '马甲绑定', /*'url' => 'profile/index/run'*/),
		);
		return $config;
	}
}

?>