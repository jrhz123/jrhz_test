<?php
/*
 * 主页：http://addon.discuz.com/?@ailab
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF

DROP TABLE IF EXISTS `pre_nimba_majia`;
CREATE TABLE `pre_nimba_majia` (
  `uid` mediumint(8) unsigned NOT NULL,
  `username` char(45) NOT NULL,
  `useruid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`uid`,`useruid`)
) ENGINE=MyISAM;
EOF;

runquery($sql);
$finish = TRUE;

?>