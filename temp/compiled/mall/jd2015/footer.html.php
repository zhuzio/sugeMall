<?php echo $this->fetch('footer_order_notice.html'); ?>
<div id="footer" class="w-full">

    <div id="service-2014" class="clearfix">
        <div class="slogen">
            <span class="item fore1">
                <i></i><b>多</b>品类齐全，轻松购物
            </span>
            <span class="item fore2">
                <i></i><b>快</b>多仓直发，极速配送
            </span>
            <span class="item fore3">
                <i></i><b>好</b>正品行货，精致服务
            </span>
            <span class="item fore4">
                <i></i><b>省</b>天天低价，畅选无忧
            </span>
        </div>
        <div class="w">
            <dl class="fore1">
                <dt>帮助中心</dt>
                <dd>
                    <div><a target="_blank" href="#">忘记密码</a></div>
                    <div><a target="_blank" href="#">我的商品</a></div>
                    <div><a target="_blank" href="#">如何注册会员</a></div>
                    <div><a target="_blank" href="#">购买咨询</a></div>
                </dd>
            </dl>
            <dl class="fore2">
                <dt>店主之家</dt>
                <dd>
                    <div><a target="_blank" href="#">如何管理店铺</a></div>
                    <div><a target="_blank" href="#">如何发货</a></div>
                    <div><a target="_blank" href="#">如何申请开店</a></div>
                    <div><a target="_blank" href="#">查看出售商品</a></div>
                </dd>
            </dl>
            <dl class="fore3">
                <dt>支付方式</dt>
                <dd>
                    <div><a target="_blank" href="#">货到付款</a></div>
                    <div><a target="_blank" href="#">在线支付</a></div>
                    <div><a target="_blank" href="#">邮局汇款</a></div>
                    <div><a target="_blank" href="#">公司转账</a></div>
                </dd>
            </dl>
            <dl class="fore4">
                <dt>售后服务</dt>
                <dd>
                    <div><a target="_blank" href="#">返修/退换货</a></div>
                    <div><a target="_blank" href="#">联系卖家</a></div>
                    <div><a target="_blank" href="#">退款申请</a></div>
                    <div><a target="_blank" href="#">退换货政策</a></div>
                </dd>
            </dl>
            <dl class="fore5">
                <dt>关于我们</dt>
                <dd>
                    <div><a target="_blank" href="#">招聘英才</a></div>
                    <div><a target="_blank" href="#">联系我们</a></div>
                    <div><a target="_blank" href="#">合作洽谈</a></div>
                    <div><a target="_blank" href="#">关于我们</a></div>
                </dd>
            </dl>
            <div id="coverage">
                <div class="dt">
                    苏格自营覆盖区县
                </div>
                <div class="dd">
                    <p>苏格已向河南88个县，50个市辖区提供自营配送服务，支持货到付款、POS机刷卡和售后上门服务。</p>
                    <p class="ar"><a target="_blank" href="#">查看详情&nbsp;&gt;</a></p>
                </div>
            </div>
            <span class="clr"></span>
        </div>
    </div>



    <div id="footer-2014" class="w">
        <div class="links">
            <a target="_blank" href="#">招聘英才</a>|
            <a target="_blank" href="#">合作洽谈</a>|
            <a target="_blank" href="#">联系我们</a>|
            <a target="_blank" href="#">关于我们</a>|
            <a target="_blank" href="#">友情链接</a>|
            <a target="_blank" href="#">销售联盟</a>|
            <a href="#" target="_blank">苏格社区</a>|
            <a href="#" target="_blank">苏格公益</a>
        </div>
        <div class="copyright">

            <div class="copyright_detail">
                © <a target="_blank" href="http://www.sugemall.com">sugemall.com</a>
                All rights reserved.<br>
                豫ICP备15006312号-1 <br>
                <a href="http://wj.haaic.gov.cn/index.html" style="position:relative;top:-20px;left:-1px"><img width="90px" border="0" src="http://img.webscan.360.cn/status/pai/hash/24e5aec10332e8c177d75b01cdbebe97"></a>
                <a target="_blank" href="http://10.8.3.31/TopICRS/certificateAction.do?id=201505210000011772"><img width="50px" src="data/files/mall/template/gswj_icon.jpg"></a>
            </div>




           </div>

        <div class="authentication">

        </div>



    </div>






