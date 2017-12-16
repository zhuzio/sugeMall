<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>
<div id="rightTop">
    <p>佣金记录</p>
    <ul class="subnav">
        <li><span>管理</span></li>
    </ul>
</div>
<div class="mrightTop">
    <div class="fontl">
        <form method="get">
             <div class="left">
                <input type="hidden" name="app" value="commission" />
                <input type="hidden" name="act" value="index" />
                <select class="querySelect" name="field"><?php echo $this->html_options(array('options'=>$this->_var['search_options'],'selected'=>$_GET['field'])); ?>
                </select>:<input class="queryInput" type="text" name="search_name" value="<?php echo htmlspecialchars($this->_var['query']['search_name']); ?>" />

                产生时间从:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['add_time_from']; ?>" id="add_time_from" name="add_time_from" class="pick_date" />
                至:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['add_time_to']; ?>" id="add_time_to" name="add_time_to" class="pick_date" />
                佣金金额从:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['order_amount_from']; ?>" name="order_amount_from" />
                至:<input class="queryInput2" type="text" style="width:60px;" value="<?php echo $this->_var['query']['order_amount_to']; ?>" name="order_amount_to" class="pick_date" />
                <input type="submit" class="formbtn" value="查询" onclick="$('[name=act]').val('index');"/>
                <!--<input type="submit" class="formbtn" value="导出" onclick="$('[name=act]').val('export');" />-->

            </div>
            <?php if ($this->_var['filtered']): ?>
            <a class="left formbtn1" href="index.php?app=commission">撤销检索</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="fontr">
        <?php if ($this->_var['orders']): ?><?php echo $this->fetch('page.top.html'); ?><?php endif; ?>
    </div>
</div>
<div class="tdare">
    <table width="100%" cellspacing="0" class="dataTable">
        <?php if ($this->_var['orders']): ?>
        <tr class="tatr1">
            <td width="10%" class="firstCell"><span ectype="order_by" fieldname="id">序号</span></td>
            <td width="10%"><span>姓名</span></td>
            <td width="10%"><span>手机号</span></td>
            <td width="10%"><span ectype="order_by" fieldname="money">佣金金额</span></td>
            <td><span>说明</span></td>
            <td width="15%"><span ectype="order_by" fieldname="createtime">日期</span></td>
            <!--<td width="10%">操作</td>-->
        </tr>
        <?php endif; ?>
        <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['order']):
?>
        <tr class="tatr2">
            <td class="firstCell"><?php echo $this->_var['order']['id']; ?></td>
            <td class="firstCell"><?php echo htmlspecialchars($this->_var['order']['truename']); ?></td>
            <td><?php echo htmlspecialchars($this->_var['order']['mobile']); ?></td>
            <td><?php echo $this->_var['order']['money']; ?></td>
            <td><?php echo $this->_var['order']['info']; ?></td>
            <td><?php echo $this->_var['order']['createtime']; ?></td>

        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="7">没有符合条件的记录</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <tr class="tatr2">
            <td colspan="3" style="text-align:right">合计：</td>
            <td>￥ <?php echo $this->_var['totalMoney']; ?> 元</td>
            <td></td>
        </tr>
    </table>
    <div id="dataFuncs">
        <div class="pageLinks">
            <?php if ($this->_var['orders']): ?><?php echo $this->fetch('page.bottom.html'); ?><?php endif; ?>
        </div>
    </div>
    <div class="clear"></div>
</div>
<?php echo $this->fetch('footer.html'); ?>
