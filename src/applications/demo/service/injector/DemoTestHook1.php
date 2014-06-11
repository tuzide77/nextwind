<?php
Wind::import("WEKIT:engine.hook.PwBaseHookInjector");
/**
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-29
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind
 */
class DemoTestHook1 extends PwBaseHookInjector {

	public function run() {
		Wind::import('APPS:demo.service.do.DemoHookExtend1');
		$service = new DemoHookExtend1();
		return $service;
	}
	
	
}

?>