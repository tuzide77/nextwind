--安装或更新时需要注册的sql写在这里--
--以下create为测试
CREATE TABLE pw_bbs_threads_hits (
  `tid` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (tid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE pw_user_data ADD app_signature_starttime INT(10) UNSIGNED NOT NULL DEFAULT 0;