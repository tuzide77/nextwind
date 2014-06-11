<?php
/**
 * phpwind8.7升级至9.0
 */
$charset = 'utf8';
$htmCharset = 'utf-8';
header('Content-type: text/html;charset=' . $htmCharset);
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
function_exists('set_magic_quotes_runtime') && @set_magic_quotes_runtime(0);
function_exists('date_default_timezone_set') && date_default_timezone_set('UTC');

define('DS', DIRECTORY_SEPARATOR);
$root = dirname(__FILE__);
if (!is_file($root . DS . 'src' . DS . 'wekit.php')) {
	$root = dirname($root);
	if (!is_file($root . DS . 'src' . DS . 'wekit.php')) {
		showError('请下载正确的phpwind9程序包');
	}
}
define('NEXTWIND_DIR', $root);
$setupTmpFolder = NEXTWIND_DIR . DS . 'data' . DS . 'setup';
$lockFile = $setupTmpFolder . DS . 'setup.lock';
$configFile = $setupTmpFolder . DS . 'setup_config.php';
$founderFile = NEXTWIND_DIR . DS . 'conf' . DS . 'founder.php';
$tmpDbFile = $setupTmpFolder . DS . 'tmp_dbsql.php';

$token = $_GET['token'];
if (file_exists($lockFile) && !$token) {
	showError('升级程序已被锁定, 如需重新运行，请先删除data/setup/setup.lock');
}

$errorLogFile = $setupTmpFolder . DS . 'error' . $token . '.log';
set_error_handler('writeError', E_ALL ^ E_NOTICE);

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$lastId = isset($_REQUEST['lastid']) ? intval($_REQUEST['lastid']) : 0;
$seprator = isset($_REQUEST['seprator']) ? intval($_REQUEST['seprator']) : 0;

$gotoActions = array('user', 'memberdata', 'memberinfo', 'punch', 'education', 'space', 'forums', 'threads', 'posts', 'truncateGThreadsTmp', 'vote', 'attachs', 
	'messages', 'weibo', 'fresh', 'announce', 'updateCache');

