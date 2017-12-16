<?php echo $this->fetch('header.html'); ?>
<link href="<?php echo $this->lib_base . "/" . 'jquery.ui/themes/ui-lightness/jquery.ui.css'; ?>" rel="stylesheet" type="text/css" />
<script src="<?php echo $this->lib_base . "/" . 'jquery.ui/jquery.ui.js'; ?>"></script>
<script src="<?php echo $this->lib_base . "/" . 'layer/layer.js'; ?>"></script>
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
    $('#update_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#update_time_to').datepicker({dateFormat: 'yy-mm-dd'});

});
</script>
<div id="rightTop">
    <p>用户请求</p>
    <ul class="subnav">
        <li><span>管理</span></li>
    </ul>
</div>
<div class="mrightTop">
    <div class="fontl">
        <form method="get">
             <div class="left">
                <input type="hidden" name="app" value="user" />
                <input type="hidden" name="act" value="req" />
                <select class="querySelect" name="field"><?php echo $this->html_options(array('options'=>$this->_var['search_options'],'selected'=>$_GET['field'])); ?>
                </select>:<input class="queryInput" type="text" name="search_name" value="<?php echo htmlspecialchars($this->_var['query']['search_name']); ?>" />
                <select class="querySelect" name="type">
                    <option value="">升级类型</option>
                    <?php echo $this->html_options(array('options'=>$this->_var['req_type_list'],'selected'=>$this->_var['query']['status'])); ?>
                </select>
                 <select class="querySelect" name="status">
                     <option value="">请求状态</option>
                     <?php echo $this->html_options(array('options'=>$this->_var['req_status_list'],'selected'=>$this->_var['query']['status'])); ?>
                 </select>
                请求时间从:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['add_time_from']; ?>" id="add_time_from" name="add_time_from" class="pick_date" />
                至:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['add_time_to']; ?>" id="add_time_to" name="add_time_to" class="pick_date" />
                 操作时间从:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['update_time_from']; ?>" id="update_time_from" name="update_time_from" class="pick_date" />
                 至:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['update_time_to']; ?>" id="update_time_to" name="update_time_to" class="pick_date" />
                <input type="submit" class="formbtn" value="查询" onclick="$('[name=act]').val('req');"/>
                <input type="submit" class="formbtn" value="导出" onclick="$('[name=act]').val('export');" />
            </div>
            <?php if ($this->_var['filtered']): ?>
            <a class="left formbtn1" href="index.php?app=user&act=req">撤销检索</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="fontr">
        <?php if ($this->_var['list']): ?><?php echo $this->fetch('page.top.html'); ?><?php endif; ?>
    </div>
</div>
<div class="tdare">
    <table width="100%" cellspacing="0" class="dataTable">
        <?php if ($this->_var['list']): ?>
        <tr class="tatr1">
            <td width="15%" class="firstCell"><span ectype="order_by" fieldname="id">序号</span></td>
            <td width="15%"><span>姓名|手机号</span></td>
            <td width="10%"><span ectype="order_by" fieldname="type">升级类型</span></td>
			<td width="10%"><span ectype="order_by" fieldname="type">升级地区</span></td>
            <td width="10%"><span ectype="order_by" fieldname="status">状态</span></td>
            <td width="10%"><span ectype="order_by" fieldname="createtime">申请日期</span></td>
            <td width="10%"><span >操作时间</span></td>
            <td width="5%"><span >操作人</span></td>
            <td>操作</td>
        </tr>
        <?php endif; ?>
        <?php $_from = $this->_var['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['order']):
?>
        <tr class="tatr2">
            <td class="firstCell"><?php echo $this->_var['order']['id']; ?></td>
            <td class="firstCell"><?php echo $this->_var['order']['user']['real_name']; ?>|<?php echo $this->_var['order']['user']['phone_mob']; ?></td>
            <td><?php echo $this->_var['order']['type_cn']; ?></td>
			<td><?php echo $this->_var['order']['sarea']['name']; ?><?php echo $this->_var['order']['xarea']['name']; ?><?php echo $this->_var['order']['area']['name']; ?></td>
            <td><?php echo $this->_var['order']['status_cn']; ?></td>
            <td><?php echo $this->_var['order']['createtime']; ?></td>
            <td><?php if ($this->_var['order']['updatetime'] != 0): ?><?php echo $this->_var['order']['updatetime']; ?><?php endif; ?></td>
            <td><?php if ($this->_var['order']['opid'] != 0): ?><?php echo $this->_var['order']['oper']['real_name']; ?><?php endif; ?></td>
            <td>
                <?php if ($this->_var['order']['status'] == 1): ?>
                <a href="index.php?app=user&amp;act=req_accept&amp;id=<?php echo $this->_var['order']['id']; ?>" onclick="return confirm('确定通过审核吗？');"><span style="color:green;">通过</span></a>
                <a href="index.php?app=user&amp;act=req_cancle&amp;id=<?php echo $this->_var['order']['id']; ?>" onclick="return confirm('确定驳回请求吗？');"><span style="color:red;">驳回</span></a>
                <?php endif; ?>
            </td>

        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="7">没有符合条件的记录</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>

    </table>
    <div id="dataFuncs">
        <div class="pageLinks">
            <?php echo $this->_var['mypage']; ?>
        </div>
    </div>
    <div class="clear"></div>
</div>
<script>
    function sendPoint(id){

    }
</script>
<?php echo $this->fetch('footer.html'); ?>
