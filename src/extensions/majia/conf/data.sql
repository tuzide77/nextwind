--安装或更新时需要注册的sql写在这里--
CREATE TABLE `pw_app_majia` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '马甲ID自增',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '参与绑定的用户ID',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '随机密码',
  PRIMARY KEY (`id`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='马甲表';