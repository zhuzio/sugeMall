接口规范版本：20150408

为商户提供php语言的MPI demo，包括：订单提交接口，单笔订单查询接口，批量订单查询接口，联机退款接口

在20130503版本上增加tradeNature字段，提供了对境外支付业务的支持

订单提交接口
post.html
post.php
pickup.php
receive.php

单笔订单查询接口
merchantOrderQuery.html
merchantOrderQuery.php

批量订单查询接口
batchOrderQuery.html
batchOrderQuery.php

退款接口
refund.html
refund.php

在20130509版本基础上修改如下内容：
1、receive.php文件中增加读取publicykey.txt文本数据时去除首尾空字符串处理。
2、php5.4版本以上自带了hex2bin()，与demo中php_rsa.php文件方法hex2bin()重复，修改此方法为hexTobin()


证书的加密解密方法包含在phpseclib包里。