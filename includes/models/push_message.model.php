<?php 
define('CHECK_PM_INTEVAL', 600); // 检查新消息的时间间隔（单位：秒）

/* 短消息 message */
class push_messageModel extends BaseModel
{
    
    //生成一条待推送的新
    public function addMessage($table , $key ,$value ,$title ,$touserid = '', $touser = '' ,$checked = '1' , $pushtype = 'one'){
        $model =  & m();
        $add = array(
            'table_name'  => $table,
            'table_key'    => $key,
            'table_value'  => $value,
            'title'  => $title,
            'checked' => $checked,
            'to_user' => $touser,
            'to_userid' => $touserid,
            'addtime'  => time(),
            'push_type' => $pushtype,
            );
      return  $model->table('push_message') -> add($add);
    }
    //定时查询是否有自己的信息
    public function getMessage($where){
        $model =  & m(); 
        $msglist = $model ->table('push_message') -> where($where)  -> find1();

        //封装执行方法的sql语句
        if(empty($msglist)){
            return ;
        }
        $msgdata = $model->table($msglist['table_name']) ->where(array($msglist['table_key'] => $msglist['table_value'])) ->find1();
        if(empty($msgdata)){
            return '';
        }
        //修改消息为已读状态
        $model ->table('push_message')->where(array('id' => $msglist[id])) ->save(array('is_read' => 1 ,'read_time' => time()));
       $fun = 'msg_'.$msglist['table_name'];
       return $this->$fun($msgdata);
    }
    //执行线下订单的通知逻辑
    public function msg_order_offline($msglist){
        if(empty($msglist)) return;
        //加载相应的模板
        
        if($msglist['status'] == '40'){
            $msg = '<div class="list-top">
                        <div class="top-pic">
                            <img src="./themes/wapmall/default/styles/default/images/currency/huok.png">
                        </div>
                        <div class="top-name">
                            <h2>'.$msglist['order_amount'].'</h2>
                            <p>'.$msglist['buyer_name'].'</p>
                        </div>
                        <div class="nff">
                            <img src="./themes/wapmall/default/styles/default/images/currency/scuess.png">
                            <span>支付成功</span>
                        </div>
                    </div>
                    <div class="list-bottom">
                        <ul>
                            <li>
                                <p>商品</p>
                                <p><span>'.$msglist['classname'].'</span><span>'.$msglist['pay_message'].'</span></p>
                            </li>
                            <li>
                                <p>交易时间</p>
                                <p><span>'.date('Y-m-d',$msglist['pay_time']).'</span><span>'.date('H:i',$msglist['pay_time']).'</span></p>
                            </li>
                            <li>
                                <p>支付方式</p>
                                <p>购物积分支付</p>
                            </li>
                            <li>
                                <p>交易流水号</p>
                                <p>'.$msglist['order_sn'].'</p>
                            </li>                       
                        </ul>
                    </div>
                    <div class="popjian"></div>
                    <span class="close">&nbsp;</span>
            </div>';
            return $msg;
        }
        

    }   

}
 ?>