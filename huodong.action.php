<?php
defined('G_IN_SYSTEM')or exit('No permission resources.');
System::load_app_class('memberbase',null,'no');
System::load_app_fun('user','go');
System::load_app_fun('my','go');
System::load_sys_fun('send');
class huodong extends memberbase {
	public function __construct(){
		parent::__construct();
		$this->db = System::load_sys_class("model");
	}

	public function recharge(){

		$user = $this->userinfo;
		if($user){
			$user_id = $user['uid'];
			$current_count = intval(date("Ymd",time()));
			$data = $this->db->GetOne("SELECT * FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count'");
		}

		//分享部分代码
        require_once("system/modules/mobile/jssdk.php");

        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();

        $hour = date("H",time());
        if($hour > 19){
        	$current_count = intval(date("Ymd",time()));
        	$arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `current_count`='$current_count' order by `id` desc");
        }else{
        	$current_count = intval(date("Ymd",time()))-1;
        	$arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `current_count`='$current_count' order by `id` desc");
        }
        $brr = array();
        foreach($arr as $key=>$val){
        	$crr = explode(',', $val['amount']);
        	$money[$key] = 0;
        	foreach ($crr as $k => $value) {
        		$money[$key] += $value;
        	}
        	$brr[$key]['money'] = $money[$key];

        	$uid = $val['user_id'];
        	$detail = $this->db->GetOne("SELECT * FROM `@#_member` WHERE `uid` = '$uid'");
        	$brr[$key]['username'] = $detail['username'];
        	$brr[$key]['phone'] = substr($detail['mobile'], 0, 3)."****".substr($detail['mobile'], 7);
        }

		//include templates("mobile/huodong","huafei2");
	}

	public function recharge000(){

		$user = $this->userinfo;
		if($user){
			$user_id = $user['uid'];
			$current_count = intval(date("Ymd",time()));
			$data = $this->db->GetCount("SELECT COUNT(*) FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count'");

			$flag = $this->db->GetOne("SELECT * FROM `@#_alipay_locat` WHERE `uid` = '$user_id' AND `status` = '1' order by `create_time` desc");
			$flag2 = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat` WHERE `uid` = '$user_id' AND `status` = '1' order by `create_time` desc");
			if(date("Ymd",$flag['create_time']) ==  date("Ymd",time()) || date("Ymd",$flag2['create_time']) ==  date("Ymd",time())){
				if($data > 1){
					$ttt = 1;
				}else{
					$ttt = 0;
				}
			}else{
				if($data > 0){
					$ttt = 1;
				}else{
					$ttt = 0;
				}
			}
		}
		//分享部分代码
        require_once("system/modules/mobile/jssdk.php");

        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK('wx4974d1b8daeca416', 'aef2cc68c515b6797420d5197e805677');

        $signPackage = $jssdk->GetSignPackage();

        $hour = date("H",time());
        if($hour > 19){
        	$current_count = intval(date("Ymd",time()));
        	$arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `current_count`='$current_count' order by `id` desc");
        }else{
        	$current_count = intval(date("Ymd",time()))-1;
        	$arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `current_count`='$current_count' order by `id` desc");
        }
        $brr = array();
        foreach($arr as $key=>$val){
        	$crr = explode(',', $val['amount']);
        	$money[$key] = 0;
        	foreach ($crr as $k => $value) {
        		$money[$key] += $value;
        	}
        	$brr[$key]['money'] = $money[$key];

        	$uid = $val['user_id'];
        	$detail = $this->db->GetOne("SELECT * FROM `@#_member` WHERE `uid` = '$uid'");
        	$brr[$key]['username'] = $detail['username'];
        	$brr[$key]['phone'] = substr($detail['mobile'], 0, 3)."****".substr($detail['mobile'], 7);
        }

		//include templates("mobile/huodong","huafei3");
	}

