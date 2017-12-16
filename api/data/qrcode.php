<?php
	function reurl(){
		$key=$_GET['key'];
		$key=base64_decode($key);
		$data=array(
			'user_id'=>	$key
		);
		fk('邀请成功',$data);
	}

?>