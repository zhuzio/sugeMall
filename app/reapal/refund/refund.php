<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>退款接口</title>
  </head>
  
  <body>
    <div align="center">
    <form action="refundResult.php" method="post">
    <table align="center">
    	<tr><td colspan="2" align="center"><h2>退款接口</h2></td></tr>
    	<tr><td>商户号：</td><td><input type="text" name="merchant_id" value=""/></td></tr>
    	<tr><td>原订单号：</td><td><input type="text" name="orig_order_no" value=""/></td></tr>
		<tr><td>退款单号：</td><td><input type="text" name="order_no" value=""/></td></tr>
		<tr><td>退款金额：</td><td><input type="text" name="amount" value=""/>单位：元</td></tr>
		<tr><td>退款说明：</td><td><input type="text" name="note" value=""/></td></tr>
    	<tr><td></td><td><input type="submit" value="提交"/></td></tr>		
    </table>
    </form>
    </div>
  </body>
</html>