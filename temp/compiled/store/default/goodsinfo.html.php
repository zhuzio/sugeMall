<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'goodsinfo.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
//<!CDATA[
/* buy */
function buy()
{
    if (goodsspec.getSpec() == null)
    {
        alert(lang.select_specs);
        return;
    }
    var spec_id = goodsspec.getSpec().id;

    var quantity = $("#quantity").val();
    if (quantity == '')
    {
        alert(lang.input_quantity);
        return;
    }
    if (parseInt(quantity) < 1)
    {
        alert(lang.invalid_quantity);
        return;
    }
    add_to_cart(spec_id, quantity);
}

/* add cart */
function add_to_cart(spec_id, quantity)
{
    var url = SITE_URL + '/index.php?app=cart&act=add';
    $.getJSON(url, {'spec_id':spec_id, 'quantity':quantity}, function(data){
        if (data.done)
        {
            $('.bold_num').text(data.retval.cart.kinds);
            $('.bold_mly').html(price_format(data.retval.cart.amount));
            $('.ware_cen').slideDown('slow');
            setTimeout(slideUp_fn, 5000);
        }
        else
        {
            alert(data.msg);
        }
    });
}

var specs = new Array();
<?php $_from = $this->_var['goods']['_specs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'spec');if (count($_from)):
    foreach ($_from AS $this->_var['spec']):
?>
<?php if ($this->_var['spec']['is_pro']): ?>
specs.push(new spec(<?php echo $this->_var['spec']['spec_id']; ?>, '<?php echo htmlspecialchars($this->_var['spec']['spec_1']); ?>', '<?php echo htmlspecialchars($this->_var['spec']['spec_2']); ?>', <?php echo $this->_var['spec']['price']; ?>,<?php echo $this->_var['spec']['pro_price']; ?>, <?php echo $this->_var['spec']['stock']; ?>,true,<?php echo ($this->_var['spec']['discount'] == '') ? '0' : $this->_var['spec']['discount']; ?>));
<?php else: ?>
specs.push(new spec(<?php echo $this->_var['spec']['spec_id']; ?>, '<?php echo htmlspecialchars($this->_var['spec']['spec_1']); ?>', '<?php echo htmlspecialchars($this->_var['spec']['spec_2']); ?>', <?php echo $this->_var['spec']['price']; ?>,0, <?php echo $this->_var['spec']['stock']; ?>,false,<?php echo ($this->_var['spec']['discount'] == '') ? '0' : $this->_var['spec']['discount']; ?>));
<?php endif; ?>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
var specQty = <?php echo $this->_var['goods']['spec_qty']; ?>;
var defSpec = <?php echo htmlspecialchars($this->_var['goods']['default_spec']); ?>;
var goodsspec = new goodsspec(specs, specQty, defSpec);
//]]>
</script>

<h2 class="ware_title"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></h2>
<h3 class="goods_subname"><?php echo htmlspecialchars($this->_var['goods']['goods_subname']); ?></h3>
<div class="ware_info">
    <div class="ware_pic">
        <div class="big_pic">
            <a href="javascript:;"><span class="jqzoom"><img src="<?php echo ($this->_var['goods']['_images']['0']['thumbnail'] == '') ? $this->_var['default_image'] : $this->_var['goods']['_images']['0']['thumbnail']; ?>" width="300" height="300" jqimg="<?php echo $this->_var['goods']['_images']['0']['image_url']; ?>" /></span></a>
        </div>

        <div class="bottom_btn">
            <!--<a class="collect" href="javascript:collect_goods(<?php echo $this->_var['goods']['goods_id']; ?>);" title="收藏该商品"></a>-->
            <div class="left_btn"></div>
            <div class="right_btn"></div>
            <div class="ware_box">
                <ul>
                    <?php $_from = $this->_var['goods']['_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_image');$this->_foreach['fe_goods_image'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods_image']['total'] > 0):
    foreach ($_from AS $this->_var['goods_image']):
        $this->_foreach['fe_goods_image']['iteration']++;
?>
                    <li <?php if (($this->_foreach['fe_goods_image']['iteration'] <= 1)): ?>class="ware_pic_hover"<?php endif; ?> bigimg="<?php echo $this->_var['goods_image']['image_url']; ?>"><img src="<?php echo $this->_var['goods_image']['thumbnail']; ?>" width="55" height="55" /></li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        </div>
        <script>
            $(function(){
                var btn_list_li = $("#btn_list > li");
                btn_list_li.hover(function(){
                    $(this).find("ul:not(:animated)").slideDown("fast");
                },function(){
                    $(this).find("ul").slideUp("fast");
                });
            });
        </script>
        <?php if ($this->_var['share']): ?>
        <ul id="btn_list">
            <li id="btn_list1" title="收藏该商品">
                <ul class="drop_down">
                    <?php $_from = $this->_var['share']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                    <?php if ($this->_var['item']['type'] == 'collect'): ?><li><?php if ($this->_var['item']['logo']): ?><img src="<?php echo $this->_var['item']['logo']; ?>" /><?php endif; ?><a target="_blank" href="<?php echo $this->_var['item']['link']; ?>"><?php echo htmlspecialchars($this->_var['item']['title']); ?></a></li><?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </li>
            <li id="btn_list2" title="分享该商品">
                <ul class="drop_down">
                    <?php $_from = $this->_var['share']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                    <?php if ($this->_var['item']['type'] == 'share'): ?><li><?php if ($this->_var['item']['logo']): ?><img src="<?php echo $this->_var['item']['logo']; ?>" /><?php endif; ?><a target="_blank" href="<?php echo $this->_var['item']['link']; ?>"><?php echo htmlspecialchars($this->_var['item']['title']); ?></a></li><?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </li>
        </ul>
        <?php endif; ?>
    </div>

    <div class="ware_text">
        <div class="rate">
            <span class="letter1">市场价: </span><span class="fontColor3" style="text-decoration:line-through;"><?php echo price_format($this->_var['goods']['market_price']); ?></span><br />
            <span class="letter1">价格: </span><span class="fontColor3" ectype="goods_price"><?php echo price_format($this->_var['goods']['_specs']['0']['price']); ?></span><br />
             
            <span class="letter1">赠送积分: </span><span class="fontColor3"><?php echo $this->_var['goods']['point']; ?></span><br />

            <?php if ($this->_var['goods']['_specs']['0']['is_pro']): ?>
            <span class="letter1">促销: </span>
            <em class="promo-price-type" title="<?php echo $this->_var['goods']['pro_desc']; ?>"><?php echo $this->_var['goods']['pro_name']; ?></em>
            <span class="promo-price" ectype="goods_pro_price"><?php echo price_format($this->_var['goods']['_specs']['0']['pro_price']); ?></span>
            <div class="countdown J_Countdown_GoodsPromotion">
                    <span class="lefttime">还剩</span>
                    <span class="flip-top J_NumDays"><?php echo $this->_var['goods']['lefttime']['d']; ?></span><em>天</em>
                    <span class="flip-top J_NumHours"><?php echo $this->_var['goods']['lefttime']['h']; ?></span><em>小时</em>
                    <span class="flip-top J_NumMins"><?php echo $this->_var['goods']['lefttime']['m']; ?></span><em>分</em>
                    <span class="flip-top J_NumSeconds"><?php echo $this->_var['goods']['lefttime']['s']; ?></span><em>秒</em>
            </div>
            <?php endif; ?>
            
            <?php if ($this->_var['goods']['if_open']): ?>
            <span class="letter1">会员价: </span>
            <?php if ($this->_var['visitor']['user_id']): ?>
            <em class="promo-price-type" title="<?php echo $this->_var['goods']['grade_name']; ?>"><?php echo $this->_var['goods']['grade_name']; ?></em><span class="promo-price" ectype="member_price"><?php echo price_format($this->_var['goods']['_specs']['0']['member_price']); ?></span><br />
            <?php else: ?>
            <em class="promo-price-type" >登录后查看是否享受该优惠</em>
           <?php endif; ?>
           <?php endif; ?>
            <div style="width: 100%;color:#e4393c;">
            <?php if ($this->_var['store']['amount_for_free_fee']): ?>满<?php echo price_format($this->_var['store']['amount_for_free_fee']); ?>元包邮<?php endif; ?><?php if ($this->_var['store']['acount_for_free_fee']): ?>,满<?php echo htmlspecialchars($this->_var['store']['acount_for_free_fee']); ?>件包邮<?php endif; ?>
            </div>
            <?php if ($this->_var['goods']['integral_max_exchange']): ?>可使用积分:<em style="color:red;font-weight: bold;padding-left:10px;"><?php echo htmlspecialchars($this->_var['goods']['integral_max_exchange']); ?></em><br /><?php endif; ?>
            <span class="letter1">品牌: </span><?php echo htmlspecialchars($this->_var['goods']['brand']); ?><br />
            标签(TAG):&nbsp;&nbsp;<?php $_from = $this->_var['goods']['tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tag');if (count($_from)):
    foreach ($_from AS $this->_var['tag']):
?><?php echo $this->_var['tag']; ?>&nbsp;&nbsp;&nbsp;<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?><br />
            销售情况: <?php echo $this->_var['sales_info']; ?><?php echo $this->_var['comments']; ?><br />
            所在地区: <?php echo htmlspecialchars($this->_var['store']['region_name']); ?><br />
            <?php echo $this->_var['goods']['scan_code']; ?><br/>
            <span style="color:blue;font-weight:bold">微信扫描产品分享到朋友圈</span>
        </div>

        <div class="handle">
            <?php if ($this->_var['goods']['spec_qty'] > 0): ?>
            <ul>
                <li class="handle_title"><?php echo htmlspecialchars($this->_var['goods']['spec_name_1']); ?>: </li>
            </ul>
            <?php endif; ?>
            <?php if ($this->_var['goods']['spec_qty'] > 1): ?>
            <ul>
                <li class="handle_title"><?php echo htmlspecialchars($this->_var['goods']['spec_name_2']); ?>: </li>
            </ul>
            <?php endif; ?>
            <ul>
                <li class="handle_title">购买数量: </li>
                <li>
                    <input type="text" class="text width1" name="" id="quantity" value="1" />
                    件（库存<span class="stock" ectype="goods_stock"><?php echo $this->_var['goods']['_specs']['0']['stock']; ?></span>件）
                </li>
            </ul>
            <?php if ($this->_var['goods']['spec_qty'] > 0): ?>
            <ul>
                <li class="handle_title">您已选择: </li>
                <li class="aggregate" ectype="current_spec"></li>
            </ul>
            <?php endif; ?>
        </div>

        <ul class="ware_btn">
            <div class="ware_cen" style="display:none">
                <div class="ware_center">
                    <h1>
                        <span class="dialog_title">商品已成功添加到购物车</span>
                        <span class="close_link" title="关闭" onmouseover="this.className = 'close_hover'" onmouseout="this.className = 'close_link'" onclick="slideUp_fn();"></span>
                    </h1>
                    <div class="ware_cen_btn">
                        <p class="ware_text_p">购物车内共有 <span class="bold_num">3</span> 种商品 共计 <span class="bold_mly">658.00</span></p>
                        <p class="ware_text_btn">
                            <input type="submit" class="btn1" name="" value="查看购物车" onclick="location.href='<?php echo $this->_var['site_url']; ?>/index.php?app=cart'" />
                            <input type="submit" class="btn2" name="" value="继续挑选商品" onclick="$('.ware_cen').css({'display':'none'});" />
                        </p>
                    </div>
                </div>
                <div class="ware_cen_bottom"></div>
            </div>

            <!--<li class="btn_c1" title="立刻购买"><a href="#"></a></li>-->
            <li class="btn_c2" title="加入购物车"><a href="javascript:buy();"></a></li>
            <li class="btn_c3" title="收藏该商品"><a href="javascript:collect_goods(<?php echo $this->_var['goods']['goods_id']; ?>);"></a></li>
        </ul>
    </div>

    <div class="clear"></div>
</div>