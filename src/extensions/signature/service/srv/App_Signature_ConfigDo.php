<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 后台菜单添加
 *
 * @author shilong <shilong1987@163.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_Signature_ConfigDo {
	
	/**
	 * 获取签名档后台菜单
	 *
	 * @param array $config
	 * @return array 
	 */
	public function getAdminMenu($config) {
		$config += array(
			'ext_signature' => array('签名档', 'app/manage/*?app=signature', '', '', 'appcenter'),
			);
		return $config;
	}
}

?>