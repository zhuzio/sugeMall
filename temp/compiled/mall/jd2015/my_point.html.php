<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
    $(function() {
        /* 预存款充值 */
        $('*[ectype="recharge-method"] input[name="method"]').click(function() {
            $('*[ectype="online"]').hide();
            $('*[ectype="offline"]').hide();
            $('*[ectype="' + $(this).val() + '"]').show();
        })
    });
    function online_chongzhi()
    {
        if (document.online_form.cz_money.value == "")
        {
            alert("tianxieyaochongzhidejine");
            document.online_form.cz_money.focus();
            return false;
        }
        return true;
        //return false;
    }
</script>
<style type="text/css">
    .table .line td{border:none;}
    .float_right {float: right;}
    .line{border-bottom:1px solid #E2E2E2}
</style>
<div class="content">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
        <div class="submenu">
            <ul class="tab">
                <li class="first active"><h2><a href="index.php?app=my_point">购买积分</a></h2></li>
                <li class="normal "><h2><a href="index.php?app=my_point&act=logall">购买记录</a></h2></li>

            </ul>
        </div>
        <div class="wrap">
            <div class="public table epay">
                <div class="title clearfix">
                    <h2 class="float-left">购买积分</h2>
                    <!--<p class="float-left">余额：<strong><?php echo $this->_var['epay']['money']; ?></strong> 元</p>-->
                    <div class="float-right link">
                        <a  href="<?php echo url('app=my_point&act=logall'); ?>">购买记录</a>
                        <!--<a  href="javascript:;">购买记录</a>-->
                    </div>
                </div>
                <div class="form czlist">
                    <dl class="clearfix">
                        <dt>购买方式：</dt>
                        <dd class="clearfix" ectype="recharge-method">
                            <div class="czlist_type">
                                <input name="method" type="radio" value="online" id="online" checked="checked"/><label for="online">线上购买</label>
                            </div>
                            <div class="czlist_type">
                                <input name="method" type="radio" value="offline" id="offline"/><label for="offline">线下汇款</label>
                            </div>
                        </dd>
                    </dl>
                    <form name="online_form" onSubmit="return online_chongzhi();" action="index.php?app=my_point&act=pointOrder" method="post" target="_blank" ectype="online">
                        <dl class="clearfix">
                            <dt>充值渠道：</dt>
                            <dd class="clearfix">
                                <div class="czlist_type" style="width: 220px;">
                                    <input name="czfs" type="radio" value="allinpay" id="allinpay" checked="checked"/>
                                    <label for="allinpay">
                                        <img height="20" src="<?php echo $this->res_base . "/" . 'images_bk/allinpay.png'; ?>" />
                                    </label>
                                </div>
                                <!--<div class="czlist_type" style="width: 220px;">
                                    <input name="czfs" type="radio" value="reapal" id="reapal" checked="checked"/>
                                    <label for="reapal">
                                        <img height="20" src="<?php echo $this->res_base . "/" . 'images_bk/reapal.png'; ?>" />
                                    </label>
                                </div>
                                <!--<div class="czlist_type" style="width: 220px;">
                                    <input name="czfs" type="radio" value="llpay" id="llpay"/>
                                    <label for="llpay">
                                        <img height="20" src="<?php echo $this->res_base . "/" . 'images_bk/llpay.gif'; ?>" />
                                    </label>
                                </div>


                                <div class="czlist_type" style="width: 220px;">
                                    <input name="czfs" type="radio" value="alipay" id="alipay" checked="checked"/>
                                    <label for="alipay">
                                        <img height="20" src="<?php echo $this->res_base . "/" . 'images_bk/28.gif'; ?>" />
                                    </label>
                                </div>

                                <div class="czlist_type" style="width: 220px;">
                                    <input name="czfs" type="radio" value="chinabank" id="chinabank"/>
                                    <label for="chinabank">
                                        <img src="<?php echo $this->res_base . "/" . 'images_bk/chinablanklogo.gif'; ?>"  />
                                    </label>
                                </div>

                                <div class="czlist_type" style="width: 220px;">
                                    <input name="czfs" type="radio" value="tenpay" id="tenpay"/>
                                    <label for="tenpay">
                                        <img src="<?php echo $this->res_base . "/" . 'images_bk/tenpaylogo.gif'; ?>" />
                                    </label>
                                </div>
                                -->
                                <div class="czlist_type" style="width: 220px;">
                                    <input name="czfs" type="radio" value="wxnative" id="wxnative"/>
                                    <label for="wxnative">
                                        <img src="<?php echo $this->res_base . "/" . 'images_bk/wxnativelogo.gif'; ?>" />
                                    </label>
                                </div>


                            </dd>
                        </dl>
                        <dl class="clearfix">
                            <dt>购买积分数：</dt>
                            <dd><input name="num" type="text" value="1" size="8" /></dd>
                        </dl>
                        <dl class="clearfix">
                            <dt>&nbsp;</dt>
                            <dd class="submit">
                                <span class="epay_btn">
                                    <input type="submit" value="提交" />
                                </span>
                            </dd>
                        </dl>
                    </form>


                    <form name="offline_form" action="index.php?app=epay&act=offline_chongzhi" method="post" ectype="offline" style="display: none;">
                        <dl class="clearfix">
                            <dt>汇款说明：</dt>
                            <dd class="clearfix" style="line-height:25px;font-size:13px;">
                                <?php echo $this->_var['epay_offline_info']; ?>
                            </dd>
                        </dl>
                        <dl class="clearfix">
                            <dt>汇款信息：</dt>
                            <dd><textarea name="message" cols="50" rows="5"></textarea>
                                <br/>汇款银行,流水号，汇款时间，汇款金额
                            </dd>
                        </dl>
                        <dl class="clearfix">
                            <dt>汇款人姓名：</dt>
                            <dd>
                                <input name="realname" value=""/>
                            </dd>
                        </dl>
                        <dl class="clearfix">
                            <dt>汇款人电话：</dt>
                            <dd><input name="mobile" value=""/></dd>
                        </dl>
                        <dl class="clearfix">
                            <dt>&nbsp;</dt>
                            <dd class="submit">
                                <span class="epay_btn">
                                    <input type="submit" value="提交" />
                                </span>
                            </dd>
                        </dl>
                    </form>
                </div>
            </div>
            <div class="wrap_bottom"></div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<div class="clear"></div>
<?php echo $this->fetch('footer.html'); ?>