<?php echo $this->_var['async_sendmail']; ?>


    <div class="mui-mbar-tabs clearfix">
        <div class="mui-mbar-tabs-mask ">
            <div class="mui-mbar-tab mui-mbar-tab-cart" style="top: 120px;">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-cart"></div>
                <div class="mui-mbar-tab-txt"><a href="<?php echo url('app=cart'); ?>">购物车</a></div>
                <div class="mui-mbar-tab-sup">
                    <div class="mui-mbar-tab-sup-bg">
                        <div class="mui-mbar-tab-sup-bd"><?php echo $this->_var['cart_goods_kinds']; ?></div>
                    </div>
                </div>
            </div>
            <div class="mui-mbar-tab mui-mbar-tab-asset" style="top: 260px;">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-asset"></div>
                <div class="mui-mbar-tab-tip" style="right: 35px;  display: none;">
                    <a href="<?php echo url('app=member'); ?>">我的资产</a>
                    <div class="mui-mbar-arr mui-mbar-tab-tip-arr">◆</div>
                </div>
            </div>
            <div class="mui-mbar-tab mui-mbar-tab-brand" style="top: 300px;">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-brand"></div>
                <div class="mui-mbar-tab-tip" style="right: 35px;  display: none;">
                    <a href="<?php echo url('app=my_favorite&type=store'); ?>">收藏的店铺</a>
                    <div class="mui-mbar-arr mui-mbar-tab-tip-arr">◆</div>
                </div>
            </div>
            <div class="mui-mbar-tab mui-mbar-tab-favor" style="top: 340px;">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-favor"></div>
                <div class="mui-mbar-tab-tip" style="right: 35px;  display: none;">
                    <a href="<?php echo url('app=my_favorite'); ?>">收藏的产品</a>
                    <div class="mui-mbar-arr mui-mbar-tab-tip-arr">◆</div>
                </div>
            </div>
            <div class="mui-mbar-tab mui-mbar-tab-foot" style="top: 380px;">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-foot"></div>
                <div class="mui-mbar-tab-tip" style="right: 35px;  display: none;">
                    <a href="<?php echo url('app=history'); ?>">我看过的</a>
                    <div class="mui-mbar-arr mui-mbar-tab-tip-arr">◆</div>
                </div>
            </div>
            <div class="mui-mbar-tab mui-mbar-tab-qrcode" style="top: 420px;">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-qrcode"></div>
                <div class="mui-mbar-tab-tip mui-mbarp-qrcode-tip" style="right: 35px;  display: none;">
                    <div class="mui-mbarp-qrcode-hd">
                        <img src="<?php echo $this->_var['default_qrcode']; ?>" width="140" height="140">
                    </div>
                </div>
            </div>
            <div class="mui-mbar-tab mui-mbar-tab-ue" style="top: 460px;">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-ue"></div>
                <div class="mui-mbar-tab-tip" style="right: 35px;  display: none;">
                    <a href="<?php echo url('app=customer_message&type=1'); ?>">用户反馈</a>
                    <div class="mui-mbar-arr mui-mbar-tab-tip-arr">◆</div>
                </div>
            </div>
            <div class="mui-mbar-tab mui-mbar-tab-top" style="bottom: 180px;" id="gotop">
                <div class="mui-mbar-tab-logo mui-mbar-tab-logo-top"></div>
                <div class="mui-mbar-tab-tip" style="right: 35px;  display: none;">
                    <a href="javascript:void(0)">返回顶部</a>
                    <div class="mui-mbar-arr mui-mbar-tab-tip-arr">◆</div>
                </div>
            </div>

        </div>
    </div>
    <script>
        $(function() {
            var screen_height = window.screen.height;
            $(".mui-mbar-tabs-mask").css("height", screen_height);
            $('.mui-mbar-tab').hover(function() {
                $(this).addClass("mui-mbar-tab-hover");
                $(this).find('.mui-mbar-tab-tip').fadeIn(500);
            }, function() {
                $(this).removeClass("mui-mbar-tab-hover");
                $(this).find('.mui-mbar-tab-tip').fadeOut(500);
            });
        });

    </script>
</div>

</body>
</html>