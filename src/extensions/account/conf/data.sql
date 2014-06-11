--安装或更新时需要注册的sql写在这里--
CREATE TABLE `pw_app_account_bind` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `uid` int(10) unsigned NOT NULL DEFAULT '0',
 `type` varchar(20) NOT NULL,
 `app_uid` bigint(20) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `bbs_user_id` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pw_app_account_info` (
 `type` varchar(20) NOT NULL,
 `app_key` varchar(64) NOT NULL,
 `app_secret` varchar(64) NOT NULL,
 `status` tinyint(4) NOT NULL,
 PRIMARY KEY (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pw_app_account_login_session` (
 `sessionid` varchar(32) NOT NULL DEFAULT '',
 `expire` int(10) unsigned NOT NULL DEFAULT '0',
 `sessiondata` text NOT NULL,
 PRIMARY KEY (`sessionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pw_app_account_taobao_userinfo` (
 `user_id` bigint(20) unsigned NOT NULL,
 `nick` varchar(32) NOT NULL DEFAULT '',
 `create_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
 PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pw_app_account_alipay_userinfo` (
 `user_id` bigint(20) NOT NULL,
 `real_name` varchar(50) NOT NULL DEFAULT '',
 `create_at` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pw_app_account_qzone_userinfo` (
 `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增用户id',
 `open_id` varchar(32) NOT NULL COMMENT 'openid',
 `nick_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户昵称',
 `avatar` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
 `avatar_mid` varchar(100) NOT NULL DEFAULT '' COMMENT '中头像',
 `avatar_big` varchar(100) NOT NULL DEFAULT '' COMMENT '大头像',
 `gender` char(20) NOT NULL DEFAULT '',
 `create_at` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
 PRIMARY KEY (`user_id`),
 UNIQUE KEY `open_id` (`open_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE `pw_app_account_sinaweibo_userinfo` (
 `user_id` bigint(20) NOT NULL COMMENT '用户UID',
 `screen_name` varchar(100) NOT NULL DEFAULT '' COMMENT '用户昵称',
 `name` varchar(100) NOT NULL DEFAULT '' COMMENT '友好显示名称',
 `province` int(11) NOT NULL DEFAULT '0' COMMENT '用户所在省级ID',
 `city` int(11) NOT NULL DEFAULT '0' COMMENT '用户所在城市ID',
 `location` varchar(255) NOT NULL DEFAULT '' COMMENT '用户所在地',
 `description` varchar(255) NOT NULL DEFAULT '' COMMENT '用户个人描述',
 `url` varchar(255) NOT NULL DEFAULT '' COMMENT '用户博客地址',
 `profile_image_url` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像地址，50×50像素',
 `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '用户的个性化域名',
 `gender` char(2) NOT NULL DEFAULT '' COMMENT '性别，m：男、f：女、n：未知',
 `followers_count` int(11) NOT NULL DEFAULT '0' COMMENT '粉丝数',
 `friends_count` int(11) NOT NULL DEFAULT '0' COMMENT '关注数',
 `statuses_count` int(11) NOT NULL DEFAULT '0' COMMENT '微博数',
 `favourites_count` int(11) NOT NULL DEFAULT '0' COMMENT '收藏数',
 `created_at` varchar(50) NOT NULL DEFAULT '' COMMENT '用户创建（注册）时间',
 `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是微博认证用户',
 `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '本地用户创建时间',
 PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table `pw_app_account_info` add  `display_order` int(4) NOT NULL DEFAULT '1';



