<?php
/**
 * 评分配置类扩展
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class App_Mark_HookDo {
	
	/**
	 * 获取用户组权限配置
	 *
	 * @param unknown_type $config
	 * @return multitype:multitype:string  multitype:string boolean  
	 */
	public function getPermissionConfig($config) {
		$config += array(
			'app_mark_open' => array('radio', 'basic', '帖子评分权限', '', array('0' => '无权限', '1' => '允许评分', '2' => '允许重复评分')),
			'app_mark_credits' => array('app', 'basic', '帖子评分设置', '', '', 'EXT:mark.template.read_mark'),
			'app_mark_manage'		=> array('radio', 'system', '评分管理','开启后，用户可以管理评分记录'),
		);
		return $config;
	}
	
	/**
	 * 获取用户组根权限配置
	 *
	 * @param array $config
	 * @return multitype:multitype:string  
	 */
	public function getPermissionCategoryConfig($config) {
		$markconfig = array(
			'other' => array(
				'sub' => array(
					'mark' => array(
						'name' => '评分',
						'items' => array(
							'app_mark_open','app_mark_credits'
						),
					),
				),
			),
			'manage_bbs' => array(
				'sub' => array(
					'mark' => array(
						'name' => '评分管理权限',
						'items' => array(
							'app_mark_manage'
						)
					),
				)
			),
		);
		return WindUtility::mergeArray($config,$markconfig);
	}
	
	public function getAdminMenu($config) {
		$config += array(
			'app_mark' => array('评分应用', 'app/manage/*?app=mark', '', '', 'appcenter'),
			);
		return $config;
	}

    public function getMarkCreditConfig($config){
    	if(!is_array($config) || empty($config)) return $config;
    	$config['app_mark_delete'] = array('评分', 'global', '你对帖子：{$subject}的评分被{$username}删除;积分变化【{$cname}:{$affect}】', false);
    	$config['app_mark'] = array('评分', 'global', '你的帖子：{$subject}被{$username}评分;积分变化【{$cname}:{$affect}】', false);
		$config['app_tomark'] = array('评分', 'global', '你对{$username}的帖子：{$subject}进行评分;积分变化【{$cname}:{$affect}】', false);
    	return $config;
    }
}

?>