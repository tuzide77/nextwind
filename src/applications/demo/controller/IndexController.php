<?php
defined('WIND_VERSION') or exit(403);

class IndexController extends WindController {

	public function beforeAction($handlerAdapter) {
		$url['baseUrl'] = PUBLIC_URL;
		$url['res'] = WindUrlHelper::checkUrl(PUBLIC_RES, PUBLIC_URL);
		$url['css'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/css', PUBLIC_URL);
		$url['images'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/images', PUBLIC_URL);
		$url['js'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/js/dev', PUBLIC_URL);
		Wekit::setGlobal($url, 'url');
		
		$config = Wind::getComponent('configParser')->parse(Wind::getRealPath('APPS:demo.WindManifest.xml', true, true));
		Wekit::setGlobal($config['application'], 'c');
	}

	public function run() {
		$this->setTemplate('index');
	}

	public function fsideAction() {
		$this->setTemplate('fside');
	}

	public function fheaderAction() {
		$this->setTemplate('fheader');
	}

	public function uiAction() {
		$this->setTemplate('ui');
	}

	public function ui_adminAction() {
		$this->setTemplate('ui_admin');
	}

	public function ui_bbsAction() {
		$this->setTemplate('ui_bbs');
	}

	public function jsAction() {
		$this->setTemplate('js');
	}
	
	public function windeditorAction() {
		$this->setTemplate('windeditor');
	}
	
	public function labelAction() {
		$this->setTemplate('label');
	}

	public function confAction() {
		//@var $config PwConfig
		$temp = $editData = array();
		$key = $this->getInput('key', 'get');
		$namespace = array('global', 'test', 'site', 'reg', 'bbs', 'verify', 'attachment', 'register', 'login', 'credit');
		$config = Wekit::load('SRV:config.PwConfig');
		$arrData = $confData = array();
		foreach ($namespace as $value) {
			$arrData = array_merge($arrData, $config->getConfig($value));
		}
		foreach ($arrData as $value) {
			$temp = array(
				'key' => strtoupper($value['namespace']) . ':' . $value['name'], 
				'type' => ucfirst($value['vtype']), 
				'value' => $value['value'], 
				'descrip' => $value['description']);
			$confData[] = $temp;
			if ($key == $temp['key']) {
				$editData = $temp;
			}
		}
		$this->setOutput($confData, 'confData');
		$this->setOutput($editData, 'editData');
		$this->setTemplate('conf');
	}

	public function doconfAction() {
		$conf = $this->getInput('conf', 'POST');
		if (!empty($conf['key'])) {
			list($namespace, $name) = explode(":", $conf['key']);
			settype($conf['value'], $conf['vtype']);
			/* @var $config PwConfig */
			$config = Wekit::load('SRV:config.PwConfig');
			$config->setConfig(strtolower($namespace), $name, $conf['value'], $conf['descrip']);
		}
		$this->forwardAction('conf');
	}

	public function verifycodeAction() {
		$_getpath = WindUrlHelper::createUrl('getverifycode');
		$this->setOutput($_getpath, 'code');
		// audio 
		//$_audioPath = "res/images/verifycode/audio";
		//$_getpath = urlencode(WindUrlHelper::createUrl('getaudiocode'));
		//echo '<object width="25" height="20" align="top" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="high" name="quality"><param name="wmode" value="transparent"><param value="'.$_audioPath.'/audio.swf?file='.$_getpath.'&songVolume=100&width=150&autoStart=true&repeatPlay=false&showDownload=false" name="movie"><embed width="25" height="20" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="'.$_audioPath.'/audio.swf?file='.$_getpath.'&songVolume=100&width=150&autoStart=true&repeatPlay=false&showDownload=false"></object>';
	
		// flash 
		//$_getpath = WindUrlHelper::createUrl('getverifycode');
		//echo '<object width="200" height="100" align="top" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="high" name="quality"><param name="wmode" value="transparent"><param value="'.$_getpath.'" name="movie"><embed width="200" height="100" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="'.$_getpath.'"></object>';
		$this->setTemplate('verifycode');
	}
	
	public function doverifycodeAction() {
		$code = $this->getInput('verifycode', 'post');
		Wind::import("LIB:utility.PwVerifyCode");
		$veryfy = new PwVerifyCode();
		if ($veryfy->checkVerifyCode($code)===true) {
			echo '验证成功';
		} else {
			echo '验证失败';
		}
	}
	
	public function getaudiocodeAction() {
		Wind::import("LIB:utility.PwVerifyCode");
		$veryfy = new PwVerifyCode();
		$veryfy->showAudioCode();
	}
	
	public function getverifycodeAction() {
		Wind::import("LIB:utility.PwVerifyCode");
		$veryfy = new PwVerifyCode();
		$veryfy->showVerifyCode();
	}
	
	public function getwatermarkAction() {
		Wind::import("LIB:utility.PwAttachment");
		$attachment = new PwAttachment();
		$demo = Wind::getRealDir('REP:pw.demo.',false).'demo.jpg';
		$cache = Wind::getRealDir('PUBLIC:attachment.',false).'demo.jpg';
		$attachment->waterMark($demo,$cache,true);
		echo "<img src='".PUBLIC_URL."/attachment/demo.jpg'>";
	}
	
	public function thumbAction() {
		Wind::import("LIB:utility.PwAttachment");
		$attachment = new PwAttachment();
		$demo = Wind::getRealDir('REP:pw.demo.',false).'demo.jpg';
		$cache = Wind::getRealDir('PUBLIC:attachment.',false).'demo.jpg';
		$attachment->thumb($demo,$cache,rand(200,500),rand(200,500),true);
		echo "<img src='".PUBLIC_URL."/attachment/demo.jpg'>";
	}
	
	public function rotateAction() {
		Wind::import("LIB:utility.PwAttachment");
		$attachment = new PwAttachment();
		$demo = Wind::getRealDir('REP:pw.demo.',false).'demo.jpg';
		$cache = Wind::getRealDir('PUBLIC:attachment.',false).'demo.jpg';
		$attachment->rotate($demo,$cache,rand(10,350),true);
		echo "<img src='".PUBLIC_URL."/attachment/demo.jpg'>";
	}
	
	public function uploadAction() {
		$this->setTemplate('upload');
		
	}
	
	public function douploadAction() {
		Wind::import("LIB:utility.PwAttachment");
		$attachment = new PwAttachment();
		$msg = $attachment->upload();
		var_dump($msg);
	}
	
	public function ftpAction() {
		Wind::import("LIB:utility.PwAttachment");
		$attachment = new PwAttachment();
		$demo = Wind::getRealDir('REP:pw.demo.',false).'demo.jpg';
		$cache = Wind::getRealDir('PUBLIC:attachment.',false).'demo.jpg';
		$msg = $attachment->ftpUpload($demo,'/');
		var_dump($msg);
	}
	
	public function emailAction() {
		Wind::import("Lib:utility.PwMail");
		$mail = new PwMail();
		$mail->sendMail('568049598@qq.com', '测试邮件', '测试邮件内容');
		
	}
	
	public function getIpFromAction() {
		Wind::import("Lib:utility.PwIptable");
		$iptable = new PwIptable();
		$ipFrom = $iptable->getIpFrom('121.0.29.75');
		var_dump ($ipFrom);
	}

}