	public function recharge111(){
		echo "<h2>活动暂时下架，不便之处请谅解！谢谢</h2>";exit();
		$user = $this->userinfo;
		if($user){
			$user_id = $user['uid'];
			$current_count = intval(date("Ymd",time()));
			$data = $this->db->GetCount("SELECT COUNT(*) FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count'");
			$flag = $this->db->GetOne("SELECT * FROM `@#_alipay_locat` WHERE `uid` = '$user_id' AND `status` = '1' order by `create_time` desc");
			$flag2 = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat` WHERE `uid` = '$user_id' AND `status` = '1' order by `create_time` desc");
			if(date("Ymd",$flag['create_time']) ==  date("Ymd",time()) || date("Ymd",$flag2['create_time']) ==  date("Ymd",time())){
				if($data > 1){
					$ttt = 1;
				}else{
					$ttt = 0;
				}
			}else{
				if($data > 0){
					$ttt = 1;
				}else{
					$ttt = 0;
				}
			}
		}
		//分享部分代码
        require_once("system/modules/mobile/jssdk.php");

        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK('wx4974d1b8daeca416', 'aef2cc68c515b6797420d5197e805677');

        $signPackage = $jssdk->GetSignPackage();

        $hour = date("H",time());
        // if($hour > 19){
        // 	$current_count = intval(date("Ymd",time()));
        // 	$arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `current_count`='$current_count' order by `id` desc");
        // }else{
        // 	$current_count = intval(date("Ymd",time()))-1;
        // 	$arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `current_count`='$current_count' order by `id` desc");
        // }

        $current_count = intval(date("Ymd",time()));
        $arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `current_count`='$current_count' order by `id` desc");

        $brr = array();
        foreach($arr as $key=>$val){
        	$crr = explode(',', $val['amount']);
        	$money[$key] = 0;
        	foreach ($crr as $k => $value) {
        		$money[$key] += $value;
        	}
        	$brr[$key]['money'] = $money[$key];

        	$uid = $val['user_id'];
        	$detail = $this->db->GetOne("SELECT * FROM `@#_member` WHERE `uid` = '$uid'");
        	$brr[$key]['username'] = $detail['username'];
        	$brr[$key]['phone'] = substr($detail['mobile'], 0, 3)."****".substr($detail['mobile'], 7);
        }

		include templates("mobile/huodong","huafei3");
	}
	/*
	status :
		0,活动结束
		1,只抽中话费
		2,都抽中话费,现金红包
		3,活动未开始
		4,需登录
		5,需去充值才能参加活动
	*/

	//抢红包
	public function inrecharge(){
		$status = 3;

		$user = $this->userinfo;
		if (!$user) {
			$status = 4;
		}//未登录


		$rcmoney = 3;//抽中的话费
		$yemoney = 4;//抽中的现金红包
		
		
		$data['uid'] = $user['uid'];
		$resultMessage = '';
		if ($status==0) {
			$resultMessage = '活动已结束';
		}elseif ($status==1) {
			$data['rcmoney'] = 3;//抽中的话费
		}elseif ($status==2) {
			$data['rcmoney'] = $rcmoney;//抽中的话费
			$data['yemoney'] = $yemoney;//抽中的现金红包
		}elseif ($status==3) {
			$x = rand(1,200);
			if($x < 122){
				$data['money'] = 0;
			}else if($x < 123){
				$data['money'] = 8;
			}else if($x < 125){
				$data['money'] = 6;
			}else if($x < 129){
				$data['money'] = 5;
			}else if($x < 135){
				$data['money'] = 3;
			}else if($x < 151){
				$data['money'] = 2;
			}else{
				$data['money'] = 1;
			}
			//$resultMessage = rand(0,20);
			//$resultMessage = '活动未开始';
		}elseif ($status==4) {
			$resultMessage = '请先登录';
		}elseif ($status==5) {
			$resultMessage = '请先充值';
		}

		$data['status'] = $status;
		$data['resultMessage'] = $resultMessage;
		echo json_encode($data);
	}

	//获取红包数量
	public function GetNumber(){
		$data['number'] = 1888;
		$data['resultMessage'] = '获取成功';
		$data['status'] = 1;
		echo json_encode($data);
	}

