<?php

/* 店铺 store */
class Sgxt_orderModel extends BaseModel
{
    var $table  = 'sgxt_order';
    var $prikey = 'id';
    var $_name  = 'sgxt_order';

    var $_relation  = array(
        'belongs_to_user'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_sgxt_order',
            'foreign_key'   => 'userid',
            'model'         => 'member',
        ),
    );
}

?>
