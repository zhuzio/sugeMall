<?php 
class earningsApp extends MemberbaseApp {

    function __construct() {
        parent::__construct();
        $this->userinfo = $_SESSION['user_info'];
        $this->mod_epay = & m('epay');
        $this->model = & m();
    }
    public function earningsUnfreeze(){
        
    }

 ?>