	//中奖记录
	public function BuyLog(){
		$user = $this->userinfo;
		if($user){
			$user_id = $user['uid'];
			$hour = date("H",time());
			// if($hour > 19){
			// 	$current_count = intval(date("Ymd",time()));
			// 	$amount = $this->db->GetOne("SELECT * FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count'");
			// }else{
			// 	$current_count = intval(date("Ymd",time())-1);
			// 	$amount = $this->db->GetOne("SELECT * FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count'");
			// }
			$current_count = intval(date("Ymd",time()));
			$amount = $this->db->GetOne("SELECT * FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count' order by `id` desc");
		}
		$str = explode(',', $amount['amount']);
		$brr = array();
		$total_money = 0;
		if(empty($amount['amount'])){
			$data['total'] = 0;
		}else{
			$data['total'] = count($str);
		}
		foreach ($str as $key => $val) {
			$brr[$key]['money'] = $val; 
			$brr[$key]['type'] = $key+1;
			$total_money += $val;
		}
		$data['total_money'] = $total_money;
		// $list = array(
		// 	array('type'=>'1','money'=>'2','time'=>'3')
		// );
		
		$data['status'] = 1;
		//$data['log'] = $list;
		$data['log'] = $brr;

		echo json_encode($data);
	}

	public function SaveTotal(){
		$amount = $_POST['total'];
		$uid = $_POST['uid'];
		$current_count = intval(date("Ymd",time()));
		$time = time();
		$this->db->Autocommit_start();

		$xxx = $this->db->GetCount("SELECT COUNT(*) FROM `@#_activity_lottery` WHERE `user_id` = 'uid' AND `current_count` = '$current_count'");
		if($xxx <= 2){
			$x = $this->db->Query("INSERT INTO `@#_activity_lottery`(amount,user_id,current_count,state) VALUE ('$amount','$uid','$current_count','1')");
		
			$total = explode(',',$amount);
			$money = 0;
			foreach ($total as $key => $val) {
				$money += $val;
			}

			//$t = $this->db->Query("UPDATE `@#_activity_lottery` SET `state` = '1' WHERE `user_id` = '$user_id' AND `current_count`='$current_count'");
			$flag = $this->db->Query("INSERT INTO `@#_member_account`(`uid`,`type`,`pay`,`content`,`money`,`time`) VALUE ('$uid','1','账户','活动红包充值','$money','$time')");
			$f = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + '$money' where (`uid` = '$uid')");
			if($x && $flag && $f){
				$this->db->Autocommit_commit();
				echo 1;  //充值成功
			}else{
				$this->db->Autocommit_rollback();
				echo 2;  //充值失败
			}
		}else{
			echo 2;
		}
	}

	public function share(){
		$data['status'] = 3;
		echo json_encode($data);
	}

	public function user(){
		$user = $this->userinfo;
		
		if($user){
			if(time()<1519995600){   //1519995600
				echo 1; //活动未开始
			}else{
				echo 2;
			}
		}else{
			echo 0;
		}
	}

