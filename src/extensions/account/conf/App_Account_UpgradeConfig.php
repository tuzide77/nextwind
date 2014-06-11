<?php 
defined('WEKIT_VERSION') or exit(403);
/**
 * 升级8.7的账号通数据
 * 1、QQ和支付宝需要调用云平台账号通的数据；
 * 2、淘宝和新浪微博直接调用8.7版本的数据库就可以；
 * 
 * 数据库的配置填写升级前
 * 
 */

return array(
		/*----老版账号通所在数据库配置(即8.7版本的数据库 )----*/
		'old_db_host' => 'localhost',
		'old_db_port' => 3306,
		'old_db_user' => 'root',
		'old_db_pass' => 'phpwind',
		'old_db_name' => 'nextwind',
		
		/**
		 * 每次升级的条数，可以根据自己的服务器性能手动调整
		 */
		'limit' => 1000,
		
		/*-----云平台配置-----*/
		/*-----以下配置在8.7版本中data/bbscache/config.php中-----*/
		/*-----QQ 和 支付宝 升级需要调用----*/
		'siteId' => '98faaeac3e96bb09f9febc63f0e80a1b', //对应 $db_siteid 的值
		'siteHash' => '10BgcCBFtQWgUJVA9XAAcHAwsOU1IDVgcAAVtTAFAFCQ0', //对应 $db_sitehash 的值
		);

