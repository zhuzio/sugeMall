<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
    $(function() {
        $('#register_form').validate({
            errorPlacement: function(error, element) {
                var error_td = element.parent('dd');
                error_td.find('label').hide();
                error_td.append(error);
            },
            success: function(label) {
                label.addClass('validate_right').text('OK!');
            },
            onkeyup: false,
            rules: {
                tuijian: {
                    required: true,
                    minlength : 11,
                    maxlength : 11,
                    byteRange: [3, 15, '<?php echo $this->_var['charset']; ?>'],
                    remote: {
                        url: 'index.php?app=member&act=check_user&ajax=1',
                        type: 'get',
                        data: {
                            tuijian: function() {
                                return $('#tuijian').val();
                            }
                        },
                        beforeSend: function() {
                            var _checking = $('#tuijian_user');
                            _checking.prev('.field_notice').hide();
                            _checking.next('label').hide();
                            $(_checking).show();
                        },
                        complete: function() {
                            $('#tuijian_user').hide();
                        }
                    }
                },
                user_name: {
                    required: true,
                    minlength : 11,
                    maxlength : 11,
                    byteRange: [3, 15, '<?php echo $this->_var['charset']; ?>'],
                    remote: {
                        url: 'index.php?app=member&act=check_user&ajax=1',
                        type: 'get',
                        data: {
                            user_name: function() {
                                return $('#user_name').val();
                            }
                        },
                        beforeSend: function() {
                            var _checking = $('#checking_user');
                            _checking.prev('.field_notice').hide();
                            _checking.next('label').hide();
                            $(_checking).show();
                        },
                        complete: function() {
                            $('#checking_user').hide();
                        }
                    }
                },
                phone_mob: {
                    required: true,
                    number: true,
                    byteRange: [11, 11, '<?php echo $this->_var['charset']; ?>'],
                    remote: {
                        url: 'index.php?app=member&act=check_mobile&type=register',
                        type: 'get',
                        data: {
                            phone_mob: function() {
                                return $('#phone_mob').val();
                            }
                        },
                        beforeSend: function() {
                            var _checking = $('#checking_mobile');
                            _checking.prev('.field_notice').hide();
                            _checking.next('label').hide();
                            $(_checking).show();

                        },
                        complete: function() {

                            $('#checking_mobile').hide();
                        }
                    }
                },
                confirm_code: {
                    required: true,
                    number: true,
                    byteRange: [6, 6, '<?php echo $this->_var['charset']; ?>'],
                    remote: {
                        url: 'index.php?app=member&act=cmc&ajax=1',
                        type: 'get',
                        data: {
                            confirm_code: function() {
                                return $('#confirm_code').val();
                            }
                        },
                        beforeSend: function() {
                            var _checking = $('#checking_code');
                            _checking.next('label').hide();
                            $(_checking).show();

                        },
                        complete: function() {
                            $('#checking_code').hide();
                        }
                    }
                },
                password: {
                    required: true,
                    minlength: 6
                },
                password_confirm: {
                    required: true,
                    equalTo: '#password'
                },
                email: {
                    required: true,
                    email: true
                },
                real_name:{
                    required: true,
                },
                province: {
                    required: true,
                },
                city: {
                    required: true,
                },
                area: {
                    required: true,
                },
                captcha: {
                    required: true,
                    remote: {
                        url: 'index.php?app=captcha&act=check_captcha',
                        type: 'get',
                        data: {
                            captcha: function() {
                                return $('#captcha1').val();
                            }
                        }
                    }
                },
                agree: {
                    required: true
                }
            },
            messages: {
                tuijian: {
                    required: '推荐人手机号不能为空',
                    minlength : '手机长度为11位',
                    maxlength : '手机长度为11位',
                    byteRange: '推荐人手机号长度错误',
                    remote: '推荐人不存在'
                },
                user_name: {
                    required: '您必须提供一个用户名',
                    minlength : '手机长度为11位',
                    maxlength : '手机长度为11位',
                    byteRange: '用户名必须在3-15个字符之间',
                    remote: '您提供的用户名已存在'
                },
                phone_mob: {
                    required: '手机号码必须输入',
                    number: '手机号码必须是数字',
                    byteRange: '手机号码长度必须为11位',
                    remote: '您提供的手机号码已存在,<a href="index.php?app=find_password">点击找回密码</a>'
                },
                confirm_code: {
                    required: '短信验证码不能为空',
                    number: '短信验证码必须是数字',
                    byteRange: '短信验证码必须为6位',
                    remote: '短信验证码错误'
                },
                password: {
                    required: '您必须提供一个密码',
                    minlength: '密码长度应在6-20个字符之间'
                },
                password_confirm: {
                    required: '您必须再次确认您的密码',
                    equalTo: '两次输入的密码不一致'
                },
                email: {
                    required: '您必须提供您的电子邮箱',
                    email: '这不是一个有效的电子邮箱'
                },
                real_name:{
                    required: '姓名不能为空',
                },
//                province: {
//                    required: '请选择省份',
//                },
//                city: {
//                    required: '请选择城市',
//                },
//                area: {
//                    required: '请选择县区',
//                },
                captcha: {
                    required: '请输入右侧图片中的文字',
                    remote: '验证码错误'
                },
                agree: {
                    required: '您必须阅读并同意该协议,否则无法注册'
                }
            }
        });


        var canSend = true;
        var time = 60;
        var dtime = 60;
        $("#sendsms").bind('click', function() {
            var btn = $(this);
            if (!canSend)
                return;
            var sendaddress = $('#phone_mob').val();
            var fhm = $("[for='phone_mob']").text();
            if (fhm != '' && fhm != "OK!") {
                alert("请输入正确的手机号码！");
                return;
            }
            if (fhm == '' && $('#phone_mob').val() == "") {
                alert("请输入正确的手机号码！");
                return;
            }
            canSend = false;
            $.ajax({
                type: "get",
                url: "index.php?app=member&act=send_code&type=register",
                data: {
                    mobile: function() {
                        return sendaddress;
                    }
                },
                success: function(msg) {
                    var hander = setInterval(function() {
                        if (time <= 0) {
                            canSend = true;
                            clearInterval(hander);
                            btn.val("重新发送验证码");
                            btn.removeAttr("disabled");
                            time = dtime;
                        } else {
                            canSend = false;
                            btn.attr({
                                "disabled": "disabled"
                            });
                            btn.val(time + "秒后可重新发送");
                            time--;
                        }
                    }, 1000);
                    if (msg) {
                        alert("短信已发送至:" + sendaddress + " 请注意查收！");
                    } else {
                        canSend = true;
                        alert("短信发送失败，请检查手机号码是否正确！");
                    }
                }
            });
        });


    });