	public function activity_time(){
		$user = $this->userinfo;
		if($user){
			$uid = $user['uid'];
			$time = time()-3*86400;
			$data = $this->db->GetOne("SELECT * FROM `@#_alipay_locat` WHERE `uid` = '$uid' AND `status` = '1' AND `create_time`>'$time' order by `create_time` desc");
			$flag = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat` WHERE `uid` = '$uid' AND `status` = '1' AND `create_time`>'$time' order by `create_time` desc");
			$t_t = time()-1519833600;
	    	$h1 = intval($t_t/86400);
	    	$h2 = $t_t-$h1*86400;

	    	$tt = date('Ymd',time());
	    	$alipay_account = $data['alipay_account'];
	    	$all_ali = $this->db->GetList("SELECT * FROM `@#_alipay_locat` WHERE `alipay_account` = '$alipay_account' AND `status`=1 AND `create_time`>'$time'");
	    
	   		foreach($all_ali as $key => $val){
	   			$brr[] = $val['uid'];
	   		}
	   		$brr = array_unique($brr);
	   		$brr = implode(',', $brr);
	   		$arr = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `user_id` in ($brr) AND `current_count`='$tt'");
	   		foreach ($arr as $key => $val) {
	   			$drr[] = $val['user_id'];
	   		}
	   		$drr = array_unique($drr);
	   		if(!empty($drr)){
		   		$drr_s = in_array($drr);
		   	}

	   		$openid = $flag['img'];

	   		$all_wx = $this->db->GetList("SELECT * FROM `@#_wxpay_locat` WHERE `img` = '$openid' AND `status`=1 AND `create_time`>'$time'");
	    
	   		foreach($all_wx as $key => $val){
	   			$brr2[] = $val['uid'];
	   		}
	   		$brr2 = array_unique($brr2);
	   		$brr2 = implode(',', $brr2);
	   		$arr2 = $this->db->GetList("SELECT * FROM `@#_activity_lottery` WHERE `user_id` in ($brr2) AND `current_count`='$tt'");
	   		foreach ($arr2 as $key => $val) {
	   			$drr2[] = $val['user_id'];
	   		}
	   		$drr2 = array_unique($drr2);
	   		if(!empty($drr2)){
		   		$drr2_s = in_array($uid,$drr2);
		   	}

	   		
	   		
	   		$arr3 = $this->db->GetCount("SELECT COUNT(*) FROM `@#_activity_lottery` WHERE `user_id` = '$uid' AND `current_count`='$tt'");
	   		if($arr3 >= 2){
	   			echo 7;exit; //当天抽奖已达上线
	   		}

	   		if(!$data && !$flag){
	   			echo 4;exit;//未进行充值;
	   		}
			// if($data || $flag){
			// 	if($h2 < 72000){ //72000
			// 		echo 1; //活动未开始
			// 	}else if($h2 >= 86399){  //86399
			// 		echo 2; //活动已结束
			// 	}else{
			// 		if($arr > 2 || $arr2 > 2){
			// 			echo 5;
			// 		}else{
			// 			echo 3;
			// 		}
			// 	}
			// }else{
			// 	echo 4; //未进行充值;
			// }
			//|| (count($drr) == 2 && !$drr_s) || (count($drr2) == 2 && !$drr2_s)
			if(count($drr) > 2 || count($drr2) > 2 || (count($drr) == 2 && !$drr_s) || (count($drr2) == 2 && !$drr2_s)){
	   			echo 5;exit; //该充值账号参与抽奖已达上线
	   		}
			echo 3;

			
		}else{
			echo 0;
		}
	}

	// public function sure_money(){
	// 	$user = $this->userinfo;
	// 	$user_id = $user['uid'];
	// 	$hour = date("H",time());
	// 	if($hour > 19){
	// 		$current_count = intval(date("Ymd",time()));
	// 		$data = $this->db->GetOne("SELECT * FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count'");
	// 	}else{
	// 		$current_count = intval(date("Ymd",time()))-1;
	// 		$data = $this->db->GetOne("SELECT * FROM `@#_activity_lottery` WHERE `user_id`='$user_id' AND `current_count`='$current_count'");
	// 	}
	// 	$total = explode(',',$data['amount']);
	// 	$money = 0;
	// 	foreach ($total as $key => $val) {
	// 		$money += $val;
	// 	}
	// 	$time = time();
	// 	if(empty($data)){
	// 		echo 3;  //没有红包
	// 	}else{
	// 		if($data['state'] == 0){
	// 			$this->db->Autocommit_start();
	// 			$t = $this->db->Query("UPDATE `@#_activity_lottery` SET `state` = '1' WHERE `user_id` = '$user_id' AND `current_count`='$current_count'");
	// 			$flag = $this->db->Query("INSERT INTO `@#_member_account`(`uid`,`type`,`pay`,`content`,`money`,`time`) VALUE ('$user_id','1','账户','元宵红包充值','$money','$time')");
	// 			$f = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + '$money' where (`uid` = '$user_id')");
	// 			if($t && $flag && $f){
	// 				$this->db->Autocommit_commit();
	// 				echo 1;  //充值成功
	// 			}else{
	// 				$this->db->Autocommit_rollback();
	// 				echo 0;  //充值失败
	// 			}
	// 		}else{
	// 			echo 2; //已经将红包充值
	// 		}
	// 	}
		