@set_time_limit(300);
define('MAX_PACKAGE', 500000);//每次传递给mysql的插入长度为1M数据
$limit = 1000;//每步转数据条数
$timestamp = time();
if ($step && !in_array($step, array('writeconfig', 'start', 'checkE'))) {
	//check token
	$encryptToken = '';
	if (file_exists($lockFile)) {
		$encryptToken = trim(file_get_contents($lockFile));
	}
	if (md5($token) != $encryptToken) {
		showError('升级程序访问异常! 重新安装请先删除setup.lock');
	}
	//加载升级配置文件
	require $configFile;
	extract($setupConfig['db_config'], EXTR_OVERWRITE);
	$targetDb = new DB($host, $port, $username, $password, $dbname, $dbpre);
	$srcDb = new DB($src_host, $src_port, $src_username, $src_password, $src_dbname, $src_dbpre);
	
	//limit系数
	$_lt = is_numeric($setupConfig['limit']) ? abs($setupConfig['limit']) : 1;
	$_lt == 0 && $_lt = 1;
	$limit = $_lt * $limit;//每步转数据条数
	$db_plist = isset($setupConfig['db_plist']) ? $setupConfig['db_plist'] : array();
	$db_tlist = isset($setupConfig['db_tlist']) ? $setupConfig['db_tlist'] : array();
}
if (empty($step)) {
	echo <<<EOT
<!doctype html>
<html>
<head>
<title>update phpwind8.7 to nextwind</title>
<meta charset="{$htmCharset}" />
<link rel="stylesheet" href="res/css/install.css" />
</head>
<body>
	<div class="wrap">
		<div class="header">
			<h1 class="logo">logo</h1>
			<div class="icon_update">升级向导</div>
			<div class="version">phpwind 8.7 to 9.0</div>
		</div>
		<div class="section">
			<div class="main cc">
			<pre class="pact" readonly="readonly">phpwind9.0的环境准备，请确认：
1、确定系统环境
PHP版本 	> 5.3.x
PDO_Mysql 安装扩展
Mysql版本（client） 	>5.x.x
附件上传 	>2M
如果确认如上条件都成立，则可以准备开始升级，升级步骤如下：
1、将phpwind9.0即Nextwind安装包解压，并将upload目录下的文件上传至安装目录。
（注意，不能直接覆盖原来8.7的环境。如果是虚拟主机，建议先将原87环境除attachment目录外，移动到backup下，这样即使出现问题后可以通过移动目录恢复87的环境。） 
2、文件转移： 
2.1、头像图片转移：将原87目录下attachment/upload文件夹，拷贝到phpwind9.0的attachment目录下;（注意如果在第一步已经完成了attachment合并，则此步可忽略。）
2.2、表情图片转移：将原87目录下images/post/smile/下的所有目录拷贝到phpwind9.0的res/images/emotion/下;
2.3、勋章图片转移：将原87目录下images/medal/下的所有目录拷贝到phpwind9.0的res/images/medal/下;
注：如果下载的phpwind9.0包是含有www目录的，则将attachment包括在内的以上目录移到www目录下的对应目录中，比如res/images/emotion/则为www/res/images/emotion/
3、将升级包up87to90.php文件上传到网站根目录。（如果下载的nextwind包是含有www目录的，则需要放到www目录下）;
4、确定以下目录的可写权限：
attachment/
conf/database.php
conf/founder.php
conf/windidconfig.php
data/
data/cache/
data/compile/
data/design/
data/log/
data/tmp/
html/
src/extensions/
themes/
themes/extres/
themes/forum/
themes/portal/
themes/site/
themes/space/
5、执行升级程序访问站点的升级程序xxx.com/up87to90.php
6、填写完整需要的数据库信息，及创始人信息
7、递交之后会执行一步基本配置信息的转换
8、转换完基本配置信息之后，会正式进入主数据升级，主数据升级页面是允许多进程升级和一键升级选择的页面，在多进程升级中，您可以一次点开多个没有依赖（每步都有说明各自所需的依赖，如果没有说明则没有）的进程。
注：如果是分进程执行，请确保每一步都执行到位。
特别说明：如果原87站点开启了ftp服务，那么在分进程页面中会存在单独的一条“用户头像转移”的步骤，请仔细看该步骤说明，该步骤不被包含到一键升级和分进程中，无论选择多进程升级或是一键升级都需要运行，否则用户头像将采用默认头像。
9、升级执行完之后将会自动进入nextwind9的首页。
注：如果需要再次升级，请删除data/setup/setup.lock文件</pre>
			</div>
			<div class="bottom tac">
			<a href="up87to90.php?step=checkE" class="btn">下一步</a>
	</div>
</body>
</html>
EOT;
} elseif ('checkE' == $step) {
	if (true !== extension_loaded('pdo_mysql')) {
		showError('PHP扩展PDO_Mysql没有安装！');
	}
	refreshTo(null, 'start');
} elseif ('start' == $step) {
	$files_writeble = array();
	$files_writeble[] = NEXTWIND_DIR . DS . 'data/';
	$files_writeble[] = NEXTWIND_DIR . DS . 'conf' . DS . 'windidconfig.php';
	$files_writeble[] = NEXTWIND_DIR . DS . 'conf' . DS . 'database.php';
	$files_writeble[] = $setupTmpFolder . '/';
	$files_writeble[] = $founderFile;
	$writable = array();
	foreach ($files_writeble as $file) {
		if (false === _checkWriteAble($file)) {
			$writable[] = $file;
		}
	}
	if ($writable) {
		showError("以下目录不可写：<br/>" . implode("<br/>", $writable));
	}
	echo <<<EOT
<!doctype html>
<html>
<head>
<title>update phpwind8.7 to nextwind</title>
<meta charset="{$htmCharset}" />
<link rel="stylesheet" href="res/css/install.css" />
</head>
<body>
	<div class="wrap">
		<div class="header">
			<h1 class="logo">logo</h1>
			<div class="icon_update">升级向导</div>
			<div class="version">phpwind 8.7 to 9.0</div>
		</div>
		<div class="section">
			<div class="step">
				<ul>
					<li class="current" style="width:25%"><em>1</em>设置升级信息</li>
					<li class="" style="width:25%"><em>2</em>初始化升级数据</li>
					<li class="" style="width:25%"><em>3</em>选择升级方式</li>
					<li class="" style="width:24.9%"><em>4</em>完成升级</li>
				</ul>
			</div>
			<form method="post" id="J_up87_form" action="up87to90.php?step=writeconfig">
			<div class="server">
				<table width="100%" style="table-layout:fixed">
					<table width="100%" style="table-layout:fixed">
					<tr><td class="td1" colspan="3">9.0数据库和创始人信息</td></tr>
				</table>
				<table width="100%" style="table-layout:fixed">
					<tr>
						<td width="100" class="tar">数据库服务器：</td>
						<td width="210"><input type="text" id="host" name="host" value="localhost" class="input"></td>
						<td><div id="J_up87_tip_host"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库用户名：</td>
						<td><input type="text" id="username" name="username" value="root" class="input"></td>
						<td><div id="J_up87_tip_username"></div></div></td>
					</tr>
					<tr>
						<td class="tar">数据库密码：</td>
						<td><input type="password" id="password" name="password" value="" class="input"></td>
						<td><div id="J_up87_tip_password"></div></div></td>
					</tr>
					<tr>
						<td class="tar">数据库名：</td>
						<td><input type="text" id="dbname" name="dbname" value="nextwind" class="input"></td>
						<td><div id="J_up87_tip_dbname"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库端口：</td>
						<td><input type="text" id="port" name="port" value="3306" class="input"></td>
						<td><div id="J_up87_tip_port"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库表前缀：</td>
						<td><input type="text" id="dbpre" name="dbpre" value="nw_" class="input"></td>
						<td><div id="J_up87_tip_dbpre"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库创建引擎：</td>
						<td><input type="radio" name="engine" checked value="1"> InnoDB<input type="radio" name="engine" value="0"> MyISAM</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td class="tar">创始人帐号：</td>
						<td><input type="text" id="f_name" name="f_name" value="admin" class="input"></td>
						<td><div id="J_up87_tip_f_name"></div></td>
					</tr>
					<tr>
						<td class="tar">创始人密码：</td>
						<td><input type="password" id="f_pass" name="f_pass" value="" class="input"></td>
						<td><div id="J_up87_tip_f_pass"></div></td>
					</tr>
					<tr>
						<td class="tar">再输入一遍：</td>
						<td><input type="password" id="f_rpass" name="f_rpass" value="" class="input"></td>
						<td><div id="J_up87_tip_f_rpass"></div></td>
					</tr>
				</table>
			</div>
			<div class="server">
				<table width="100%" style="table-layout:fixed">
					<tr>
						<td class="td1" width="100">8.7数据库信息</td>
						<td class="td1" width="210">&nbsp;</td>
						<td class="td1">&nbsp;</td>
					</tr>
					<tr>
						<td class="tar">数据库服务器：</td>
						<td><input type="text" id="src_host" name="src_host" value="localhost" class="input"></td>
						<td><div id="J_up87_tip_src_host"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库用户名：</td>
						<td><input type="text" id="src_username" name="src_username" value="root" class="input"></td>
						<td><div id="J_up87_tip_src_username"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库密码：</td>
						<td><input type="password" id="src_password" name="src_password" value="" class="input"></td>
						<td><div id="J_up87_tip_src_password"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库名：</td>
						<td><input type="text" id="src_dbname" name="src_dbname" value="phpwind" class="input"></td>
						<td><div id="J_up87_tip_src_dbname"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库端口：</td>
						<td><input type="text" id="src_port" name="src_port" value="3306" class="input"></td>
						<td><div id="J_up87_tip_src_port"></div></td>
					</tr>
					<tr>
						<td class="tar">数据库表前缀：</td>
						<td><input type="text" id="src_dbpre" name="src_dbpre" value="pw_" class="input"></td>
						<td><div id="J_up87_tip_src_dbpre"></div></td>
					</tr>
				</table>
			</div>
			<div class="server">
				<table width="100%" style="table-layout:fixed">
					<tr>
						<td class="td1" width="120">升级性能系数设置</td>
						<td class="td1" width="200">&nbsp;</td>
						<td class="td1">&nbsp;</td>
					</tr>
					<tr><td colspan="3" class="">说明：该系数将决定升级的时候一次转移的数据量，如果性能属于上层，则可以设置高值，如果系统性能属于下层，则建议设置<=1的一个值。默认的基数为1000，私信默认基数为200，帖子信息基数为500。</td></tr>
					<tr><td class="tar">升级性能系数为：</td>
						<td>
							<select name="limit">
								<option value="0.2">0.2</option>
								<option value="0.4">0.4</option>
								<option value="0.5">0.5</option>
								<option value="1" selected>1</option>
								<option value="1.2">1.2</option>
								<option value="1.5">1.5</option>
								<option value="1.8">1.8</option>
								<option value="2">2</option>
								<option value="2.5">2.5</option>
								<option value="3">3</option>
								<option value="3.5">3.5</option>
								<option value="4">4</option>
								<option value="4.5">4.5</option>
								<option value="5">5</option>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="bottom tac"><button type="submit" class="btn btn_submit">下一步</button></div>
		</form>
		</div>
	</div>
	<div class="footer">
		&copy; 2003-2103 <a href="http://www.phpwind.com" target="_blank">phpwind.com</a>（阿里巴巴集团旗下品牌）
	</div>
<script src="res/js/dev/jquery.js"></script>
<script src="res/js/dev/util_libs/validate.js"></script>
<script>
$(function(){
	var focus_tips={'host':'数据库服务器地址，一般为localhost','port':'建议使用默认','dbpre':'建议使用默认，同一数据库安装多个phpwind时需修改'};
	var form=$("#J_up87_form");form.validate({errorPlacement:function(error,element){
	$('#J_up87_tip_'+element[0].name).html(error)},errorElement:'div',errorClass:'tips_error',validClass:'',onkeyup:false,focusInvalid:false,highlight:false,
	rules:{host:{required:true},username:{required:true},dbname:{required:true},port:{required:true},dbpre:{required:true},f_name:{required:true},f_pass:{required:true},f_rpass:{required:true,equalTo:'#f_pass'},
		src_host:{required:true},src_username:{required:true},src_dbname:{required:true},src_port:{required:true},src_dbpre:{required:true}},
	unhighlight:function(element,errorClass,validClass){var tip_elem=$('#J_up87_tip_'+element.name);if(element.value){tip_elem.html('<span class="'+validClass+'"><span>')}},
	onfocusin:function(element){var id=element.name,tips=focus_tips[id]?focus_tips[id]:'';$('#J_up87_tip_'+id).html('<span class="gray" data-text="text">'+tips+'</span>')},
	onfocusout:function(element){this.element(element)},messages:{host:{required:'Nextwind数据库服务器不能为空'},username:{required:'Nextwind数据库用户名不能为空'},dbname:{required:'Nextwind数据库服务器端口不能为空'},port:{required:'Nextwind数据库服务器端口不能为空'},dbpre:{required:'Nextwind数据库表前缀不能为空'},
		f_name:{required:'创始人帐号不能为空'},f_pass:{required:'创始人密码不能为空'},f_rpass:{required:'确认密码不能为空',equalTo:'两次输入的密码不一致。请重新输入'},
		src_host:{required:'PW8.7数据库服务器不能为空'},src_username:{required:'PW8.7数据库用户名不能为空'},src_dbname:{required:'PW8.7数据库名不能为空'},src_port:{required:'PW8.7数据库服务器端口不能为空'},src_dbpre:{required:'PW8.7数据库表前缀不能为空'}}})
});
</script>
</body>
</html>
EOT;
} elseif ('writeconfig' == $step) {
	// [start-writeconfig]可写目录判断
	$dbConfig = array(
		//9.0 database info
		'host' => $_POST['host'], 
		'username' => $_POST['username'], 
		'password' => $_POST['password'], 
		'dbname' => $_POST['dbname'], 
		'dbpre' => $_POST['dbpre'], 
		'port' => $_POST['port'], 
		//8.x database info
		'src_host' => $_POST['src_host'], 
		'src_username' => $_POST['src_username'], 
		'src_password' => $_POST['src_password'], 
		'src_dbname' => $_POST['src_dbname'], 
		'src_dbpre' => $_POST['src_dbpre'], 
		'src_port' => $_POST['src_port']);
	$dbI18n = array(
		'host' => 'Nextwind数据库服务器不能为空',
		'username' => "Nextwind数据库用户名不能为空",
		'dbname' => "Nextwind数据库名不能为空",
		'dbpre' => 'Nextwind数据库表前缀不能为空',
		'port' => 'Nextwind数据库服务器端口不能为空',
		'src_host' => 'PW8.7数据库服务器不能为空',
		'src_username' => "PW8.7数据库用户名不能为空",
		'src_dbname' => "PW8.7数据库名不能为空",
		'src_dbpre' => 'PW8.7数据库表前缀不能为空',
		'src_port' => 'PW8.7数据库服务器端口不能为空',
	);
	foreach ($dbConfig as $key => $value) {
		if (!in_array($key, array('password', 'src_password')) && empty($value)) showError($dbI18n[$key]);
	}
	$found = array(
		'name' => $_POST['f_name'],
		'password' => $_POST['f_pass'],
	);
	$foundI18n = array('name' => '创始人帐号不能为空', 'password' => '创始人密码不能为空', 'equalPwd' => '创始人两次密码不一致');
	foreach ($found as $key => $value) {
		if (empty($value)) showError($foundI18n[$key]);
	}
	if ($_POST['f_rpass'] != $found['password']) {
		showError($foundI18n['equalPwd']);
	}
	$dbConfig['engin'] = intval($_POST['engin']) == 1 ? 'InnoDB' : 'MyISAM';
	$config = array('db_config' => $dbConfig, 'limit' => intval($_POST['limit']));
	
	//87数据库配置判断
	$srcDb = new DB($dbConfig['src_host'], $dbConfig['src_port'], $dbConfig['src_username'], $dbConfig['src_password'], $dbConfig['src_dbname'], $dbConfig['src_dbpre']);
	
	//nextwind数据库配置判断
	$link = @mysql_connect("{$dbConfig['host']}:{$dbConfig['port']}", $dbConfig['username'], $dbConfig['password'], true);
	if ($link) {
		if (!@mysql_select_db($dbConfig['dbname'], $link)) {
			if (!@mysql_query("CREATE DATABASE " . $dbConfig['dbname'] . " DEFAULT CHARSET=" . $charset, $link)) {
				showError('数据库' . $dbConfig['dbname'] . ' 创建失败! 请检查是否有权限! ');
			}
		}
	} else {
		showError("Access denied for user '{$dbConfig['username']}'@'{$dbConfig['host']}' (using password: YES)");
	}
	
	//获得分表配置
	$_list = $srcDb->get_all("SELECT * FROM pw_config WHERE db_name IN ('db_plist','db_tlist')", MYSQL_ASSOC, 'db_name');
	$config['db_plist'] = $config['db_tlist'] = array();
	foreach ($_list as $_k => $value) {
		$_list = unserialize($value['db_value']);
		if (is_array($_list)) {
			$_list = array_keys($_list);
			sort($_list);
			$config[$_k] = $_list;
		}
	}
	//获得ftp配置
	$_list = $srcDb->get_all("SELECT * FROM pw_config WHERE db_name LIKE '%ftp%'", MYSQL_ASSOC, 'db_name');
	$config['db_ftp'] = array();
	$config['db_ftp']['db_ifftp'] = $_list['db_ifftp']['db_value'];
	if ($_list['db_ifftp']['db_value']) {
		$config['db_ftp']['db_ftpweb'] = $_list['db_ftpweb']['db_value'];
		$config['db_ftp']['ftp_server'] = $_list['ftp_server']['db_value'];
		$config['db_ftp']['ftp_dir'] = $_list['ftp_dir']['db_value'];
		$config['db_ftp']['ftp_user'] = $_list['ftp_user']['db_value'];
		$config['db_ftp']['ftp_pass'] = $_list['ftp_pass']['db_value'];
		$config['db_ftp']['ftp_timeout'] = $_list['ftp_timeout']['db_value'];
		$config['db_ftp']['ftp_port'] = $_list['ftp_port']['db_value'];
	}
	is_dir($setupTmpFolder) or mkdirRecur($setupTmpFolder);
	file_put_contents($configFile, "<?php\n \$setupConfig = " . var_export($config, true) . ';');
	@chmod($configFile, 0777);
	
	//写入创始人文件
	$founders = array($found['name'] => md5($found['password']));
	file_put_contents($founderFile, "<?php\ndefined('WEKIT_VERSION') or exit(403); return " . var_export($founders, true) . ';');
	@chmod($founderFile, 0777);
	
	//写入Lock文件
	$token = generatestr(16);
	file_put_contents($lockFile, md5($token));
	refreshTo(null, 'dbprepare');
} elseif ('dbprepare' == $step) {
	//[start-dbprepare]创建P9数据结构
	$sqlFiles = array(
		'wind_structure.sql', 
		'pw_design.sql', 
		'pw_acloud.sql',
	);
	$arrSQL = array();
	foreach ($sqlFiles as $file) {
		$file = NEXTWIND_DIR . '/src/applications/install/lang/' . $file;
		if (!is_file($file)) continue;
		$content = file_get_contents($file);
		if (!empty($content)) $arrSQL = array_merge_recursive($arrSQL, _sqlParser($content, $charset, $dbpre, $engin)); //TODO fill in charset,engine..
	}
	file_put_contents($tmpDbFile, "<?php\n return " . var_export($arrSQL['SQL'], true) . ';');
	refreshTo(null, 'dbdata');
} elseif ('dbdata' == $step) {
	//[start-dbdata]写入P9基础数据
	$targetDb->query("SET NAMES '{$charset}'");//character_set_client=binary 在gbk包情况下，会出现表描述是乱码的情况
	$tableSql = include $tmpDbFile;
	foreach ($tableSql['DROP'] as $sql) {
		$targetDb->query($sql);
	}
	foreach ($tableSql['CREATE'] as $sql) {
		$targetDb->query($sql);
	}
	
	foreach ($tableSql['UPDATE'] as $sql) {
		$targetDb->query($sql);
	}
	
	$targetDb->query(
		"REPLACE INTO `pw_admin_role` (`id`, `name`, `auths`, `created_time`, `modified_time`) VALUES
(1, '管理员', 'custom_set,config_site,config_nav,config_register,config_mobile,config_credit,config_editor,config_emotion,config_attachment,config_watermark,config_verifycode,config_seo,config_rewrite,config_domain,config_email,config_pay,config_area,config_school,u_groups,u_upgrade,u_manage,u_forbidden,u_check,bbs_article,contents_tag,contents_message,contents_report,bbs_contentcheck_forum,contentcheck_word,contents_user_tag,bbs_recycle,bbs_configbbs,bbs_setforum,bbs_setbbs,design_page,design_component,design_module,design_push,design_permissions,database_backup,cache_m,data_hook,cron_operations,log_manage,app_album,app_vote,app_medal,app_task,app_punch,app_link,app_message,app_announce,platform_server,platform_appList,platform_server_check,platform_index,platform_siteStyle,platform_upgrade', 1340275489, 1347092145)
	");
	//设置默认导航
	$targetDb->query(
		"REPLACE INTO `pw_common_nav` (`navid`, `parentid`, `rootid`, `type`, `sign`, `name`, `style`, `link`, `alt`, `target`, `isshow`, `orderid`) VALUES
(1, 0, 1, 'main', 'default|index|run|', '首页', '', 'index.php', '', 0, 0, 1),
(2, 0, 2, 'main', 'bbs|index|run|', '论坛', '|||', 'index.php?m=bbs', '', 0, 1, 2),
(3, 0, 3, 'main', 'like|like|run|', '喜欢', '|||', 'index.php?m=like&c=like', '', 0, 1, 3),
(4, 0, 4, 'main', '', '云平台', '|||', 'http://open.phpwind.com', '', 1, 1, 6),
(5, 0, 5, 'main', 'tag|index|run|', '话题', '|||', 'index.php?m=tag', '', 0, 1, 4),
(6, 0, 6, 'main', 'appcenter|index|run|', '应用', '', 'index.php?m=appcenter', '', 0, 1, 5),
(7, 0, 7, 'my', 'space', '我的空间', '', 'index.php?m=space', '', 0, 1, 1),
(8, 0, 8, 'my', 'fresh', '我的关注', '', 'index.php?m=my&c=fresh', '', 0, 1, 2),
(9, 0, 9, 'my', 'forum', '我的版块', '', 'index.php?m=bbs&c=forum&a=my', '', 0, 1, 3),
(10, 0, 10, 'my', 'article', '我的帖子', '', 'index.php?m=my&c=article', '', 0, 1, 4),
(11, 0, 11, 'my', 'vote', '我的投票', '', 'index.php?m=vote&c=my', '', 0, 1, 5),
(12, 0, 12, 'my', 'task', '我的任务', '', 'index.php?m=task', '', 0, 1, 6),
(13, 0, 13, 'my', 'medal', '我的勋章', '', 'index.php?m=medal', '', 0, 1, 7),
(14, 0, 14, 'bottom', '', '关于phpwind', '', 'http://www.phpwind.com/index.php?m=aboutus&a=index&menuid=16', '', 0, 1, 1),
(15, 0, 15, 'bottom', '', '联系我们', '|||', 'http://www.phpwind.com/index.php?m=aboutus&a=index&menuid=20', '', 0, 1, 2),
(16, 0, 16, 'bottom', '', '程序建议', '', 'http://www.phpwind.net/thread-htm-fid-39.html', '', 0, 1, 3),
(17, 0, 17, 'bottom', '', '问题反馈', '', 'http://www.phpwind.net/thread-htm-fid-54.html', '', 0, 1, 3),
(18, 0, 18, 'main', 'bbs|forumlist|run|', '版块', '', 'index.php?m=bbs&c=forumlist', '', 0, 1, 2)");
	
	//设置默认首页导航
	$targetDb->query(sprintf("REPLACE INTO `pw_common_config` (`name`, `namespace`, `value`, `vtype`, `description`) VALUES 
('homeUrl', 'site', 'index.php', 'string', ''), 
('homeRouter', 'site', '%s', 'array', '')", 'a:3:{s:1:"m";s:3:"bbs";s:1:"c";s:5:"index";s:1:"a";s:3:"run";}'));

	//添加临时表
	createTmpTables($charset);
	refreshTo('credit', 'init');
} elseif ('init' == $step) {
	//初始化db配置数据、update操作
	if ('credit' == $action) {
		//[init-credit]
		$creditMap = array('money' => 1, 'rvrc' => 2, 'credit' => 3, 'currency' => 4);
		$_commonConfig = array();
		$creditConfig = array(
			1 => array('open' => 1, 'log' => 1), 
			2 => array('open' => 1, 'log' => 1), 
			3 => array('open' => 1, 'log' => 1), 
			4 => array('open' => 1, 'log' => 1));
		//1、收集87中默认的4个积分配置
		$sql = "SELECT * FROM pw_config WHERE db_name LIKE 'db_money%' OR db_name like 'db_rvrc%' OR db_name LIKE 'db_currency%' OR db_name LIKE 'db_credit%'";
		$_tmp = $srcDb->query($sql);
		while ($row = $srcDb->fetch_array($_tmp)) {
			$name = substr($row['db_name'], -4);
			$r = preg_match('/^db_(.*)(name|unit)$/', $row['db_name'], $matches);
			if (!$r) continue;
			$creditConfig[$creditMap[$matches[1]]][$name] = $row['db_value'];
		}
		//2、收集87中扩展的积分设置
		$sql = "SELECT * FROM pw_credits ORDER BY cid ";
		$_tmp = $srcDb->query($sql);
		$lastId = 4;
		while ($row = $srcDb->fetch_array($_tmp)) {
			$lastId++;
			$creditMap[$row['cid']] = $lastId;
			$creditConfig[$lastId] = array(
				'open' => 1,
				'log' => 0,
				'name' => $row['name'], 
				'unit' => $row['unit'], 
				'desc' => $row['description']);
		}
		//将87到9的积分映射表存储到配置文件中
		$setupConfig['db_credit'] = $creditMap;
		file_put_contents($configFile, "<?php\n \$setupConfig = " . var_export($setupConfig, true) . ';');
		//3、如果扩展的积分字段超过8个，则创建字段---9中预留的积分字段为8个
		$_creditNum = count($creditConfig);
		if ($_creditNum > 8) {
			$sql = 'ALTER TABLE %s ADD COLUMN credit%d INT(10) NOT NULL DEFAULT 0';
			for ($i = 9; $i <= $_creditNum; $i++) {
				$_sql = sprintf($sql, 'pw_windid_user_data', $i);
				$targetDb->query($_sql);
				$_sql = sprintf($sql, 'pw_user_data', $i);
				$targetDb->query($_sql);
			}
		}
		//4、更新windid_config
		$sql = sprintf("REPLACE INTO pw_windid_config (`name`, `namespace`, `value`, `vtype`) VALUES ('credits', 'credit', '%s', 'array')", serialize($creditConfig));
		$targetDb->query($sql);
		//5、更新pw_common_config
		$_commonConfig['credits'] = $creditConfig;
		//6、全局积分策略
		$pointMap = array(
			'Digest' => 'digest_topic', 
			'Post' => 'post_topic', 
			'Reply' => 'post_reply', 
			'Undigest' => 'remove_digest', 
			'Delete' => 'delete_topic', 
			'Deleterp' => 'delete_reply');
		$sql = "SELECT * FROM pw_config WHERE db_name = 'db_creditset'";
		$row = $srcDb->get_one($sql);
		$strategy = unserialize($row['db_value']);
		$newStrategy = array();
		foreach ($strategy as $key => $value) {
			$newStrategy[$pointMap[$key]] = array('limit' => '', 'credit' => array());
			$_vAb = in_array($key, array('Undigest', 'Delete', 'Deleterp'));
			foreach ($value as $_k => $_v) {
				$_nk = $creditMap[$_k];
				$_v = abs($_v);
				$_vAb && $_v = -$_v;
				$newStrategy[$pointMap[$key]]['credit'][$_nk] = $_v;
			}
		}
		$_commonConfig['strategy'] = $newStrategy;
		Config::storeConfig('credit', $_commonConfig);
		refreshTo('usergroups', 'init');
	} elseif ('usergroups' == $action) {
		$newGroupSetting = $newGroupSetting_system = $newGroupSetting_systemforum = $oldGroupSettings = array();
		// [init-usergroups]同步原用户组设置
		$sql = 'SELECT fid,gid,rkey,type,rvalue FROM pw_permission WHERE uid=0 AND fid=0';
		$query = $srcDb->query($sql);
		while ($row = $srcDb->fetch_array($query)) {
			$gid = $row['gid'];
			$oldGroupSettings[$gid][$row['rkey']] = $row;
		}
		foreach ($oldGroupSettings as $gid => $v) {
			if (!isset($v['viewip'])) $v['viewip'] = array('rvalue' => 0);
			if (!isset($v['replylock'])) $v['replylock'] = array('rvalue' => 0);
			extract($v, EXTR_PREFIX_ALL, 'ogp');
			$newGroupSetting[$gid] = array(
				'allow_visit' => intval($ogp_allowvisit['rvalue']), 
				'user_binding' => intval($ogp_userbinding['rvalue']), 
				'allow_report' => intval($ogp_allowreport['rvalue']), 
				'user_binding' => intval($ogp_userbinding['rvalue']), 
				'allow_publish_vedio' => intval($ogp_allowvideo['rvalue']), 
				'allow_publish_music' => intval($ogp_allowmusic['rvalue']), 
				//'multimedia_auto_open' => intval($ogp_allowvideo['rvalue']),
				'message_allow_send' => intval($ogp_allowmessage['rvalue']), 
				'message_max_send' => intval($ogp_maxsendmsg['rvalue']), 
				'tag_allow_add' => 1,  //原没有
				'remind_open' => intval($ogp_allowat['rvalue']), 
				'remind_max_num' => intval($ogp_atnum['rvalue']), 
				//'invite_allow_buy' => 1,//TODO,原为全局配置
				//'invite_buy_credit_num' => '',//TODO,原为全局配置
				//'invite_limit_24h' => 1,//原为购买时间间隔
				'allow_read' => intval($ogp_allowread['rvalue']), 
				'allow_post' => intval($ogp_allowpost['rvalue']), 
				'allow_reply' => intval($ogp_allowrp['rvalue']), 
				'post_check' => (0 == intval($ogp_atccheck['rvalue'])) ? 2 : 1, //原先开启发帖审核，升级之后为按板块设置
				'threads_perday' => intval($ogp_postlimit['rvalue']), 
				'thread_edit_time' => intval($ogp_edittime['rvalue']), 
				'post_pertime' => intval($ogp_postpertime['rvalue']), 
				'post_url_num' => intval($ogp_posturlnum['rvalue']), 
				'allow_upload' => intval($ogp_allowupload['rvalue']), 
				'allow_download' => intval($ogp_allowdownload['rvalue']), 
				'uploads_perday' => intval($ogp_allownum['rvalue']), 
				//sell_credits sell_credit_range enhide_credits出售隐藏帖允许的积分类型，原为全局设置
				'allow_sign' => intval($ogp_allowhonor['rvalue']), 
				'allow_add_vote' => intval($ogp_allownewvote['rvalue']), 
				'allow_participate_vote' => intval($ogp_allowvote['rvalue']), 
				'allow_view_vote' => intval($ogp_viewvote['rvalue']), 
				'reply_locked_threads' => intval($ogp_replylock['rvalue']), 
				'view_ip_address' => intval($ogp_viewip['rvalue']), 
				'allow_thread_extend' => array(
					'sell' => intval($ogp_allowsell['rvalue']), 
					'hide' => intval($ogp_allowhidden['rvalue'])),
				'sign_max_length' => intval($ogp_signnum['rvalue']),//帖子签名最大字节数
				'message_allow_send' => intval($ogp_allowmessege['rvalue']),//发送消息是否开启
				'message_max_send' => intval($ogp_maxsendmsg['rvalue']),//每日发送消息条数
				'look_thread_log' => intval($ogp_atclog['rvalue']),//查看帖子操作记录
			);
			$newGroupSetting_systemforum[$gid] = array(
				'operate_thread' => array(
					'up_time' => intval($ogp_pushtime['rvalue']),
					'topped_type' => $ogp_topped['rvalue'] > 3 ? 3 : $ogp_topped['rvalue']));
			$_operate_thread = array(
				'digest' => 'digestadmin',
				'highlight' => 'coloradmin',
				'down' => 'downadmin',
				'type' => 'tpctype',
				'toppedreply' => 'replaytopped',
				'move' => 'moveatc',
				'copy' => 'copyatc',
				'delete' => 'modother',
				'edit' => 'deltpcs',
				'deleteatt' => 'delattach',
				'shield' => 'shield',
				'read' => 'inspect',
				'lock' => 'lockadmin',
				'ban' => 'bantype',
				'up' => 'pushadmin',
				'topped' => 'topped',
			);
			foreach ($_operate_thread as $_k => $_v) {
				if (intval($v[$_v]['rvalue']) > 0) {
					$newGroupSetting_systemforum[$gid]['operate_thread'][$_k] = 1;
				}
			}
			$newGroupSetting_system[$gid] = array(
				'force_operate_reason' => intval($ogp_enterreason['rvalue']), 
				'fresh_delete' => intval($ogp_delweibo['rvalue']),//新鲜事删除权限
			);
			//管理组3,4,5的门户权限根据安装的默认数据进行配置
			if (in_array($gid, array(3, 4))) {
				$newGroupSetting_system[$gid]['design_allow_manage'] = array('push' => 4);
			} elseif (5 == $gid) {
				$newGroupSetting_system[$gid]['design_allow_manage'] = array('push' => 1);
			}
		}
		$sqlValues = array();
		foreach (array('newGroupSetting', 'newGroupSetting_systemforum', 'newGroupSetting_system') as $v) {
			$rtype = preg_match('/_(\w+)$/', $v, $m) ? $m[1] : 'basic';
			foreach ($$v as $gid => $v2) {
				foreach ($v2 as $rkey => $v3) {
					if (is_array($v3)) {
						$vtype = 'array';
						$rvalue = serialize($v3);
					} else {
						$vtype = 'string';
						$rvalue = $v3;
					}
					$sqlValues[] = implode(',', array($gid, "'$rkey'", "'$rtype'", "'$rvalue'", "'$vtype'"));
				}
			}
		}
		$sqlValues && $targetDb->query("REPLACE INTO pw_user_permission_groups (`gid`, `rkey`, `rtype`, `rvalue`, `vtype`) VALUES (" . implode('), (', $sqlValues) . ')');
		
		//用户组提升方案
		$sql = "SELECT * FROM pw_config WHERE db_name = 'db_upgrade'";
		$row = $srcDb->get_one($sql);
		$newUpgradeConfig = array();
		if ($row && $row['db_value']) {
			$upgradeConfig = unserialize($row['db_value']);
			if (is_array($upgradeConfig)) {
				$upgrade = array(
					'postnum' => 'postnum',
					'digests' => 'digest',
					'onlinetime' => 'onlinetime');
				foreach ($upgradeConfig as $_key => $_value) {
					$_tmpK = '';
					if (array_key_exists($_key, $upgrade)) {
						$_tmpK = $upgrade[$_key];
					} else {
						$_tmpK = getCreditMap($_key, true);
					}
					if ($_tmpK) {
						$newUpgradeConfig[$_tmpK] = $_value;
					}
				}
			}
		}
		Config::storeConfig('site', array('upgradestrategy' => $newUpgradeConfig));
		
		refreshTo('commonconfig', 'init');
	} elseif ('commonconfig' == $action) {
		// [init-commonconfig] 全局设置
		//[站点设置]
		$configMap = array(
			'db_bbsname' => 'info.name',  //网站名称
			'db_bbsurl' => 'info.url',  //网站地址
			'db_ceoemail' => 'info.mail',  //管理员EMAIL
			'db_icp' => 'info.icp',  //ICP
			'db_statscode' => 'statisticscode',  //第三方统计代码
			'db_bbsifopen' => array(
				'visit.state', 
				'visit.group', 
				'onlinetime'),  //网站状态
			'db_visitgroup' => '',  //允许访问的用户组 visit.gid
			'db_visitmsg' => '',  //外部提示信息 visit.message
			'db_whybbsclose' => '',  //关闭状态说明
			'db_onlinetime' => '',  //在线用户时限
			'db_visitips' => 'visit.ip',  //允许访问的IP段
			'db_visituser' => 'visit.member',  //允许访问的会员
			'db_timedf' => 'time.timezone',  //默认时区
			'db_cvtimes' => 'time.cv',  //服务器时间校正
			'db_refreshtime' => 'refreshtime',  //刷新页面时间间隔
			'db_debug' => 'debug',  //DEBUG 模式运行站点
			'db_adminreason' => 'managereasons',  //备选管理操作原因
// 			'db_ckpath' => 'cookie.path',  //Cookie 路径
// 			'db_ckdomain' => 'cookie.domain',		//Cookie 作用域
			'db_job_isopen' => 'task.isOpen',//任务开关控制
			'db_md_ifopen' => 'medal.isopen',//勋章控制开关
		);
		Config::transferConfig('site', $configMap, 'transSiteSetting');
		Config::storeConfig('site', array('hash' => generatestr(8)));
		//[注册设置]
		$configMap = array(
			'rg_allowregister' => array(
				'type', 
				'active', 
				'welcome.type', 
				'security.username.min', 
				'security.password.min'),  //允许新用户注册
			'rg_rgpermit' => 'protocol',  //注册协议内容
			'rg_allowsameip' => 'security.ip',  //同一IP重复注册[小时]
			'rg_ifcheck' => 'active.check',  //新用户注册审核
			'rg_whyregclose' => 'close.msg',  //关闭注册原因
			'rg_banname' => 'security.ban.username',  //禁用用户名
			'rg_emailcheck' => '',  //新用户邮件激活
			'rg_regsendemail' => '',  //发送欢迎信息
			'rg_regsendmsg' => '',  //发送欢迎信息
			'rg_welcomemsg' => '',  //欢迎信息内容
			'rg_namelen' => '',  //用户名长度控制
			'rg_pwdlen' => '',  //密码长度控制
			'rg_npdifferf' => '',  //注册帐号与密码相同
			'rg_pwdcomplex' => ''		//强制密码复杂度
		);
		Config::transferConfig('register', $configMap, 'transReg');
		//[登录设置]
		$configMap = array('db_safegroup' => array('question.groups'), 'db_logintype' => '');		//用户登录方式
		Config::transferConfig('login', $configMap, 'transLogin');
		//[BBS-编辑器]
		$configMap = array(
			'db_cvtimes' => 'ubb.cvtimes',  //帖子中标签转换控制
			'db_windpost' => array(
				'ubb.img.open', 
				'post.check.open', 
				'post.check.start_hour', 
				'post.check.start_min', 
				'post.check.end_hour', 
				'post.check.end_min', 
				'read.defined_floor_name', 
				'read.display_info', 
				'thread.new_thread_minutes'),  //[img]标签/[img]大小控制[像素]/[size]标签最大值控制/[flash]标签/多媒体标签/[iframe]标签
			'db_titlemax' => 'title.length.max',  //帖子标题最大长度
			'db_postmin' => 'content.length.min',  //内容长度控制
			'db_postmax' => 'content.length.max',  //内容长度控制
			'db_openpost' => '',  //定时发帖审核
			'db_newtime' => '',  //新帖控制
			'db_perpage' => 'thread.perpage',  //每页显示主题数
			'db_maxpage' => 'thread.max_pages',  //最大显示页数
			'db_readperpage' => 'read.perpage',  //每页帖子楼层数
			'db_floorunit' => 'read.floor_name',  //帖子楼层单位
			'db_floorname' => '',  //预设帖子楼层名称
			'db_readinfo' => 'read.display_member_info',  //显示个人信息
			'db_showcustom' => ''		//显示栏目
		);
		Config::transferConfig('bbs', $configMap, 'transUbb');
		// [附件相关]
		$configMap = array(
			'db_attachhide' => 'pathsize',  //附件路径控制[KB]
			'db_attachnum' => 'attachnum',  //附件上传数量限制
			'db_uploadfiletype' => array(
				'extsize', 
				'thumb', 
				'thumb.size.width', 
				'thumb.size.height', 
				'mark.position', 
				'mark.gif', 
				'mark.type', 
				'mark.fontfamily', 
				'storage.type'),  //附件类型和尺寸控制[KB]
			'db_ifathumb' => '',  //帖子图片缩略设置
			'db_athumbtype' => '', 
			'db_athumbsize' => '',  //缩略图大小设置
			'db_quality' => 'thumb.quality',  //缩略图质量
			'db_waterwidth' => 'mark.limitwidth',  //图片附件尺寸控制-宽
			'db_waterheight' => 'mark.limitheight',  //图片附件大小控制-高
			'db_waterpos' => '',  //水印位置
			'db_ifgif' => '',  //为GIF图片加水印
			'db_watermark' => '',  //水印类型
			'db_waterimg' => 'mark.file',  //水印文件
			'db_watertext' => 'mark.text',  //水印文字
			'db_waterfonts' => '',  //水印字体
			'db_waterfont' => 'mark.fontsize',  //水印文字大小
			'db_watercolor' => 'mark.fontcolor',  //水印文字颜色
			'db_waterpct' => 'mark.transparency',  //水印透明度
			'db_jpgquality' => 'mark.quality',//图片质量
			'db_ifftp' => array('storage.type'),
			'db_ftpweb' => 'ftp.url',
			'ftp_pass' => 'ftp.pwd',
			'ftp_server' => 'ftp.server',
			'ftp_port' => 'ftp.port',
			'ftp_dir' => 'ftp.dir',
			'ftp_user' => 'ftp.user',
			'ftp_timeout' => 'ftp.timeout',
		);
		Config::transferConfig('attachment', $configMap, 'transAttachment');
		//验证码策略
		$configMap = array('db_gdcheck' => array('showverify'));
		Config::transferConfig('verify', $configMap, 'transVerify');
		// [SEO]
		$sql = "SELECT * FROM pw_config WHERE db_name ='db_seoset'";
		$oldConfig = $srcDb->get_one($sql, MYSQL_ASSOC);
		if ($oldConfig) {
			$oldConfig = unserialize($oldConfig['db_value']);
			$newConfig = array();
			$newConfig['seo_bbs_forumlist_0'] = array(
				'mod' => 'bbs', 
				'page' => 'forumlist', 
				'param' => '0');
			$newConfig['seo_bbs_thread_0'] = array(
				'mod' => 'bbs', 
				'page' => 'thread', 
				'param' => '0');
			$newConfig['seo_bbs_read_0'] = array('mod' => 'bbs', 'page' => 'read', 'param' => '0');
			$newConfig['seo_bbs_forumlist_0']['title'] = isset($oldConfig['title']['index']) ? $oldConfig['title']['index'] : '';
			$newConfig['seo_bbs_forumlist_0']['keywords'] = isset($oldConfig['metaKeywords']['index']) ? $oldConfig['metaKeywords']['index'] : '';
			$newConfig['seo_bbs_forumlist_0']['description'] = isset($oldConfig['metaDescription']['index']) ? $oldConfig['metaDescription']['index'] : '';
			$newConfig['seo_bbs_new_0'] = $newConfig['seo_bbs_forumlist_0'];
			$newConfig['seo_bbs_new_0']['page'] = 'new';
			$newConfig['seo_bbs_thread_0']['title'] = isset($oldConfig['title']['thread']) ? $oldConfig['title']['thread'] : '';
			$newConfig['seo_bbs_thread_0']['keywords'] = isset($oldConfig['metaKeywords']['thread']) ? $oldConfig['metaKeywords']['thread'] : '';
			$newConfig['seo_bbs_thread_0']['description'] = isset($oldConfig['metaDescription']['thread']) ? $oldConfig['metaDescription']['thread'] : '';
			$newConfig['seo_bbs_read_0']['title'] = isset($oldConfig['title']['read']) ? $oldConfig['title']['read'] : '';
			$newConfig['seo_bbs_read_0']['keywords'] = isset($oldConfig['metaKeywords']['read']) ? $oldConfig['metaKeywords']['read'] : '';
			$newConfig['seo_bbs_read_0']['description'] = isset($oldConfig['metaDescription']['read']) ? $oldConfig['metaDescription']['read'] : '';
			$sql = array();
			foreach ($newConfig as $_v) {
				$sql[] = sprintf("('%s', '%s', '%s', '%s', '%s', '%s')", $_v['mod'], $_v['page'], $_v['param'], $_v['title'], $_v['keywords'], $_v['description']);
			}
			$sql && $targetDb->query(sprintf('REPLACE INTO pw_seo (`mod`, `page`, `param`, `title`, `keywords`, `description`) VALUES %s', implode(',', $sql)));
		}
		// [电子邮件]
		$configMap = array(
			'ml_mailifopen' => array('mailOpen'), 
			'ml_smtphost' => 'mail.host', 
			'ml_smtpport' => 'mail.port', 
			'ml_smtpfrom' => 'mail.from', 
			'ml_smtpauth' => 'mail.auth', 
			'ml_smtpuser' => 'mail.user', 
			'ml_smtppass' => 'mail.password');
		Config::transferConfig('email', $configMap, 'transEmail');
		//[网上支付]
		$configMap = array(
			'ol_onlinepay' => 'ifopen',  //网上支付功能是否开启
			'ol_whycolse' => 'reason',  //网上支付功能关闭原因
			'ol_payto' => 'alipay',  //支付宝帐号
			'ol_alipaypartnerID' => 'alipaypartnerID',  //合作者身份(PID)
			'ol_alipaykey' => 'alipaykey',  //交易安全校验码(key)
			'ol_tenpay' => 'tenpay',  //财付通帐号
			'ol_tenpaycode' => 'tenpaykey',  //商户密钥
			'ol_paypal' => 'paypal',  //贝宝帐号
			'ol_paypalcode' => 'paypalkey',  //贝宝密钥
			'ol_99bill' => '99bill',  //快钱帐号
			'ol_99billcode' => '99billkey'		//快钱密钥
		//alipayinterface//支付宝接口
		);
		Config::transferConfig('pay', $configMap);
		
		refreshTo('hkconfig', 'init');
	} elseif ('hkconfig' == $action) {
		//[打卡设置]
		$fieldMap = array('o_punchopen' => 'punch.open', 'o_punch_reward' => 'punch.reward');
		$hkconfig = $GLOBALS['srcDb']->get_all(sprintf("SELECT * FROM pw_hack WHERE hk_name IN ('%s')", implode("','", array_keys($fieldMap))), MYSQL_ASSOC, 'hk_name');
		$_newconfig = array();
		foreach ($fieldMap as $key => $value) {
			$_newconfig[$value] = $hkconfig[$key]['hk_value'];
		}
		$reward = unserialize($_newconfig['punch.reward']);
		if ($reward) {
			$_newconfig['punch.reward'] = array(
				'type' => getCreditMap($reward['type']), 
				'min' => $reward['min'], 
				'max' => $reward['max'], 
				'step' => $reward['step']);
			$_newconfig['punch.reward'] = $_newconfig['punch.reward'];
		}
		Config::storeConfig('site', $_newconfig);
		refreshTo('end', 'init');
	} elseif ('end' == $action) {
		gotoUrl('user', 'convert', true);
	}
} elseif ($step == 'convert') {
	empty($lastId) && $lastId = 0;
	$transfers = 0;
	$TOTAL = 0;
	$PERCENT = '100%';
  	$_subTMessage = "";//用来记录分表信息

	if ('user' == $action) {
		//[convert-user]
		//原pw_members表数据
		$associateFields = array(
			'pw_user' => array(
				'uid' => 'uid', 
				'username' => 'username', 
				'email' => 'email', 
				'password' => 'password', 
				'groupid' => 'groupid', 
				'memberid' => 'memberid', 
				'realname' => 'realname', 
				'regdate' => 'regdate', 
				'userstatus' => 'status',
				'groups' => 'groups'), 
			'pw_windid_user' => array(
				'uid' => 'uid', 
				'username' => 'username', 
				'email' => 'email', 
				'password' => 'password', 
				1 => 'salt', 
				'regdate' => 'regdate'), 
			'pw_windid_user_info' => array(
				'uid' => 'uid', 
				//'icon' => 'icon',
				'gender' => 'gender', 
				'bday' => 'byear', 
				1 => 'bmonth', 
				2 => 'bday', 
				'home' => 'hometown', 
				'apartment' => 'location', 
				'site' => 'homepage', 
				'oicq' => 'qq', 
				'aliww' => 'aliww', 
				'authmobile' => 'mobile',
				'msn' => 'msn',
				'introduce' => 'profile',  //需要转换escapeChar), 
			),
			'pw_user_info' => array( //[pw_user_info]
				'uid' => 'uid', 
				'gender' => 'gender', 
				'bday' => 'byear', 
				1 => 'bmonth', 
				2 => 'bday', 
				'home' => 'hometown', 
				'apartment' => 'location', 
				'site' => 'homepage', 
				'oicq' => 'qq', 
				'aliww' => 'aliww', 
				'authmobile' => 'mobile', 
				'msn' => 'msn', 
				'signature' => 'bbs_sign',  //需要转换escapeChar
				'introduce' => 'profile',  //需要转换escapeChar
				5 => 'secret'			//用户隐私设置来自pw_ouserdata表
			),
		);
		$collect = array('checkUser' => array(), 'banUser' => array(), 'userBelong' => array(), 'joinForums' => array());
		$callbacks = array(
			'pw_user' => '_callbackUser', 
			'pw_user_info' => '_callbackUserInfo', 
			'pw_windid_user' => '_callbackWindidUser', 
			'pw_windid_user_info' => '_callbackWindidUserinfo');
		$nextId = transferDataByPk('pw_members', $associateFields, 'uid', $lastId, $limit, $callbacks);
		//将未验证用户插入验证表
		if ($collect['checkUser']) {
			$targetDb->query(sprintf('REPLACE INTO pw_user_register_check (`uid`,`ifchecked`,`ifactived`) VALUES %s', implode(',', $collect['checkUser'])));
		}
		//非全局禁言
		if ($collect['banUser']) {
			$sql = sprintf('SELECT uid FROM pw_banuser WHERE uid IN (%s) AND `fid` > 0', implode(',', $collect['banUser']));
			$uids = $srcDb->get_all($sql, MYSQL_ASSOC, 'uid');
			$uids = array_keys($uids);
			if ($uids) {
				$targetDb->query(sprintf('UPDATE pw_user SET groupid=0 WHERE uid IN (%s)', implode(',', $uids)));
			}
		}
		//用户拥有的附加权限更新
		if ($collect['userBelong']) {
			$targetDb->query(sprintf('REPLACE INTO pw_user_belong (`uid`, `gid`) VALUES %s', implode(',', $collect['userBelong'])));
		}
		//用户加入的版块
		if ($collect['joinForums']) {
			$targetDb->query(sprintf("REPLACE INTO pw_bbs_forum_user (uid,fid,join_time) VALUES %s", implode(',', $collect['joinForums'])));
		}
		//计算当前进度
		calculatePercent("SELECT COUNT(*) FROM pw_members", "SELECT COUNT(*) FROM pw_members WHERE uid <= $nextId");
		refreshTo('memberdata');
	} elseif ('memberdata' == $action) {
		//TDO [convert-memberdata]
		//原pw_memberdata表数据
		$associateFields = array(
			'pw_user_data' => array(
				'uid' => 'uid', 
				'lastvisit' => 'lastvisit', 
				'onlineip' => 'lastloginip', 
				'lastpost' => 'lastpost', 
				'thisvisit' => 'lastactivetime', 
				'onlinetime' => 'onlinetime', 
				'postnum' => 'postnum', 
				'digests' => 'digest', 
				'todaypost' => 'todaypost', 
				'follows' => 'follows', 
				'fans' => 'fans', 
				//'postcheck' => 'postcheck', 
				'money' => 'credit1',  //以下积分参见getCreditMap()
				'rvrc' => 'credit2', 
				'credit' => 'credit3', 
				'currency' => 'credit4', 
				'punch' => 'punch'), 
			'pw_windid_user_data' => array(
				'uid' => 'uid', 
				'money' => 'credit1',  //以下积分参见getCreditMap()
				'rvrc' => 'credit2', 
				'credit' => 'credit3', 
				'currency' => 'credit4'
			),
		);
		$callbacks = array(
			'pw_user_data' => '_callbackUserData', 
			'pw_windid_user_data' => '_callbackWindidUserData');
		
		$nextId = transferDataByPk('pw_memberdata', $associateFields, 'uid', $lastId, $limit, $callbacks);
		calculatePercent("SELECT COUNT(*) FROM pw_memberdata", "SELECT COUNT(*) FROM pw_memberdata WHERE uid <= $nextId");
		refreshTo('memberinfo');
	} elseif ('memberinfo' == $action) {
		//[convert]pw_memberinfo  [memberinfo] 
		$nextId = $lastId;
		$rt = $srcDb->query(sprintf("SELECT * FROM pw_memberinfo WHERE uid > %d AND tradeinfo !='' ORDER BY uid LIMIT %d", $lastId, $limit));
		$sql = "UPDATE %s SET alipay = '%s' WHERE uid=%d";
		while ($row = $srcDb->fetch_array($rt)) {
			$nextId = $row['uid'];
			$transfers ++;
			if (!$row['tradeinfo']) continue;
			$trades = unserialize($row['tradeinfo']);
			if (is_array($trades) && isset($trades['alipay']) && $trades['alipay']) {
				$targetDb->query(sprintf($sql, 'pw_user_info', trim($trades['alipay']), $row['uid']));
				$targetDb->query(sprintf($sql, 'pw_windid_user_info', trim($trades['alipay']), $row['uid']));
			}
		}
		calculatePercent("SELECT COUNT(*) FROM pw_memberinfo WHERE tradeinfo !=''", "SELECT COUNT(*) FROM pw_memberinfo WHERE uid <= $nextId AND tradeinfo !=''");
		refreshTo('secret');
	} elseif ('secret' == $action) {
		//[conver-data]用户隐私设置处理
		$nextId = $lastId;
		$user['secret'] = array();
		$_rt = $srcDb->query(sprintf('SELECT * FROM pw_ouserdata WHERE uid > %d ORDER BY uid LIMIT %d', $lastId, $limit));
		while($one = $srcDb->fetch_array($_rt)) {
			$transfers ++;
			$nextId = $one['uid'];
			$secret = array();
			switch ($one['index_privacy']) {
				case '1':
					$secret['space'] = 2;
					break;
				case '2':
					$secret['space'] = 1;
					break;
				default:
					$secret['space'] = 0;
					break;
			}
			$tmp = 0;
			switch ($one['info_privacy']) {
				case '1':
					$tmp = 2;
					break;
				case '2':
					$tmp = 1;
					break;
				default:
					$tmp = 0;
					break;
			}
			$_tmps = array(
				'constellation',
				'local',
				'nation',
				'aliwangwang',
				'qq',
				'msn',
				'mobile',
				'work',
				'education');
			foreach ($_tmps as $_key) {
				$secret[$_key] = $tmp;
			}
			$targetDb->query(sprintf("UPDATE pw_user_info SET secret='%s' WHERE uid=%d", serialize($user['secret']), $one['uid']));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_ouserdata', "SELECT COUNT(*) FROM pw_ouserdata WHERE uid <= $nextId");
		refreshTo('punch');
	} elseif ('punch' == $action) {
		//[convert-pw_user_data-punch]打卡数据迁移
		$nextId = $lastId;
		$sql = 'SELECT * FROM pw_member_behavior_statistic WHERE uid > %d AND behavior=4 ORDER BY uid LIMIT %d';
		$rt = $srcDb->query(sprintf($sql, $lastId, $limit));
		$_punchsBh = $_punchsUd = array();
		while ($one = $srcDb->fetch_array($rt)) {
			$nextId = $one['uid'];
			$transfers ++;
			$_punchsUd[$one['uid']] = $one['num'];
			$expired_time = $one['lastday'] + 86400 * 2;
			$_punchsBh[$one['uid']] = array($one['uid'], 'punch_day', $one['num'], $expired_time, $one['lastday'] + 10);
		}
		if ($_punchsUd) {
			$sql = "SELECT a.uid as uid,username,punch FROM pw_memberdata d LEFT JOIN pw_members a ON d.uid = a.uid WHERE punch != '' AND d.uid IN (%s)";
			$rt = $srcDb->query(sprintf($sql, implode(',', array_keys($_punchsUd))));
			$sql = "UPDATE pw_user_data SET punch = '%s' WHERE uid = %d";
			while($row = $srcDb->fetch_array($rt)) {
				$punch = array();
				$punch['days'] = $_punchsUd[$row['uid']];
				$punch['time'] = $row['punch'];
				$punch['username'] = $row['username'];
				if (isset($_punchsBh[$row['uid']])) {
					$_punchsBh[$row['uid']][4] = $row['punch'];
					$targetDb->query(sprintf($sql, $targetDb->escape_string(serialize($punch)), $row['uid']));
				} else {
					$targetDb->query(sprintf($sql, '', $row['uid']));
				}
			}
			$length = 0;
			$_data = array();
			$sql = "REPLACE INTO pw_user_behavior (uid, behavior, `number`, `expired_time`, `extend_info`) VALUES %s";
			foreach ($_punchsBh as $_uid => $_one) {
				$_tmp = sprintf("('%s')", implode("','", $_one));
				$_len = strlen($_tmp);
				if (($length + $_len) > MAX_PACKAGE) {
					$targetDb->query(sprintf($sql, implode(',', $_data)));
					$_data = array();
					$length = $_len;
				} else {
					$length = $length + $_len;
				}
				$_data[] = $_tmp;
			}
			$_data && $targetDb->query(sprintf($sql, implode(',', $_data)));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_member_behavior_statistic WHERE behavior=4', "SELECT COUNT(*) FROM pw_member_behavior_statistic WHERE uid <= $nextId AND behavior=4");
		refreshTo('membercredit');
	} elseif ('membercredit' == $action) {
		//[convert]pw_membercredit 用户的扩展积分转换
		$nextId = $lastId;
		$limit = 200 * $_lt;
		$sql = "SELECT uid FROM pw_membercredit WHERE uid > %d GROUP BY uid ORDER BY uid LIMIT %d";
		$uids = $srcDb->get_all(sprintf($sql, $lastId, $limit), MYSQL_ASSOC, 'uid');
		if ($uids) {
			$uids = array_keys($uids);
			$nextId = max($uids);
			$transfers = count($uids);
			
			$_sql = "SELECT * FROM pw_membercredit WHERE uid IN (%s)";
			$rt = $srcDb->query(sprintf($_sql, implode(',', $uids)));
			$credits = array();
			while($row = $srcDb->fetch_array($rt)) {
				if (!isset($setupConfig['db_credit'][$row['cid']])) continue;
				if (!isset($credits[$row['uid']])) $credits[$row['uid']] = array();
				//$credits[$row['uid']][$row['cid']] = sprintf("credit%d=%d", ($row['cid'] + 4), $row['value']);
				$credits[$row['uid']][$row['cid']] = sprintf("credit%d=%d", $setupConfig['db_credit'][$row['cid']], $row['value']);
			}
			$sql = "UPDATE %s SET %s WHERE uid=%d";
			foreach ($credits as $uid => $_credit) {
				if (!$_credit) continue;
				$targetDb->query(sprintf($sql, 'pw_user_data', implode(',', $_credit), $uid));
				$targetDb->query(sprintf($sql, 'pw_windid_user_data', implode(',', $_credit), $uid));
			}
		}
		calculatePercent('SELECT COUNT(*) FROM (SELECT COUNT(*) FROM pw_membercredit GROUP BY uid) AS a', "SELECT COUNT(*) FROM (SELECT COUNT(*) FROM pw_membercredit WHERE uid <= $nextId GROUP BY uid) AS a");
		refreshTo('education');
	} elseif ('education' == $action) {
		// [教育经历]
		$fieldsMap = array(
			'pw_user_education' => array(
				'uid' => 'uid', 
				'educationid' => 'id', 
				'schoolid' => 'schoolid', 
				'educationlevel' => 'degree', 
				'starttime' => 'start_time'));
		$callbacks = array('pw_user_education' => '_callbackEducationTransfer');
		$nextId = transferDataByPk('pw_user_education', $fieldsMap, 'educationid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_user_education', "SELECT COUNT(*) FROM pw_user_education WHERE educationid <= $nextId");
		refreshTo('works');
	} elseif ('works' == $action) {
		// [工作经历]
		$fieldsMap = array(
			'pw_user_work' => array(
				'careerid' => 'id', 
				'uid' => 'uid', 
				'companyid' => 'company', 
				'starttime' => 'starty', 
				1 => 'startm', 
				2 => 'endy', 
				3 => 'endm'));
		$callbacks = array('pw_user_work' => '_callbackWorkTransfer');
		$nextId = transferDataByPk('pw_user_career', $fieldsMap, 'careerid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_user_career', "SELECT COUNT(*) FROM pw_user_career WHERE careerid <= $nextId");
		refreshTo('membertag');
	} elseif ('membertag' == $action) {
		// [convert-membertag]
		$associateFields = array(
			'pw_user_tag' => array(
				'tagid' => 'tag_id',
				'tagname' => 'name',
				'ifhot' => 'ifhot',
				'num' => 'used_count'));
		$callbacks = array('pw_user_tag' => '_callbackMemberTag');
		$nextId = transferDataByPk('pw_membertags', $associateFields, 'tagid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_membertags', "SELECT COUNT(*) FROM pw_membertags WHERE tagid <= $nextId");
		refreshTo('membertagrelation');
	} elseif ('membertagrelation' == $action) {
		// [convert-membertagrelation]
		$associateFields = array(
			'pw_user_tag_relation' => array(
				'tagid' => 'tag_id',
				'userid' => 'uid',
				'crtime' => 'created_time'));
		$nextId = transferData('pw_membertags_relations', $associateFields, $lastId, $limit);
		calculatePercent('SELECT COUNT(*) FROM pw_membertags_relations', $nextId);
		refreshTo('space');
	} elseif ('space' == $action) {
		// [space]
		$fieldsMap = array(
			'pw_space' => array(
				'uid' => 'uid',
				'name' => 'space_name',
				'descript' => 'space_descrip',
				'visits' => 'visit_count',
				'visitors' => 'visitors',
				'tovisitors' => 'tovisitors'));
		$nextId = transferDataByPk('pw_space', $fieldsMap, 'uid', $lastId, $limit);
		calculatePercent('SELECT COUNT(*) FROM pw_space', "SELECT COUNT(*) FROM pw_space WHERE uid <= $nextId");
		refreshTo('blacklist');
	} elseif ('blacklist' == $action) {
		//[convert]黑名单处理
		//TODO [pw_ms_config] 短消息的黑名单设置
		$nextId = $lastId;
		$sql = sprintf("SELECT uid, GROUP_CONCAT(touid) AS touid FROM pw_attention_blacklist WHERE uid > %d GROUP BY uid LIMIT %d", $lastId, $limit);
		$_rt = $srcDb->query($sql);
		$blackList = array();
		while($row = $srcDb->fetch_array($_rt)) {
			$transfers ++;
			$nextId = $row['uid'];
			$_touids = serialize(array_unique(explode(',', $row['touid'])));
			$blackList[] = "({$row['uid']}, '{$_touids}')";
		}
		if ($blackList) {
			$targetDb->query(sprintf("REPLACE INTO pw_windid_user_black (uid, blacklist) VALUES %s", implode(',', $blackList)));
		}

		calculatePercent('SELECT COUNT(*) FROM (SELECT COUNT(*) FROM pw_attention_blacklist GROUP BY uid) AS a', "SELECT COUNT(*) FROM (SELECT COUNT(*) FROM pw_attention_blacklist WHERE uid <= $nextId GROUP BY uid) AS a");
		refreshTo('banusersign');
	} elseif ('banusersign' == $action) {
		// [pw_user_ban]禁止签名转移
		$fieldMap = array(
			'uid' => 'uid',
			//'username' => '',
			'type' => 'typeid',
			'admin' => 'created_userid',
			'reason' => 'reason',
			'time' => 'created_time');
		$nextId = $lastId;
		$sql = sprintf("SELECT * FROM pw_ban WHERE id > %d ORDER BY id LIMIT %d", $lastId, $limit);
		$_rt = $srcDb->query($sql);
		$_data = $_admins = array();
		while($row = $srcDb->fetch_array($_rt)) {
			$transfers ++;
			$nextId = $row['id'];
			if ($row['type'] != 1) continue;
			$tmpData= _transFieldsMap($fieldMap, $row);
			foreach ($tmpData as $_k => $_v) {
				$tmpData[$_k] = $srcDb->escape_string($_v);
			}
			$tmpData['typeid'] = 4;
			$_data[] = _resortData($fieldMap, $tmpData);
			$_admins[] = $row['admin'];
		}
		if ($_data) {
			$_uids = _getUidsByUsernames(array_unique($_admins));
			$_dbData = array();
			foreach ($_data as $_k => $_one) {
				$_one['created_userid'] = intval($_uids[$_one['created_userid']]);
				$_dbData[] = sprintf("('%s')", implode("','", $_one));
			}
			$targetDb->query(sprintf('REPLACE INTO pw_user_ban (%s) VALUES %s', implode(',', $fieldMap), implode(',', $_dbData)));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_ban', "SELECT COUNT(*) FROM pw_ban WHERE id <= $nextId");
		refreshTo('banuserspeak');
	} elseif ('banuserspeak' == $action) {
		// 全局禁止转移
		$fieldMap = array(
			'uid' => 'uid', 
			//'username' => '',
			'type' => 'typeid', 
			'admin' => 'created_userid', 
			'reason' => 'reason', 
			'startdate' => 'created_time', 
			'days' => 'end_time');
		$nextId = $lastId;
		$sql = sprintf("SELECT * FROM pw_banuser WHERE id > %d AND fid=0 ORDER BY id LIMIT %d", $lastId, $limit);
		$_rt = $srcDb->query($sql);
		$_data = $_admins = array();
		while($row = $srcDb->fetch_array($_rt)) {
			$transfers ++;
			$nextId = $row['id'];
			$tmpData= _transFieldsMap($fieldMap, $row);
			$tmpData['end_time'] = $row['days'] ? $row['days'] * 86400 + $row['startdate'] : 0;
			foreach ($tmpData as $_k => $_v) {
				$tmpData[$_k] = $srcDb->escape_string($_v);
			}
			$tmpData['typeid'] = 1;
			$_data[] = _resortData($fieldMap, $tmpData);
			$_admins[] = $row['admin'];
		}
		if ($_data) {
			$_uids = _getUidsByUsernames(array_unique($_admins));
			$_dbData = array();
			foreach ($_data as $_k => $_one) {
				$_one['created_userid'] = intval($_uids[$_one['created_userid']]);
				$_dbData[] = sprintf("('%s')", implode("','", $_one));
			}
			$targetDb->query(sprintf('REPLACE INTO pw_user_ban (%s) VALUES %s', implode(',', $fieldMap), implode(',', $_dbData)));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_banuser WHERE fid=0', "SELECT COUNT(*) FROM pw_banuser WHERE id <= $nextId AND fid=0");
		refreshTo('attention');
	} elseif ('attention' == $action) {
		// [convert-attention]//关注,粉丝
		$associateFields = array(
			'pw_attention' => array(
				'uid' => 'uid',
				'friendid' => 'touid',
				'joindate' => 'created_time'));
		$nextId = transferData('pw_attention', $associateFields, $lastId, $limit);
		calculatePercent('SELECT COUNT(*) FROM pw_attention', $nextId);
		refreshTo('usergroups');
	} elseif ('usergroups' == $action) {
		// [convert-usergroups]
		//原pw_usergroups表数据
		$associateFields = array(
			'pw_user_groups' => array(
				'gid' => 'gid',
				'grouptitle' => 'name',
				'gptype' => 'type',
				'groupimg' => 'image',
				'grouppost' => 'points'));
		$callbacks = array('pw_user_groups' => '_callbackUserGroup');
		$nextId = transferDataByPk('pw_usergroups', $associateFields, 'gid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_usergroups', "SELECT COUNT(*) FROM pw_usergroups WHERE gid <= $nextId");
		refreshTo('forums');
	} elseif ('forums' == $action) {
		//[convert-forums]
		//原pw_forums bbs_forum
		$associateFields = array(
			'pw_bbs_forum' => array(
				'fid' => 'fid', 
				'fup' => 'parentid', 
				'type' => 'type', 
				'ifsub' => 'issub', 
				'childid' => 'hassub', 
				'name' => 'name', 
				'descrip' => 'descrip', 
				'vieworder' => 'vieworder', 
				'forumadmin' => 'manager', 
				'fupadmin' => 'uppermanager', 
				'logo' => 'icon', 
				'logo' => 'logo', 
				'across' => 'across', 
				'showsub' => 'isshowsub', 
				'password' => 'password', 
				'allowvisit' => 'allow_visit', 
				'allowread' => 'allow_read', 
				'allowpost' => 'allow_post', 
				'allowrp' => 'allow_reply', 
				'allowupload' => 'allow_upload', 
				'allowdownload' => 'allow_download'));
		$seo = array();
		$callbacks = array('pw_bbs_forum' => '_callbackForums');
		$nextId = transferDataByPk('pw_forums', $associateFields, 'fid', $lastId, $limit, $callbacks);
		if ($seo) {
			$sql = "REPLACE INTO pw_seo (`mod`,`page`,`param`,`title`,`keywords`,`description`) VALUES %s";
			$targetDb->query(sprintf($sql, implode(',', $seo)));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_forums', "SELECT COUNT(*) FROM pw_forums WHERE fid <= $nextId");
		refreshTo('forumdata');
	} elseif ('forumdata' == $action) {
		// [convert-forumdata]
		//原pw_forumdata表数据
		$associateFields = array(
			'pw_bbs_forum_statistics' => array(
				'fid' => 'fid', 
				'tpost' => 'todayposts', 
				'article' => 'article', 
				'topic' => 'threads', 
				'subtopic' => 'subthreads'));
		$nextId = transferDataByPk('pw_forumdata', $associateFields, 'fid', $lastId, $limit);
		calculatePercent('SELECT COUNT(*) FROM pw_forumdata', "SELECT COUNT(*) FROM pw_forumdata WHERE fid <= $nextId");
		refreshTo('forumextra');
	} elseif ('forumextra' == $action) {
		//[convert-forumextra]
		//forum_extra
		$associateFields = array(
			'pw_bbs_forum_extra' => array(
				'fid' => 'fid', 
				'forumset' => 'settings_basic', 
				'creditset' => 'settings_credit'));
		$callbacks = array('pw_bbs_forum_extra' => '_callbackForumExtra');
		$nextId = transferDataByPk('pw_forumsextra', $associateFields, 'fid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_forumsextra', "SELECT COUNT(*) FROM pw_forumsextra WHERE fid <= $nextId");
		refreshTo('topictype');
	} elseif ('topictype' == $action) {
		//[convert-topictype]
		//主题分类
		$associateFields = array(
			'pw_bbs_topic_type' => array(
				'id' => 'id', 
				'fid' => 'fid', 
				'upid' => 'parentid', 
				'name' => 'name', 
				'vieworder' => 'vieworder', 
				'ifsys' => 'issys', 
				'logo' => 'logo'));
		$nextId = transferDataByPk('pw_topictype', $associateFields, 'id', $lastId, $limit);
		calculatePercent('SELECT COUNT(*) FROM pw_topictype', "SELECT COUNT(*) FROM pw_topictype WHERE id <= $nextId");
		refreshTo('threads');
	} elseif ('threads' == $action) {
		//[convert-threads]升级帖子数据
		$associateFields = array(
			'pw_bbs_threads' => array(
				'tid' => 'tid', 
				'fid' => 'fid', 
				'type' => 'topic_type', 
				'subject' => 'subject', 
				'inspect' => 'inspect', 
				'ifshield' => 'ifshield', 
				'digest' => 'digest', 
				'topped' => 'topped', 
				'ifcheck' => 'ischeck', 
				'replies' => 'replies', 
				'hits' => 'hits', 
				'special' => 'special', 
				'tpcstatus' => 'tpcstatus', 
				'ifupload' => 'ifupload', 
				'postdate' => 'created_time', 
				'author' => 'created_username', 
				'authorid' => 'created_userid', 
				'lastpost' => 'lastpost_time', 
				'lastposter' => 'lastpost_username', 
				'specialsort' => 'special_sort', 
				'topreplays' => 'reply_topped', 
				'titlefont' => 'highlight', 
				1 => 'disabled'), 
			'pw_bbs_threads_index' => array(
				'tid' => 'tid', 
				'fid' => 'fid', 
				'postdate' => 'created_time', 
				'lastpost' => 'lastpost_time',
				1 => 'disabled',
				), 
			'pw_bbs_threads_cate_index' => array(
				'tid' => 'tid', 
				'fid' => 'fid', 
				1 => 'cid', 
				2 => 'disabled', 
				'postdate' => 'created_time', 
				'lastpost' => 'lastpost_time'));
		$collect = array('groupThreadTmp' => array());
		$callbacks = array(
			'pw_bbs_threads' => '_callbackBbsThreads', 
			'pw_bbs_threads_index' => '_callbackBbsThreadsIndex',
			'pw_bbs_threads_cate_index' => '_callbackThreadCateIndex');
		$nextId = transferDataByPk('pw_threads', $associateFields, 'tid', $lastId, $limit, $callbacks);
		if ($collect['groupThreadTmp']) {
			$targetDb->query(sprintf("REPLACE INTO tmp_group_to_thread (`tid`) VALUES %s", implode(',', $collect['groupThreadTmp'])));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_threads', "SELECT COUNT(*) FROM pw_threads WHERE tid <= $nextId");
		refreshTo('tmsgs');
	} elseif ('tmsgs' == $action) {
		$limit = 500 * $_lt;
		// [convert-tmsgs] 分表情况 分表从pw_merge_tmsgs表中读取，
		$associateFields = array(
			'pw_bbs_threads_content' => array(
				'tid' => 'tid', 
				'ifconvert' => 'useubb', 
				'aid' => 'aids', 
				'tags' => 'tags', 
				'content' => 'content',
				'remindinfo' => 'manage_remind',
				'ipfrom' => 'ipfrom',
				'ifsign' => 'usehtml',
				1 => 'sell_count',
			),
			//主题的帖子出售记录
			'pw_bbs_threads_buy' => array(
				1 => 'tid', 
				2 => 'pid', 
				3 => 'created_userid',
				4 => 'created_time', 
				5 => 'ctype', 
				6 => 'cost'));
		$collect = array('ipUpdates' => array(), 'threadBuy' => array());
		$callbacks = array(
			'pw_bbs_threads_content' => '_callbackTmsgs', 
			'pw_bbs_threads_buy' => '_callbackThreadsBuy');
		
		list($_srcTable, $_subT) = getSubTable('pw_tmsgs', $db_tlist);
		$nextId = transferDataByPk($_srcTable, $associateFields, 'tid', $lastId, $limit, $callbacks);
		if ($collect['ipUpdates']) {
			$sql = "UPDATE pw_bbs_threads SET created_ip='%s' WHERE tid=%d";
			foreach ($collect['ipUpdates'] as $_tid => $ip) {
				$targetDb->query(sprintf($sql, $ip, $_tid));
			}
		}
		if ($collect['threadBuy']) {
			$sql = 'INSERT INTO pw_bbs_threads_buy (`tid`, `pid`, `created_userid`, `created_time`, `ctype`, `cost`) VALUES %s';
			$targetDb->query(sprintf($sql, implode(',', $collect['threadBuy'])));
		}
		//分表
		calculatePercent("SELECT COUNT(*) FROM $_srcTable", "SELECT COUNT(*) FROM $_srcTable WHERE tid <= $nextId");
		$db_tlist && $_subTMessage = $_srcTable;
		//分表跳转
		refreshSubTableTo('posts', $db_tlist, $_subT);
	} elseif ('posts' == $action) {
		// [convert-posts] 分表情况 分表从pw_merge_posts读取，如果存在pw_merge_posts表则从表中处理
		$associateFields = array(
			'pw_bbs_posts' => array(
				'pid' => 'pid', 
				'fid' => 'fid', 
				'tid' => 'tid', 
				'ifcheck' => 'ischeck', 
				'ifshield' => 'ifshield', 
				'ifconvert' => 'useubb', 
				'aid' => 'aids', 
				'subject' => 'subject', 
				'content' => 'content', 
				'postdate' => 'created_time', 
				'author' => 'created_username', 
				'authorid' => 'created_userid', 
				'userip' => 'created_ip', 
				'remindinfo' => 'manage_remind',
				'ipfrom' => 'ipfrom',
				'ifsign' => 'usehtml',
				1 => 'disabled',
				2 => 'sell_count'), 
			//回复的出售记录
			'pw_bbs_threads_buy' => array(
				1 => 'tid', 
				2 => 'pid', 
				3 => 'created_userid', 
				4 => 'created_time', 
				5 => 'ctype', 
				6 => 'cost'));
		$collect = array('threadBuy' => array());
		$callbacks = array(
			'pw_bbs_posts' => '_callbackPosts', 
			'pw_bbs_threads_buy' => '_callbackThreadsBuy');
		
		list($_srcTable, $_subT) = getSubTable('pw_posts', $db_plist);
		$nextId = transferDataByPk($_srcTable, $associateFields, 'pid', $lastId, $limit, $callbacks);
		if ($collect['threadBuy']) {
			$sql = 'INSERT INTO pw_bbs_threads_buy (`tid`, `pid`, `created_userid`, `created_time`, `ctype`, `cost`) VALUES %s';
			$targetDb->query(sprintf($sql, implode(',', $collect['threadBuy'])));
		}
		//分表
		calculatePercent("SELECT COUNT(*) FROM $_srcTable", "SELECT COUNT(*) FROM $_srcTable WHERE pid <= $nextId");
		$db_plist && $_subTMessage = $_srcTable;
		//分表跳转
		refreshSubTableTo('truncateGThreadsTmp', $db_plist, $_subT);
	} elseif ('truncateGThreadsTmp' == $action) {
		//转移群组数据到版块 "群组升级"" 分类 "群组话题" 版块
		$data = $targetDb->get_value("SELECT count(*) FROM tmp_group_to_thread");
		if ($data == 0) {
			refreshTo('favors');
		}
		$sql = "INSERT INTO pw_bbs_forum (`parentid`, `type`, `name`, `hassub`, `isshow`, `created_time`) VALUES (0, 'category', '群组升级', 1, 1, $timestamp)";
		$targetDb->query($sql);
		$categoryid = $targetDb->insert_id();
		$sql = "INSERT INTO pw_bbs_forum (`parentid`, `type`, `name`, `isshow`, `newtime`, `created_time`) VALUES ({$categoryid}, 'forum', '群组话题', 1, 3, {$timestamp})";
		$targetDb->query($sql);
		$fid = $targetDb->insert_id();
		$targetDb->query(sprintf("REPLACE INTO tmp_group_to_thread SET tid = 0, cid = %d, fid = %d", $categoryid, $fid));
		refreshTo('truncateGThreadsTmpDo');
	} elseif ('truncateGThreadsTmpDo' == $action) {
		$data = $targetDb->get_one("SELECT * FROM tmp_group_to_thread WHERE tid=0");
		if (!$data) {
			refreshTo('favors');
		}
		$categoryid = $data['cid'];
		$fid = $data['fid'];
		$_article = $_threads = 0;
		$_gtids = array();
		$_query = $targetDb->query(sprintf("SELECT * FROM tmp_group_to_thread WHERE tid > %d ORDER BY tid LIMIT %d", $lastId, $limit));
		while ($row = $srcDb->fetch_array($_query)) {
			$nextId = $row['tid'];
			$transfers ++;
			$_threads ++;
			$_article ++;
			$_gtids[] = $row['tid'];
			if ($_threads == 100) {
				$_gtids = implode(',', $_gtids);
				$targetDb->query(sprintf("UPDATE pw_bbs_threads SET fid= %d WHERE tid IN (%s)", $fid, $_gtids));
				$targetDb->query(sprintf("UPDATE pw_bbs_posts SET fid=%d WHERE tid IN (%s)", $fid, $_gtids));
				$targetDb->query(sprintf("UPDATE pw_bbs_threads_index SET fid=%d WHERE tid IN (%s)", $fid, $_gtids));
				$_article += $targetDb->affected_rows();
				$_gtids = array();
			}
		}
		if ($_gtids) {
			$_gtids = implode(',', $_gtids);
			$targetDb->query(sprintf("UPDATE pw_bbs_threads SET fid= %d WHERE tid IN (%s)", $fid, $_gtids));
			$targetDb->query(sprintf("UPDATE pw_bbs_posts SET fid=%d WHERE tid IN (%s)", $fid, $_gtids));
			$targetDb->query(sprintf("UPDATE pw_bbs_threads_index SET fid=%d WHERE tid IN (%s)", $fid, $_gtids));
			$_article += $targetDb->affected_rows();
		}
		
		$sql = sprintf("REPLACE INTO pw_bbs_forum_statistics (fid,article,threads) VALUES (%d, %d, %d)", $fid, $_article, $_threads);
		$targetDb->query($sql);
		$_total = $targetDb->get_value('SELECT COUNT(*) FROM tmp_group_to_thread');
		$_current = $targetDb->get_value("SELECT COUNT(*) FROM tmp_group_to_thread WHERE tid <= $nextId");
		calculatePercent(intval($_total), intval($_current));
		refreshTo('favors');
	} elseif ('favors' == $action) {
		//[收藏转喜欢]
		$nextId = $lastId;
		$_query = $srcDb->query(sprintf("SELECT * FROM pw_collection WHERE type='postfavor' AND id > %d  ORDER BY id LIMIT %d", $lastId, $limit));
		$_threads = $_users = $_likes = $_likeLogs = array();
		while ($row = $srcDb->fetch_array($_query)) {
			$nextId = $row['id'];
			$transfers ++;
			//排除自己收藏自己的帖子
			$_content = unserialize($row['content']);
			if ($_content['uid'] == $row['uid']) continue;
				
			if (!isset($_likes[$row['typeid']])) {
				$_likes[$row['typeid']] = array(
					'typeid' => 1,
					'fromid' => $row['typeid'],
					'isspecial' => 0,
					'users' => array($row['uid']));
				$_threads[$row['typeid']] = 1;
				$_likeLogs[$row['typeid']] = array();
			} else {
				array_unshift($_likes[$row['typeid']]['users'], $row['uid']);
				$_threads[$row['typeid']]++;
			}
				
			$_likeLogs[$row['typeid']][$row['uid']] = array(
				'uid' => $row['uid'],
				'likeid' => 0,
				'created_time' => $row['postdate']);
			if (!isset($_users[$row['uid']])) {
				$_users[$row['uid']] = 1;
			} else {
				$_users[$row['uid']]++;
			}
		}
		if ($_likes) {
			//更新帖子的被喜欢次数
			$_query = $targetDb->query(sprintf("SELECT * FROM pw_bbs_threads WHERE tid IN (%s)", implode(',', array_keys($_likes))));
			while ($row = $targetDb->fetch_array($_query)) {
				$_likes[$row['tid']]['isspecial'] = $row['special'] > 1 ? 1 : 0;
				$_threads[$row['tid']] += $row['like_count'];
			}
			foreach ($_threads as $_tid => $_likeCount) {
				$targetDb->query(sprintf("UPDATE pw_bbs_threads SET like_count = %d WHERE tid = %d", $_likeCount, $_tid));
			}
			//更新喜欢的记录
			$_likeContents = $targetDb->get_all(sprintf("SELECT * FROM pw_like_content WHERE typeid = 1 AND fromid IN (%s)", implode(',', array_keys($_likes))), MYSQL_ASSOC, 'fromid');
			foreach ($_likes as $_tid => $_content) {
				$_likeid = 0;
				if (isset($_likeContents[$_tid])) {
					//已有喜欢项则更新最新的喜欢用户
					$likeid = $_likeContents['likeid'];
					if (count($_content['uid']) < 10) {
						$_tmp = explode(',', $_likeContents[$_tid]['users']);
						$_content['users'] = $_content['users'] + $_tmp;
					}
					$_user = array_splice($_content['users'], 0, 10);
					$targetDb->query(sprintf("UPDATE pw_like_content SET users = '%s' WHERE fromid=%d AND typeid=1", implode(',', $_user), $_tid));
				} else {
					//没有喜欢项则添加
					$_content['users'] = array_splice($_content['users'], 0, 10);
					$_content['users'] = implode(',', $_content['users']);
					$targetDb->query(sprintf("INSERT INTO pw_like_content (`typeid`,`fromid`,`isspecial`,`users`) VALUES ('%s')", implode("','", $_content)));
					$_likeid = $targetDb->insert_id();
				}
				$_logs = array();
				foreach ($_likeLogs[$_tid] as $_uid => $_log) {
					$_log['likeid'] = $_likeid;
					$_logs[] = implode(',', $_log);
				}
				$_logs && $targetDb->query(sprintf("INSERT INTO pw_like_log (`uid`,`likeid`,`created_time`) VALUES (%s)", implode('),(', $_logs)));
			}
			//更新用户的喜欢次数
			$_oldUids = $targetDb->get_all(sprintf("SELECT * FROM pw_user_data WHERE uid IN (%s)", implode(',', array_keys($_users))), MYSQL_ASSOC, 'uid');
			foreach ($_users as $_uid => $_count) {
				$_count += $_oldUids[$_uid]['likes'];
				$targetDb->query(sprintf("UPDATE pw_user_data SET likes =%d WHERE uid = %d", $_count, $_uid));
			}
		}
		calculatePercent("SELECT COUNT(*) FROM pw_collection WHERE type='postfavor'", "SELECT COUNT(*) FROM pw_collection WHERE type='postfavor' AND id <= $nextId");
		refreshTo('deletedthreads');
	} elseif ('deletedthreads' == $action) {
		// [convert-deletedthreads]
		$nextId = $lastId;
		$limit = $_lt * 500;
		$sql = "SELECT * FROM pw_recycle LIMIT $lastId,$limit";
		$query = $srcDb->query($sql);
		$posts = $threads = array();
		while ($row = $srcDb->fetch_array($query)) {
			if ($row['pid']) {
				$posts[$row['fid']][] = $row['pid'];
			} else {
				$threads[$row['fid']][] = $row['tid'];
			}
			$nextId ++;
			$transfers ++;
		}
		if ($posts) {
			foreach ($posts as $k => $v) {
				$targetDb->query(sprintf("UPDATE pw_bbs_posts SET tid=%d WHERE pid IN (%s)", $k, implode(',', $v)));
			}
		}
		if ($threads) {
			foreach ($threads as $k => $v) {
				$targetDb->query(sprintf("UPDATE pw_bbs_threads SET fid=%d WHERE tid IN (%s)", $k, implode(',', $v)));
			}
		}
		calculatePercent('SELECT COUNT(*) FROM pw_recycle', $nextId);
		refreshTo('recycle');
	} elseif ('recycle' == $action) {
		// [convert-recycle]
		$associateFields = array(
			'pw_recycle_topic' => array(
				'fid' => 'fid',
				'tid' => 'tid',
				'deltime' => 'operate_time',
				'admin' => 'operate_username'),
			'pw_recycle_reply' => array(
				'fid' => 'fid',
				'tid' => 'tid',
				'pid' => 'pid',
				'deltime' => 'operate_time',
				'admin' => 'operate_username'));
		$callbacks = array(
			'pw_recycle_topic' => '_callbackRecycleTopic',
			'pw_recycle_reply' => '_callbackRecycleReply');
		$nextId = transferData('pw_recycle', $associateFields, $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_recycle', $nextId);
		refreshTo('digestindex');
	} elseif ('digestindex' == $action) {
		// [convert-digestindex]//精华帖索引表
		$limit *= 2;
		$fMap = _getForumTypeMaps();
		$nextId = $lastId;
		$data = array();
		$sql = sprintf("SELECT * FROM pw_threads WHERE tid > %d ORDER BY tid ASC LIMIT %d", $lastId, $limit);
		$query = $srcDb->query($sql);
		while ($row = $srcDb->fetch_array($query)) {
			$nextId = $row['tid'];
			$transfers ++;
			if ($row['digest'] <= 0) continue;
			$data[] = sprintf("(%d,%d,%d,%d,%d,%d)", $row['tid'], $row['fid'], $fMap[$row['fid']], $row['type'], $row['postdate'], $row['lastpost']);
		}
		$data && $targetDb->query(sprintf('REPLACE INTO pw_bbs_threads_digest_index (tid,fid,cid,topic_type,created_time,lastpost_time) VALUES %s', implode(',', $data)));
		calculatePercent('SELECT COUNT(*) FROM pw_threads', "SELECT COUNT(*) FROM pw_threads WHERE tid <= $nextId");
		refreshTo('vote');
	} elseif ('vote' == $action) {
		// [convert-vote] 投票贴转换
		$fieldsMap = array(
			'pw_app_poll' => array(
				'pollid' => 'poll_id',
				//  'modifiable' => '',//允许修改投票结果
				'previewable' => 'isafter_view',  //投票后才可见结果
				//  'multiple' => '',//是否多选开关
				'mostvotes' => 'option_limit',  //允许选择的项目
				'voters' => 'voter_num',  //多少人投票
				'regdatelimit' => 'regtime_limit',  //允许投票的用户注册日期限制
				'timelimit' => 'expired_time',  //单位天
				1 => 'isinclude_img',  //缺省0
				2 => 'app_type',  //缺省0
				//  'tid' => '',//[]需要从对应的帖子中获取发帖者/发帖时间
				//  'voteopts' => '',//需要转化到 pw_app_poll_option表中
			),
		);
		$collect = array('voteopts' => array(), 'pollTids' => array(), 'expired_time' => array());
		$callbacks = array('pw_app_poll' => '_callbackThreadPoll');
		$nextId = transferDataByPk('pw_polls', $fieldsMap, 'pollid', $lastId, $limit, $callbacks);
		//更新投票的发表者及时间及投票和帖子的关系表数据
		if ($collect['pollTids']) {
			$pollThread = array();
			$sql = sprintf("SELECT tid,postdate,authorid FROM pw_threads WHERE tid IN (%s)", implode(',', array_keys($collect['pollTids'])));
			$threadInfo = $srcDb->get_all($sql, MYSQL_ASSOC, 'tid');
			$sql = "UPDATE pw_app_poll SET created_time=%d, created_userid=%d, `expired_time`=%d WHERE poll_id=%d";
			foreach ($threadInfo as $_tid => $_one) {
				$_tmpExp = $collect['expired_time'][$collect['pollTids'][$_tid]];
				$_tmpExp = $_tmpExp ? ($_tmpExp + $_one['postdate']) : 0;
				$targetDb->query(sprintf($sql, $_one['postdate'], $_one['authorid'], $_tmpExp, $collect['pollTids'][$_tid]));
				$pollThread[] = sprintf("(%d, %d, %d)", $_tid, $collect['pollTids'][$_tid], $_one['authorid']);
			}
			$pollThread && $targetDb->query(sprintf("REPLACE INTO pw_app_poll_thread (tid, poll_id, created_userid) VALUES %s", implode(',', $pollThread)));
		}
		//投票帖子的投票项处理
		if ($collect['voteopts']) {
			$_rt = $srcDb->query(sprintf("SELECT * FROM pw_voter WHERE tid IN (%s)", implode(',', array_keys($collect['voteopts']))));
			$voterList = array();
			while ($one = $srcDb->fetch_array($_rt)) {
				if (!isset($voterList[$one['tid']][$one['vote']])) $voterList[$one['tid']][$one['vote']] = array();
				$voterList[$one['tid']][$one['vote']][] = array(
					$one['uid'],
					$collect['pollTids'][$one['tid']],
					$one['vote'],
					$one['time']);
			}
			$_voterData = array();
			$optSql = "INSERT INTO pw_app_poll_option (`poll_id`, `voted_num`, `content`) VALUES (%d,%d,'%s')";
			foreach ($collect['voteopts'] as $_tid => $opts) {
				$_voterList = isset($voterList[$_tid]) ? $voterList[$_tid] : array();
				$poll_id = $collect['pollTids'][$_tid];
				foreach ($opts as $key => $value) {
					$targetDb->query(sprintf($optSql, $poll_id, $value[1], $targetDb->escape_string(unescapeStr($value[0]))));
					if (!isset($_voterList[$key])) continue;
					$optId = $targetDb->insert_id();
					foreach ($_voterList[$key] as $_k => $_v) {
						$_v[2] = $optId;
						$_voterData[] = sprintf("('%s')", implode("','", $_v));
					}
				}
			}
			if ($_voterData) {
				//插入投票记录pw_app_poll_voter
				$sql = "REPLACE INTO pw_app_poll_voter (`uid`, `poll_id`, `option_id`, `created_time`) VALUES %s";
				$targetDb->query(sprintf($sql, implode(",", $_voterData)));
			}
		}
		calculatePercent('SELECT COUNT(*) FROM pw_polls', "SELECT COUNT(*) FROM pw_polls WHERE pollid <= $nextId");
		refreshTo('tags');
	} elseif ('tags' == $action) {
		// [convert-tags]
		$associateFields = array(
			'pw_tag' => array(
				'tagid' => 'tag_id',
				'tagname' => 'tag_name',
				'ifhot' => 'ifhot',
				'num' => 'content_count'));
		$callbacks = array('pw_tag' => '_callbackTag');
		$nextId = transferDataByPk('pw_tags', $associateFields, 'tagid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_tags', "SELECT COUNT(*) FROM pw_tags WHERE tagid <= $nextId");
		refreshTo('tagdata');
	} elseif ('tagdata' == $action) {
		// [convert-tagdata]
		$associateFields = array(
			'pw_tag_relation' => array(
				'tagid' => 'tag_id',
				'1' => 'content_tag_id',
				'2' => 'type_id',
				'tid' => 'param_id'));
		$callbacks = array('pw_tag_relation' => '_callbackTagdata');
		$nextId = transferData('pw_tagdata', $associateFields, $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_tagdata', $nextId);
		refreshTo('topped');
	} elseif ('topped' == $action) {
		// [convert-topped]
		$limit = 500 * $_lt;
		$nextId = $lastId;
		$query = $srcDb->query("SELECT * FROM pw_poststopped LIMIT $lastId, $limit");
		$tmpTids = $tops = $postTops = array();
		while ($row = $srcDb->fetch_array($query)) {
			if ($row['pid'] > 0) {
				$postTops[$row['pid']] = array(
					'pid' => $row['pid'],
					'tid' => $row['tid'],
					'floor' => $row['floor'],
					'created_time' => $row['uptime'],
					'created_userid' => 0);
			} else {
				$tops[] = array(
					'fid' => $row['fid'],
					'tid' => $row['tid'],
					'sort_type' => "'topped'",
					'created_time' => $row['uptime'],
					//	'created_time' => $timestamp,
					'end_time' => $row['overtime']);
				$tmpTids[] = $row['tid'];
			}
			$nextId ++;
			$transfers++;
		}
		//帖子排序表更新
		if ($tmpTids) {
			$tidTopped = array();
			$tmpTids = array_unique($tmpTids);
			$query = $srcDb->query(sprintf("SELECT tid, topped FROM pw_threads WHERE tid IN (%s)", implode(',', $tmpTids)));
			while ($row = $srcDb->fetch_array($query)) {
				$tidTopped[$row['tid']] = $row['topped'];
			}
			foreach ($tops as $k => $v) {
				$v['extra'] = $tidTopped[$v['tid']] ? $tidTopped[$v['tid']] : 0;
				$tops[$k] = implode(',', $v);
			}
			$tops && $targetDb->query(sprintf("REPLACE INTO pw_bbs_threads_sort (fid,tid,sort_type,created_time,end_time,extra) VALUES (%s)", implode('),(', $tops)));
		}
		//贴内置顶数据转移
		if ($postTops) {
			$_query = $targetDb->query(sprintf("SELECT * FROM pw_bbs_posts WHERE pid IN (%s)", implode(',', array_keys($postTops))));
			$_admins = array();
			while ($row = $srcDb->fetch_array($_query)) {
				$_tmp = explode("\t", $row['manage_remind']);
				$_admins[$row['pid']] = $_tmp[count($_tmp) - 2];
			}
			$_usernames = $srcDb->get_all(sprintf("SELECT * FROM pw_members WHERE username IN ('%s')", implode("','", array_unique($_admins))), MYSQL_ASSOC, 'username');
			foreach ($postTops as $k => $v) {
				if (isset($_admins[$k])) {
					$_username = $_admins[$k];
					$v['created_userid'] = intval($_usernames[$_username]['uid']);
				}
				$postTops[$k] = implode(',', $v);
			}
			$targetDb->query(sprintf("REPLACE INTO pw_bbs_posts_topped (pid,tid,floor,created_time,created_userid) VALUES (%s)", implode('),(', $postTops)));
		}

		calculatePercent('SELECT COUNT(*) FROM pw_poststopped', $nextId);
		refreshTo('attachs');
	} elseif ('attachs' == $action) {
		// [convert-attachs]
		$associateFields = array(
			'pw_attachs' => array(
				'aid' => 'aid', 
				'name' => 'name', 
				'type' => 'type', 
				'size' => 'size', 
				'attachurl' => 'path', 
				'ifthumb' => 'ifthumb', 
				'uid' => 'created_userid', 
				'uploadtime' => 'created_time', 
				'descrip' => 'descrip'), 
			'pw_attachs_thread' => array(
				'aid' => 'aid', 
				'fid' => 'fid', 
				'tid' => 'tid', 
				'pid' => 'pid', 
				'name' => 'name', 
				'type' => 'type', 
				'size' => 'size', 
				'hits' => 'hits', 
				'attachurl' => 'path', 
				'ifthumb' => 'ifthumb', 
				'special' => 'special', 
				'needrvrc' => 'cost', 
				'ctype' => 'ctype', 
				'uid' => 'created_userid', 
				'uploadtime' => 'created_time', 
				'descrip' => 'descrip'));
		$callbacks = array('pw_attachs_thread' => '_callbackAttachsThread');
		$nextId = transferDataByPk('pw_attachs', $associateFields, 'aid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_attachs', "SELECT COUNT(*) FROM pw_attachs WHERE aid <= $nextId");
		refreshTo('attachsThreadBuy');
	} elseif ('attachsThreadBuy' == $action) {
		//附件购买
		$fieldMap = array(
			'pw_attachs_thread_buy' => array(
				'aid' => 'aid',
				'uid' => 'created_userid',
				'ctype' => 'ctype',
				'cost' => 'cost',
				'createdtime' => 'created_time'));
		$callbacks = array('pw_attachs_thread_buy' => '_callbackAttachThreadBuy');
		$nextId = transferData('pw_attachbuy', $fieldMap, $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_attachbuy', $nextId);
		refreshTo('attachsThreadDownload');
	} elseif ('attachsThreadDownload' == $action) {
		//下载记录
		$fieldMap = array(
			'pw_attachs_thread_download' => array(
				'aid' => 'aid',
				'uid' => 'created_userid',
				'ctype' => 'ctype',
				'cost' => 'cost',
				'createdtime' => 'created_time'));
		$callbacks = array('pw_attachs_thread_download' => '_callbackAttachThreadBuy');
		$nextId = transferData('pw_attachdownload', $fieldMap, $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_attachdownload', $nextId);
		refreshTo('messages');
	} elseif ('messages' == $action) {
		$limit = 300;
		$sql = "SELECT m.mid, m.create_uid, r.uid, m.content, m.created_time FROM pw_ms_relations AS r LEFT JOIN pw_ms_messages AS m ON ( r.mid = m.mid ) WHERE m.mid> $lastId AND r.isown =0 AND categoryid=1 AND r.typeid =100 ORDER BY m.mid ASC LIMIT $limit";
		$query = $srcDb->query($sql);
		$data = array();
		$length = 0;
		while ($row = $srcDb->fetch_array($query)) {
			$nextId = $row['mid'];
			$transfers++;
			$content = $srcDb->escape_string($row['content']);
			$_tmp = "({$row['mid']},{$row['create_uid']},{$row['uid']},'$content',{$row['created_time']})";
			$_tmpL = strlen($_tmp);
			if (($length + $_tmpL) > MAX_PACKAGE) {
				$targetDb->query(sprintf("REPLACE INTO pw_windid_message (message_id,from_uid,to_uid,content,created_time) VALUES %s", implode(',', $data)));
				$length = 0;
				$data = array();
			}
			$length += $_tmpL;
			$data[] = $_tmp;
		}
		$data && $targetDb->query(sprintf("REPLACE INTO pw_windid_message (message_id,from_uid,to_uid,content,created_time) VALUES %s", implode(',', $data)));
		refreshTo('msgdialogs');
	} elseif ('msgdialogs' == $action) {
		$limit = 500;
		$nextId = $lastId;
		$sql = "SELECT max( message_id ) AS max_message_id, count( message_id ) AS message_count, from_uid, to_uid, content, created_time FROM `pw_windid_message` GROUP BY from_uid, to_uid LIMIT $lastId, $limit";
		$query = $targetDb->query($sql);
		$tmpMessageIds = $tmpUids = $data = $keys = array();
		while ($row = $targetDb->fetch_array($query)) {
			$tmpMessageIds[] = $row['max_message_id'];
			$tmpUids[] = $row['from_uid'];
			$tmpUids[] = $row['to_uid'];
			$key = "{$row['from_uid']}_{$row['to_uid']}";
			$keys[$row['max_message_id']][] = $key;
			$data[$key] = array(
				'from_uid' => $row['from_uid'],
				'to_uid' => $row['to_uid'],
				'message_count' => $row['message_count'],
			);
			$key = "{$row['to_uid']}_{$row['from_uid']}";
			$keys[$row['max_message_id']][] = $key;
			$data[$key] = array(
				'from_uid' => $row['to_uid'],
				'to_uid' => $row['from_uid'],
				'message_count' => $row['message_count'],
			);
			$nextId++;
			$transfers++;
		}
		if ($tmpUids) {
			$tmpUids = array_unique($tmpUids);
			$users = _getUserNamesByUids($tmpUids);
		}
		if ($tmpMessageIds) {
			$sql = sprintf("SELECT message_id,content,created_time FROM pw_windid_message WHERE message_id IN (%s)",implode(',', $tmpMessageIds));
			$query = $targetDb->query($sql);
			while ($row = $targetDb->fetch_array($query)) {
				foreach ($keys[$row['message_id']] as $v) {
					list($fromUid,$toUid) = explode('_', $v);
					$data[$v] += array(
						'last_message' => $targetDb->escape_string(serialize(array(
							'from_uid' => $fromUid,
							'to_uid'   => $toUid,
							'from_username' => $users[$fromUid],
							'to_username' => $users[$toUid],
							'content'	=> $row['content']
						))),
						'modified_time' => $row['created_time']
					);
				}
			}
			foreach ($data as $k=>$v) {
				$data[$k] = sprintf("%d,%d,%d,'%s',%d",$v['from_uid'],$v['to_uid'],$v['message_count'],$v['last_message'],$v['modified_time']);
			}
			$data && $targetDb->query(sprintf("REPLACE INTO pw_windid_message_dialog (from_uid,to_uid,message_count,last_message,modified_time) VALUES (%s)", implode('),(', $data)));
		}
		refreshTo('msgrelations');
	} elseif ('msgrelations' == $action) {
		$limit = 500;
		$nextId = $lastId;
		$sql = "SELECT m.message_id,d.dialog_id FROM pw_windid_message AS m RIGHT JOIN pw_windid_message_dialog AS d ON (m.from_uid=d.from_uid AND m.to_uid=d.to_uid) WHERE m.message_id>$nextId ORDER BY m.message_id LIMIT $limit";
		$query = $targetDb->query($sql);
		$data = array();
		while ($row = $targetDb->fetch_array($query)) {
			$data[] = "{$row['dialog_id']},{$row['message_id']},1";
		}
		$sql = "SELECT m.message_id,d.dialog_id FROM pw_windid_message AS m RIGHT JOIN pw_windid_message_dialog AS d ON (m.from_uid=d.to_uid AND m.to_uid=d.from_uid) WHERE m.message_id>$nextId ORDER BY m.message_id LIMIT $limit";
		$query = $targetDb->query($sql);
		while ($row = $targetDb->fetch_array($query)) {
			$nextId = $row['message_id'];
			$transfers++;
			$data[] = "{$row['dialog_id']},{$row['message_id']},1";
		}
		$data && $targetDb->query(sprintf("INSERT INTO pw_windid_message_relation (dialog_id,message_id,is_read) VALUES (%s)", implode('),(', $data)));
		refreshTo('weibo');
	} elseif ('weibo' == $action) {
		$fieldsMap = array(
			'pw_weibo' => array(
				'mid' => 'weibo_id',
				'uid' => 'created_userid',
				'username' => 'created_username',
				'content' => 'content',
				'type' => 'type',
				'replies' => 'comments',
				'postdate' => 'created_time'));
		$collect = array('weiboTmp' => array(), 'weiboFresh' => array());
		$callbacks = array('pw_weibo' => '_callbackWeibo');
		$nextId = transferDataByPk('pw_weibo_content', $fieldsMap, 'mid', $lastId, $limit, $callbacks);
		//添加临时数据
		if ($collect['weiboTmp']) {
			$targetDb->query(sprintf("REPLACE INTO tmp_weibo (weibo_id) VALUES %s", implode(',', $collect['weiboTmp'])));
		}
		//产生新鲜事
		if ($collect['weiboFresh']) {
			_callbackFresh($collect['weiboFresh']);
		}
		calculatePercent('SELECT COUNT(*) FROM pw_weibo_content', "SELECT COUNT(*) FROM pw_weibo_content WHERE mid <= $nextId");
		refreshTo('weiboComment');
	} elseif ('weiboComment' == $action) {
		//pw_weibo_comment微博评论转移
		$fieldsMap = array(
			'cid' => 'comment_id',
			'uid' => 'created_userid',
			'mid' => 'weibo_id',
			1 => 'created_username',
			'content' => 'content',
			'postdate' => 'created_time');
		$nextId = $lastId;
		$sql = sprintf("SELECT * FROM pw_weibo_comment WHERE cid > %d ORDER BY cid LIMIT %d", $lastId, $limit);
		$_rt = $srcDb->query($sql);
		$data = $uids = array();
		while($row = $srcDb->fetch_array($_rt)) {
			$nextId = $row['cid'];
			$transfers ++;
			$one = $targetDb->get_one(sprintf("SELECT * FROM tmp_weibo WHERE weibo_id=%d", $row['mid']));
			if (!$one) continue;
			$tmpData = _transFieldsMap($fieldsMap, $row);
			foreach ($tmpData as $_k => $_v) {
				$tmpData[$_k] = $targetDb->escape_string(unescapeStr($_v));
			}
			$data[] = $tmpData;
			$uids[] = $row['uid'];
		}
		if ($data) {
			$usernames = _getUserNamesByUids(array_unique($uids));
			$_data = array();
			$length = 0;
			foreach ($data as $_k => $_one) {
				$_one['created_username'] = $targetDb->escape_string($usernames[$_one['created_userid']]);
				$_tmp = sprintf("('%s')", implode("','", $_one));
				$_tmpL = strlen($_tmp);
				if (($_tmpL + $length) > MAX_PACKAGE) {
					$targetDb->query(sprintf('REPLACE INTO pw_weibo_comment (%s) VALUES %s', implode(',', $fieldsMap), implode(',', $_data)));
					$length = 0;
					$_data[] = array();
				}
				$length += $_tmpL;
				$_data[] = $_tmp;
			}
			$_data && $targetDb->query(sprintf('REPLACE INTO pw_weibo_comment (%s) VALUES %s', implode(',', $fieldsMap), implode(',', $_data)));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_weibo_comment', "SELECT COUNT(*) FROM pw_weibo_comment WHERE cid <= $nextId");
		refreshTo('fresh');
	} elseif ('fresh' == $action) {
		$nextId = $lastId;
		$_uids = $srcDb->get_all(sprintf("SELECT authorid FROM pw_threads FORCE INDEX (idx_authorid) WHERE authorid > %d AND fid > 0 AND ifcheck=1 GROUP BY authorid ORDER BY authorid LIMIT %d", $lastId, $limit));
		$_data = array();
		foreach ($_uids as $_uid) {
			$nextId = $_uid['authorid'];
			$transfers ++;
			$sql = sprintf("SELECT tid, postdate FROM pw_threads WHERE authorid = %d AND fid > 0 AND ifcheck=1 ORDER BY tid DESC LIMIT 20", $_uid['authorid']);
			$_rt = $srcDb->query($sql);
			while ($row = $srcDb->fetch_array($_rt)) {
				$_t = array(
					'type' => 1,
					'src_id' => $row['tid'],
					'created_userid' => $_uid['authorid'],
					'created_time' => $row['postdate']);
				$_data[] = sprintf("('%s')", implode("','", $_t));
			}
		}
		if ($_data) {
			_callbackFresh($_data);
		}
		calculatePercent('SELECT COUNT(*) FROM (SELECT COUNT(*) FROM pw_threads FORCE INDEX (idx_authorid) WHERE fid > 0 AND ifcheck=1 GROUP BY authorid) AS A', "SELECT COUNT(*) FROM (SELECT COUNT(*) FROM pw_threads FORCE INDEX (idx_authorid) WHERE authorid <= $nextId AND fid > 0 AND ifcheck=1 GROUP BY authorid) AS A");
		refreshTo('announce');
	} elseif ('announce' == $action) {
		//[convert-announce]
		$associateFields = array(
			'pw_announce' => array(
				'aid' => 'aid',
				'vieworder' => 'vieworder',
				'author' => 'created_userid',
				'1' => 'typeid',
				'url' => 'url',
				'subject' => 'subject',
				'content' => 'content',
				'startdate' => 'start_date',
				'enddate' => 'end_date'));
		$callbacks = array('pw_announce' => '_callbackAnnounce');
		$nextId = transferDataByPk('pw_announce', $associateFields, 'aid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_announce', "SELECT COUNT(*) FROM pw_announce WHERE aid <= $nextId");
		refreshTo('link');
	} elseif ('link' == $action) {
		//[友情链接]
		$fieldMap = array(
			'pw_link' => array(
				'sid' => 'lid',
				'threadorder' => 'vieworder',
				'name' => 'name',
				'url' => 'url',
				'descrip' => 'descrip',
				'logo' => 'logo',
				'ifcheck' => 'ifcheck',
				'username' => 'contact'));
		$callbacks = array('pw_link' => '_callbackLink');
		$nextId = transferDataByPk('pw_sharelinks', $fieldMap, 'sid', $lastId, $limit, $callbacks);
		calculatePercent('SELECT COUNT(*) FROM pw_sharelinks', "SELECT COUNT(*) FROM pw_sharelinks WHERE sid <= $nextId");
		refreshTo('linkRelations');
	} elseif ('linkRelations' == $action) {
		//[友情链接与类别的关联表]
		$fieldMap = array(
			'pw_link_relations' => array(
				'sid' => 'lid',
				'stid' => 'typeid'));
		$nextId = transferData('pw_sharelinksrelation', $fieldMap, $lastId, $limit);
		calculatePercent('SELECT COUNT(*) FROM pw_sharelinksrelation', $nextId);
		refreshTo('linkType');
	} elseif ('linkType' == $action) {
		//[友情链接类别]
		$fieldMap = array(
			'pw_link_type' => array(
				'stid' => 'typeid',
				'name' => 'typename',
				'vieworder' => 'vieworder'));
		$nextId = transferDataByPk('pw_sharelinkstype', $fieldMap, 'stid', $lastId, $limit);
		calculatePercent('SELECT COUNT(*) FROM pw_sharelinkstype', "SELECT COUNT(*) FROM pw_sharelinkstype WHERE stid <= $nextId");
		refreshTo('emotion');
	} elseif ('emotion' == $action) {
		//[表情转移]
		$nextId = $lastId;
		$_query = $srcDb->query(sprintf("SELECT * FROM pw_smiles WHERE id > %d ORDER BY id LIMIT %d", $lastId, $limit));
		$_emotions = $_categorys = $_search = array();
		while ($row = $srcDb->fetch_array($_query)) {
			$nextId= $row['id'];
			$transfers++;
			if ($row['type'] <= 0) {
				$_tmp = array(
					'category_id' => $row['id'],
					'category_name' => $row['name'],
					'emotion_folder' => $row['path'],
					'emotion_apps' => 'bbs|weibo|cms|face',
					'orderid' => $row['vieworder'],
					'isopen' => $row['type'] < 0 ? 0 : 1,
				);
				$_categorys[] = "('" . implode("','", $_tmp) . "')";
			} else {
				$_emotions[$row['id']] = array(
					'emotion_id' => $row['id'],
					'emotion_icon' => $row['path'],
					'vieworder' => $row['vieworder'],
					'category_id' => $row['type'],
					'isused' => 1,
					'emotion_folder' => '');
				$_search[] = $row['type'];
			}
		}
		$_categorys && $targetDb->query(sprintf("REPLACE INTO pw_common_emotion_category (`category_id`,`category_name`,`emotion_folder`,`emotion_apps`,`orderid`,`isopen`) VALUES %s", implode(',', $_categorys)));
	
		$_result = array();
		if ($_search) {
			$_result = $srcDb->get_all(sprintf('SELECT * FROM pw_smiles WHERE id IN (%s)', implode(',', $_search)), MYSQL_ASSOC, 'id');
		}
		foreach ($_emotions as $_k => $_item) {
			if (isset($_result[$_item['category_id']])) {
				$_item['emotion_folder'] = $_result[$_item['category_id']]['path'];
			}
			$_emotions[$_k] = "('" . implode("','", $_item) . "')";
		}
		$_emotions && $targetDb->query(sprintf('REPLACE INTO pw_common_emotion (`emotion_id`,`emotion_icon`,`vieworder`,`category_id`,`isused`,`emotion_folder`) VALUES %s', implode(',', $_emotions)));

		calculatePercent('SELECT COUNT(*) FROM pw_smiles', "SELECT COUNT(*) FROM pw_smiles WHERE id <= $nextId");
		refreshTo('task');
	} elseif ('task' == $action) {
		// [convert-task]任务转换
		//任务表
		$fieldMap = array(
			'pw_task' => array(
				'id' => 'taskid',  //任务ID
				'title' => 'title',  //任务标题
				'description' => 'description',  //任务描述
				'icon' => 'icon',  //任务图标
				'starttime' => 'start_time',  //任务开始时间
				'endtime' => 'end_time',  //任务结束时间
				'period' => 'period',  //任务周期[小时]
				'reward' => 'reward',  //任务奖励
				'sequence' => 'view_order',  //顺序
				'usergroup' => 'user_groups',  //可申请用户组
				'prepose' => 'pre_task',  //前置任务
			//  'number' => 'number',//申请人数限制
			//  'member' => 'member',//
				'auto' => 'is_auto',  //符合条件自动申请
			//  'finish' => 'finish',//完成限制,是否允许放弃
				'display' => 'is_display_all',  //显示设置
			//  'type' => 'type',
	 		//	'job' => 'job',//类型 
				'factor' => 'conditions',  //完成任务条件
				'isopen' => 'is_open'		//启用
			// 'isuserguide' => 'isuserguide',//表明该任务是否是用户引导类型
			),
		);
		$collect = array('taskGroups' => array(), 'taskTmp' => array());
		$callbacks = array('pw_task' => '_callbackTask');
		$nextId = transferDataByPk('pw_job', $fieldMap, 'id', $lastId, $limit, $callbacks);
		if ($collect['taskGroups']) {
			$targetDb->query(sprintf('REPLACE INTO pw_task_group (`taskid`, `gid`, `is_auto`, `end_time`) VALUES %s', implode(',', $collect['taskGroups'])));
		}
		if ($collect['taskTmp']) {
			$targetDb->query(sprintf("REPLACE INTO tmp_task (`task_id`, `extends`) VALUES %s", implode(',', $collect['taskTmp'])));
		}
		calculatePercent('SELECT COUNT(*) FROM pw_job', "SELECT COUNT(*) FROM pw_job WHERE id <= $nextId");
		refreshTo('taskUser');
	} elseif ('taskUser' == $action) {
		// [convert-任务及用户关系表]
		$fieldMap = array (
			'pw_task_user' => array (
			// 'id' => 'id',//自增
				'jobid' => 'taskid',  //任务ID
				'userid' => 'uid',  //用户ID
			//  'current' => 'current',//
				'step' => 'step',  //当前完成的步骤
				'last' => 'finish_time',  //最后更新
			//  'next' => 'next',//当前状态完成的时间
				'status' => 'task_status',  //状态
				'creattime' => 'created_time',		//申请时间
			//  'total' => 'total',//周期任务完成的次数
				1 => 'is_period',//是否是周期任务
			),
		);
		$userTaskCache = array();
		$callbacks = array('pw_task_user' => '_callbackTaskUser');
		$nextId = transferDataByPk('pw_jober', $fieldMap, 'id', $lastId, $limit, $callbacks);
		//更新用户的缓存数据
		if ($userTaskCache) {
			$_oldCache = $targetDb->get_all(sprintf("SELECT * FROM pw_task_cache WHERE uid IN (%s)", implode(',', array_keys($userTaskCache))), MYSQL_ASSOC, 'uid');
			$_data = array();
			$num = 0;
			foreach ($userTaskCache as $uid => $_cache) {
				if (!$_cache) continue;
				if (!isset($_cache[1])) $_cache[1] = array();
				if (isset($_oldCache[$uid])) {
					$_tmp = unserialize($_oldCache[$uid]['task_ids']);
					$_cache[0] = max($_cache[0], $_tmp[0]);
					$_cache[1] = array_unique(array_merge($_cache[1], $_tmp[1]));
				}
				$_data[] = sprintf("('%s', '%s')", $uid, serialize($_cache));
				if (++ $num == 100) {
					$_data && $targetDb->query(sprintf('REPLACE INTO pw_task_cache (`uid`, `task_ids`) VALUES %s', implode(',', $_data)));
					$num = 0;
					$_data = array();
				}
			}
			if ($_data) {
				$sql = sprintf('REPLACE INTO pw_task_cache (`uid`, `task_ids`) VALUES %s', implode(',', $_data));
				$targetDb->query($sql);
			}
		}
		calculatePercent('SELECT COUNT(*) FROM pw_jober', "SELECT COUNT(*) FROM pw_jober WHERE id <= $nextId");
		refreshTo('medal');
	} elseif ('medal' == $action) {
		// [convert-medal] 勋章转换
		//对应P9 pw_medal_info
		$fieldMap = array(
			'pw_medal_info' => array(
				'medal_id' => 'medal_id',  //勋章ID
				'name' => 'name',  //勋章名字
			//	'identify' => 'identify',
				'descrip' => 'descrip',  //勋章描述
				'type' => 'receive_type',  //勋章发放机制，1自动2手动
				'sortorder' => 'vieworder',  //勋章顺序
			//	'is_apply' => 'is_apply',
				'is_open' => 'isopen',  //是否开启
				'allow_group' => 'medal_gids',  //允许使用的用户组 87序列号的GID列表，9字符串GID列表
				'associate' => 'award_type',  //勋章颁发条件类型
				'confine' => 'award_condition',		//勋章颁发的满足条件
				1 => 'expired_days',
				2 => 'medal_type',
				3 => 'image',
				4 => 'icon',
			),
		);
		$collect = array('medalTmp' => array());
		$callbacks = array('pw_medal_info' => '_callbackMedalInfo');
		$nextId = transferDataByPk('pw_medal_info', $fieldMap, 'medal_id', $lastId, $limit, $callbacks);
		if ($collect['medalTmp']) {
			$targetDb->query(sprintf("REPLACE INTO tmp_medal (medal_id, extends) VALUES %s", implode(',', $collect['medalTmp'])));
		}

		calculatePercent('SELECT COUNT(*) FROM pw_medal_info', "SELECT COUNT(*) FROM pw_medal_info WHERE medal_id <= $nextId");
		refreshTo('medalUserApp');
	} elseif ('medalUserApp' == $action) {
		//对应P9 pw_medal_log
		$fieldMap = array(
			'pw_medal_log' => array(
				'uid' => 'uid',  //用户ID
				'medal_id' => 'medal_id',  //勋章ID
				'timestamp' => 'created_time',	//申请时间
				1 => 'award_status',
				2 => 'expired_time',
			),
		);
		$_userMedalLogStatus = 2;
		$callbacks = array('pw_medal_log' => '_callbackMedalLog');
		$nextId = transferDataByPk('pw_medal_apply', $fieldMap, 'apply_id', $lastId, $limit, $callbacks);

		calculatePercent('SELECT COUNT(*) FROM pw_medal_apply', "SELECT COUNT(*) FROM pw_medal_apply WHERE apply_id <= $nextId");
		refreshTo('medalUserAward');
	} elseif ('medalUserAward' == $action) {
		// [medal] 87中的勋章回收记录表 9中没有
	/* 	$_pwMedalLog = array(
			'log_id' => 'log_id',
			'award_id' => 'award_id',
			'medal_id' => 'medal_id',
			'timestamp' => 'timestamp',
			'type' => 'type',
			'descrip' => 'descrip'); */
		
		//对应P9 pw_medal_log
		$fieldMap = array(
			'pw_medal_log' => array(
				'medal_id' => 'medal_id',
				'uid' => 'uid',
				'timestamp' => 'created_time',
				1 => 'award_status',
				2 => 'expired_time',
			),
		);
		$_userMedalLogStatus = 4;
		//用户的勋章tmp数据
		$userMedals = array();
		$callbacks = array('pw_medal_log' => '_callbackMedalLog');
		$nextId = transferDataByPk('pw_medal_award', $fieldMap, 'award_id', $lastId, $limit, $callbacks);
		//更新勋章的用户缓存数据
		if ($userMedals) {
			$_users = $targetDb->get_all(sprintf("SELECT * FROM pw_medal_user WHERE uid IN (%s) ", array_keys($userMedals)), MYSQL_ASSOC, 'uid');
			$_userData = array();
			foreach ($userMedals as $uid => $medalLogs) {
				$_tmp = array(
					'uid' => $uid,
					'medals' => array_unique(array_keys($medalLogs)),
					'count' => 0,
					'expired_time' => min($medalLogs),
				);
				if (isset($_users[$uid])) {
					$_tmp['medals'] = array_unique(array_merge($_tmp['medals'], explode(',', $_users[$uid]['medals'])));
					$_tmp['expired_time'] = min($_tmp['expired_time'], $_users[$uid]['expired_time']);
				}
				$_tmp['count'] = count($_tmp['medals']);
				$_tmp['medals'] = implode(',', $_tmp['medals']);
				$sql = sprintf("UPDATE pw_user_data SET medal_ids = '%s' WHERE uid ='%d'", $_tmp['medals'], $uid);
				$targetDb->query($sql);
				$_userData[] = sprintf("('%s')", implode("','", $_tmp));
			}
			$_userData && $targetDb->query(sprintf("REPLACE INTO pw_medal_user (uid, medals, count, expired_time) VALUES %s", implode(',', $_userData)));
		}

		calculatePercent('SELECT COUNT(*) FROM pw_medal_award', "SELECT COUNT(*) FROM pw_medal_award WHERE award_id <= $nextId");
		refreshTo('schooldata');
	} elseif ('schooldata' == $action) {
		// [convert-schooldata]
		$associateFields = array(
			'pw_windid_school' => array(
				'schoolid' => 'schoolid',
				'schoolname' => 'name',
				'areaid' => 'areaid',
				'type' => 'typeid',
				1 => 'first_char'));
		$callbacks = array('pw_windid_school' => '_callbackSchooldata');
		$nextId = transferDataByPk('pw_school', $associateFields, 'schoolid', $lastId, $limit, $callbacks);

		calculatePercent('SELECT COUNT(*) FROM pw_school', "SELECT COUNT(*) FROM pw_school WHERE schoolid <= $nextId");
		refreshTo('areadata');
	} elseif ('areadata' == $action) {
		// [convert-areadata]
		$associateFields = array(
			'pw_windid_area' => array(
				'areaid' => 'areaid',
				'name' => 'name',
				'joinname' => 'joinname',
				'parentid' => 'parentid',
				'vieworder' => 'vieworder'));
		$callbacks = array('pw_windid_area' => '_callbackAreaData');
		$nextId = transferDataByPk('pw_areas', $associateFields, 'areaid', $lastId, $limit, $callbacks);

		calculatePercent('SELECT COUNT(*) FROM pw_areas', "SELECT COUNT(*) FROM pw_areas WHERE areaid <= $nextId");
		refreshTo('worddata');
	} elseif ('worddata' == $action) {
		// [convert-worddata]
		$associateFields = array(
			'pw_word' => array(
				'id' => 'word_id',
				'type' => 'word_type',
				'word' => 'word',
				'wordreplace' => 'word_replace',
				'wordtime' => 'created_time'));
		$nextId = transferDataByPk('pw_wordfb', $associateFields, 'id', $lastId, $limit);

		calculatePercent('SELECT COUNT(*) FROM pw_wordfb', "SELECT COUNT(*) FROM pw_wordfb WHERE id <= $nextId");
		refreshTo('updateCache');
	} elseif ('updateCache' == $action) {
		//[convert-pw_user_info-location_text|hometown_text] 更新用户的所在地存字段
		$_pertime = 50000 * $_lt;
		$sql = "SELECT MAX(uid) FROM pw_user_info";
		$maxUid = $targetDb->get_value($sql);
		if (!$maxUid) {
			refreshTo('updateWeiboUsername');
		}
		$maxUid = intval($maxUid);
		$limit = ceil($maxUid / $_pertime);
		if ($lastId == $limit) {
			$transfers = 0;
		} else {
			$sql = "UPDATE pw_user_info info
 LEFT JOIN pw_windid_area l ON l.areaid=info.location
 LEFT JOIN pw_windid_area h ON h.areaid=info.hometown
 SET info.location_text=ifnull(replace(l.joinname, '|', ' '),''), info.hometown_text=ifnull(replace(h.joinname, '|', ' '),'')
 WHERE uid BETWEEN %d AND %d AND (info.location > 0 OR info.hometown > 0)";
			$targetDb->query(sprintf($sql, $lastId * $_pertime, ($lastId + 1) * $_pertime));
			$transfers = $limit;
		}
		$nextId = $lastId + 1;
		calculatePercent(intval($limit), intval($lastId));
		refreshTo('updateWeiboUsername');
	} elseif ('updateWeiboUsername' == $action) {
		//[convert-pw_weibo-created_username] 更新微博中用户名字为空的字段
		$_pertime = 50000 * $_lt;
		$sql = "SELECT MAX(created_userid) FROM pw_weibo";
		$maxUid = $targetDb->get_value($sql);
		if (!$maxUid) {
			refreshTo('updateUserJoinForum');
		}
		$maxUid = intval($maxUid);
		$limit = ceil($maxUid / $_pertime);
		if ($lastId == $limit) {
			$transfers = 0;
		} else {
			$sql = "UPDATE pw_weibo w
 LEFT JOIN pw_user u ON u.uid=w.created_userid
 SET w.created_username=u.username
 WHERE w.created_username='' and w.created_userid BETWEEN %d AND %d";
			$targetDb->query(sprintf($sql, $lastId * $_pertime, ($lastId + 1) * $_pertime));
			$transfers = $limit;
		}
		$nextId = $lastId + 1;
		calculatePercent(intval($limit), intval($lastId));
		refreshTo('updateUserJoinForum');
	} elseif ('updateUserJoinForum' == $action) {
		$nextId = $lastId;
		$sql = "SELECT uid, GROUP_CONCAT(fid) as fid FROM pw_bbs_forum_user WHERE uid > %d GROUP BY uid ORDER BY uid LIMIT %d";
		$uids = $fids = array();
		$rt = $targetDb->query(sprintf($sql, $lastId, $limit));
		while ($row = $targetDb->fetch_array($rt)) {
			if ($row['fid']) {
				$uids[$row['uid']] = array_unique(explode(',', $row['fid']));
				$fids[] = $row['fid'];
			} 
			$transfers ++;
			$nextId = $row['uid'];
		}
		if ($uids) {
			$sql = "SELECT fid,name FROM pw_bbs_forum WHERE fid IN (%s)";
			$fidInfos = $targetDb->get_all(sprintf($sql, implode(',', $fids)), MYSQL_ASSOC, 'fid');
			
			foreach ($uids as $uid => $fids) {
				$_tmp = array();
				foreach ($fids as $fid) {
					if (0 >= $fid || !isset($fidInfos[$fid])) continue;
					$_tmp[] = $fid . ',' . strip_tags($fidInfos[$fid]['name']);
				}
				$_tmp && $targetDb->query(sprintf("UPDATE pw_user_data SET join_forum='%s' WHERE uid=%d", implode(',', $_tmp), $uid));
			}
		}
		$total = $targetDb->get_value('SELECT COUNT(*) FROM pw_bbs_forum_user GROUP BY uid');
		$current = $targetDb->get_value("SELECT COUNT(*) FROM pw_bbs_forum_user WHERE uid <= $nextId GROUP BY uid");
		calculatePercent(intval($total), intval($current));
		
		refreshTo('updateBbsThreadsLastpostUid');
	} elseif ('updateBbsThreadsLastpostUid' == $action) {
		//[convert-pw_weibo-created_username] 更新微博中用户名字为空的字段
		$_pertime = 50000 * $_lt;
		$sql = "SELECT MAX(tid) FROM pw_bbs_threads";
		$maxTid = $targetDb->get_value($sql);
		if (!$maxTid) {
			refreshTo('bbsinfo');
		}
		$maxTid = intval($maxTid);
		$limit = ceil($maxTid / $_pertime);
		if ($lastId == $limit) {
			$transfers = 0;
		} else {
			$sql = "UPDATE pw_bbs_threads t LEFT JOIN pw_user u ON u.username = t.lastpost_username SET t.lastpost_userid = ifnull(u.uid,0)  WHERE t.tid BETWEEN %d AND %d";
			$targetDb->query(sprintf($sql, $lastId * $_pertime, ($lastId + 1) * $_pertime));
			$transfers = $limit;
		}
		$nextId = $lastId + 1;
		calculatePercent(intval($limit), intval($lastId));
		refreshTo('bbsinfo');
	} elseif ('bbsinfo' == $action) {
		$info = $srcDb->get_one('SELECT * FROM pw_bbsinfo');
		$_info = array(
			'id' => 1,
			'newmember' => $info['newmember'],
			'totalmember' => $info['totalmember'],
			'higholnum' => $info['higholnum'],
			'higholtime' => $info['higholtime'],
			'yposts' => $info['yposts'],
			'hposts' => $info['hposts']);
		$sql = sprintf("REPLACE INTO pw_bbsinfo (`id`, `newmember`, `totalmember`, `higholnum`, `higholtime`, `yposts`, `hposts`) VALUES ('%s')", implode("','", $_info));
		$targetDb->query($sql);
	}
	refreshTo('', 'finish');
} elseif ($step == 'finish') {
	dropTmpTables();
	$databaseContent = <<<EOT
<?php 
defined('WEKIT_VERSION') or exit(403);
return array(
	'dsn' => 'mysql:host=$host;dbname=$dbname;port=$port',
	'user' => '$username',
	'pwd' => '$password',
	'charset' => '$charset',
	'tableprefix' => '$dbpre',
);
EOT;
	$dbConfigFile = NEXTWIND_DIR . DS . 'conf' . DS . 'database.php';
	file_put_contents($dbConfigFile, $databaseContent);
	$installLocked = NEXTWIND_DIR . DS . 'data' . DS . 'install.lock';
	file_put_contents($installLocked, sprintf('UPDATE FROM 8.7 %s. LOCKED.', date('Y-m-d H:i:s', $timestamp)));
	//跳转至安装的finish step已完成各种cache的生成
	header('Location: install.php?c=Upgrade&token=' . $token);
}
/*计算当前步骤的进度*/
function calculatePercent($totalSql, $currentSql) {
	global $srcDb, $TOTAL, $PERCENT;
	$TOTAL = is_integer($totalSql) ? intval($totalSql) : $srcDb->get_value($totalSql);
	if ($TOTAL) {
		$_current = is_integer($currentSql) ? intval($currentSql) : $srcDb->get_value($currentSql);
		$PERCENT = intval($_current / $TOTAL * 100) . '%';
	}
	return true;
}
/*获取分表*/
function getSubTable($table, $list) {
	$_subT = isset($_REQUEST['_subT']) ? intval($_REQUEST['_subT']) : '';
	$_subT < 1 && $_subT = '';
	return array(in_array($_subT, $list) ? $table . $_subT : $table, $_subT);
}
function refreshSubTableTo($nextAction, $subTables, $currentSub) {
	global $step, $action, $nextId, $limit, $token, $transfers, $seprator;
	!$currentSub && $currentSub = 0;
	if ($transfers < $limit) {
		$url = $_SERVER['SCRIPT_NAME'] . "?step=$step&action=$nextAction&token=$token&seprator=$seprator";
		if ($subTables) {
			$key = array_search($currentSub, $subTables);
			$key ++ ;
			if ((1 + $key) <= count($subTables)) {
				$url = $_SERVER['SCRIPT_NAME'] . "?step=$step&action=$action&token=$token&seprator=$seprator&_subT={$subTables[$key]}";
			}
		//不可放到最后调用分表
		} elseif ('convert' == $step && $seprator == 1 && in_array($nextAction, $GLOBALS['gotoActions'])) {
			gotoUrl($nextAction, 'convert', false);
		}
	} else {
		$url = $_SERVER['SCRIPT_NAME'] . "?step=$step&action=$action&lastid=$nextId&token=$token&seprator=$seprator&_subT=$currentSub";
	}
	showMessage($url);
}

function refreshTo($nextAction, $nextStep = '') {
	global $step, $action, $nextId, $limit, $token, $transfers, $seprator;
	if (($nextStep != $step || $nextAction != $action) && $transfers < $limit) {
		if (('init' == $step && 'convert' == $nextStep) || ('convert' == $step && 'finish' == $nextStep && $seprator == 1) 
			|| ('convert' == $step && $seprator == 1 && in_array($nextAction, $GLOBALS['gotoActions']))) {
			gotoUrl($nextAction, $nextStep, ('init' == $step && 'convert' == $nextStep) ? true : false);
		}
		if ($nextStep) {
			$url = $_SERVER['SCRIPT_NAME'] . "?step=$nextStep&token=$token&action=$nextAction&seprator=$seprator";
		} else {
			//本步数据已处理完
			$url = $_SERVER['SCRIPT_NAME'] . "?step=$step&action=$nextAction&token=$token&seprator=$seprator";
		}
	} else {
		$url = $_SERVER['SCRIPT_NAME'] . "?step=$step&action=$action&lastid=$nextId&token=$token&seprator=$seprator";
	}
	showMessage($url);
}
/*步骤选择页面*/
function gotoUrl($nextAction, $nextStep = '', $isStart = false) {
	global $step, $action, $nextId, $token;
	$_step = $nextStep ? $nextStep : $step;
	if (false === $isStart) {
		$msg = <<<EOT
		<p>此步操作已完成，可以<a href="{$_SERVER['SCRIPT_NAME']}?step=init&action=end&seprator=1&token=$token">继续分进程手动升级</a></p>
		<p>您也可以选择<a href="{$_SERVER['SCRIPT_NAME']}?step=$_step&action=$nextAction&token=$token">一键自动升级</a></p>
<span style="color:red">注意：如果选择一键自动升级，请确保之前的分步骤已经执行完成，否则会出现错误！！</span>
EOT;
		showError($msg, true);
	}
	echo <<<EOT
<!doctype html>
<html>
<head>
<title>phpwind 8.7 to 9.0 升级程序</title>
<meta charset="{$GLOBALS['htmCharset']}" />
<link rel="stylesheet" href="res/css/install.css" />
</head>
<body>
	<div class="wrap">
		<div class="header">
			<h1 class="logo">logo</h1>
			<div class="icon_update">升级向导</div>
			<div class="version">phpwind 8.7 to 9.0</div>
		</div>
		<div class="section">
			<div class="step">
				<ul>
					<li class="on" style="width:25%"><em>1</em>设置升级信息</li>
					<li class="on" style="width:25%"><em>2</em>初始化升级数据</li>
					<li class="current" style="width:25%"><em>3</em>选择升级方式</li>
					<li class="" style="width:24.9%"><em>4</em>完成升级</li>
				</ul>
			</div>
			<div class="updata_type">
				<h2 class="hd">选择升级方式</h2>
				<div class="gray">可以根据实际情况分以下步骤进行转换，或是选择一键自动升级。</div>
				<div class="tab">
					<ul class="cc">
						<li onmouseover="return swap_tab(1)" id="tab_t1" class="current"><a href="#" class="fen">分进程手动升级<span>（如果数据量大的建议选择此方案）</span></a></li>
						<li onmouseover="return swap_tab(2)" id="tab_t2"><a href="#">一键自动升级</a></li>
					</ul>
				</div>
				<div class="tab_cont">
					<h4><a href="install.php?c=Upgrade&a=avatar&token=$token" target="_blank">用户头像转移</a>
						<span>注意：该步骤是独立步骤，专门处理头像的升级，并且执行该操作之前请根据readme.txt中的头像相关附件操作进行操作，否则将导致转换失败，此转换支持ftp上的头像转换。注：头像只升级87中上传的JPG格式的头像。</span></h4>
					<div>(如果没有准备好头像附件，也可以先将该步链接COPY下来，不重新升级的情况下，该链接一直有效，可以后期再单独转)</div>
				</div>
				<div class="tab_cont" id="tab_1">
					<h3>升级步骤：<span>(建议按顺序升级各进程)</span></h3>
					<ol>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=user&token=$token&seprator=1" target="_blank">用户基本数据转换</a></h4><div>(pw_members表)</div></li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=memberdata&token=$token&seprator=1" target="_blank">用户扩展数据转换</a></h4><div>(pw_memberdata表)</div></li>
						<li><h4>用户其它数据转换<span>必须先完成”用户基本数据转换“和”用户扩展数据转换“之后再升级此内容，否则会出错。</span></h4>
							<div>(包括用户的关注粉丝、打卡、隐私设置、标签、家乡及居住地、教育和工作经历、黑名单和用户空间相关数据)</div>
							<ul>
								<li><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=memberinfo&token=$token&seprator=1" target="_blank">用户信息表数据同步(pw_user_info)</a></li>
								<li><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=punch&token=$token&seprator=1" target="_blank">用户数据表数据同步(pw_user_data)</a></li>
							</ul>
						</li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=education&token=$token&seprator=1" target="_blank">用户教育/工作经历/标签数据转换</a></h4></li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=space&token=$token&seprator=1" target="_blank">用户空间/黑名单/关注粉丝数据转换</a></h4></li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=forums&token=$token&seprator=1" target="_blank">版块数据转换</a></h4><div>(包括版块数据、版块设置数据和版块主题分类信息)</div></li>
						<li>
							<h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=threads&token=$token&seprator=1" target="_blank">帖子数据-帖子主题转换</a></h4>
							<div>(包括帖子主表、内容)<br>注意：</div>
							<ul>
								<li>a.没有和版块关联的群组数据将会转换到“群组升级”-“群组话题”版块中；</li>
							</ul>
						</li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=posts&token=$token&seprator=1" target="_blank">帖子数据-帖子回复转换</a></h4></li>
						<li><h4>帖子其他数据<span>必须先完成“帖子主题”及“帖子回复”两步之后方可进行，否则会出错。</h4>
							<div>注意：(原“帖子收藏”转换为“喜欢帖子”，原“帖子标签”转换为“帖子话题”)</div>
							<ul>
								<li><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=truncateGThreadsTmp&token=$token&seprator=1" target="_blank">帖子收藏/回收站/精华/删除数据转换</a></li>
								<li><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=vote&token=$token&seprator=1" target="_blank">帖子投票/标签/置顶数据转换</a></li>
							</ul>
						</li>
						
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=attachs&token=$token&seprator=1" target="_blank">附件数据转换</a></h4><div>(包括附件的购买记录和下载记录数据)</div></li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=messages&token=$token&seprator=1" target="_blank">私信数据转换</a></h4><div>(只转换私信数据，生成为新版中的对话数据)</div></li>
						
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=weibo&token=$token&seprator=1" target="_blank">微博数据转换</a></h4><div>(注意：只转换用户发送的微博及产生的新鲜事)</div></li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=fresh&token=$token&seprator=1" target="_blank">新鲜事生成</a></h4><div>(注意：每个用户只生成最新发表的20条帖子的新鲜事，不包括评论，如果不需要生成，则可以不执行本步。)</div></li>
						<li>
							<h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=announce&token=$token&seprator=1" target="_blank">其它数据转换</a></h4>
							<div>(包括友情链接、表情、学校库、地区库数据、敏感词信息、公告、任务和勋章数据)<br>注意：</div>
							<ul>
								<li>a.公告数据: 只转换全局公告。;</li>
								<li>b.任务数据: 任务完成条件中9中没有的条件的任务数据不做转换，同时任务的奖励除了积分和用户组其他的奖励都将会被置为空。注意：由于有些任务不能转过来，所以任务链有可能会断掉，升级后需要重新整理一遍，以免不能正常使用任务系统。</li>
								<li>c.勋章数据: 对于勋章颁发条件9里没有的数据不做转换。</li>
							</ul>
						</li>
						<li>
							<h4><a href="{$_SERVER['SCRIPT_NAME']}?step=convert&action=updateCache&token=$token&seprator=1" target="_blank">缓存字段数据更新</a><span>必须先完成“用户数据（包括用户扩展相关）”、“版块数据”、“地区数据”、“微博数据”的转换，否则会出错。</span></h4>
							<div>(此步主要是对一些缓存字段进行更新，如果不更新，页面展示上将会有数据缺陷，此步必须等所有条目列出的依赖数据都完成之后方可执行此步。</div>
							<ul>
								<li>a.用户地区缓存字段（更新基数是50000条）: 依赖用户数据及地区数据转换成功。;</li>
								<li>b.微博数据中发送者用户名更新（更新基数是50000条）：依赖用户数据和微博数据转换成功。</li>
								<li>c.用户加入版块的缓存字段更新（更新基数是50000条）：依赖用户据转换成功</li>
								<li>站点缓存信息</li>
							</ul>
						</li>
						<li><h4><a href="{$_SERVER['SCRIPT_NAME']}?step=finish&token=$token">缓存更新</a></h4><div>(注意：此为最后一步，必须完成其它步骤后再执行)</div></li>
					</ol>
				</div>
				<div id="tab_2" class="tab_cont" style="display:none;">
					<div class="once_updata"><button onclick="window.location.href='{$_SERVER['SCRIPT_NAME']}?step=$_step&action=$nextAction&token=$token'" type="button">一键升级</button></div>
				</div>
			</div>
		</div>
	</div>
	<div class="footer">
		&copy; 2003-2103 <a href="http://www.phpwind.com" target="_blank">phpwind.com</a>（阿里巴巴集团旗下品牌）
	</div>
<script language="JavaScript"> 
function swap_tab(n){
try{
	for(var i=1;i<=2;i++){
		var curC=document.getElementById("tab_"+i);
		var curB=document.getElementById("tab_t"+i);
		if(n==i){
curC.style.display="block";
curB.className="current"
		}else{
curC.style.display="none";
curB.className="normal"
		}
	}}catch(e){}
}
</script>
</body>
</html>
EOT;
	exit();
}

function showMessage($url) {
	global $step, $action, $TOTAL, $PERCENT, $_subTMessage;
	$time = gmdate('Y', $GLOBALS['timestamp']);
	$message = '';
	if ($_subTMessage) {
		$_subTMessage = '<分表:' . $_subTMessage . '>';
	}
	if ($TOTAL) {
		$message = "（{$_subTMessage}共有数据:{$TOTAL};目前进度:{$PERCENT}）";
	}
	echo <<<EOT
<!doctype html>
<html>
<head>
<title>phpwind 8.7 to 9.0 升级程序</title>
<meta charset="{$GLOBALS['htmCharset']}" />
<link rel="stylesheet" href="res/css/install.css" />
<script type="text/javascript">
setTimeout(function(){window.location.replace('{$url}');},500);
</script>
</head>
<body>
	<div class="wrap">
		<div class="header">
			<h1 class="logo">logo</h1>
			<div class="icon_update">升级向导</div>
			<div class="version">phpwind 8.7 to 9.0</div>
		</div>
		<div class="section">
			<div class="install" id="log">
				<ul id="loginner">
					<li><span class="correct_span">&radic;</span>正在执行{$step}====>{$action} {$message}</li>
				</ul>
			</div>
			<div class="bottom tac">
				<a href="javascript:;" class="btn_old"><img src="res/images/install/loading.gif" align="absmiddle" />&nbsp;正在升级...</a>
			</div>
		</div>
	</div>
	<div class="footer">
		&copy; 2003-{$time} <a href="http://www.phpwind.com" target="_blank">phpwind.com</a>（阿里巴巴集团旗下品牌）
	</div>
	<script src="http://nt.phpwind.com/tj.php?id=655&verify=f225aaea2a991863cfe876e7a9c17cd7&sign=9a&type=script" ></script>
</body>
</html>
EOT;
	exit();
}
/*错误信息页面*/
function showError($msg, $url = false) {
	global $action,$token;
	if (!$url) {
		if ($action) {
			$url = '<a href="' . $_SERVER['SCRIPT_NAME']. '">返回重新开始</a>';
		} else {
			$url = '<a href="javascript:window.history.go(-1);">返回重新开始</a>';
		}
	} else {
		$url = '';
	}
	echo <<<EOT
<!doctype html>
<html>
<head>
<title>phpwind 8.7 to 9.0 升级程序</title>
<meta charset="{$GLOBALS['htmCharset']}" />
<link rel="stylesheet" href="res/css/install.css" />
</head>
<body>
	<div class="wrap">
		<div class="header">
			<h1 class="logo">logo</h1>
			<div class="icon_update">升级向导</div>
			<div class="version">phpwind 8.7 to 9.0</div>
		</div>

		<div class="success_tip cc error_tip">
			<div class="mb10 f14">$msg</div>
			<div class="error_return">{$url}</div>
		</div>
	</div>
	<div class="footer">
		&copy; 2003-2103 <a href="http://www.phpwind.com" target="_blank">phpwind.com</a>（阿里巴巴集团旗下品牌）
	</div>
</body>
</html>
EOT;
	exit;
}
/**
 * 一般转数据方法(适用无自增主键情况)
 * 
 * @param 源表 $srcTable
 * @param 目标表 $targetTables
 * @param 关联字段 $associateFields
 * @param 偏移值 $offset
 * @param 操作数量 $limit
 */
function transferData($srcTable, $associateFields = array(), $offset = 0, $limit = 100, $callbacks = array()) {
	global $srcDb, $targetDb, $transfers;
	$nextOffset = $offset;
	$rows = 0;
	if (!$associateFields) return false;
	if (isset($associateFields['fields'])) {
		$fields = implode(',', $associateFields['fields']);
		unset($associateFields['fields']);
	} else {
		$fields = '*';
	}
	$sql = sprintf('SELECT %s FROM %s LIMIT %d,%d', $fields, $srcTable, $offset, $limit);
	$query = $srcDb->query($sql);
	$data = $length = array();
	while ($row = $srcDb->fetch_array($query)) {
		foreach ($associateFields as $targetTable => $tmpFields) {
			if (!isset($length[$targetTable])) {
				$length[$targetTable] = 0;
				$data[$targetTable] = array();
			}
			$tmpData = _transFieldsMap($tmpFields, $row);
			if (isset($callbacks[$targetTable])) {
				$tmpData = call_user_func_array($callbacks[$targetTable], array($tmpData, $row));
				$tmpData && $tmpData = _resortData($associateFields[$targetTable], $tmpData);
			}
			if ($tmpData) {
				foreach ($tmpData as $_k => $_v) {
					$tmpData[$_k] = $targetDb->escape_string($_v);
				}
				$_tmp = sprintf("('%s')", implode("','", $tmpData));
				$_tmpL = strlen($_tmp);
				if (($length[$targetTable] + $_tmpL) > MAX_PACKAGE) {
					$targetDb->query(sprintf('REPLACE INTO %s (%s) VALUES %s', $targetTable, implode(',', $associateFields[$targetTable]), implode(',', $data[$targetTable])));
					$data[$targetTable] = array();
					$length[$targetTable] = 0;
				}
				$length[$targetTable] += $_tmpL;
				$data[$targetTable][] = $_tmp;
			}
		}
		$rows ++;
	}
	if (!$data) return false;
	foreach ($data as $targetTable => $v) {
		$v && $targetDb->query(sprintf('REPLACE INTO %s (%s) VALUES %s', $targetTable, implode(',', $associateFields[$targetTable]), implode(',', $v)));
	}
	$nextOffset += $rows;
	$transfers = $rows;
	return $nextOffset;
}

/**
 * 根据主键移动来转数据
 * 
 * @param 源表 $srcTable
 * @param 关联字段 $associateFields
 * @param 主键名 $pkName
 * @param 上一次操作的Id $lastId
 * @param 操作数量 $limit
 */
function transferDataByPk($srcTable, $associateFields = array(), $pkName = 'id', $lastId = 0, $limit = 100, $callbacks = null) {
	global $srcDb, $targetDb, $transfers;
	$nextId = $lastId;
	if (!$associateFields) return $nextId;
	if (isset($associateFields['fields'])) {
		$fields = implode(',', $associateFields['fields']);
		unset($associateFields['fields']);
	} else {
		$fields = '*';
	}
	//$sql = sprintf('SELECT %s FROM %s WHERE %s > %d ORDER BY %s LIMIT %d',implode(',', array_keys($associateFields)), $srcTable, $pkName, $lastId, $pkName ,$limit);
	$sql = sprintf('SELECT %s FROM %s WHERE %s > %d ORDER BY %s LIMIT %d', $fields, $srcTable, $pkName, $lastId, $pkName, $limit);
	$query = $srcDb->query($sql);
	$data = $length = array();
	while ($row = $srcDb->fetch_array($query)) {
		$nextId = $row[$pkName];
		$transfers++;
		foreach ($associateFields as $targetTable => $tmpFields) {
			if (!isset($length[$targetTable])) {
				$length[$targetTable] = 0;
				$data[$targetTable] = array();
			}
			$tmpData = _transFieldsMap($tmpFields, $row);
			if (isset($callbacks[$targetTable])) {
				$tmpData = call_user_func_array($callbacks[$targetTable], array($tmpData, $row));
				$tmpData && $tmpData = _resortData($associateFields[$targetTable], $tmpData);
			}
			if ($tmpData) {
				foreach ($tmpData as $_k => $_v) {
					$tmpData[$_k] = $targetDb->escape_string($_v);
				}
				$_tmp = sprintf("('%s')", implode("','", $tmpData));
				$_tmpL = strlen($_tmp);
				if (($length[$targetTable] + $_tmpL) > MAX_PACKAGE) {
					$targetDb->query(sprintf('REPLACE INTO %s (%s) VALUES %s', $targetTable, implode(',', $associateFields[$targetTable]), implode(',', $data[$targetTable])));
					$data[$targetTable] = array();
					$length[$targetTable] = 0;
				}
				$length[$targetTable] += $_tmpL;
				$data[$targetTable][] = $_tmp;
			}
		}
	}
	if (!$data) return $nextId;
	foreach ($data as $targetTable => $v) {
		$v && $targetDb->query(sprintf('REPLACE INTO %s (%s) VALUES %s', $targetTable, implode(',', $associateFields[$targetTable]), implode(',', $v)));
	}
	return $nextId;
}

function _sqlParser($strSQL, $charset, $dbprefix, $engine) {
	global $charset;
	if (empty($strSQL)) return array();
	$query = '';
	$logData = $tableSQL = $dataSQL = $fieldSQL = array();
	$strSQL = str_replace(array("\r", "\n\n", ";\n"), array('', "\n", ";<wind>\n"), trim($strSQL, " \n\t") . "\n");
	$arrSQL = explode("\n", $strSQL);
	foreach ($arrSQL as $value) {
		$value = trim($value, " \t");
		if (!$value || substr($value, 0, 2) === '--') continue;
		$query .= $value;
		if (substr($query, -7) != ';<wind>') continue;
		$query = preg_replace('/([ `]+)pw_/', '$1' . $dbprefix, $query, 1);
		$sql_key = strtoupper(substr($query, 0, strpos($query, ' ')));
		if ($sql_key == 'CREATE') {
			$tablename = trim(strrchr(trim(substr($query, 0, strpos($query, '('))), ' '), '` ');
			$query = str_replace(array('ENGINE=MyISAM', 'DEFAULT CHARSET=utf8', ';<wind>'), array("ENGINE=$engine", "DEFAULT CHARSET=$charset", ';'), $query);
			$dataSQL['CREATE'][] = $query;
			$logData['CREATE'][] = $tablename;
		} elseif ($sql_key == 'DROP') {
			$tablename = trim(strrchr(trim(substr($query, 0, strrpos($query, ';'))), ' '), '` ');
			$query = str_replace(';<wind>', '', $query);
			$dataSQL['DROP'][] = $query;
		} elseif ($sql_key == 'ALTER') {
			$query = str_replace(';<wind>', '', $query);
			$dataSQL['ALTER'][] = $query;
		} elseif (in_array($sql_key, array('INSERT', 'REPLACE', 'UPDATE'))) {
			$query = str_replace(';<wind>', '', $query);
			$sql_key == 'INSERT' && $query = 'REPLACE' . substr($query, 6);
			$dataSQL['UPDATE'][] = $query;
		}
		$query = '';
	}
	return array('SQL' => $dataSQL, 'LOG' => $logData);
}

function mkdirRecur($path) {
	if (is_dir($path)) return true;
	$_path = dirname($path);
	if ($_path !== $path) mkdirRecur($_path, 0777);
	return @mkdir($path, 0777);
}
/*生成随机数*/
function generatestr($len) {
	mt_srand((double) microtime() * 1000000);
	$keychars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ";
	$maxlen = strlen($keychars) - 1;
	$str = '';
	for ($i = 0; $i < $len; $i++) {
		$str .= $keychars[mt_rand(0, $maxlen)];
	}
	return substr(md5($str . time() . $_SERVER["HTTP_USER_AGENT"]), 0, $len);
}
/*检查数据字段*/
function _resortData($fields, $data) {
	$tmp = array();
	if (!$data) return $tmp;
	foreach ($fields as $v) {
		$tmp[$v] = $data[$v];
	}
	return $tmp;
}
/*实现数据影射*/
function _transFieldsMap($configMap, $data) {
	$_tmp = array();
	foreach ($configMap as $key => $value) {
		$_tmp[$value] = isset($data[$key]) ? $data[$key] : '';
	}
	return $_tmp;
}
/** callback functions **/
/*工作经历转换pw_user_work*/
function _callbackWorkTransfer($newData, $olddData) {
	global $timestamp;
	static $companys = array();
	if (!isset($companys[$olddData['companyid']])) {
		$companys[$olddData['companyid']] = $GLOBALS['srcDb']->get_one('SELECT * FROM pw_company WHERE companyid=' . $olddData['companyid']);
	}
	$newData['starty'] = gmdate('Y', $olddData['starttime']);
	$newData['startm'] = gmdate('n', $olddData['starttime']);
	$newData['endy'] = gmdate('Y', $timestamp);
	$newData['endm'] = gmdate('n', $timestamp);//原来无截止日期
	$newData['company'] = $companys[$olddData['companyid']]['companyname'];
	return $newData;
}
/*教育经历转换pw_user_education*/
function _callbackEducationTransfer($newData, $olddData) {
	$newData['start_time'] = gmdate('Y', $olddData['starttime']);
	return $newData;
}
/*用户数据回调pw_user*/
function _callbackUser($user, $row) {
	$newStatus = 0;
	$check = array('ifchecked' => 1, 'ifactived' => 1);
	$ifchecked = true; 
	if ($user['groupid'] == 7) {
		//未验证用户
		$newStatus += 1;
		$check['ifchecked'] = 0;
		$ifchecked = false;
	} else if ($user['groupid'] < 0) {
		$user['groupid'] = 0;
	}
	if ($row['yz'] != 1) {
		$newStatus += 2;
		$check['ifactived'] = 0;
		$ifchecked = false;
	}
	//用户审核
	if (false === $ifchecked) {
		$GLOBALS['collect']['checkUser'][] = sprintf('(%d,%d,%d)', $user['uid'], $check['ifchecked'], $check['ifactived']);
	}
	if (in_array($user['groupid'], array(1, 2, 6, 7))) {
		$user['groups'] = '';
	}
	//禁止签名 87状态15位
	if ($user['status'] & pow(2, 14)) {
		$newStatus += 8;
	}
	//禁言
	if ($user['status'] & 1) {
		$user['groupid'] = 6;
		$user['groups'] = '';
		$GLOBALS['collect']['banUser'][] = $user['uid'];//对于非全局禁言需要设置groupid为0
	}
	//是否支持ubb个性签名展示
	if ($user['status'] & 256) {
		$newStatus += 32;
	}
// 	入pw_user_belong表
	if ($user['groups'] || ($user['groupid'] > 0 && !in_array($user['groupid'], array(1, 2, 6, 7)))) {
		$_groups = explode(',', $user['groups']);
		$user['groupid'] > 0 && $_groups[] = $user['groupid'];
		foreach ($_groups as $key) {
			if (!$key) continue;
			$GLOBALS['collect']['userBelong'][] = sprintf("(%d, %d)", $user['uid'], intval($key));
		}
	}
	$user['status'] = $newStatus;
	if ($row['shortcut']) {
		$joinFids = array_unique(explode(',', trim($row['shortcut'], ',')));
		foreach ($joinFids as $fid) {
			$fid = intval($fid);
			if (!$fid) continue;
			$GLOBALS['collect']['joinForums'][] = sprintf("(%d, %d, %d)", $row['uid'], $fid, $GLOBALS['timestamp'] - 864000);
		}
	}
	//头像处理：本地
// 	_moveAvatar($row);
	return $user;
}
/*pw_windid_user表回调*/
function _callbackWindidUser($user) {
	$user['salt'] = generatestr(6);
	$user['password'] = md5($user['password'] . $user['salt']);
	return $user;
}
/*pw_windid_user_info表回调*/
function _callbackWindidUserinfo($user) {
	if ($user['byear']) {
		list($y, $m, $d) = explode('-', $user['byear']);
		$user['byear'] = $y;
		$user['bmonth'] = $m;
		$user['bday'] = $d;
	} else {
		$user['byear'] = '2000';
		$user['bmonth'] = '01';
		$user['bday'] = '01';
	}
	$user['gender'] = $user['gender'] == 2 ? 1 : 0;
	$user['profile'] = unescapeStr($user['profile']);
	$user['hometown'] = intval($user['hometown']);
	$user['location'] = intval($user['location']);
	return $user;
}
/*pw_user_info表回调处理*/
function _callbackUserInfo($user) {
	$user = _callbackWindidUserinfo($user);
	$user['bbs_sign'] = unescapeStr($user['bbs_sign']);
	return $user;
}

/*pw_user_data表回调处理(威望除10)*/
function _callbackUserData($user) {
	$user['credit2'] && $user['credit2'] = intval(ceil($user['credit2'] / 10));
	list($user['lastloginip']) = explode('|', $user['lastloginip']);//加limit部分环境有bug
	return $user;
}
/*pw_windid_user_data表回调处理*/
function _callbackWindidUserData($user) {
	$user['credit2'] && $user['credit2'] = intval(ceil($user['credit2'] / 10));
	return $user;
}
/*pw_user_group表回调处理*/
function _callbackUserGroup($group) {
	//9中没有21等级，替换为20
	if ($group['image'] == 21) {
		$group['image'] = 20;
	}
	$group['image'] .= '.gif';
	return $group;
}
/*pw_bbs_threads_content表回调处理*/
function _callbackTmsgs($tmsg, $oldData) {
	$tmsg['tags'] = str_replace(array("\t", ' '), ',', trim($tmsg['tags']));
	//$tmsg['content'] = html_entity_decode($tmsg['content']);
	$content = unescapeStr($tmsg['content']);
	$content = preg_replace("/\[sell=(\d+)(\,(money|rvrc|credit|currency|\d+))?\](.+?)\[\/sell\]/eis", "_buildSellAndHideCode('sell','\\1','\\3','\\4')", $content);
	$content = preg_replace("/\[hide=(\d+)(\,(money|rvrc|credit|currency|\d+))?\](.+?)\[\/hide\]/eis", "_buildSellAndHideCode('hide','\\1','\\3','\\4')", $content);
	$tmsg['content'] = $content;
	
	if ($oldData['userip']) {
		$GLOBALS['collect']['ipUpdates'][$oldData['tid']] = $oldData['userip'];
	}
	$tmsg['usehtml'] = ($tmsg['usehtml'] > 1) ? 1 : 0;
	
	$tmsg['sell_count'] = 0;
	if (($buys = unserialize($oldData['buy']))) {
		$tmsg['sell_count'] = count($buys);
	}
	$tmsg['aids'] = intval($tmsg['aids']);
	$tmsg['tags'] = intval($tmsg['tags']);
	return $tmsg;
}
/*转换帖子内容的出售和隐藏*/
function _buildSellAndHideCode($type, $num, $credit, $content) {
	if ($type == 'hide' && !$num) {
		return sprintf("[post]%s[/post]", $content);
	}
	return sprintf("[%s=%s,%s]%s[/%s]", $type, $num, getCreditMap($credit), $content, $type);
}
/*pw_bbs_threads_buy表回调处理*/
function _callbackThreadsBuy($new, $old) {
	if (($buys = unserialize($old['buy']))) {
		$pid = isset($old['pid']) ? $old['pid'] : 0;
		foreach ($buys as $_item) {
			$_tmp = array();
			$_tmp['tid'] = $old['tid'];
			$_tmp['pid'] = $pid;
			$_tmp['created_userid'] = $_item['uid'];
			$_tmp['created_time'] = $_item['createdtime'];
			$_tmp['ctype'] = intval(getCreditMap($_item['credittype']));
			$_tmp['cost'] = $_item['creditvalue'];
			$GLOBALS['collect']['threadBuy'][] = sprintf("('%s')", implode("', '", $_tmp));
		}
	}
	return array();
}
/*pw_bbs_posts表回调处理*/
function _callbackPosts($post, $oldData) {
	if ($post['fid'] == 0) {
		$post['disabled'] = 2;
	} else if ($post['ischeck'] == 0) {
		$post['disabled'] = 1;
	} else {
		$post['disabled'] = 0;
	}
	$content = unescapeStr($post['content']);
	$content = preg_replace("/\[sell=(\d+)(\,(money|rvrc|credit|currency|\d+))?\](.+?)\[\/sell\]/eis", "_buildSellAndHideCode('sell','\\1','\\3','\\4')", $content);
	$content = preg_replace("/\[hide=(\d+)(\,(money|rvrc|credit|currency|\d+))?\](.+?)\[\/hide\]/eis", "_buildSellAndHideCode('hide','\\1','\\3','\\4')", $content);
	$post['content'] = $content;
	
	$post['usehtml'] = $post['usehtml'] > 1 ? 1 : 0;
	
	$post['sell_count'] = 0;
	if (($buys = unserialize($oldData['buy']))) {
		$post['sell_count'] = count($buys);
	}
	$post['aids'] = intval($post['aids']);
	return $post;
}
/*pw_recycle_topic表回调处理*/
function _callbackRecycleTopic($topic, $row) {
	if ($row['pid']) return array();
	return $topic;
}
/*pw_recycle_reply回复回收站*/
function _callbackRecycleReply($reply, $row) {
	if (!$row['pid']) return array();
	return $reply;
}
/*pw_tag_relation表回调处理*/
function _callbackTagdata($tagdata) {
	$tagdata['type_id'] = 1;
	$tagdata['content_tag_id'] = $tagdata['tag_id'];
	return $tagdata;
}
/*pw_user_tag表回调处理*/
function _callbackMemberTag($tagdata) {
	$tagdata['ifhot'] = abs($tagdata['ifhot'] - 1);
	return $tagdata;
}
/*pw_tag表回调处理*/
function _callbackTag($tagdata) {
	$tagdata['ifhot'] = abs($tagdata['ifhot'] - 1);
	return $tagdata;
}
/*pw_windid_school表回调处理*/
function _callbackSchooldata($school) {
	$school['first_char'] = getFirstChar($school['name']);
	return $school;
}
/*pw_windid_area表回调处理*/
function _callbackAreaData($area) {
	$area['joinname'] = str_replace(',', '|', $area['joinname']);
	return $area;
}
/*pw_bbs_threads帖子处理*/
function _callbackBbsThreads($thread, $row) {
	$thread['subject'] = unescapeStr($thread['subject']);
	if ($thread['fid'] == 0) {
		//来自群组
		if ($thread['tpcstatus'] == 1) {
			$thread['ischeck'] = 1;
			$thread['disabled'] = 0;
			$GLOBALS['collect']['groupThreadTmp'][] = sprintf("(%d)", $thread['tid']);
		} else {
			$thread['disabled'] = 2;
		}
	} elseif ($row['ifcheck'] == 0) {
		$thread['disabled'] = 1;
	} else {
		$thread['disabled'] = 0;
	}
	if ($thread['ifupload'] == 3) {
		$thread['ifupload'] = 4;
	}
	if ($thread['highlight']) {
		list($color, $bold, $italic, $underline) = explode('~', $thread['highlight']);
		$thread['highlight'] = sprintf('%s~%s~%s~%s', $color ? $color : '', $bold ? 1 : '', $italic ? 1 : '', $underline ? 1 : '');
	}
	$thread['tpcstatus'] = 0;
	//锁定贴
	if ($row['locked']) {
		$thread['tpcstatus'] += 1;
	}
	if ($thread['special'] == 1) {
		$thread['special'] = 'poll';
	} else {
		$thread['special'] = '';
	}
	return $thread;
}
/*pw_bbs_threads_index帖子索引页更新*/
function _callbackBbsThreadsIndex($thread, $row) {
	if ($thread['fid'] == 0) {
		//来自群组
		if ($row['tpcstatus'] == 1) {
			$thread['disabled'] = 0;
		} else {
			$thread['disabled'] = 2;
		}
	} elseif ($row['ifcheck'] == 0) {
		$thread['disabled'] = 1;
	} else {
		$thread['disabled'] = 0;
	}
	return $thread;
}
/*pw_bbs_threads_cate_index帖子分类的索引处理*/
function _callbackThreadCateIndex($thread, $row) {
	static $map = null;
	if ($map === null) {
		$map = _getForumTypeMaps();
	}
	if ($thread['fid'] == 0) {
		$thread['disabled'] = 2;
	} elseif ($row['ifcheck'] == 0) {
		$thread['disabled'] = 1;
	} else {
		$thread['disabled'] = 0;
	}
	$thread['cid'] = intval($map[$thread['fid']]);
	return $thread;
}
/*pw_announce论坛公告处理*/
function _callbackAnnounce($ann, $row) {
	//只处理全局公告
	if ($row['fid'] > 0) {
		return array();
	}
	$ann['typeid'] = $ann['content'] ? 0 : 1;
	$ann['created_userid'] = 1;
	$ann['end_date'] = intval($ann['end_date']);
	return $ann;
}
/*pw_link友情链接*/
function _callbackLink($link, $row) {
	$link['name'] = unescapeStr($link['name']);
	$link['descrip'] = unescapeStr($link['descrip']);
	return $link;
}
/*pw_bbs_forum回调处理*/
function _callbackForums($forum, $oldForum) {
	if ($oldForum['title'] || $oldForum['metadescrip'] || $oldForum['keywords']) {
		$data = array();
		$data[] = $oldForum['title'] ? $GLOBALS['targetDb']->escape_string($oldForum['title']) : '';
		$data[] = $oldForum['keywords'] ? $GLOBALS['targetDb']->escape_string($oldForum['keywords']) : '';
		$data[] = $oldForum['metadescrip'] ? $GLOBALS['targetDb']->escape_string($oldForum['metadescrip']) : '';
		$GLOBALS['seo'][] = sprintf("('bbs', 'thread', %d, '%s')", $forum['fid'], implode("','", $data));
	}
	return $forum;
}
/*pw_bbs_forum_extra版块的扩展信息处理*/
function _callbackForumExtra($forum) {
	global $srcDb;
	if ($forum['settings_basic']) {
		$newForumset = array();
		$_tmpForum = $srcDb->get_one("SELECT * FROM pw_forums WHERE fid={$forum['fid']}");
		$newForumset['allowtype'] = $newForumset['typeorder'] = array();
		if ($_tmpForum['allowtype']) {
			if ($_tmpForum['allowtype'] & 1) {
				$newForumset['allowtype'][] = 'default';
				$newForumset['typeorder']['default'] = 0;
			}
			if ($_tmpForum['allowtype'] & 2) {
				$newForumset['allowtype'][] = 'poll';
				$newForumset['typeorder']['poll'] = 0;
			}
		}
		$newForumset['allowhide'] = $_tmpForum['allowhide'];
		$newForumset['allowsell'] = $_tmpForum['allowsell'];
		$newForumset['contentcheck'] = $_tmpForum['f_check'];
		$newForumset['topic_type'] = $_tmpForum['t_type'] ? 1 : 0;
		$newForumset['force_topic_type'] = $_tmpForum['t_type'] == 2 ? 1 : 0;
		
		$forumset = unserialize($forum['settings_basic']);
		if (is_array($forumset)) {
			$newForumset['jumpurl'] = $forumset['link'];
			$newForumset['numofthreadtitle'] = $forumset['cutnums'];
			$newForumset['threadperpage'] = $forumset['threadnum'];
			$newForumset['readperpage'] = $forumset['readnum'];
			$newForumset['minlengthofcontent'] = $forumset['contentminlen'];
			$newForumset['locktime'] = $forumset['lock'];
			$newForumset['edittime'] = $forumset['postedittime'];
			$newForumset['ifthumb'] = intval($forumset['allowsell']);
			$newForumset['threadorderby'] = $forumset['orderway'] == 'postdate' ? 1 : 0;
			if ($forumset['thumbsize']) {
				list($_tmpThumbWidth, $_tmpThumbHeight) = explode("\t", $forumset['thumbsize']);
				$newForumset['thumbwidth'] = $_tmpThumbWidth;
				$newForumset['thumbheight'] = $_tmpThumbHeight;
			}
			$newForumset['water'] = $forumset['watermark'] == 1 ? 0 : 2;
			$newForumset['topic_type_display'] = $forumset['addtpctype'];
			if ($forumset['newtime']) {
				$newTime = intval($forumset['newtime']) / 60;
				$GLOBALS['targetDb']->query("UPDATE pw_bbs_forum SET newtime='{$newTime}' WHERE fid='{$forum['fid']}'");
			}
		}
		$newForumset = serialize($newForumset);
		$forum['settings_basic'] = $newForumset;
	}
	if ($forum['settings_credit']) {
		$newCreditset = array();
		$creditset = unserialize($forum['settings_credit']);
		$assoc = array(
			'Post' => 'post_topic', 
			'Delete' => 'delete_topic', //负号需要带过去
			'Reply' => 'post_reply', 
			'Deleterp' => 'delete_reply', //负号需要带过去
			'Digest' => 'digest_topic', 
			'Undigest' => 'remove_digest',//负号需要带过去
			//'upload_att' => '',//原来只有单个积分设置在forumset
			//'download_att' => '',
		);
		if ($creditset) {
			$_un = array('Delete', 'Deleterp', 'Undigest');
			foreach ($creditset as $k => $v) {
				$newKey = $assoc[$k];
				$_of = in_array($k, $_un);
				foreach ($v as $k2 => $v2) {
					$newId = getCreditMap($k2);
					if (!$newId) continue;
					$newCreditset[$newKey]['credit'][$newId] = $_of ? -$v2 : $v2;
				}
			}
		}
		$newCreditset = serialize($newCreditset);
		$forum['settings_credit'] = $newCreditset;
	}
	return $forum;
}
/*pw_app_poll投票帖处理*/
function _callbackThreadPoll($poll, $oldData) {
	$poll['isinclude_img'] = $poll['app_type'] = 0;
	$tid = $oldData['tid'];
	/* $threadInfo = $GLOBALS['srcDb']->get_one("SELECT * FROM pw_threads WHERE tid='{$tid}'");
	$poll['created_time'] = $threadInfo['postdate'];
	$poll['created_userid'] = $threadInfo['authorid']; */
	$GLOBALS['collect']['pollTids'][$tid] = $poll['poll_id'];
	$poll['expired_time'] = $poll['expired_time'] ? (86400 * intval($poll['expired_time'])) : 0;//过期时间秒
	$GLOBALS['collect']['expired_time'][$poll['poll_id']] = $poll['expired_time'];

	$voteopts = unserialize($oldData['voteopts']);
	if ($voteopts) {
		$GLOBALS['collect']['voteopts'][$tid] = $voteopts;
	}
	$poll['option_limit'] = intval($poll['option_limit']);
	$poll['isafter_view'] = intval($poll['isafter_view']);
	$poll['voter_num'] = intval($poll['voter_num']);
	return $poll;
}
/*pw_attachs_thread转换*/
function _callbackAttachsThread($attach, $oldData) {
	$attach['ctype'] = intval(getCreditMap($attach['ctype']));
	return $attach;
}
/*pw_attachs_thread_buy|pw_attachs_thread_download帖子附件购买记录处理*/
function _callbackAttachThreadBuy($new, $old) {
	$new['ctype'] = getCreditMap($new['ctype']);
	return $new;
}
/*pw_task任务回调方法*/
function _callbackTask($newData, $oldData) {
	if ($oldData['isuserguide']) {
		return array();
	}
	//完成条件
	$_condition = array();
	switch (strtolower($oldData['job'])) {
		case 'doupdatedata':
			$_condition['type'] = 'member';
			$_condition['child'] = 'profile';
			$_condition['url'] = 'profile/index/run';
			break;
		case 'doupdateavatar':
			$_condition['type'] = 'member';
			$_condition['child'] = 'avatar';
			$_condition['url'] = 'profile/avatar/run?_left=avatar';
			break;
		case 'dosendmessage':
			$_oldC = unserialize($newData['conditions']);
			$_condition['type'] = 'member';
			$_condition['child'] = 'msg';
			$_condition['name'] = $_oldC['user'];
			$_condition['url'] = sprintf('message/message/add?username=%s', $_oldC['user']);
			break;
			/* case 'doaddfriend':
				$_oldC = unserialize($newData['conditions']);
			$_condition['type'] = 'member';
			$_condition['child'] = 'fans';
			$_condition['num'] = $_oldC['num'] ? $_oldC['num'] : 1;
			$_condition['url'] = 'my/fans/run';
			break; */
		case 'dopost':
			$_oldC = unserialize($newData['conditions']);
			$_condition['type'] = 'bbs';
			$_condition['child'] = 'postThread';
			$_condition['num'] = $_oldC['num'];
			$_condition['fid'] = $_oldC['fid'];
			$_condition['url'] = sprintf('bbs/post/run?fid=%d', $_oldC['fid']);
			break;
		case 'doreply':
			$_oldC = unserialize($newData['conditions']);
			if ($_oldC['type'] == 1) {
				$_condition['type'] = 'bbs';
				$_condition['child'] = 'reply';
				$_condition['num'] = $_oldC['replynum'];
				$_condition['tid'] = $_oldC['tid'];
				$_condition['url'] = sprintf('bbs/read/run?tid=%d', $_oldC['tid']);
			}
			break;
		default:
			break;
	}
	if (!$_condition) return array();
	$newData['is_display_all'] = $newData['is_display_all'] == 1 ? 0 : 1;
	$newData['conditions'] = serialize($_condition);
	$newData['end_time'] = $newData['end_time'] ? $newData['end_time'] : 4197024000;
	$newData['icon'] = $newData['icon'] ? 'job/' . $newData['icon'] : '';
	$newData['user_groups'] = trim($newData['user_groups']);
	$newData['user_groups'] = empty($newData['user_groups']) ? -1 : $newData['user_groups'];
	if ($newData['is_open']) {
		$userGroup = $newData['user_groups'] == -1 ? array(-1) : explode(',', $newData['user_groups']);
		foreach ($userGroup as $_gid) {
			$GLOBALS['collect']['taskGroups'][] = sprintf("('%s', '%s', '%s', '%s')", $newData['taskid'], $_gid, $newData['is_auto'], $newData['end_time']);
		}
	}
	//奖励转换
	if (!$newData['reward']) {
		$newData['reward'] = serialize(array());
	} else {
		$reward = unserialize($newData['reward']);
		if (!$reward || !in_array($reward['category'], array('credit', 'usergroup'))) {
			$newData['reward'] = serialize(array());
		} else {
			$_reward = array();
			switch ($reward['category']) {
				case 'usergroup':
					$_groupInfo = getGroupInfo($reward['type']);
					$_reward['type'] = 'group';
					$_reward['key'] = 'id-name';
					$_reward['value'] = $reward['type'] . '-' . $_groupInfo['grouptitle'];
					$_reward['time'] = $reward['day'];
					$_reward['descript'] = sprintf('用户组[%s]%s天', $_groupInfo['grouptitle'], $reward['day']);
					break;
				case 'credit':
					$_creditId = getCreditMap($reward['type']);
					$_credit = getCreditInfo($_creditId);
					$_reward['type'] = 'credit';
					$_reward['key'] = 'id-name-unit';
					$_reward['value'] = $_creditId . '-' . $_credit['name'] . '-' . $_credit['unit'];
					$_reward['num'] = $reward['num'];
					$_reward['descript'] = sprintf('%s%s%s', $reward['num'], $_credit['unit'], $_credit['name']);
					break;
				default:
					break;
			}
			$newData['reward'] = serialize($_reward);
		}
	}
	
    $tmp = array($newData['period'] > 0 ? 1 : 0, $newData['is_auto'] > 0 ? 1 : 0, isset($_condition['num']) ? $_condition['num'] : 0);
    $GLOBALS['collect']['taskTmp'][] = sprintf("(%d, '%s')", $newData['taskid'], implode(',', $tmp));
	return $newData;
}
/*pw_task_user回调处理*/
function _callbackTaskUser($newData, $oldData) {
	$_taskCache = $GLOBALS['targetDb']->get_one('SELECT * FROM tmp_task WHERE task_id=' . $newData['taskid']);
	if (!$_taskCache) return array();
	list($newData['is_period'], $_auto, $_num) = explode(',', $_taskCache['extends']);
	$newData['step'] = $oldData['total'] ? array('total' => $oldData['total']) : array();
	if ($_num) {
		$newData['step']['num'] = $oldData['step'];
		$newData['step']['percent'] = intval(($oldData['step'] / $_num) * 100) . '%';
	}
	$newData['step'] = serialize($newData['step']);
	//任务状态
	$status = $newData['task_status'];
	switch ($status) {
		case 2:
			$newData['task_status'] = 2;
			break;
		case 3:
			$newData['task_status'] = 4;
			break;
		case 0:
		case 1:
		default:
			$newData['task_status'] = 1;
			break;
	}
	//更新用户任务缓存
	!isset($GLOBALS['userTaskCache'][$newData['uid']]) && $GLOBALS['userTaskCache'][$newData['uid']] = array(0, array());
	if ($_auto) {
		$GLOBALS['userTaskCache'][$newData['uid']][0] = max($newData['taskid'], $GLOBALS['userTaskCache'][$newData['uid']][0]);
	}
	if ($newData['is_period'] && $newData['task_status'] == 4) {
		$GLOBALS['userTaskCache'][$newData['uid']][1] = $newData['taskid'];
	}
	return $newData;
}
/*pw_medal_info回调*/
function _callbackMedalInfo($newData, $oldData) {
	if (in_array($oldData['associate'], array('continue_post', 'post'))) {
		return array();
	}
	
	//'associate' => 'receive_type',// 勋章颁发条件类型
	switch ($newData['award_type']) {
		case 'continue_login':
			$newData['award_type'] = 1;
			break;
		case 'continue_thread_post':
			$newData['award_type'] = 3;
			break;
		case 'shafa':
			$newData['award_type'] = 4;
			break;
		case 'fans':
			$newData['award_type'] = 5;
			break;
		default:
			//手动勋章
			if ($newData['receive_type'] != 2) {
				return array();
			}
			$newData['award_type'] = 0;
			break;
	}
	$newData['medal_type'] = 2;

	//87image:images/medal/[big|small]/xxx.jpg image:res/images/medal/big/xxx.jpg  icon:res/images/medal/icon/xxx.jpg
	$newData['image'] = 'big/' . $oldData['image'];
	$newData['icon'] = 'small/' . $oldData['image'];
	//'allow_group' => 'medal_gids',//允许使用的用户组 87序列号的GID列表，9字符串GID列表
	$_gids = $newData['medal_gids'] ? unserialize($newData['medal_gids']) : array();
	$newData['medal_gids'] = implode(',', $_gids);

	//'expired_days' //勋章的过期时间
	/* if (颁发机制是自动颁发，) 过期时间默认是永久即为0
	* if (自动颁发，并且颁发条件是1,2,3连续性的) 过期时间为3天
	* if (手动颁发) 过期时间为永久并且颁发条件清空为0
	*/
	$newData['expired_days'] = 0;
	if (2 == $newData['receive_type']) {
		$newData['expired_days'] = $newData['award_condition'] = 0;
	} elseif (in_array($newData['award_type'], array(1, 2, 3))) {
		$newData['expired_days'] = 3;
	}
	//'' => 'path' //图片路径，87中不允许自己上传所以转换的时候该值为空
	$tmp = array($newData['receive_type'], $newData['expired_days']);
	$GLOBALS['collect']['medalTmp'][] = sprintf("(%d, '%s')", $newData['medal_id'], implode(',', $tmp));
	return $newData;
}
/*pw_medal_log回调*/
function _callbackMedalLog($newData, $oldData) {
	$_medal = $GLOBALS['targetDb']->get_one('SELECT * FROM tmp_medal WHERE medal_id = ' . $newData['medal_id']);
	if (!$_medal) return array();
	list($_receive_type, $_expired_days) = explode(',', $_medal['extends']);
	//'' => 'expired_time', //过期时间
	/* 勋章颁发有效期 过期时间
	 * if(勋章颁发类型 == 2 && 勋章的有效期expired_days > 0) ? ($time + $info['expired_days']*24*60) : 0;
	 */
	$newData['expired_time'] = 0;
	if ($_receive_type == 2 && $_expired_days > 0) {
		$newData['expired_time'] = $newData['created_time'] + $_expired_days * 24 * 60;
	}
	//'' => 'award_status',//状态
	//从87中的pw_medal_apply表中转过来状态为2,从pw_medal_award表转过来状态为4
	$newData['award_status'] = $GLOBALS['_userMedalLogStatus'];
	//pw_medal_award中type
	//'' => 'log_order',//顺序
	if ($newData['award_status'] == 4) {
		if (!isset($GLOBALS['userMedal'][$newData['uid']])) $GLOBALS['userMedal'][$newData['uid']] = array();
		$GLOBALS['userMedal'][$newData['uid']][$newData['medal_id']] = $newData['expired_time'];
	}
	return $newData;
}
/*pw_weibo发送的微博处理*/
function _callbackWeibo($new, $old) {
	if ($new['type'] > 0) return array();
	$new['type'] = 0;
	$GLOBALS['collect']['weiboTmp'][] = sprintf("(%d)", $new['weibo_id']);
	//产生新鲜事
	$GLOBALS['collect']['weiboFresh'][] = sprintf("(3,%d,%d,%d)", $new['weibo_id'], $new['created_userid'], $new['created_time']);
	$new['content'] = unescapeStr($new['content']);
	return $new;
}
/*pw_attention_fresh添加新鲜事*/
function _callbackFresh($data) {
	$sql = "INSERT INTO pw_attention_fresh (`type`, `src_id`, `created_userid`, `created_time`) VALUES %s";
	$GLOBALS['targetDb']->query(sprintf($sql, implode(',', $data)));
}

/** end callback functions **/
/*获取输入串的首字母*/
function getFirstChar($name) {
	global $charset;
	$asc = ord($name[0]);
	if ($asc < 160) { //非中文
		if ($asc >= 48 && $asc <= 57) {
			return $name[0]; //数字
		} elseif (($asc >= 65 && $asc <= 90) || ($asc >= 97 && $asc <= 122)) {
			return strtoupper($name[0]); // A--Z
		} else {
			return '~'; //其他
		}
	} else { //中文
		$str = strtolower($charset) == 'utf8' ? @iconv("UTF-8", "GB2312//IGNORE", $name) : $name;
		$asc = ord($str[0]) * 1000 + ord($str[1]);
		if ($asc >= 176161 && $asc < 176197) {
			return 'A';
		} elseif ($asc >= 176197 && $asc < 178193) {
			return 'B';
		} elseif ($asc >= 178193 && $asc < 180238) {
			return 'C';
		} elseif ($asc >= 180238 && $asc < 182234) {
			return 'D';
		} elseif ($asc >= 182234 && $asc < 183162) {
			return 'E';
		} elseif ($asc >= 183162 && $asc < 184193) {
			return 'F';
		} elseif ($asc >= 184193 && $asc < 185254) {
			return 'G';
		} elseif ($asc >= 185254 && $asc < 187247) {
			return 'H';
		} elseif ($asc >= 187247 && $asc < 191166) {
			return 'J';
		} elseif ($asc >= 191166 && $asc < 192172) {
			return 'K';
		} elseif ($asc >= 192172 && $asc < 194232) {
			return 'L';
		} elseif ($asc >= 194232 && $asc < 196195) {
			return 'M';
		} elseif ($asc >= 196195 && $asc < 197182) {
			return 'N';
		} elseif ($asc >= 197182 && $asc < 197190) {
			return 'O';
		} elseif ($asc >= 197190 && $asc < 198218) {
			return 'P';
		} elseif ($asc >= 198218 && $asc < 200187) {
			return 'Q';
		} elseif ($asc >= 200187 && $asc < 200246) {
			return 'R';
		} elseif ($asc >= 200246 && $asc < 203250) {
			return 'S';
		} elseif ($asc >= 203250 && $asc < 205218) {
			return 'T';
		} elseif ($asc >= 205218 && $asc < 206244) {
			return 'W';
		} elseif ($asc >= 206244 && $asc < 209185) {
			return 'X';
		} elseif ($asc >= 209185 && $asc < 212209) {
			return 'Y';
		} elseif ($asc >= 212209) {
			return 'Z';
		} else {
			return '~';
		}
	}
}
/*转移头像*/
function _moveAvatar($user) {
	if ($GLOBALS['setupConfig']['db_ftp']['db_ifftp']) return true;
	if ($user['icon']) {
		$udir = str_pad(substr($user['uid'], -2), 2, '0', STR_PAD_LEFT);
		$img = $udir . '/' . $user['uid'] . '.jpg';
		$avatarBigUrl = NEXTWIND_DIR . DS . 'attachment/upload/middle' . DS . $img;
		$avatarMiddleUrl = NEXTWIND_DIR . DS . 'attachment/upload/middle' . DS . $img;
		$avatarSmallUrl = NEXTWIND_DIR . DS . 'attachment/upload/small' . DS . $img;
	} else {
		//default avatar
		$avatarBigUrl = NEXTWIND_DIR . DS . 'res/images/face/face_big.jpg';
		$avatarMiddleUrl = NEXTWIND_DIR . DS . 'res/images/face/face_middle.jpg';
		$avatarSmallUrl = NEXTWIND_DIR . DS . 'res/images/face/face_small.jpg';
	}
	$uid = sprintf("%09d", $user['uid']);
	$newDir = NEXTWIND_DIR . DS . 'attachment/avatar/' . substr($uid, 0, 3) . '/' . substr($uid, 3, 2) . '/' . substr($uid, 5, 2);
	file_exists($newDir) or mkdirRecur($newDir);
	file_exists($avatarBigUrl) && copy($avatarBigUrl, $newDir . DS . $user['uid'] . '.jpg');
	file_exists($avatarMiddleUrl) && copy($avatarMiddleUrl, $newDir . DS . $user['uid'] . '_middle.jpg');
	file_exists($avatarSmallUrl) && copy($avatarSmallUrl, $newDir . DS . $user['uid'] . '_small.jpg');
	return true;
}
/*根据用户id列表批量获取用户名*/
function _getUserNamesByUids($uids) {
	global $srcDb;
	$ret = array();
	if (!$uids) return $ret;
	$query = $srcDb->query(sprintf("SELECT uid,username FROM pw_members WHERE uid IN (%s)", implode(',', $uids)));
	while ($row = $srcDb->fetch_array($query)) {
		$ret[$row['uid']] = $row['username'];
	}
	return $ret;
}

/*根据用户名字列表获取用户id列表*/
function _getUidsByUsernames($usernames) {
	global $srcDb;
	$ret = array();
	if (!$usernames) return $ret;
	$query = $srcDb->query(sprintf("SELECT uid,username FROM pw_members WHERE username IN ('%s')", implode("','", $usernames)));
	while ($row = $srcDb->fetch_array($query)) {
		$ret[$row['username']] = $row['uid'];
	}
	return $ret;
}

/*获取版块Fid对应分类ID map*/
function _getForumTypeMaps() {
	global $srcDb;
	$ret = $f = $s = $s2 = array();
	$query = $srcDb->query("SELECT fid,fup,type FROM pw_forums");
	while ($row = $srcDb->fetch_array($query)) {
		switch ($row['type']) {
			case 'category':
				$ret[$row['fid']] = $row['fid'];
				break;
			case 'forum':
				$f[$row['fup']][] = $row['fid'];
				break;
			case 'sub':
				$s[$row['fup']][] = $row['fid'];
				break;
			case 'sub2':
				$s2[$row['fup']][] = $row['fid'];
				break;
		}
	}
	foreach ($f as $k => $v) {
		foreach ($v as $v2)
			$ret[$v2] = $k;
	}
	foreach ($s as $k => $v) {
		foreach ($v as $v2)
			$ret[$v2] = $ret[$k];
	}
	foreach ($s2 as $k => $v) {
		foreach ($v as $v2)
			$ret[$v2] = $ret[$k];
	}
	return $ret;
}

/*反解87中的escapeStr方法*/
function unescapeStr($string) {
	/* $string = str_replace(array("\0","%00","\r"), '', $string); //modified@2010-7-5
	$string = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $string);
	$string = str_replace(array("%3C",'<'), '&lt;', $string);
	$string = str_replace(array("%3E",'>'), '&gt;', $string);
	$string = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $string); */
	$search = array('&ensp;', '&emsp;', '&nbsp;', '&lt;', '&gt;', '&amp;', '&quot;', '&copy;', '&reg;', '™', '&times;', '&divide;');
	$replace = array(' ', ' ', ' ', '<', '>', '&', '"', '©', '®', '™', '×', '÷');
	$string = str_replace($search, $replace, $string);
	
	$search = array('&#8194;', '&#8195;', '&#160;', '&#60;', '&#62;', '&#38;', '&#34;', '&#39;', '&#169;', '&#174;', '&#8482;', '&#215;', '&#247;');
	$replace = array(' ', ' ', ' ', '<', '>', '&', '"', "'", '©', '®', '™', '×', '÷');
	$string = str_replace($search, $replace, $string);
	return $string;
}

/*将87中的积分ID或是name转换为9中新的ID号 返回*/
function getCreditMap($pw87, $pre = false) {
	$creditMap = $GLOBALS['setupConfig']['db_credit'];
	return isset($creditMap[$pw87]) ? ($pre ? 'credit' . $creditMap[$pw87] : $creditMap[$pw87]) : '';
}
/*pw_common_config获得积分的配置信息*/
function getCreditInfo($id = '') {
	static $credits = array();
	if (!$credits) {
		$sql = "SELECT * FROM pw_common_config WHERE namespace='credit' AND name='credits'";
		$one = $GLOBALS['targetDb']->get_one($sql);
		$credits = unserialize($one['value']);
	}
	return $id ? $credits[$id] : $credits;
}
/*pw_usergroups 获得用户组信息*/
function getGroupInfo($gid = '') {
	static $groups = array();
	if (!$groups) {
		$sql = 'SELECT * FROM pw_usergroups';
		$rt = $GLOBALS['srcDb']->query($sql);
		while ($one = $GLOBALS['srcDb']->fetch_array($rt)) {
			$groups[$one['gid']] = $one;
		}
	}
	return $gid ? $groups[$gid] : $groups;
}
/*日志记录*/
function writeError($errno, $errstr, $errfile, $errline, $errcontext) {
	if (error_reporting() == 0) return false;
	$errorD = sprintf("[%s] %s(%d): \"%s\" In file %s (%s)Line[%s-%s].\r\n", date("Y-m-d H:i:s"), 'ERROR', $errno, $errstr, $errfile, $errline, $GLOBALS['step'], $GLOBALS['action']);
	file_put_contents($GLOBALS['errorLogFile'], $errorD, FILE_APPEND);
	return false;
}

/*创建临时表*/
function createTmpTables($charset) {
	$tmpTables = array();
	//原87中没有和版块关联的群组中的数据转移到版块中，中间表
	$tmpTables[] = "DROP TABLE IF EXISTS `tmp_group_to_thread`";
	$tmpTables[] = "CREATE TABLE `tmp_group_to_thread` (
	`tid` int(10) unsigned NOT NULL DEFAULT '0',
	`cid` smallint(5) unsigned NOT NULL DEFAULT '0',
	`fid` smallint(5) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`tid`)
	) ENGINE=myisam DEFAULT CHARSET=$charset";
	//原87中能够转移到9里的任务相关数据临时表
	$tmpTables[] = "DROP TABLE IF EXISTS `tmp_task`";
	$tmpTables[] = "CREATE TABLE `tmp_task` (
	`task_id` int(10) unsigned NOT NULL DEFAULT '0',
	`extends` varchar(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`task_id`)
	) ENGINE=myisam DEFAULT CHARSET=$charset";
	//原87中能够转移到9里的勋章相关数据临时表
	$tmpTables[] = "DROP TABLE IF EXISTS `tmp_medal`";
	$tmpTables[] = "CREATE TABLE `tmp_medal` (
	`medal_id` int(10) unsigned NOT NULL DEFAULT '0',
	`extends` varchar(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`medal_id`)
	) ENGINE=myisam DEFAULT CHARSET=$charset";
	//原87中用户发布的微博数据转移中间临时表
	$tmpTables[] = "DROP TABLE IF EXISTS `tmp_weibo`";
	$tmpTables[] = "CREATE TABLE `tmp_weibo` (
	`weibo_id` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`weibo_id`)
	) ENGINE=myisam DEFAULT CHARSET=$charset";
	foreach ($tmpTables as $_sql) {
		$GLOBALS['targetDb']->query($_sql);
	}
	//添加辅助索引:idx_fromuid_touid
	$_indexs = $GLOBALS['targetDb']->get_all('SHOW INDEX FROM pw_windid_message_dialog', MYSQL_ASSOC, 'Key_name');
	if (!array_key_exists('idx_fromuid_touid', $_indexs)) {
		$GLOBALS['targetDb']->query('ALTER TABLE pw_windid_message_dialog ADD KEY `idx_fromuid_touid` (`from_uid`,`to_uid`)');
	}
}
/*删除临时表*/
function dropTmpTables() {
	$tables = array('tmp_group_to_thread', 'tmp_task', 'tmp_medal', 'tmp_weibo');
	foreach ($tables as $_t) {
		$GLOBALS['targetDb']->query(sprintf('DROP TABLE IF EXISTS %s', $_t));
	}
	//删除辅助索引
	$_indexs = $GLOBALS['targetDb']->get_all('SHOW INDEX FROM pw_windid_message_dialog', MYSQL_ASSOC, 'Key_name');
	if (array_key_exists('idx_fromuid_touid', $_indexs)) {
		$GLOBALS['targetDb']->query('ALTER TABLE pw_windid_message_dialog DROP KEY `idx_fromuid_touid`');
	}
}
/*检查目录可写行*/
function _checkWriteAble($pathfile) {
	if (!$pathfile) return false;
	$isDir = substr($pathfile, -1) == '/' ? true : false;
	if ($isDir) {
		if (is_dir($pathfile)) {
			mt_srand((double) microtime() * 1000000);
			$pathfile = $pathfile . 'pw_' . uniqid(mt_rand()) . '.tmp';
		} elseif (@mkdir($pathfile)) {
			return _checkWriteAble($pathfile);
		} else {
			return false;
		}
	}
	@chmod($pathfile, 0777);
	$fp = @fopen($pathfile, 'ab');
	if ($fp === false) return false;
	fclose($fp);
	$isDir && @unlink($pathfile);
	return true;
}

/*配置转换处理*/
class Config {
	static public function transferConfig($namespace, $configMap, $callBack = '') {
		if (!$configMap) return false;
		$sql = "SELECT * FROM pw_config WHERE db_name IN ('" . implode("','", array_keys($configMap)) . "')";
		$oldConfig = $GLOBALS['srcDb']->get_all($sql, MYSQL_ASSOC, 'db_name');
		$newConfig = array();
		foreach ($oldConfig as $_oldN => $_oldT) {
			$_tmpNew = $configMap[$_oldN];
			if (empty($_tmpNew)) continue;
			if (is_array($_tmpNew) && $callBack) {
				$newConfig = call_user_func_array(array('Config', $callBack), array($newConfig, $oldConfig));
			} else {
				$newConfig[$_tmpNew] = $_oldT['db_value'];
				if ('array' == $_oldT['vtype']) {
					$newConfig[$_tmpNew] = unserialize($_oldT['db_value']);
				}
			}
		}
		return self::storeConfig($namespace, $newConfig);
	}
	static public function transEmail($newConfig, $oldConfig) {
		$newConfig['mailOpen'] = intval($oldConfig['ml_mailifopen']['db_value']);
		$newConfig['mailMethod'] = 'smtp';
		return $newConfig;
	}
	static public function transVerify($newConfig, $oldConfig) {
		$newConfig['content.length'] = 4;
		$newConfig['content.questions'] = array();
		$newConfig['content.showanswer'] = 0;
		$newConfig['content.type'] = array(3);
		$newConfig['randtype'] = array('size', 'angle', 'graph');
		$newConfig['type'] = 'image';
		$newConfig['showverify'] = array();
		$newConfig['height'] = 60;
		$newConfig['width'] = 240;
		$newConfig['voice'] = 0;
		$_check = intval($oldConfig['db_gdcheck']['db_value']);
		if ($_check == 63) {
			$newConfig['showverify'] = array(
				'register', 
				'userlogin', 
				'resetpwd', 
				'sendmsg', 
				'postthread', 
				'uploadpic', 
				'adminlogin');
		} elseif ($_check > 0) {
			if ($_check & 1) {
				$newConfig['showverify'][] = 'register';
			}
			if ($_check & 2) {
				$newConfig['showverify'][] = 'userlogin';
			}
			if ($_check & 4) {
				$newConfig['showverify'][] = 'postthread';
			}
			if ($_check & 8) {
				$newConfig['showverify'][] = 'sendmsg';
			}
			if ($_check & 16) {
				$newConfig['showverify'][] = 'resetpwd';
				$newConfig['showverify'][] = 'uploadpic';
			}
			if ($_check & 32) {
				$newConfig['showverify'][] = 'adminlogin';
			}
		}
		return $newConfig;
	}
	static public function transAttachment($newConfig, $oldConfig) {
		//附件类型和尺寸控制[KB]
		$newConfig['extsize'] = unserialize($oldConfig['db_uploadfiletype']['db_value']);
		//缩略设置
		if (1 == $oldConfig['db_ifathumb']['db_value']) {
			$type = intval($oldConfig['db_athumbtype']['db_value']);
			$newConfig['thumb'] = $type == 1 ? 2 : 1;
		} else {
			$newConfig['thumb'] = 0;
		}
		//缩略图大小设置
		list($newConfig['thumb.size.width'], $newConfig['thumb.size.height']) = explode("\t", $oldConfig['db_athumbsize']['db_value']);
		//水印位置
		$_pos = intval($oldConfig['db_waterpos']['db_value']);
		if (4 == $_pos) {
			$newConfig['mark.position'] = 7;
		} elseif (5 == $_pos) {
			$newconfig['mark.position'] = 8;
		} elseif (6 == $_pos) {
			$newConfig['mark.position'] = 9;
		} elseif (in_array($_pos, array(1, 2, 3))) {
			$newConfig['mark.position'] = $_pos;
		} else {
			$newConfig['mark.position'] = 5;
		}
		//为GIF图片加水印
		$newConfig['mark.gif'] = intval($oldConfig['db_ifgif']['db_value']) > 0 ? 1 : 0;
		//水印类型
		$_t = intval($oldConfig['db_watermark']['db_value']);
		if ($_t > 0) {
			$newConfig['mark.type'] = $_t;
			$newConfig['mark.markset'] = array('bbs', 'diary', 'cms', 'album');
		} else {
			$newConfig['mark.type'] = 2;
			$newConfig['mark.text'] = 'phpwind';
		}
		//水印字体
		$newConfig['mark.fontfamily'] = 'en_arial.ttf';
		//FTP设置
		$newConfig['storage.type'] = $oldConfig['db_ifftp']['db_value'] ? 'ftp' : 'local';
		//同步到头像使用ftp
		$windidConfig = $commonConfig = array();
		$windidConfig[] = sprintf("('attachment','storage.type','string','%s')", $newConfig['storage.type']);
		$_local = array('name' => '本地存储', 'alias' => 'local', 'avatarmanagelink' => '', 'description' => '本地存储。附件、图片等将存储在本地磁盘上。默认定义位置为 attachment', 'components' => array('path' => 'WINDID:library.storage.WindidStorageLocal'), 'managelink' => '');
		$windidConfig[] = sprintf("('storage','local','string','%s')", serialize($_local));
		
		$_ftp = array('name' => 'FTP 远程附件存储', 'alias' => 'ftp', 'avatarmanagelink' => 'windid/storage/ftp/', 'description' => 'FTP 远程附件存储', 'components' => array('path' => 'WINDID:library.storage.WindidStorageFtp'), 'managelink' => 'windid/storage/ftp/');
		$windidConfig[] = sprintf("('storage','ftp','string','%s')", serialize($_ftp));
		
		$commonConfig[] = sprintf("('site','avatar.storage','string','%s')", $newConfig['storage.type']);
		if ($oldConfig['db_ifftp']['db_value']) {
			$commonConfig[] = sprintf("('components','storage','array','%s')", 'a:1:{s:4:"path";s:24:"LIB:storage.PwStorageFtp";}');
			$windidConfig[] = sprintf("('attachment','avatarurl','string','%s')", $oldConfig['db_ftpweb']['db_value']);
			$windidConfig[] = sprintf("('attachment','ftp.server','string','%s')", $oldConfig['ftp_server']['db_value']);
			$windidConfig[] = sprintf("('attachment','ftp.port','string','%s')", $oldConfig['ftp_port']['db_value']);
			$windidConfig[] = sprintf("('attachment','ftp.user','string','%s')", $oldConfig['ftp_user']['db_value']);
			$windidConfig[] = sprintf("('attachment','ftp.pwd','string','%s')", $oldConfig['ftp_pass']['db_value']);
			$windidConfig[] = sprintf("('attachment','ftp.timeout','string','%s')", $oldConfig['ftp_timeout']['db_value']);
			$windidConfig[] = sprintf("('attachment','ftp.dir','string','%s')", $oldConfig['ftp_dir']['db_value']);
			$commonConfig[] = sprintf("('site','avatar.url','string','%s')", $oldConfig['db_ftpweb']['db_value']);
		} else {
			$commonConfig[] = sprintf("('components','storage','array','%s')", 'a:1:{s:4:"path";s:26:"LIB:storage.PwStorageLocal";}');
		}
		$GLOBALS['targetDb']->query(sprintf('REPLACE INTO pw_common_config (`namespace`, `name`,`vtype`,`value`) VALUES %s', implode(',', $commonConfig)));
		$GLOBALS['targetDb']->query(sprintf('REPLACE INTO pw_windid_config (`namespace`, `name`,`vtype`,`value`) VALUES %s', implode(',', $windidConfig)));
		return $newConfig;
	}
	static public function transUbb($newConfig, $oldConfig) {
		$value = unserialize($oldConfig['db_windpost']['db_value']);
		if (is_array($value)) {
			$newConfig['ubb.img.open'] = $value['pic'];
			$newConfig['ubb.img.width'] = $value['picwidth'];
			$newConfig['ubb.img.height'] = $value['picheight'];
			$newConfig['ubb.size.max'] = $value['size'];
			$newConfig['ubb.flash.open'] = $value['flash'];
			$newConfig['ubb.media.open'] = $value['mpeg'];
			$newConfig['ubb.iframe.open'] = $value['iframe'];
		}
		//定时审核帖子
		$_opencheck = explode("\t", $oldConfig['db_openpost']['db_value']);
		$newConfig['post.check.open'] = $_opencheck[0];
		$newConfig['post.check.start_hour'] = $_opencheck[1];
		$newConfig['post.check.start_min'] = $_opencheck[2];
		$newConfig['post.check.end_hour'] = $_opencheck[3];
		$newConfig['post.check.end_min'] = $_opencheck[4];
		//预设帖子楼层名称
		$_tmp = '';
		if (isset($oldConfig['db_floorname'])) {
			$_oldFloor = unserialize($oldConfig['db_floorname']['db_value']);
			if (is_array($_oldFloor)) {
				$_last = 0;
				foreach ($_oldFloor as $_f => $_n) {
					if (empty($_tmp)) {
						$_tmp = $_f . ':' . $_n;
					} elseif ($_f - $_last == 1) {
						$_tmp .= ',' . $_n;
					} else {
						$_tmp .= "\r\n" . $_f . ':' . $_n;
					}
					$_last = $_f;
				}
			}
		}
		$newConfig['read.defined_floor_name'] = $_tmp;
		$newConfig['thread.new_thread_minutes'] = ceil(intval($oldConfig['db_newtime']['db_value']) / 60);
		//热帖回复数量,87没有该数据，默认设置为安装数据3
		$newConfig['thread.hotthread_replies'] = 3;
		//显示栏目
		$newConfig['read.display_info_vieworder'] = array(
			'uid' => '0', 
			'regdate' => '1', 
			'lastvisit' => '2', 
			'fans' => '3', 
			'follows' => '4', 
			'posts' => '5', 
			'homepage' => '6', 
			'location' => '7', 
			'qq' => '8', 
			'aliww' => '9', 
			'birthday' => '10', 
			'hometown' => '11', 
			'1' => '12', 
			'2' => '13', 
			'3' => '14', 
			'4' => '15', 
			'5' => '16', 
			'6' => '17', 
			'7' => '18', 
			'8' => '19');
		$_oldShow = unserialize($oldConfig['db_showcustom']['db_value']);
		$_newShow = array();
		if (is_array($_oldShow)) {
			foreach ($_oldShow as $_v) {
				$i = getCreditMap($_v);
				$_newShow[$i] = 1;
				if ($i > 8) {
					$newConfig['read.display_info_vieworder'][$i] = 11 + $i;
				}
			}
		}
		$newConfig['read.display_info'] = $_newShow;
		return $newConfig;
	}

	static public function transLogin($newConfig, $oldConfig) {
		$_oldGids = array();
		if (isset($oldConfig['db_safegroup'])) {
			$_oldGids = array_unique(explode(',', $oldConfig['db_safegroup']['db_value']));
		}
		$newConfig['question.groups'] = array();
		foreach ($_oldGids as $gid) {
			$gid > 0 && $newConfig['question.groups'][] = $gid;
		}
		$newConfig['resetpwd.mail.content'] = '尊敬的{username}，这是来自{sitename}的密码重置邮件。\n点击下面的链接重置您的密码：<br/>\n{url}<br/>\n如果链接无法点击，请将链接粘贴到浏览器的地址栏中访问。<br/>\n{sitename} <br/>\n{time}';
		$newConfig['resetpwd.mail.title'] = '{username}您好，这是{sitename}发送给您的密码重置邮件';
		$newConfig['trypwd'] = 5;
		$loginType = intval($oldConfig['db_logintype']);
		$newConfig['ways'] = array();
		if ($loginType & 1) {
			$newConfig['ways'][] = 3;
		}
		if ($loginType & 2) {
			$newConfig['ways'][] = 1;
		}
		if ($loginType & 4) {
			$newConfig['ways'][] = 2;
		}
		return $newConfig;
	}

	static public function transReg($newConfig, $oldConfig) {
		//注册状态
		$newConfig['type'] = $oldConfig['rg_allowregister']['db_value'];
		$invite = array(
			'inv_days' => 'invite.expired',  //有效期限
			'inv_credit' => 'invite.credit.type',  //消费积分类型
			'inv_onlinesell' => 'invite.pay.open',  //邀请码在线支付功能
			'inv_price' => 'invite.pay.money'		//支付金额[元]
		);
		$sql = "SELECT * FROM pw_hack WHERE hk_name IN ('" . implode("','", array_keys($invite)) . "')";
		$_invitConfig = $GLOBALS['srcDb']->get_all($sql, MYSQL_ASSOC, 'hk_name');
		foreach ($invite as $_ok => $_nk) {
			$newConfig[$_nk] = $_invitConfig[$_ok]['hk_value'];
		}
		
		//激活邮件rg_emailcheck
		$newConfig['active.mail'] = $oldConfig['rg_emailcheck']['db_value'];
		$newConfig['active.mail.content'] = '尊敬的{username}，\n<br/>欢迎你注册成为{sitename}的会员！\n<br/>请点击下面的链接进行帐号的激活：\n<br/>{url}\n<br/>如果不能点击链接，请复制到浏览器地址输入框访问。\n<br/>\n<br/>{sitename}\n<br/>{time}';
		$newConfig['active.mail.title'] = '来自{sitename}的注册激活邮件';
		
		//发送欢迎邮件
		$newConfig['welcome.type'] = array();
		if (1 == $oldConfig['rg_regsendemail']['db_value']) {
			$newConfig['welcome.type'][] = 2;
		}
		if (1 == $oldConfig['rg_regsendmsg']['db_value']) {
			$newConfig['welcome.type'][] = 1;
		}
		$newConfig['welcome.title'] = '欢迎你注册成为{sitename}的会员';
		$newConfig['welcome.content'] = str_replace('$rg_name', '{username}', $oldConfig['rg_welcomemsg']['db_value']);
		
		//用户名长度rg_namelen
		if (!$oldConfig['rg_namelen']['db_value']) {
			$newConfig['security.username.min'] = 1;
			$newConfig['security.username.max'] = 15;
		} else {
			$_tmp = explode("\t", $oldConfig['rg_namelen']['db_value']);
			if (empty($_tmp[0])) {
				$_tmp[0] = 1;
			} else {
				$_tmp[0] = abs(intval($_tmp[0]));
			}
			if (!isset($_tmp[1]) || empty($_tmp[1])) {
				$_tmp[1] = 15;
			} else {
				$_tmp[1] = abs(intval($_tmp[1]));
			}
			$min = min($_tmp[0], $_tmp[1]);
			$max = max($_tmp[0], $_tmp[1]);
			$newConfig['security.username.min'] = $min < 1 ? 1 : ($min > 15 ? 15 : $min);
			$newConfig['security.username.max'] = $max > 15 ? 15 : $max;
		}
		
		//密码长度rg_pwdlen
		if (!$oldConfig['rg_pwdlen']['db_value']) {
			$newConfig['security.password.min'] = 1;
			$newConfig['security.password.max'] = '';
		} else {
			$_tmp = explode("\t", $oldConfig['rg_pwdlen']['db_value']);
			if (empty($_tmp[0])) {
				$_tmp[0] = 1;
			} else {
				$_tmp[0] = abs(intval($_tmp[0]));
			}
			if (!isset($_tmp[1]) || empty($_tmp[1])) {
				$_tmp[1] = '';
			} else {
				$_tmp[1] = abs(intval($_tmp[1]));
			}
			list($min, $max) = $_tmp;
			$newConfig['security.password.min'] = $min < 1 ? 1 : $min;
			$newConfig['security.password.max'] = $max;
		}
		
		$pwComplex = $oldConfig['rg_pwdcomplex']['db_value'];
		$_tmp = explode(',', $pwComplex);
		$newConfig['security.password'] = array();
		foreach ($_tmp as $_k) {
			if (!in_array($_k, array(1, 2, 3, 4))) continue;
			$newConfig['security.password'][] = pow(2, intval($_k - 1));
		}
		if (1 == $oldConfig['rg_npdifferf']['db_value']) {
			$newConfig['security.password'][] = 9;
		}
		//短信默认数据
		$newConfig['mobile.message.content'] = '您的验证码是：{mobilecode}，请在页面填写验证码完成验证。（如非本人操作，可不予理会）【{sitename}】';
		
		$_data = array();
		$_data[] = "('reg', 'security.ban.username', 'string', '" . $newConfig['security.ban.username'] . "')";
		$_data[] = "('reg', 'security.username.min', 'string', '" . $newConfig['security.username.min'] . "')";
		$_data[] = "('reg', 'security.username.max', 'string', '" . $newConfig['security.username.max'] . "')";
		$_data[] = "('reg', 'security.password.min', 'string', '" . $newConfig['security.password.min'] . "')";
		$_data[] = "('reg', 'security.password.max', 'string', '" . $newConfig['security.password.max'] . "')";
		$sql = sprintf('REPLACE INTO pw_windid_config (`namespace`, `name`, `vtype`, `value`) VALUES %s', implode(',', $_data));
		$GLOBALS['targetDb']->query($sql);
		
		return $newConfig;
	}

	static public function transSiteSetting($newConfig, $oldConfig) {
		//'db_visitmsg' => '', //外部提示信息 visit.message
		//'db_whybbsclose' => '',//关闭状态说明
		$bbsifopen = $oldConfig['db_bbsifopen']['db_value'];
		switch (intval($bbsifopen)) {
			case 1: //完全开放
				$newConfig['visit.state'] = 0;
				break;
			case 2: //内部开放
				$newConfig['visit.state'] = 1;
				$newConfig['visit.message'] = $oldConfig['db_visitmsg']['db_value'];
				break;
			case 0: //完全关闭
			default:
				$newConfig['visit.state'] = 2;
				$newConfig['visit.message'] = $oldConfig['db_whybbsclose']['db_value'];
				break;
		}
		//允许访问用户组
		$gids = array_unique(explode(',', $oldConfig['db_visitgroup']['db_value']));
		$groups = self::getAllGroups();
		$newConfig['visit.group'] = array();
		foreach ($groups as $key => $_item) {
			$_tmpG = array_keys($_item);
			$p = array_intersect($_tmpG, $gids);
			if (!$p) continue;
			if (array_diff($_tmpG, $p)) continue;
			$newConfig['visit.group'][] = $key;
		}
		$newConfig['visit.gid'] = $gids;
		//cookie域设置
		$newConfig['cookie.path'] = $newConfig['cookie.domain'] = '';
		$newConfig['cookie.pre'] = 'nextwind_';
		$newConfig['onlinetime'] = ceil($oldConfig['db_onlinetime']['db_value'] / 60);
		//windid时区设置同步
		$newConfig['windid'] = 'local';//设置windid系统设置为本地系统
		$_windidData = array();
		$_windidData[] = "('site', 'timezone', 'string', '" . $newConfig['time.timezone'] . "')";
		$_windidData[] = "('site', 'timecv', 'string', '" . $newConfig['time.cv'] . "')";
		$sql = sprintf('REPLACE INTO pw_windid_config (`namespace`, `name`, `vtype`, `value`) VALUES %s', implode(',', $_windidData));
		$GLOBALS['targetDb']->query($sql);
		return $newConfig;
	}

	static public function storeConfig($namespace, $config) {
		$v = array();
		foreach ($config as $_k => $_v) {
			if (!$_k) continue;
			$type = 'string';
			if (is_array($_v)) {
				$type = 'array';
				$_v = serialize($_v);
			}
			$v[] = sprintf("('%s', '%s', '%s', '%s')", $_k, $namespace, $GLOBALS['targetDb']->escape_string($_v), $type);
		}
		if (!$v) return false;
		$sql = sprintf("REPLACE INTO `pw_common_config` (`name`, `namespace`, `value`, `vtype`) VALUES %s", implode(',', $v));
		return $GLOBALS['targetDb']->query($sql);
	}
	static public function getAllGroups() {
		static $groups = array();
		if ($groups) return $groups;
		$_groups = getGroupInfo();
		foreach ($_groups as $gid => $one) {
			$groups[$one['gptype']][$one['gid']] = $one['gid'];
		}
		return $groups;
	}
}
/*简单数据库操作*/
class DB {
	var $dbpre;
	var $link;

	function __construct($host, $port, $user, $password, $db, $pre = 'pw_') {
		$this->link = mysql_connect("$host:$port", $user, $password, true);
		$pre && $this->dbpre = $pre;
		if ($this->link) {
			mysql_query("SET character_set_connection=" . $GLOBALS['charset'] . ",character_set_results=" . $GLOBALS['charset'] . ",character_set_client=binary", $this->link);
			if (!mysql_select_db($db, $this->link)) {
				showError('87数据库 `' . $db . '` 不存在！请检查配置！');
			}
		} else {
			showError("Access denied for user '{$user}'@'{$host}' (using password: YES)");
		}
	}

	function query($sql) {
		if ($this->dbpre != 'pw_') {
			$sql = str_replace(array(' pw_', '`pw_', " 'pw_"), array(" $this->dbpre", "`$this->dbpre", " '$this->dbpre"), $sql);
		}
		$r = mysql_query($sql, $this->link);
		if (false === $r) {
			showError('SQL ERROR:' . mysql_error($this->link) . ' IN "' . $sql . '"');
		}
		return $r;
	}
	//TODO
	function getTableName($table) {
		if ($this->dbpre != 'pw_') {
			$table = str_replace('pw_', $this->dbpre, $table);
		}
		return $table;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function get_one($sql, $result_type = MYSQL_ASSOC) {
		$query = $this->query($sql);
		return mysql_fetch_array($query, $result_type);
	}

	function insert_id() {
		return $this->get_value('SELECT LAST_INSERT_ID()');
	}

	function get_value($sql, $result_type = MYSQL_NUM, $field = 0) {
		$query = $this->query($sql);
		$rt = mysql_fetch_array($query, $result_type);
		return isset($rt[$field]) ? $rt[$field] : false;
	}

	function get_all($sql, $result_type = MYSQL_ASSOC, $index = '') {
		$query = $this->query($sql);
		$data = array();
		while ($row = mysql_fetch_array($query, $result_type)) {
			if (isset($row[$index])) {
				$data[$row[$index]] = $row;
			} else {
				$data[] = $row;
			}
		}
		return $data;
	}

	function escape_string($str) {
		return addslashes($str);
	}
}
