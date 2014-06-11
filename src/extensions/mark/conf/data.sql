CREATE TABLE IF NOT EXISTS `pw_app_mark_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `fid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '版块ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '帖子ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复ID',
  `created_userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分用户uid',
  `created_username` varchar(15) NOT NULL DEFAULT '' COMMENT '评分用户username',
  `ping_userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评分用户uid',
  `ctype` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '积分类型',
  `cnum` varchar(10) NOT NULL DEFAULT '' COMMENT '积分数',
  `reason` varchar(255) NOT NULL DEFAULT '' COMMENT '原因',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `idx_tid_pid_createduserid` (`tid`,`pid`,`created_userid`)
) ENGINE=MyISAM  COMMENT='评分记录表';

ALTER TABLE `pw_bbs_threads` ADD `app_mark` varchar(150) NOT NULL DEFAULT '';
ALTER TABLE `pw_bbs_posts` ADD `app_mark` varchar(150) NOT NULL DEFAULT '';

REPLACE INTO `pw_user_permission_groups` (`gid`, `rkey`, `rtype`, `rvalue`, `vtype`) VALUES
(3, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"200";s:6:"markdt";s:1:"0";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"200";s:6:"markdt";s:1:"0";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(3, 'app_mark_manage', 'system', '1', 'string'),
(3, 'app_mark_open', 'basic', '2', 'string'),
(4, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"150";s:6:"markdt";s:1:"0";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"150";s:6:"markdt";s:1:"0";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(4, 'app_mark_manage', 'system', '1', 'string'),
(4, 'app_mark_open', 'basic', '2', 'string'),
(5, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"150";s:6:"markdt";s:1:"0";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"150";s:6:"markdt";s:1:"0";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(5, 'app_mark_manage', 'system', '1', 'string'),
(5, 'app_mark_open', 'basic', '2', 'string'),
(8, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"10";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"10";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(8, 'app_mark_open', 'basic', '1', 'string'),
(9, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"20";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"20";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(9, 'app_mark_open', 'basic', '1', 'string'),
(10, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"30";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"30";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(10, 'app_mark_open', 'basic', '1', 'string'),
(11, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"40";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"40";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(11, 'app_mark_open', 'basic', '1', 'string'),
(12, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"50";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"50";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(12, 'app_mark_open', 'basic', '1', 'string'),
(13, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"60";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"60";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(13, 'app_mark_open', 'basic', '1', 'string'),
(14, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"70";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"70";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(14, 'app_mark_open', 'basic', '1', 'string'),
(15, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"80";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:2:"-5";s:3:"max";s:1:"5";s:6:"dayMax";s:2:"80";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(15, 'app_mark_open', 'basic', '1', 'string'),
(16, 'app_mark_credits', 'basic', 'a:6:{i:1;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"100";s:6:"markdt";s:1:"1";}i:2;a:5:{s:6:"isopen";s:1:"1";s:3:"min";s:3:"-10";s:3:"max";s:2:"10";s:6:"dayMax";s:3:"100";s:6:"markdt";s:1:"1";}i:3;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:4;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:5;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}i:6;a:4:{s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"dayMax";s:0:"";s:6:"markdt";s:1:"0";}}', 'array'),
(16, 'app_mark_open', 'basic', '2', 'string');

ALTER TABLE pw_bbs_threads ADD `app_mark` varchar(150) NOT NULL DEFAULT '';
ALTER TABLE pw_bbs_posts ADD `app_mark` varchar(150) NOT NULL DEFAULT '';