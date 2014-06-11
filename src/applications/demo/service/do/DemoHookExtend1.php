<?php
Wind::import("APPS:demo.service.do.DemoHookInterface");
class DemoHookExtend1 implements DemoHookInterface {

	/* (non-PHPdoc)
	 * @see DemoHookInterface::display()
	 */
	public function display() {
		PwHook::template('testHook2', 'hook_sigment1', true, array('d' => 'bbb'), 'ddd');
	}
}

?>