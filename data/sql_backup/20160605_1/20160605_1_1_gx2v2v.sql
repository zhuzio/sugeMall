-- TOTAL : 1
-- ECMall 2.0 SQL Dump Program
-- Apache/2.4.18 (Win32) OpenSSL/1.0.2e PHP/5.3.29
-- 
-- DATE : 2016-06-05 09:48:18
-- MYSQL SERVER VERSION : 5.5.47
-- PHP VERSION : 5.3.29
-- ECMall VERSION : 2.3.0
-- Vol : 1
DROP TABLE IF EXISTS suge_acategory;
CREATE TABLE suge_acategory (
  cate_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  cate_name varchar(100) NOT NULL DEFAULT '',
  parent_id int(10) unsigned NOT NULL DEFAULT '0',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  `code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (cate_id)
) ENGINE=MyISAM;
INSERT INTO suge_acategory ( `cate_id`, `cate_name`, `parent_id`, `sort_order`, `code` ) VALUES  ('1','商城帮助','0','0','help');
INSERT INTO suge_acategory ( `cate_id`, `cate_name`, `parent_id`, `sort_order`, `code` ) VALUES  ('2','商城公告','0','0','notice');
INSERT INTO suge_acategory ( `cate_id`, `cate_name`, `parent_id`, `sort_order`, `code` ) VALUES  ('3','内置文章','0','0','system');
DROP TABLE IF EXISTS suge_address;
CREATE TABLE suge_address (
  addr_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  user_id int(10) unsigned NOT NULL DEFAULT '0',
  consignee varchar(60) NOT NULL DEFAULT '',
  region_id int(10) unsigned DEFAULT NULL,
  region_name varchar(255) DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  zipcode varchar(20) DEFAULT NULL,
  phone_tel varchar(60) DEFAULT NULL,
  phone_mob varchar(60) DEFAULT NULL,
  PRIMARY KEY (addr_id),
  KEY user_id (user_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_article;
CREATE TABLE suge_article (
  article_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL DEFAULT '',
  title varchar(100) NOT NULL DEFAULT '',
  cate_id int(10) NOT NULL DEFAULT '0',
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  link varchar(255) DEFAULT NULL,
  content text,
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  if_show tinyint(3) unsigned NOT NULL DEFAULT '1',
  add_time int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (article_id),
  KEY `code` (`code`),
  KEY cate_id (cate_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
INSERT INTO suge_article ( `article_id`, `code`, `title`, `cate_id`, `store_id`, `link`, `content`, `sort_order`, `if_show`, `add_time` ) VALUES  ('1','eula','用户服务协议','3','0','','<p>特别提醒用户认真阅读本《用户服务协议�\�(下称《协议�\�) 中各条款。除非您接受本《协议》条款，否则您无权使用本网站提供的相关服务。您的使用行为将视为对本《协议》的接受，并同意接受本《协议》各项条款的约束�\� <br /> <br /> <strong>一、定�\�</strong><br /></p>\r\n<ol>\r\n<li>\"用户\"指符合本协议所规定的条件，同意遵守本网站各种规则、条款（包括但不限于本协议），并使用本网站的个人或机构�\�</li>\r\n<li>\"卖家\"是指在本网站上出售物品的用户�\�\"买家\"是指在本网站购买物品的用户�\�</li>\r\n<li>\"成交\"指买家根据卖家所刊登的交易要求，在特定时间内提出最优的交易条件，因而取得依其提出的条件购买该交易物品的权利�\�</li>\r\n</ol>\r\n<p><br /> <br /> <strong>二、用户资�\�</strong><br /> <br /> 只有符合下列条件之一的人员或实体才能申请成为本网站用户，可以使用本网站的服务�\�</p>\r\n<ol>\r\n<li>年满十八岁，并具有民事权利能力和民事行为能力的自然人�\�</li>\r\n<li>未满十八岁，但监护人（包括但不仅限于父母）予以书面同意的自然人；</li>\r\n<li>根据中国法律或设立地法律、法规和/或规章成立并合法存在的公司、企事业单位、社团组织和其他组织�\�</li>\r\n</ol>\r\n<p><br /> 无民事行为能力人、限制民事行为能力人以及无经营或特定经营资格的组织不当注册为本网站用户或超过其民事权利或行为能力范围从事交易的，其与本网站之间的协议自始无效，本网站一经发现，有权立即注销该用户，并追究其使用本网�\�\"服务\"的一切法律责任�\�<br /> <br /> <strong>�\�.用户的权利和义务</strong><br /></p>\r\n<ol>\r\n<li>用户有权根据本协议的规定及本网站发布的相关规则，利用本网站网上交易平台登录物品、发布交易信息、查询物品信息、购买物品、与其他用户订立物品买卖合同、在本网站社区发帖、参加本网站的有关活动及有权享受本网站提供的其他的有关资讯及信息服务�\�</li>\r\n<li>用户有权根据需要更改密码和交易密码。用户应对以该用户名进行的所有活动和事件负全部责任�\�</li>\r\n<li>用户有义务确保向本网站提供的任何资料、注册信息真实准确，包括但不限于真实姓名、身份证号、联系电话、地址、邮政编码等。保证本网站及其他用户可以通过上述联系方式与自己进行联系。同时，用户也有义务在相关资料实际变更时及时更新有关注册资料�\�</li>\r\n<li>用户不得以任何形式擅自转让或授权他人使用自己在本网站的用户帐号�\�</li>\r\n<li>用户有义务确保在本网站网上交易平台上登录物品、发布的交易信息真实、准确，无误导性�\�</li>\r\n<li>用户不得在本网站网上交易平台买卖国家禁止销售的或限制销售的物品、不得买卖侵犯他人知识产权或其他合法权益的物品，也不得买卖违背社会公共利益或公共道德的物品�\�</li>\r\n<li>用户不得在本网站发布各类违法或违规信息。包括但不限于物品信息、交易信息、社区帖子、物品留言，店铺留言，评价内容等�\�</li>\r\n<li>用户在本网站交易中应当遵守诚实信用原则，不得以干预或操纵物品价格等不正当竞争方式扰乱网上交易秩序，不得从事与网上交易无关的不当行为，不得在交易平台上发布任何违法信息�\�</li>\r\n<li>用户不应采取不正当手段（包括但不限于虚假交易、互换好评等方式）提高自身或他人信用度，或采用不正当手段恶意评价其他用户，降低其他用户信用度�\�</li>\r\n<li>用户承诺自己在使用本网站网上交易平台实施的所有行为遵守国家法律、法规和本网站的相关规定以及各种社会公共利益或公共道德。对于任何法律后果的发生，用户将以自己的名义独立承担所有相应的法律责任�\�</li>\r\n<li>用户在本网站网上交易过程中如与其他用户因交易产生纠纷，可以请求本网站从中予以协调。用户如发现其他用户有违法或违反本协议的行为，可以向本网站举报。如用户因网上交易与其他用户产生诉讼的，用户有权通过司法部门要求本网站提供相关资料�\�</li>\r\n<li>用户应自行承担因交易产生的相关费用，并依法纳税�\�</li>\r\n<li>未经本网站书面允许，用户不得将本网站资料以及在交易平台上所展示的任何信息以复制、修改、翻译等形式制作衍生作品、分发或公开展示�\�</li>\r\n<li>用户同意接收来自本网站的信息，包括但不限于活动信息、交易信息、促销信息等�\�</li>\r\n</ol>\r\n<p><br /> <br /> <strong>四�\� 本网站的权利和义�\�</strong><br /></p>\r\n<ol>\r\n<li>本网站不是传统意义上�\�\"拍卖�\�\"，仅为用户提供一个信息交流、进行物品买卖的平台，充当买卖双方之间的交流媒介，而非买主或卖主的代理商、合�\�  人、雇员或雇主等经营关系人。公布在本网站上的交易物品是用户自行上传进行交易的物品，并非本网站所有。对于用户刊登物品、提供的信息或参与竞标的过程�\�  本网站均不加以监视或控制，亦不介入物品的交易过程，包括运送、付款、退款、瑕疵担保及其它交易事项，且不承担因交易物品存在品质、权利上的瑕疵以及交�\�  方履行交易协议的能力而产生的任何责任，对于出现在拍卖上的物品品质、安全性或合法性，本网站均不予保证�\�</li>\r\n<li>本网站有义务在现有技术水平的基础上努力确保整个网上交易平台的正常运行，尽力避免服务中断或将中断时间限制在最短时间内，保证用户网上交易活动的顺利进行�\�</li>\r\n<li>本网站有义务对用户在注册使用本网站网上交易平台中所遇到的问题及反映的情况及时作出回复�\� </li>\r\n<li>本网站有权对用户的注册资料进行查阅，对存在任何问题或怀疑的注册资料，本网站有权发出通知询问用户并要求用户做出解释、改正，或直接做出处罚、删除等处理�\�</li>\r\n<li>�\�  户因在本网站网上交易与其他用户产生纠纷的，用户通过司法部门或行政部门依照法定程序要求本网站提供相关资料，本网站将积极配合并提供有关资料；用户将�\�  纷告知本网站，或本网站知悉纠纷情况的，经审核后，本网站有权通过电子邮件及电话联系向纠纷双方了解纠纷情况，并将所了解的情况通过电子邮件互相通知�\�  方�\� </li>\r\n<li>因网上交易平台的特殊性，本网站没有义务对所有用户的注册资料、所有的交易行为以及与交易有关的其他事项进行事先审查，但如发生以下情形，本网站有权限制用户的活动、向用户核实有关资料、发出警告通知、暂时中止、无限期地中止及拒绝向该用户提供服务�\�         \r\n<ul>\r\n<li>用户违反本协议或因被提及而纳入本协议的文件；</li>\r\n<li>存在用户或其他第三方通知本网站，认为某个用户或具体交易事项存在违法或不当行为，并提供相关证据，而本网站无法联系到该用户核证或验证该用户向本网站提供的任何资料；</li>\r\n<li>存在用户或其他第三方通知本网站，认为某个用户或具体交易事项存在违法或不当行为，并提供相关证据。本网站以普通非专业交易者的知识水平标准对相关内容进行判别，可以明显认为这些内容或行为可能对本网站用户或本网站造成财务损失或法律责任�\� </li>\r\n</ul>\r\n</li>\r\n<li>在反网络欺诈行动中，本着保护广大用户利益的原则，当用户举报自己交易可能存在欺诈而产生交易争议时，本网站有权通过表面判断暂时冻结相关用户账号，并有权核对当事人身份资料及要求提供交易相关证明材料�\�</li>\r\n<li>根据国家法律法规、本协议的内容和本网站所掌握的事实依据，可以认定用户存在违法或违反本协议行为以及在本网站交易平台上的其他不当行为，本网站有权在本网站交易平台及所在网站上以网络发布形式公布用户的违法行为，并有权随时作出删除相关信息，而无须征得用户的同意�\�</li>\r\n<li>�\�  网站有权在不通知用户的前提下删除或采取其他限制性措施处理下列信息：包括但不限于以规避费用为目的；以炒作信用为目的；存在欺诈等恶意或虚假内容；与�\�  上交易无关或不是以交易为目的；存在恶意竞价或其他试图扰乱正常交易秩序因素；该信息违反公共利益或可能严重损害本网站和其他用户合法利益的�\�</li>\r\n<li>�\�  户授予本网站独家的、全球通用的、永久的、免费的信息许可使用权利，本网站有权对该权利进行再授权，依此授权本网站有�\�(全部或部份地)  使用、复制、修订、改写、发布、翻译、分发、执行和展示用户公示于网站的各类信息或制作其派生作品，以现在已知或日后开发的任何形式、媒体或技术，将上�\�  信息纳入其他作品内�\�</li>\r\n</ol>\r\n<p><br /> <br /> <strong>五、服务的中断和终�\�</strong><br /></p>\r\n<ol>\r\n<li>�\�  本网站未向用户收取相关服务费用的情况下，本网站可自行全权决定以任何理�\�  (包括但不限于本网站认为用户已违反本协议的字面意义和精神，或用户在超过180天内未登录本网站�\�)  终止对用户的服务，并不再保存用户在本网站的全部资料（包括但不限于用户信息、商品信息、交易信息等）。同时本网站可自行全权决定，在发出通知或不发出�\�  知的情况下，随时停止提供全部或部分服务。服务终止后，本网站没有义务为用户保留原用户资料或与之相关的任何信息，或转发任何未曾阅读或发送的信息给用�\�  或第三方。此外，本网站不就终止对用户的服务而对用户或任何第三方承担任何责任�\� </li>\r\n<li>如用户向本网站提出注销本网站注册用户身份，需经本网站审核同意，由本网站注销该注册用户，用户即解除与本网站的协议关系，但本网站仍保留下列权利�\�         \r\n<ul>\r\n<li>用户注销后，本网站有权保留该用户的资�\�,包括但不限于以前的用户资料、店铺资料、商品资料和交易记录等�\� </li>\r\n<li>用户注销后，如用户在注销前在本网站交易平台上存在违法行为或违反本协议的行为，本网站仍可行使本协议所规定的权利�\� </li>\r\n</ul>\r\n</li>\r\n<li>如存在下列情况，本网站可以通过注销用户的方式终止服务：         \r\n<ul>\r\n<li>在用户违反本协议相关规定时，本网站有权终止向该用户提供服务。本网站将在中断服务时通知用户。但如该用户在被本网站终止提供服务后，再一次直接或间接或以他人名义注册为本网站用户的，本网站有权再次单方面终止为该用户提供服务�\�</li>\r\n<li>一旦本网站发现用户注册资料中主要内容是虚假的，本网站有权随时终止为该用户提供服务； </li>\r\n<li>本协议终止或更新时，用户未确认新的协议的�\� </li>\r\n<li>其它本网站认为需终止服务的情况�\� </li>\r\n</ul>\r\n</li>\r\n<li>因用户违反相关法律法规或者违反本协议规定等原因而致使本网站中断、终止对用户服务的，对于服务中断、终止之前用户交易行为依下列原则处理�\�         \r\n<ul>\r\n<li>本网站有权决定是否在中断、终止对用户服务前将用户被中断或终止服务的情况和原因通知用户交易关系方，包括但不限于对该交易有意向但尚未达成交易的用�\�,参与该交易竞价的用户，已达成交易要约用户�\�</li>\r\n<li>服务中断、终止之前，用户已经上传至本网站的物品尚未交易或交易尚未完成的，本网站有权在中断、终止服务的同时删除此项物品的相关信息�\� </li>\r\n<li>服务中断、终止之前，用户已经就其他用户出售的具体物品作出要约，但交易尚未结束，本网站有权在中断或终止服务的同时删除该用户的相关要约和信息�\�</li>\r\n</ul>\r\n</li>\r\n<li>本网站若因用户的行为（包括但不限于刊登的商品、在本网站社区发帖等）侵害了第三方的权利或违反了相关规定，而受到第三方的追偿或受到主管机关的处分时，用户应赔偿本网站因此所产生的一切损失及费用�\�</li>\r\n<li>对违反相关法律法规或者违反本协议规定，且情节严重的用户，本网站有权终止该用户的其它服务�\�</li>\r\n</ol>\r\n<p><br /> <br /> <strong>六、协议的修订</strong><br /> <br /> 本协议可由本网站随时修订，并将修订后的协议公告于本网站之上，修订后的条款内容自公告时起生效，并成为本协议的一部分。用户若在本协议修改之后，仍继续使用本网站，则视为用户接受和自愿遵守修订后的协议。本网站行使修改或中断服务时，不需对任何第三方负责�\�<br /> <br /> <strong>七�\� 本网站的责任范围 </strong><br /> <br /> 当用户接受该协议时，用户应明确了解并同意�\�</p>\r\n<ol>\r\n<li>是否经由本网站下载或取得任何资料，由用户自行考虑、衡量并且自负风险，因下载任何资料而导致用户电脑系统的任何损坏或资料流失，用户应负完全责任�\�</li>\r\n<li>用户经由本网站取得的建议和资讯，无论其形式或表现，绝不构成本协议未明示规定的任何保证�\�</li>\r\n<li>基于以下原因而造成的利润、商誉、使用、资料损失或其它无形损失，本网站不承担任何直接、间接、附带、特别、衍生性或惩罚性赔偿（即使本网站已被告知前款赔偿的可能性）�\�         \r\n<ul>\r\n<li>本网站的使用或无法使用�\�</li>\r\n<li>经由或通过本网站购买或取得的任何物品，或接收之信息，或进行交易所随之产生的替代物品及服务的购买成本�\�</li>\r\n<li>用户的传输或资料遭到未获授权的存取或变更�\�</li>\r\n<li>本网站中任何第三方之声明或行为�\�</li>\r\n<li>本网站其它相关事宜�\�</li>\r\n</ul>\r\n</li>\r\n<li>本网站只是为用户提供一个交易的平台，对于用户所刊登的交易物品的合法性、真实性及其品质，以及用户履行交易的能力等，本网站一律不负任何担保责任。用户如果因使用本网站，或因购买刊登于本网站的任何物品，而受有损害时，本网站不负任何补偿或赔偿责任�\�</li>\r\n<li>�\�  网站提供与其它互联网上的网站或资源的链接，用户可能会因此连结至其它运营商经营的网站，但不表示本网站与这些运营商有任何关系。其它运营商经营的网站均  由各经营者自行负责，不属于本网站控制及负责范围之内。对于存在或来源于此类网站或资源的任何内容、广告、产品或其它资料，本网站亦不予保证或负责。因�\�  用或依赖任何此类网站或资源发布的或经由此类网站或资源获得的任何内容、物品或服务所产生的任何损害或损失，本网站不负任何直接或间接的责任�\�</li>\r\n</ol>\r\n<p><br /> <br /> <strong>�\�.、不可抗�\�</strong><br /> <br /> 因不可抗力或者其他意外事件，使得本协议的履行不可能、不必要或者无意义的，双方均不承担责任。本合同所称之不可抗力意指不能预见、不能避免并不能克服�\�  客观情况，包括但不限于战争、台风、水灾、火灾、雷击或地震、罢工、暴动、法定疾病、黑客攻击、网络病毒、电信部门技术管制、政府行为或任何其它自然或人  为造成的灾难等客观情况�\�<br /> <br /> <strong>九、争议解决方�\�</strong><br /></p>\r\n<ol>\r\n<li>本协议及其修订本的有效性、履行和与本协议及其修订本效力有关的所有事宜，将受中华人民共和国法律管辖，任何争议仅适用中华人民共和国法律�\�</li>\r\n<li>�\�  使用本网站服务所引起与本网站的任何争议，均应提交深圳仲裁委员会按照该会届时有效的仲裁规则进行仲裁。相关争议应单独仲裁，不得与任何其它方的争议在任  何仲裁中合并处理，该仲裁裁决是终局，对各方均有约束力。如果所涉及的争议不适于仲裁解决，用户同意一切争议由人民法院管辖�\�</li>\r\n</ol>','255','1','1240122848');
INSERT INTO suge_article ( `article_id`, `code`, `title`, `cate_id`, `store_id`, `link`, `content`, `sort_order`, `if_show`, `add_time` ) VALUES  ('2','cert_autonym','什么是实名认证','3','0','','<p><strong>什么是实名认证�\�</strong></p>\r\n<p>&ldquo;认证店铺&rdquo;服务是一项对店主身份真实性识别服务。店主可以通过站内PM、电话或管理员EMail的方�\� 联系并申请该项认证。经过管理员审核确认了店主的真实身份，就可以开通该项认证�\�</p>\r\n<p>通过该认证，可以说明店主身份的真实有效性，为买家在网络交易的过程中提供一定的信心和保证�\�</p>\r\n<p><strong>认证申请的方式：</strong></p>\r\n<p>Email：XXXX@XX.com</p>\r\n<p>管理员：XXXXXX</p>','255','1','1240122848');
INSERT INTO suge_article ( `article_id`, `code`, `title`, `cate_id`, `store_id`, `link`, `content`, `sort_order`, `if_show`, `add_time` ) VALUES  ('3','cert_material','什么是实体店铺认证','3','0','','<p><strong>什么是实体店铺认证�\�</strong></p>\r\n<p>&ldquo;认证店铺&rdquo;服务是一项对店主身份真实性识别服务。店主可以通过站内PM、电话或管理员EMail的方�\� 联系并申请该项认证。经过管理员审核确认了店主的真实身份，就可以开通该项认证�\�</p>\r\n<p>通过该认证，可以说明店主身份的真实有效性，为买家在网络交易的过程中提供一定的信心和保证�\�</p>\r\n<p><strong>认证申请的方式：</strong></p>\r\n<p>Email：XXXX@XX.com</p>\r\n<p>管理员：XXXXXX</p>','255','1','1240122848');
INSERT INTO suge_article ( `article_id`, `code`, `title`, `cate_id`, `store_id`, `link`, `content`, `sort_order`, `if_show`, `add_time` ) VALUES  ('4','setup_store','开店协�\�','3','0','','<p>使用本公司服务所须遵守的条款和条件�\�<br /><br />1.用户资格<br />本公司的服务仅向适用法律下能够签订具有法律约束力的合同的个人提供并仅由其使用。在不限制前述规定的前提下，本公司的服务不向18周岁以下或被临时或无限期中止的用户提供。如您不合资格，请勿使用本公司的服务。此外，您的帐户（包括信用评价）和用户名不得向其他方转让或出售。另外，本公司保留根据其意愿中止或终止您的帐户的权利�\�<br /><br />2.您的资料（包括但不限于所添加的任何商品）不得�\�<br />*具有欺诈性、虚假、不准确或具误导性；<br />*侵犯任何第三方著作权、专利权、商标权、商业秘密或其他专有权利或发表权或隐私权�\�<br />*违反任何适用的法律或法规（包括但不限于有关出口管制、消费者保护、不正当竞争、刑法、反歧视或贸易惯�\�/公平贸易法律的法律或法规）；<br />*有侮辱或者诽谤他人，侵害他人合法权益的内容；<br />*有淫秽、色情、赌博、暴力、凶杀、恐怖或者教唆犯罪的内容�\�<br />*包含可能破坏、改变、删除、不利影响、秘密截取、未经授权而接触或征用任何系统、数据或个人资料的任何病毒、特洛依木马、蠕虫、定时炸弹、删除蝇、复活节彩蛋、间谍软件或其他电脑程序�\�<br /><br />3.违约<br />如发生以下情形，本公司可能限制您的活动、立即删除您的商品、向本公司社区发出有关您的行为的警告、发出警告通知、暂时中止、无限期地中止或终止您的用户资格及拒绝向您提供服务：<br />(a)您违反本协议或纳入本协议的文件；<br />(b)本公司无法核证或验证您向本公司提供的任何资料�\�<br />(c)本公司相信您的行为可能对您、本公司用户或本公司造成损失或法律责任�\�<br /><br />4.责任限制<br />本公司、本公司的关联公司和相关实体或本公司的供应商在任何情况下均不就因本公司的网站、本公司的服务或本协议而产生或与之有关的利润损失或任何特别、间接或后果性的损害（无论以何种方式产生，包括疏忽）承担任何责任。您同意您就您自身行为之合法性单独承担责任。您同意，本公司和本公司的所有关联公司和相关实体对本公司用户的行为的合法性及产生的任何结果不承担责任�\�<br /><br />5.无代理关�\�<br />用户和本公司是独立的合同方，本协议无意建立也没有创立任何代理、合伙、合营、雇员与雇主或特许经营关系。本公司也不对任何用户及其网上交易行为做出明示或默许的推荐、承诺或担保�\�<br /><br />6.一般规�\�<br />本协议在所有方面均受中华人民共和国法律管辖。本协议的规定是可分割的，如本协议任何规定被裁定为无效或不可执行，该规定可被删除而其余条款应予以执行�\�</p>','255','1','1240122848');
INSERT INTO suge_article ( `article_id`, `code`, `title`, `cate_id`, `store_id`, `link`, `content`, `sort_order`, `if_show`, `add_time` ) VALUES  ('5','msn_privacy','MSN在线通隐私策�\�','3','0','','<p>Msn在线通隐私策略旨在说明您在本网站使用Msn在线通功能时我们如何保护您的Msn帐号信息�\�<br /> 我们认为隐私权非常重要。我们希望此隐私保护中心有助于您在本网站更好使用Msn在线�\�<br /> <strong>我们收集的信�\�</strong></p><blockquote>* 您在本网站激活Msn在线通时,程序将会记录您的Msn在线通帐�\�</blockquote><p><br /> <strong>您的选择</strong></p><blockquote>* 您可以在本网站随时注销您的Msn在线通帐�\�</blockquote><p><br /> <strong>其他隐私声明</strong></p><blockquote>* 如果我们需要改变本网站Msn在线通的隐私策略, 我们会把相关的改动在此页面发�\�.</blockquote>','255','1','1240122848');
DROP TABLE IF EXISTS suge_attribute;
CREATE TABLE suge_attribute (
  attr_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  attr_name varchar(60) NOT NULL DEFAULT '',
  input_mode varchar(10) NOT NULL DEFAULT 'text',
  def_value varchar(255) DEFAULT NULL,
  PRIMARY KEY (attr_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_brand;
CREATE TABLE suge_brand (
  brand_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  brand_name varchar(100) NOT NULL DEFAULT '',
  brand_logo varchar(255) DEFAULT NULL,
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  recommended tinyint(3) unsigned NOT NULL DEFAULT '0',
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  if_show tinyint(2) unsigned NOT NULL DEFAULT '1',
  tag varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (brand_id),
  KEY tag (tag)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_cart;
CREATE TABLE suge_cart (
  rec_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  user_id int(10) unsigned NOT NULL DEFAULT '0',
  session_id varchar(32) NOT NULL DEFAULT '',
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  goods_name varchar(255) NOT NULL DEFAULT '',
  spec_id int(10) unsigned NOT NULL DEFAULT '0',
  specification varchar(255) DEFAULT NULL,
  price decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  quantity int(10) unsigned NOT NULL DEFAULT '1',
  goods_image varchar(255) DEFAULT NULL,
  PRIMARY KEY (rec_id),
  KEY session_id (session_id),
  KEY user_id (user_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_category_goods;
CREATE TABLE suge_category_goods (
  cate_id int(10) unsigned NOT NULL DEFAULT '0',
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cate_id,goods_id),
  KEY goods_id (goods_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_category_store;
CREATE TABLE suge_category_store (
  cate_id int(10) unsigned NOT NULL DEFAULT '0',
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cate_id,store_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_collect;
CREATE TABLE suge_collect (
  user_id int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT 'goods',
  item_id int(10) unsigned NOT NULL DEFAULT '0',
  keyword varchar(60) DEFAULT NULL,
  add_time int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (user_id,`type`,item_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_coupon;
CREATE TABLE suge_coupon (
  coupon_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  coupon_name varchar(100) NOT NULL DEFAULT '',
  coupon_value decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  use_times int(10) unsigned NOT NULL DEFAULT '0',
  start_time int(10) unsigned NOT NULL DEFAULT '0',
  end_time int(10) unsigned NOT NULL DEFAULT '0',
  min_amount decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  if_issue tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (coupon_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_coupon_sn;
CREATE TABLE suge_coupon_sn (
  coupon_sn varchar(20) NOT NULL,
  coupon_id int(10) unsigned NOT NULL DEFAULT '0',
  remain_times int(10) NOT NULL DEFAULT '-1',
  PRIMARY KEY (coupon_sn),
  KEY coupon_id (coupon_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_friend;
CREATE TABLE suge_friend (
  owner_id int(10) unsigned NOT NULL DEFAULT '0',
  friend_id int(10) unsigned NOT NULL DEFAULT '0',
  add_time varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (owner_id,friend_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_function;
CREATE TABLE suge_function (
  func_code varchar(20) NOT NULL DEFAULT '',
  func_name varchar(60) NOT NULL DEFAULT '',
  `privileges` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (func_code)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_gcategory;
CREATE TABLE suge_gcategory (
  cate_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  cate_name varchar(100) NOT NULL DEFAULT '',
  parent_id int(10) unsigned NOT NULL DEFAULT '0',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  if_show tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (cate_id),
  KEY store_id (store_id,parent_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_goods;
CREATE TABLE suge_goods (
  goods_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(10) NOT NULL DEFAULT 'material',
  goods_name varchar(255) NOT NULL DEFAULT '',
  description text,
  cate_id int(10) unsigned NOT NULL DEFAULT '0',
  cate_name varchar(255) NOT NULL DEFAULT '',
  brand varchar(100) NOT NULL,
  spec_qty tinyint(4) unsigned NOT NULL DEFAULT '0',
  spec_name_1 varchar(60) NOT NULL DEFAULT '',
  spec_name_2 varchar(60) NOT NULL DEFAULT '',
  if_show tinyint(3) unsigned NOT NULL DEFAULT '1',
  closed tinyint(3) unsigned NOT NULL DEFAULT '0',
  close_reason varchar(255) DEFAULT NULL,
  add_time int(10) unsigned NOT NULL DEFAULT '0',
  last_update int(10) unsigned NOT NULL DEFAULT '0',
  default_spec int(11) unsigned NOT NULL DEFAULT '0',
  default_image varchar(255) NOT NULL DEFAULT '',
  recommended tinyint(4) unsigned NOT NULL DEFAULT '0',
  cate_id_1 int(10) unsigned NOT NULL DEFAULT '0',
  cate_id_2 int(10) unsigned NOT NULL DEFAULT '0',
  cate_id_3 int(10) unsigned NOT NULL DEFAULT '0',
  cate_id_4 int(10) unsigned NOT NULL DEFAULT '0',
  price decimal(10,2) NOT NULL DEFAULT '0.00',
  tags varchar(102) NOT NULL,
  PRIMARY KEY (goods_id),
  KEY store_id (store_id),
  KEY cate_id (cate_id),
  KEY cate_id_1 (cate_id_1),
  KEY cate_id_2 (cate_id_2),
  KEY cate_id_3 (cate_id_3),
  KEY cate_id_4 (cate_id_4),
  KEY brand (brand(10)),
  KEY tags (tags)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_goods_attr;
CREATE TABLE suge_goods_attr (
  gattr_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  attr_name varchar(60) NOT NULL DEFAULT '',
  attr_value varchar(255) NOT NULL DEFAULT '',
  attr_id int(10) unsigned DEFAULT NULL,
  sort_order tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (gattr_id),
  KEY goods_id (goods_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_goods_image;
CREATE TABLE suge_goods_image (
  image_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  image_url varchar(255) NOT NULL DEFAULT '',
  thumbnail varchar(255) NOT NULL DEFAULT '',
  sort_order tinyint(4) unsigned NOT NULL DEFAULT '0',
  file_id int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (image_id),
  KEY goods_id (goods_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_goods_qa;
CREATE TABLE suge_goods_qa (
  ques_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  question_content varchar(255) NOT NULL,
  user_id int(10) unsigned NOT NULL,
  store_id int(10) unsigned NOT NULL,
  email varchar(60) NOT NULL,
  item_id int(10) unsigned NOT NULL DEFAULT '0',
  item_name varchar(255) NOT NULL DEFAULT '',
  reply_content varchar(255) NOT NULL,
  time_post int(10) unsigned NOT NULL,
  time_reply int(10) unsigned NOT NULL,
  if_new tinyint(3) unsigned NOT NULL DEFAULT '1',
  `type` varchar(10) NOT NULL DEFAULT 'goods',
  PRIMARY KEY (ques_id),
  KEY user_id (user_id),
  KEY goods_id (item_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_goods_spec;
CREATE TABLE suge_goods_spec (
  spec_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  spec_1 varchar(60) NOT NULL DEFAULT '',
  spec_2 varchar(60) NOT NULL DEFAULT '',
  color_rgb varchar(7) NOT NULL DEFAULT '',
  price decimal(10,2) NOT NULL DEFAULT '0.00',
  stock int(11) NOT NULL DEFAULT '0',
  sku varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (spec_id),
  KEY goods_id (goods_id),
  KEY price (price)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_goods_statistics;
CREATE TABLE suge_goods_statistics (
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  views int(10) unsigned NOT NULL DEFAULT '0',
  collects int(10) unsigned NOT NULL DEFAULT '0',
  carts int(10) unsigned NOT NULL DEFAULT '0',
  orders int(10) unsigned NOT NULL DEFAULT '0',
  sales int(10) unsigned NOT NULL DEFAULT '0',
  comments int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (goods_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_groupbuy;
CREATE TABLE suge_groupbuy (
  group_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  group_name varchar(255) NOT NULL DEFAULT '',
  group_desc varchar(255) NOT NULL DEFAULT '',
  start_time int(10) unsigned NOT NULL DEFAULT '0',
  end_time int(10) unsigned NOT NULL DEFAULT '0',
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  spec_price text NOT NULL,
  min_quantity smallint(5) unsigned NOT NULL DEFAULT '0',
  max_per_user smallint(5) unsigned NOT NULL DEFAULT '0',
  state tinyint(3) unsigned NOT NULL DEFAULT '0',
  recommended tinyint(3) unsigned NOT NULL DEFAULT '0',
  views int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (group_id),
  KEY goods_id (goods_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_groupbuy_log;
CREATE TABLE suge_groupbuy_log (
  group_id int(10) unsigned NOT NULL DEFAULT '0',
  user_id int(10) unsigned NOT NULL DEFAULT '0',
  user_name varchar(60) NOT NULL DEFAULT '',
  quantity smallint(5) unsigned NOT NULL DEFAULT '0',
  spec_quantity text NOT NULL,
  linkman varchar(60) NOT NULL DEFAULT '',
  tel varchar(60) NOT NULL DEFAULT '',
  order_id int(10) unsigned NOT NULL DEFAULT '0',
  add_time int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (group_id,user_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_mail_queue;
CREATE TABLE suge_mail_queue (
  queue_id int(11) unsigned NOT NULL AUTO_INCREMENT,
  mail_to varchar(150) NOT NULL DEFAULT '',
  mail_encoding varchar(50) NOT NULL DEFAULT '',
  mail_subject varchar(255) NOT NULL DEFAULT '',
  mail_body text NOT NULL,
  priority tinyint(1) unsigned NOT NULL DEFAULT '2',
  err_num tinyint(1) unsigned NOT NULL DEFAULT '0',
  add_time int(11) NOT NULL DEFAULT '0',
  lock_expiry int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (queue_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_member;
CREATE TABLE suge_member (
  user_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  user_name varchar(60) NOT NULL DEFAULT '',
  email varchar(60) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  real_name varchar(60) DEFAULT NULL,
  gender tinyint(3) unsigned NOT NULL DEFAULT '0',
  birthday date DEFAULT NULL,
  phone_tel varchar(60) DEFAULT NULL,
  phone_mob varchar(60) DEFAULT NULL,
  im_qq varchar(60) DEFAULT NULL,
  im_msn varchar(60) DEFAULT NULL,
  im_skype varchar(60) DEFAULT NULL,
  im_yahoo varchar(60) DEFAULT NULL,
  im_aliww varchar(60) DEFAULT NULL,
  reg_time int(10) unsigned DEFAULT '0',
  last_login int(10) unsigned DEFAULT NULL,
  last_ip varchar(15) DEFAULT NULL,
  logins int(10) unsigned NOT NULL DEFAULT '0',
  ugrade tinyint(3) unsigned NOT NULL DEFAULT '0',
  portrait varchar(255) DEFAULT NULL,
  outer_id int(10) unsigned NOT NULL DEFAULT '0',
  activation varchar(60) DEFAULT NULL,
  feed_config text NOT NULL,
  PRIMARY KEY (user_id),
  KEY user_name (user_name),
  KEY email (email),
  KEY outer_id (outer_id)
) ENGINE=MyISAM;
INSERT INTO suge_member ( `user_id`, `user_name`, `email`, `password`, `real_name`, `gender`, `birthday`, `phone_tel`, `phone_mob`, `im_qq`, `im_msn`, `im_skype`, `im_yahoo`, `im_aliww`, `reg_time`, `last_login`, `last_ip`, `logins`, `ugrade`, `portrait`, `outer_id`, `activation`, `feed_config` ) VALUES  ('1','admin','1401917815@qq.com','e10adc3949ba59abbe56e057f20f883e',null,'0',null,null,null,null,null,null,null,null,'1465061964','1465062360','127.0.0.1','4','0',null,'0',null,'');
DROP TABLE IF EXISTS suge_message;
CREATE TABLE suge_message (
  msg_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  from_id int(10) unsigned NOT NULL DEFAULT '0',
  to_id int(10) unsigned NOT NULL DEFAULT '0',
  title varchar(100) NOT NULL DEFAULT '',
  content text NOT NULL,
  add_time int(10) unsigned NOT NULL DEFAULT '0',
  last_update int(10) unsigned NOT NULL DEFAULT '0',
  `new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  parent_id int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (msg_id),
  KEY from_id (from_id),
  KEY to_id (to_id),
  KEY parent_id (parent_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_module;
CREATE TABLE suge_module (
  module_id varchar(30) NOT NULL DEFAULT '',
  module_name varchar(100) NOT NULL DEFAULT '',
  module_version varchar(5) NOT NULL DEFAULT '',
  module_desc text NOT NULL,
  module_config text NOT NULL,
  enabled tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (module_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_navigation;
CREATE TABLE suge_navigation (
  nav_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL DEFAULT '',
  title varchar(60) NOT NULL DEFAULT '',
  link varchar(255) NOT NULL DEFAULT '',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  open_new tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (nav_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_order;
CREATE TABLE suge_order (
  order_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  order_sn varchar(20) NOT NULL DEFAULT '',
  `type` varchar(10) NOT NULL DEFAULT 'material',
  extension varchar(10) NOT NULL DEFAULT '',
  seller_id int(10) unsigned NOT NULL DEFAULT '0',
  seller_name varchar(100) DEFAULT NULL,
  buyer_id int(10) unsigned NOT NULL DEFAULT '0',
  buyer_name varchar(100) DEFAULT NULL,
  buyer_email varchar(60) NOT NULL DEFAULT '',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  add_time int(10) unsigned NOT NULL DEFAULT '0',
  payment_id int(10) unsigned DEFAULT NULL,
  payment_name varchar(100) DEFAULT NULL,
  payment_code varchar(20) NOT NULL DEFAULT '',
  out_trade_sn varchar(20) NOT NULL DEFAULT '',
  pay_time int(10) unsigned DEFAULT NULL,
  pay_message varchar(255) NOT NULL DEFAULT '',
  ship_time int(10) unsigned DEFAULT NULL,
  invoice_no varchar(255) DEFAULT NULL,
  finished_time int(10) unsigned NOT NULL DEFAULT '0',
  goods_amount decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  discount decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  order_amount decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  evaluation_status tinyint(1) unsigned NOT NULL DEFAULT '0',
  evaluation_time int(10) unsigned NOT NULL DEFAULT '0',
  anonymous tinyint(3) unsigned NOT NULL DEFAULT '0',
  postscript varchar(255) NOT NULL DEFAULT '',
  pay_alter tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (order_id),
  KEY order_sn (order_sn,seller_id),
  KEY seller_name (seller_name),
  KEY buyer_name (buyer_name),
  KEY add_time (add_time)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_order_extm;
CREATE TABLE suge_order_extm (
  order_id int(10) unsigned NOT NULL DEFAULT '0',
  consignee varchar(60) NOT NULL DEFAULT '',
  region_id int(10) unsigned DEFAULT NULL,
  region_name varchar(255) DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  zipcode varchar(20) DEFAULT NULL,
  phone_tel varchar(60) DEFAULT NULL,
  phone_mob varchar(60) DEFAULT NULL,
  shipping_id int(10) unsigned DEFAULT NULL,
  shipping_name varchar(100) DEFAULT NULL,
  shipping_fee decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (order_id),
  KEY consignee (consignee)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_order_goods;
CREATE TABLE suge_order_goods (
  rec_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  order_id int(10) unsigned NOT NULL DEFAULT '0',
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  goods_name varchar(255) NOT NULL DEFAULT '',
  spec_id int(10) unsigned NOT NULL DEFAULT '0',
  specification varchar(255) DEFAULT NULL,
  price decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  quantity int(10) unsigned NOT NULL DEFAULT '1',
  goods_image varchar(255) DEFAULT NULL,
  evaluation tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  credit_value tinyint(1) NOT NULL DEFAULT '0',
  is_valid tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (rec_id),
  KEY order_id (order_id,goods_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_order_log;
CREATE TABLE suge_order_log (
  log_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  order_id int(10) unsigned NOT NULL DEFAULT '0',
  operator varchar(60) NOT NULL DEFAULT '',
  order_status varchar(60) NOT NULL DEFAULT '',
  changed_status varchar(60) NOT NULL DEFAULT '',
  remark varchar(255) DEFAULT NULL,
  log_time int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (log_id),
  KEY order_id (order_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_pageview;
CREATE TABLE suge_pageview (
  rec_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  view_date date NOT NULL DEFAULT '0000-00-00',
  view_times int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (rec_id),
  UNIQUE KEY storedate (store_id,view_date)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_partner;
CREATE TABLE suge_partner (
  partner_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  title varchar(100) NOT NULL DEFAULT '',
  link varchar(255) NOT NULL DEFAULT '',
  logo varchar(255) DEFAULT NULL,
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (partner_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_payment;
CREATE TABLE suge_payment (
  payment_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  payment_code varchar(20) NOT NULL DEFAULT '',
  payment_name varchar(100) NOT NULL DEFAULT '',
  payment_desc varchar(255) DEFAULT NULL,
  config text,
  is_online tinyint(3) unsigned NOT NULL DEFAULT '1',
  enabled tinyint(3) unsigned NOT NULL DEFAULT '1',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (payment_id),
  KEY store_id (store_id),
  KEY payment_code (payment_code)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_privilege;
CREATE TABLE suge_privilege (
  priv_code varchar(20) NOT NULL DEFAULT '',
  priv_name varchar(60) NOT NULL DEFAULT '',
  parent_code varchar(20) DEFAULT NULL,
  `owner` varchar(10) NOT NULL DEFAULT 'mall',
  PRIMARY KEY (priv_code)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_recommend;
CREATE TABLE suge_recommend (
  recom_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  recom_name varchar(100) NOT NULL DEFAULT '',
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (recom_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_recommended_goods;
CREATE TABLE suge_recommended_goods (
  recom_id int(10) unsigned NOT NULL DEFAULT '0',
  goods_id int(10) unsigned NOT NULL DEFAULT '0',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (recom_id,goods_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_region;
CREATE TABLE suge_region (
  region_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  region_name varchar(100) NOT NULL DEFAULT '',
  parent_id int(10) unsigned NOT NULL DEFAULT '0',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (region_id),
  KEY parent_id (parent_id)
) ENGINE=MyISAM;
INSERT INTO suge_region ( `region_id`, `region_name`, `parent_id`, `sort_order` ) VALUES  ('1','中国','0','255');
DROP TABLE IF EXISTS suge_scategory;
CREATE TABLE suge_scategory (
  cate_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  cate_name varchar(100) NOT NULL DEFAULT '',
  parent_id int(10) unsigned NOT NULL DEFAULT '0',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (cate_id),
  KEY parent_id (parent_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_sessions;
CREATE TABLE suge_sessions (
  sesskey char(32) NOT NULL DEFAULT '',
  expiry int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  adminid int(11) NOT NULL DEFAULT '0',
  ip char(15) NOT NULL DEFAULT '',
  `data` char(255) NOT NULL DEFAULT '',
  is_overflow tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (sesskey),
  KEY expiry (expiry)
) ENGINE=MyISAM;
INSERT INTO suge_sessions ( `sesskey`, `expiry`, `userid`, `adminid`, `ip`, `data`, `is_overflow` ) VALUES  ('86ba37ec9d0edcd08136bf231582080b','1465063931','0','0','127.0.0.1','admin_info|a:5:{s:7:\"user_id\";s:1:\"1\";s:9:\"user_name\";s:5:\"admin\";s:8:\"reg_time\";s:10:\"1465061964\";s:10:\"last_login\";s:10:\"1465062312\";s:7:\"last_ip\";s:9:\"127.0.0.1\";}','0');
DROP TABLE IF EXISTS suge_sessions_data;
CREATE TABLE suge_sessions_data (
  sesskey varchar(32) NOT NULL DEFAULT '',
  expiry int(11) NOT NULL DEFAULT '0',
  `data` longtext NOT NULL,
  PRIMARY KEY (sesskey),
  KEY expiry (expiry)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_sgrade;
CREATE TABLE suge_sgrade (
  grade_id tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  grade_name varchar(60) NOT NULL DEFAULT '',
  goods_limit int(10) unsigned NOT NULL DEFAULT '0',
  space_limit int(10) unsigned NOT NULL DEFAULT '0',
  skin_limit int(10) unsigned NOT NULL DEFAULT '0',
  charge varchar(100) NOT NULL DEFAULT '',
  need_confirm tinyint(3) unsigned NOT NULL DEFAULT '0',
  description varchar(255) NOT NULL DEFAULT '',
  functions varchar(255) DEFAULT NULL,
  skins text NOT NULL,
  sort_order tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (grade_id)
) ENGINE=MyISAM;
INSERT INTO suge_sgrade ( `grade_id`, `grade_name`, `goods_limit`, `space_limit`, `skin_limit`, `charge`, `need_confirm`, `description`, `functions`, `skins`, `sort_order` ) VALUES  ('1','系统默认','5','2','1','100�\�/�\�','0','测试用户请选择“默认等级”，可以立即开通�\�',null,'default|default','255');
DROP TABLE IF EXISTS suge_shipping;
CREATE TABLE suge_shipping (
  shipping_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  shipping_name varchar(100) NOT NULL DEFAULT '',
  shipping_desc varchar(255) DEFAULT NULL,
  first_price decimal(10,2) NOT NULL DEFAULT '0.00',
  step_price decimal(10,2) NOT NULL DEFAULT '0.00',
  cod_regions text,
  enabled tinyint(3) unsigned NOT NULL DEFAULT '1',
  sort_order tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (shipping_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_store;
CREATE TABLE suge_store (
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  store_name varchar(100) NOT NULL DEFAULT '',
  owner_name varchar(60) NOT NULL DEFAULT '',
  owner_card varchar(60) NOT NULL DEFAULT '',
  region_id int(10) unsigned DEFAULT NULL,
  region_name varchar(100) DEFAULT NULL,
  address varchar(255) NOT NULL DEFAULT '',
  zipcode varchar(20) NOT NULL DEFAULT '',
  tel varchar(60) NOT NULL DEFAULT '',
  sgrade tinyint(3) unsigned NOT NULL DEFAULT '0',
  apply_remark varchar(255) NOT NULL DEFAULT '',
  credit_value int(10) NOT NULL DEFAULT '0',
  praise_rate decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  domain varchar(60) DEFAULT NULL,
  state tinyint(3) unsigned NOT NULL DEFAULT '0',
  close_reason varchar(255) NOT NULL DEFAULT '',
  add_time int(10) unsigned DEFAULT NULL,
  end_time int(10) unsigned NOT NULL DEFAULT '0',
  certification varchar(255) DEFAULT NULL,
  sort_order smallint(5) unsigned NOT NULL DEFAULT '0',
  recommended tinyint(4) NOT NULL DEFAULT '0',
  theme varchar(60) NOT NULL DEFAULT '',
  store_banner varchar(255) DEFAULT NULL,
  store_logo varchar(255) DEFAULT NULL,
  description text,
  image_1 varchar(255) NOT NULL DEFAULT '',
  image_2 varchar(255) NOT NULL DEFAULT '',
  image_3 varchar(255) NOT NULL DEFAULT '',
  im_qq varchar(60) NOT NULL DEFAULT '',
  im_ww varchar(60) NOT NULL DEFAULT '',
  im_msn varchar(60) NOT NULL DEFAULT '',
  enable_groupbuy tinyint(1) unsigned NOT NULL DEFAULT '0',
  enable_radar tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (store_id),
  KEY store_name (store_name),
  KEY owner_name (owner_name),
  KEY region_id (region_id),
  KEY domain (domain)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_uploaded_file;
CREATE TABLE suge_uploaded_file (
  file_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  file_type varchar(60) NOT NULL DEFAULT '',
  file_size int(10) unsigned NOT NULL DEFAULT '0',
  file_name varchar(255) NOT NULL DEFAULT '',
  file_path varchar(255) NOT NULL DEFAULT '',
  add_time int(10) unsigned NOT NULL DEFAULT '0',
  belong tinyint(3) unsigned NOT NULL DEFAULT '0',
  item_id int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (file_id),
  KEY store_id (store_id)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_user_coupon;
CREATE TABLE suge_user_coupon (
  user_id int(10) unsigned NOT NULL,
  coupon_sn varchar(20) NOT NULL,
  PRIMARY KEY (user_id,coupon_sn)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS suge_user_priv;
CREATE TABLE suge_user_priv (
  user_id int(10) unsigned NOT NULL DEFAULT '0',
  store_id int(10) unsigned NOT NULL DEFAULT '0',
  privs text NOT NULL,
  PRIMARY KEY (user_id,store_id)
) ENGINE=MyISAM;
INSERT INTO suge_user_priv ( `user_id`, `store_id`, `privs` ) VALUES  ('1','0','all');
-- END ECMall 2.0 SQL Dump Program 