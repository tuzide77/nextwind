<?php
defined('WEKIT_VERSION') or exit(403);

/**
 * 动他一下服务
 *
 * @author chenjm <sky_hold@163.com>
 * @copyright http://www.phpwind.net
 * @license http://www.phpwind.net
 */
class App_Dongta_Service {
	
	/**
	 * 获取动他一下后台菜单
	 *
	 * @param array $config
	 * @return array 
	 */
	public function spaceButton($space) {
		echo '<a rel="nofollow" href="' . WindUrlHelper::createUrl('app/dongta/index/act') . '" class="dongta J_qlogin_trigger J_dongta_act" data-uid="' . $space->spaceUser['uid'] . '"><em></em>打招呼</a>';
		echo '<script>var URL_DONGTA = \'' . WindUrlHelper::createUrl('app/dongta/index/send') . '\';Wind.ready(function(){Wind.js(\'' . Wekit::url()->extres . '/dongta/js/dongta.js\');});</script>';
	}

	public function spaceCss() {
		echo '<style>
.space_user_info .operate .dongta em {
	background: url("' . Wekit::getGlobal('url', 'extres') . '/dongta/images/dongta.png") no-repeat scroll center center transparent;
}
</style>' . "\n";
	}

	public function readCss() {
		echo '<style>
.floor_info .operate .dongta {
	background: url("' . Wekit::getGlobal('url', 'extres') . '/dongta/images/dongta.png") no-repeat scroll -4px 2px transparent;
	margin-right: 9px;
	padding-left: 16px;
}
</style>' . "\n";
	}
}

?>