	// }


	function test(){
		$user = $this->userinfo;
		if($user){
			$uid = $user['uid'];
			$time = time()-7*86400;
			$data = $this->db->GetOne("SELECT * FROM `@#_alipay_locat` WHERE `uid` = '$uid' AND `status` = '1' AND `create_time`>'$time'");
			$flag = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat` WHERE `uid` = '$uid' AND `status` = '1' AND `create_time`>'$time'");
			$t_t = time()-1519833600;
	    	$h1 = intval($t_t/86400);
	    	$h2 = $t_t-$h1*86400;

	    	$tt = date('Ymd',time());
	    	$alipay_account = $data['alipay_account'];
	    	$all_ali = $this->db->GetList("SELECT * FROM `@#_alipay_locat` WHERE `alipay_account` = '$alipay_account' AND `status`=1 AND `create_time`>'$time'");
	    
	   		foreach($all_ali as $key => $val){
	   			$brr[] = $val['uid'];
	   		}
	   		$brr = array_unique($brr);
	   		$brr = implode(',', $brr);
	   		$arr = $this->db->GetCount("SELECT * FROM `@#_activity_lottery` WHERE `user_id` in ($brr) AND `current_count`='$tt'");

	   		$wxpay_account = $flag['mobile'];
	   		$all_wx = $this->db->GetList("SELECT * FROM `@#_wxpay_locat` WHERE `mobile` = '$wxpay_account' AND `status`=1 AND `create_time`>'$time'");
	    
	   		foreach($all_wx as $key => $val){
	   			$brr2[] = $val['uid'];
	   		}
	   		$brr2 = array_unique($brr2);
	   		$brr2 = implode(',', $brr2);
	   		$arr2 = $this->db->GetCount("SELECT * FROM `@#_activity_lottery` WHERE `user_id` in ($brr2) AND `current_count`='$tt'");
	   	
			if($data || $flag){
				if($h2 < 72000){ //72000
					echo 1; //活动未开始
				}else if($h2 >= 86399){  //86399
					echo 2; //活动已结束
				}else{
					if($arr > 2 || $arr2 > 2){
						echo 5;
					}else{
						echo 3;
					}
				}
			}else{
				echo 4; //未进行充值;
			}
			
		}else{
			echo 0;
		}
		//include templates("mobile/huodong","test");
	}

	public function xxxx(){
		$data = $this->db->GetList("select `sid` from `@#_shoplist` where `time` > '1525042800'");
		foreach ($data as $key => $val) {
			$arr[] = $val['sid'];
		}
		$arr = array_unique($arr);
		$data2 = $this->db->GetList("select `sid` from `@#_shoplist` where `time` > '1525042800' and `q_uid` is null");
		foreach ($data2 as $key => $val) {
			$brr[] = $val['sid'];
		}
		$brr[] = '2510269';
		$brr[] = '2510272';
		$brr[] = '2510265';
		$brr[] = '2510270';
		$brr = array_unique($brr);
		$brr = implode(',', $brr);
		$data3 = $this->db->GetList("select `id`,`sid` from `@#_shoplist` where `time` > '1525042800' and `sid` not in ($brr)");
		foreach ($data3 as $key => $val) {
			$crr[$val['id']] = $val['sid'];
		}
		$crr = array_unique($crr);
		foreach ($crr as $key => $val) {
			$drr[] = $key;
		}
		$drr = implode(',', $drr);

		// $data4 = $this->db->GetList("select `sid`,`title` from `@#_shoplist` where `time` > '1525042800' and `time` < '1525057200' and `title` like '%香烟%'");
		// foreach ($data4 as $key => $val) {
		// 	$drr[$val['sid']] = $val['title'];
		// }
		// $drr = array_unique($drr);
		//var_dump(count($brr));
		
		var_dump($drr);
	}

}