<?php echo $this->fetch('header.html'); ?>
<link href="<?php echo $this->lib_base . "/" . 'jquery.ui/themes/ui-lightness/jquery.ui.css'; ?>" rel="stylesheet" type="text/css" />
<script src="<?php echo $this->lib_base . "/" . 'jquery.ui/jquery.ui.js'; ?>"></script>
<script src="<?php echo $this->lib_base . "/" . 'layer/layer.js'; ?>"></script>
<script type="text/javascript">
  $(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});

  });
</script>
<div id="rightTop">
  <p>会员管理</p>
  <ul class="subnav">
    <li><span>管理</span></li>
    <li><a class="btn1" href="index.php?app=user&amp;act=weixin">微信会员</a></li>
    <li><a class="btn1" href="index.php?app=user&amp;act=add">新增</a></li>
  </ul>
</div>

<div class="mrightTop">
  <div class="fontl">
    <form method="get">
       <div class="left">
          <input type="hidden" name="app" value="user" />
          <input type="hidden" name="act" value="index" />
          <select class="querySelect" name="field_name"><?php echo $this->html_options(array('options'=>$this->_var['query_fields'],'selected'=>$_GET['field_name'])); ?>
          </select>
          <input class="queryInput" type="text" name="field_value" value="<?php echo htmlspecialchars($_GET['field_value']); ?>" />
         <select class="querySelect" name="type">
           <option value="">选择用户类型</option>
           <?php echo $this->html_options(array('options'=>$this->_var['user_type_list'],'selected'=>$this->_var['query']['type'])); ?>
         </select>

         <select class="querySelect" name="status">
           <option value="">选择用户状态</option>
           <?php echo $this->html_options(array('options'=>$this->_var['user_status_list'],'selected'=>$this->_var['query']['status'])); ?>
         </select>
         注册时间从:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['add_time_from']; ?>" id="add_time_from" name="add_time_from" class="pick_date" />
         至:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['add_time_to']; ?>" id="add_time_to" name="add_time_to" class="pick_date" />
         排序:
          <select class="querySelect" name="sort"><?php echo $this->html_options(array('options'=>$this->_var['sort_options'],'selected'=>$_GET['sort'])); ?>
          </select>

         <select class="querySelect" name="province" data-next="city" onchange="changeArea(this,this.value)">
           <option value="">请选择省份</option>
           <?php echo $this->html_options(array('options'=>$this->_var['provinceList'],'selected'=>$this->_var['query']['province'])); ?>
         </select>
         <select class="querySelect" id="city" name="city" data-next="area" onchange="changeArea(this,this.value)">
           <option value="">请选择城市</option>
           <?php echo $this->html_options(array('options'=>$this->_var['cityList'],'selected'=>$this->_var['query']['city'])); ?>
         </select>
         <select class="querySelect" id="area" name="area" data-next="" onchange="changeArea(this,this.value)">
           <option value="">请选择县区</option>
           <?php echo $this->html_options(array('options'=>$this->_var['areaList'],'selected'=>$this->_var['query']['area'])); ?>
         </select>
         <br/>
          <input type="submit" class="formbtn" value="查询" />
      </div>
      <?php if ($this->_var['filtered']): ?>
      <a class="left formbtn1" href="index.php?app=user">撤销检索</a>
      <?php endif; ?>
    </form>
  </div>
  <div class="fontr"><?php echo $this->fetch('page.top.html'); ?></div>
</div>
<div class="tdare">
  <table width="100%" cellspacing="0" class="dataTable">
    <?php if ($this->_var['users']): ?>
    <tr class="tatr1">
      <td width="20" class="firstCell"><input type="checkbox" class="checkall" /></td>
      <td>会员名 | 真实姓名</td>
      <td><span>所在区域</span></td>
      <td><span ectype="order_by" fieldname="type">用户类型</span></td>
      <td>代理区域</td>
      <td><span ectype="order_by" fieldname="status">状态</span></td>
      <td><span ectype="order_by" fieldname="pay_point">购物积分</span></td>
      <td><span>定返总额</span></td>
      <td><span>剩余权</span></td>
      <td><span>减少权</span></td>
      <td><span>结余积分</span></td>
      <td><span>未返积分</span></td>
      <td><span>总获赠积分</span></td>
      <td><span>推荐人</span></td>
      <td><span>注册时间</span></td>
      <td>是否是管理员</td>
      <td class="handler">操作</td>
    </tr>
    <?php endif; ?>
    <?php $_from = $this->_var['users']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['user']):
