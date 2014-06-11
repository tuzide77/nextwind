<?php
Wind::import('APPS:profile.service.PwProfileExtendsDoBase');

/**
 * 马甲列表展示
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: App_Majia_ProfileDo.php 25423 2013-03-15 06:29:21Z xiaoxia.xuxx $
 * @package majia.service.srv.do
 */
class App_Majia_ProfileDo extends PwProfileExtendsDoBase {
	
	/* (non-PHPdoc)
	 * @see PwProfileExtendsDoBase::createHtml()
	 */
	public function createHtml($left, $tab) {
		$config = Wekit::C('app_majia');
		$list = array();
		$method = !in_array($this->bp->user->gid, $config['band.allow.groups']) ? 'displayMajiaForbidden' : 'displayList';
		if (!$config['isopen']) {
			$method = 'displayMajiaClose';
		} else {
			Wind::import('EXT:majia.service.srv.App_Majia_MajiaBandingBp');
			$bp = new App_Majia_MajiaBandingBp($this->bp->user);
			$list = $bp->doGetBanded();
		}
		PwHook::template($method, 'EXT:majia.template.my_run', true, $this->bp->user, $list, intval($config['band.max.num']));
	}
}