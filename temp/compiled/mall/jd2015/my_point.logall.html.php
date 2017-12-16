<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
    $(function() {
        $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
        $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
    });
</script>
<style type="text/css">
    .table .line td{border:none;}
    .float_right {float: right;}
    .line{border-bottom:1px solid #E2E2E2}
</style>
<div class="content">
    <div class="totline"></div><div class="botline"></div>
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="wrap">
            <div class="public table">

                <table>
                    <?php if ($this->_var['sgxtorder_list']): ?>
                    <tr class="line tr_bgcolor">
                        <th width="10%">订单号</th>
                        <th width="10%">金额</th>
                        <th width="10%">购买时间</th>
                    </tr>
                    <?php $_from = $this->_var['sgxtorder_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sgxtorder');if (count($_from)):
    foreach ($_from AS $this->_var['sgxtorder']):
?>
                    <tr class="line">
                    <td align="center"><?php echo $this->_var['sgxtorder']['orderid']; ?></td>
                    <td align="center"><?php echo $this->_var['sgxtorder']['amount']; ?></td>
                    <td align="center"><?php echo local_date("Y-m-d H:i",$this->_var['sgxtorder']['pay_createtime']); ?></td>
                </tr>
                    <?php endforeach; else: ?>
                    <tr class="no_data">
                        <td colspan="3">暂无数据</td>
                    </tr>
                    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <?php endif; ?>
                    <?php if ($this->_var['sgxtorder_list']): ?>
                    <tr class="sep-row">
                        <td colspan="4"></td>
                    </tr>
                    <tr class="operations">
                        <th colspan="4">
                            <p class="position2 clearfix">
                                <?php echo $this->fetch('member.page.bottom.html'); ?>
                            </p>
                        </th>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="wrap_bottom"></div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<div class="clear"></div>
<?php echo $this->fetch('footer.html'); ?>