?>
    <tr class="tatr2">
      <td class="firstCell"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['user']['user_id']; ?>" /></td>
      <td><?php echo htmlspecialchars($this->_var['user']['user_name']); ?> | <?php echo htmlspecialchars($this->_var['user']['real_name']); ?></td>
      <td><?php echo htmlspecialchars($this->_var['user']['local']); ?></td>
      <td><?php echo $this->_var['user']['type_cn']; ?></td>
      <td><?php echo htmlspecialchars($this->_var['user']['dlarea']); ?></td>
      <td><?php echo $this->_var['user']['status_cn']; ?></td>
      <td><?php echo $this->_var['user']['pay_point']; ?></td>


      <td><?php echo $this->_var['user']['return_point']; ?></td>
      <td><?php echo $this->_var['user']['last_seed']; ?></td>
      <td><?php echo $this->_var['user']['used_seed']; ?></td>
      <td><?php echo $this->_var['user']['point']; ?></td>
      <td><?php echo $this->_var['user']['freeze_point']; ?></td>
      <td><?php echo $this->_var['user']['all_point']; ?></td>



      <td><?php echo $this->_var['user']['parent']['real_name']; ?>|<?php echo $this->_var['user']['parent']['user_name']; ?></td>
      <td><?php echo $this->_var['user']['reg_time']; ?></td>
      <td><?php if ($this->_var['user']['if_admin']): ?>  是
      <?php else: ?><a href="index.php?app=admin&amp;act=add&amp;id=<?php echo $this->_var['user']['user_id']; ?>" onclick="parent.openItem('admin_manage', 'user');">设为管理员</a><?php endif; ?>
      </td>
      <td class="handler">
      <?php if (! $this->_var['if_system_manager'] && $this->_var['user']['privs'] == all): ?>系统管理员
      </td>
      <?php else: ?>
      <span style="width: 100px">
        <a href="index.php?app=user&amp;act=edit&amp;id=<?php echo $this->_var['user']['user_id']; ?>">编辑</a> | <a href="index.php?app=user&amp;act=super_login&amp;user_id=<?php echo $this->_var['user']['user_id']; ?>" target=_blank>登陆</a> |<a href="javascript:drop_confirm('你确定要删除它吗？该操作不会删除ucenter及其他整合应用中的用户', 'index.php?app=user&amp;act=drop&amp;id=<?php echo $this->_var['user']['user_id']; ?>');">删除</a>
        <?php if ($this->_var['user']['store_id']): ?>
        | <a href="index.php?app=store&amp;act=edit&amp;id=<?php echo $this->_var['user']['store_id']; ?>" onclick="parent.openItem('store_manage', 'store');">店铺</a>
        <?php endif; ?>
        <?php if ($this->_var['user']['status'] == 1): ?>
        | <a href="index.php?app=user&amp;act=changeStatus&amp;id=<?php echo $this->_var['user']['user_id']; ?>&status=0" onclick="return confirm('是否冻结用户?');">冻结用户</a>
        <?php endif; ?>
        <?php if ($this->_var['user']['status'] == 0): ?>
        | <a href="index.php?app=user&amp;act=changeStatus&amp;id=<?php echo $this->_var['user']['user_id']; ?>&status=1" onclick="return confirm('是否恢复用户?');">恢复用户</a>
        <?php endif; ?>
      </span>
      </td>
      <?php endif; ?>
    </tr>
    <?php endforeach; else: ?>
    <tr class="no_data">
      <td colspan="10">没有符合条件的记录</td>
    </tr>
    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </table>
  <?php if ($this->_var['users']): ?>
  <div id="dataFuncs">
    <div id="batchAction" class="left paddingT15"> &nbsp;&nbsp;
      <input class="formbtn batchButton" type="button" value="删除" name="id" uri="index.php?app=user&act=drop" presubmit="confirm('你确定要删除它吗？该操作不会删除ucenter及其他整合应用中的用户');" />
    </div>
    <div class="pageLinks"><?php echo $this->fetch('page.bottom.html'); ?></div>
    <div class="clear"></div>
  </div>
  <?php endif; ?>
</div>
<script>
  function changeArea(obj,pid){
    var next = $(obj).attr('data-next');
    if(next != ''){
      getAreaChild(next,pid);
    }
  }
  function getAreaChild(next,pid){
    $.ajax({
      url : 'index.php?app=user&act=getarea',
      type : 'POST',
      dataType : 'JSON',
      data:{'pid':pid},
      success : function(res){
        res = JSON.parse(res);
        if(res.status == 0){
          str = next == 'city' ? '请选择城市' : '请选择县区';
          html = '<option value="">'+str+'</option>';
          $("#"+next).html();
          for(var i in res.data){
            html += '<option value="'+res.data[i].id+'">'+res.data[i].name+'</option>';
          }
          console.log(html);
          $("#"+next).html(html);
        }
      }
    });
  }
</script>
<?php echo $this->fetch('footer.html'); ?>