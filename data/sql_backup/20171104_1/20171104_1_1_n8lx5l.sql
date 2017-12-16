-- TOTAL : 1
-- ECMall 2.0 SQL Dump Program
-- Apache/2.4.23 (Win32) OpenSSL/1.0.2j PHP/5.4.45
-- 
-- DATE : 2017-11-04 11:09:55
-- MYSQL SERVER VERSION : 5.5.53
-- PHP VERSION : 5.4.45
-- ECMall VERSION : 2.3.0
-- Vol : 1
DROP TABLE IF EXISTS ecm_user_coupon;
CREATE TABLE ecm_user_coupon (
  user_id int(10) unsigned NOT NULL,
  coupon_sn varchar(20) NOT NULL,
  PRIMARY KEY (user_id,coupon_sn)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS ecm_wxch_qr;
CREATE TABLE ecm_wxch_qr (
  qid int(7) NOT NULL AUTO_INCREMENT,
  wxid char(28) NOT NULL,
  `type` varchar(2) NOT NULL,
  expire_seconds int(4) NOT NULL,
  action_name varchar(30) NOT NULL,
  scene_id int(7) NOT NULL,
  ticket varchar(120) NOT NULL,
  scene varchar(200) NOT NULL,
  qr_path varchar(200) NOT NULL,
  subscribe int(8) unsigned NOT NULL,
  scan int(8) unsigned NOT NULL,
  `function` varchar(100) NOT NULL,
  affiliate int(8) NOT NULL,
  endtime int(10) NOT NULL,
  dateline int(10) NOT NULL,
  media_id varchar(225) NOT NULL,
  PRIMARY KEY (qid)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS ecm_wxkeyword;
CREATE TABLE ecm_wxkeyword (
  kid int(11) unsigned NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  kename varchar(300) DEFAULT NULL,
  kecontent varchar(500) DEFAULT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1:文本 2：图文',
  kyword varchar(255) DEFAULT NULL,
  titles varchar(1000) DEFAULT NULL,
  imageinfo varchar(1000) DEFAULT NULL,
  linkinfo varchar(1000) DEFAULT NULL,
  ismess tinyint(1) DEFAULT NULL,
  isfollow tinyint(1) DEFAULT NULL,
  iskey tinyint(1) DEFAULT NULL,
  PRIMARY KEY (kid)
) ENGINE=MyISAM;
INSERT INTO ecm_wxkeyword ( `kid`, `user_id`, `kename`, `kecontent`, `type`, `kyword`, `titles`, `imageinfo`, `linkinfo`, `ismess`, `isfollow`, `iskey` ) VALUES  ('1','6698',null,'欢迎光临展展商贸','1',null,'','','',null,'1',null);
INSERT INTO ecm_wxkeyword ( `kid`, `user_id`, `kename`, `kecontent`, `type`, `kyword`, `titles`, `imageinfo`, `linkinfo`, `ismess`, `isfollow`, `iskey` ) VALUES  ('2','16023',null,'你好，亮一点装饰很高兴为您服务，在线热线0851-24257378','1',null,'','','',null,'1',null);
-- END ECMall 2.0 SQL Dump Program 