</script>
<script type="text/javascript">
    $(function() {
        poshytip_message($('#user_name'));
        poshytip_message($('#password'));
        poshytip_message($('#password_confirm'));
        poshytip_message($('#email'));
        poshytip_message($('#captcha1'));
    });
</script>
<div id="main" class="w-full">
    <div id="page-register" class="w login-register mt20 mb20">
        <div class="w clearfix">
            <div class="col-main">
                <ul class="clearfix">
                    <li class="icon_1"><i></i>购买商品支付订单</li>
                    <li class="icon_2"><i></i>申请开店销售商品</li>
                    <li class="icon_3"><i></i>收藏你喜欢的商品</li>
                    <li class="icon_4"><i></i>收藏你喜欢的店铺</li>
                    <li class="icon_5"><i></i>商品咨询服务评价</li>
                    <li class="icon_6"><i></i>安全交易诚信无忧</li>
                </ul>
                <h4>如果您是本站用户</h4>
                <div class="login-field">
                    <span>我已经注册过帐号，立即<a href="index.php?app=member&act=login" class="login-field-btn">请登录</a></span>
                    <span>或者 <a href="index.php?app=find_password" class="find-password">找回密码</a></span>
                </div>
            </div>
            <div class="col-sub">
                <div class="form">
                    <div class="title">用户注册</div>
                    <div class="content">
                        <form name="" id="register_form" method="post" action="">
                            <dl class="clearfix">
                                <dt>推荐人</dt>
                                <dd>
                                    <input type="text" style="width:245px;height:26px;" id="tuijian" class="input"  name="tuijian" title="请输入推荐人的手机号"  />
                                    <br /><label></label>
                                </dd>
                            </dl>
                            <dl class="clearfix">
                                <dt>手机号</dt>
                                <dd>
                                    <input type="text" style="width:245px;height:26px;" id="user_name" class="input"  name="user_name" title="请填入自己的手机号"  />
                                    <br /><label></label>
                                </dd>
                            </dl>
                            <dl class="clearfix">
                                <dt>密&nbsp;&nbsp;&nbsp;码</dt>
                                <dd>
                                    <input class="input" type="password" id="password" name="password" title="长度在6-20个字符之间,由字母、数字和标点符号组成" />
                                    <div class="clr"></div><label></label>
                                </dd>
                            </dl>
                            <dl class="clearfix">
                                <dt>确认密码</dt>
                                <dd>
                                    <input class="input" type="password" id="password_confirm" name="password_confirm" title="请再次输入你的密码" />
                                    <div class="clr"></div><label></label>
                                </dd>
                            </dl>
                            <?php if ($this->_var['msg_enabled']): ?>
                            <dl class="clearfix">
                                <dt>姓名</dt>
                                <dd>
                                    <input type="text" id="real_name" name="real_name" class="input" title="请填写真实姓名" maxlength="11" />
                                    <br /><label></label>
                                </dd>
                            </dl>
                            <dl class="clearfix">
                                <dt>验证码</dt>
                                <dd>
                                    <input class="input" type="text" id="confirm_code" name="confirm_code" value=""/>
                                    <br /><label></label>
                                </dd>
                            </dl>
                            <dl class="clearfix">
                                <dt>&nbsp;</dt>
                                <dd>
                                    <input type="button" id="sendsms" value="免费发送短信验证码"/>
                                    <br /><label></label>
                                </dd>
                            </dl>
                            <?php endif; ?>
                            <dl class="clearfix">
                                <dt>电子邮箱</dt>
                                <dd>
                                    <input class="input" type="text" id="email" name="email" title="请输入你的常用电邮，将来用于找回密码和接收商城信息" />
                                    <div class="clr"></div><label></label>
                                </dd>
                            </dl>
                            <dl>
                                <dt>区域</dt>
                                <dd>
                                    <select name="province" class="input" id="province" data-next="city" onchange="areaChange(this,this.value)">
                                        <option value="">选择省份</option>
                                    </select>
                                    <br />
                                    <div class="clr"></div><label></label>


                                    <select name="city" class="input" id="city" data-next="area" onchange="areaChange(this,this.value)">
                                        <option value="">选择城市</option>
                                    </select>
                                    <br />
                                    <div class="clr"></div><label></label>


                                    <select name="area" class="input" id="area" data-next="" onchange="areaChange(this,this.value)">
                                        <option value="">选择县区</option>
                                    </select>
                                    <br />
                                    <div class="clr"></div><label></label>

                                    <br/>
                                </dd>
                            </dl>
                            <?php if ($this->_var['captcha']): ?>
                            <dl class="clearfix">
                                <dt>验证码</dt>
                                <dd class="captcha clearfix">
                                    <input type="text" class="input float-left" name="captcha"  id="captcha1" title="请输入验证码，不区分大小写" />
                                    <img height="26" id="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" class="float-left" />
                                    <a href="javascript:change_captcha($('#captcha'));" class="float-left">看不清，换一张</a>
                                    <div class="clr"></div><label></label>
                                </dd>
                            </dl>
                            <?php endif; ?>
                            <dl class="clearfix">
                                <dt>&nbsp;</dt>
                                <dd class="mall-eula">
                                    <input id="clause" type="checkbox" name="agree" value="1" class="agree-checkbox" checked="checked" />
                                    <span>我已阅读并同意 <a href="<?php echo url('app=article&act=system&code=eula'); ?>" target="_blank">用户服务协议</a></span>
                                    <div class="clr"></div><label></label>
                                </dd>
                            </dl>
                            <dl class="clearfix">
                                <dt>&nbsp;</dt>
                                <dd>
                                    <input type="submit" name="Submit"value="立即注册"class="register-submit"title="立即注册" />
                                    <input type="hidden" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
                                </dd>
                            </dl>
                        </form>                  
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        getAreaChild('province',1);
    })
    function getAreaChild(objid,pid){
        $.ajax({
            url : 'index.php?app=regcheck&act=getAreaList',
            type : 'POST',
            dataType : 'JSON',
            data : {'pid':pid},
            success : function(res){
                res = JSON.parse(res);
                if(res.status==0){
                    var str = '请选择城市';
                    if(objid == 'area'){
                        str = '请选择县区';
                    }else if(objid == 'province'){
                        str = '请选择省份';
                    }
                    $("#"+objid).html('');
                    html = '<option value="">'+str+'</option>'
                    for(var i in res.data){
                        html += '<option value="'+res.data[i].id+'">'+res.data[i].name+'</option>';
                    }
                    $("#"+objid).html(html);
                }
            }
        });
    }

    function areaChange(obj,value){
        var type = $(obj).attr('data-next');
        if(type != ''){
            getAreaChild(type,value);
        }
    }
</script>
<?php echo $this->fetch('footer.html'); ?>
