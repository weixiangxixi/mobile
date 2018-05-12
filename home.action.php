<?php
defined('G_IN_SYSTEM')or exit('No permission resources.');
System::load_app_class('base','member','no');
System::load_app_fun('my','go');
System::load_app_fun('user','go');
System::load_sys_fun('send');
System::load_sys_fun('user');
class home extends base {
	public function __construct(){
		parent::__construct();
		if(ROUTE_A!='userphotoup' and ROUTE_A!='singphotoup'){
			if(!$this->userinfo){
					header("Location: ".WEB_PATH."/mobile/user/login");exit;
				if(!$uid && !$_GET['wxid']){
					if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
						header("Location: ".WEB_PATH."/api/wxlogin");exit;
					}
				}
				if(!isset($_GET['wxid'])){
					//_messagemobile("请登录",WEB_PATH."/mobile/user/login",3);
					header("Location: ".WEB_PATH."/mobile/user/login");exit;
				}else{
					$wxid = $_GET['wxid'];
					if (empty($wxid)){
						//_messagemobile("请登录",WEB_PATH."/mobile/user/login",3);
						header("Location: ".WEB_PATH."/mobile/user/login");exit;
					}
					$mem=$this->db->GetOne("select * from `@#_member_band` where `b_code`='".$wxid."'");
					if (empty($mem)){
						//_messagemobile("请登录",WEB_PATH."/mobile/user/login",3);
						header("Location: ".WEB_PATH."/mobile/user/login");exit;
					}
					$this->userinfo=$member=$this->db->GetOne("select * from `@#_member` where `uid`='".$mem['b_uid']."'");
					_setcookie("uid",_encrypt($member['uid']),60*60*24*7);
					_setcookie("ushell",_encrypt(md5($member['uid'].$member['password'].$member['mobile'].$member['email'])),60*60*24*7);
				}
			}
		}
		
		if(!$this->userinfo['mobile']){
			header("location:".WEB_PATH."/mobile/user/mobile/");exit;
		}
		
		$this->db = System::load_sys_class('model');
	}

	public function xieyi(){
		include templates("mobile/index","xieyi");
	}
	public function collectlist(){
		$member=$this->userinfo;
        $uid = $member['uid'];

        if (!$member) {
        	header("location:/");exit;
        }

		$webname = $this->_cfg['web_name'];
        $title = "商品列表_" . _cfg("web_name");
        $key = "我的收藏";
		include templates("mobile/user","collectlist");
	}
	public function addCollect(){
		$sid = htmlspecialchars($this->segment(4));

		$member=$this->userinfo;
        $uid = $member['uid'];

        if (!$member) {
        	$data['code'] = 1;
	        $data['msg'] = '请登录';
	        echo json_encode($data);exit();
        }

        $time = time();
        $list = $this->db->GetList("SELECT * FROM `@#_collection` where `sid` ='$sid' and `uid` ='$uid'");

        if (!empty($list)) {
        	$data['code'] = 1;
        	$data['msg'] = '添加失败';
        	echo json_encode($data);exit();
        }

        $this->db->Query("INSERT INTO `@#_collection` SET  `sid` ='$sid' , `uid` ='$uid',`time` = '$time' ");

        $data['code'] = 0;
        $data['msg'] = '添加成功';
        echo json_encode($data);
	}
	public function delCollect(){

        $id = htmlspecialchars($this->segment(4));

        $member=$this->userinfo;
        $uid = $member['uid'];

        if (!$member) {
        	$data['code'] = 1;
	        $data['msg'] = '请登录';
	        echo json_encode($data);exit();
        }

        $this->db->Query("DELETE FROM `@#_collection` where `id`= '$id'");

        $data['code'] = 0;
        $data['msg'] = '删除成功';
        echo json_encode($data);
    }
	public function payqrcode(){
		$webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$title="账户充值";
		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_id` = '8' ");
		include templates("mobile/user","recharge2");
	}
	public function check_qrcode(){
		
		$member=$this->userinfo;
		$code = trim($_GET['code']);
		$uid = $member['uid'];
		if (empty($_GET)) {
			exit('0');
		}
		$status = 0;
		$pay = $this->db->GetOne("SELECT * FROM `@#_member_addmoney_record` where `code` = '$code' and `uid` = '$uid' limit 1");
		if ($pay['status'] == '已付款') {
			$status = 1;
		}
		echo $status;exit();
	}
	public function index_shop_list(){
		// if (!empty($_GET)) {
			$time = time() + 3600;
			$list=$this->db->GetList("select * from `@#_shoplist` where `xsjx_time` < ".$time);
			$k = 0;
			foreach ($list as $k => $v) {
				$data[$k]['id'] = $v['id'];
				$data[$k]['q_end_time'] = $v['q_end_time'];
				$data[$k]['q_showtime'] = $v['q_showtime'];
				$data[$k]['qishu'] = $v['qishu'];
				$data[$k]['thumb'] = $v['thumb'];
				$data[$k]['times'] = intval(($v['xsjx_time'] - time()) / 60);
				$data[$k]['title'] = $v['title'];
			}
			//var_dump($data);
			echo json_encode($data);
		//}
	}
		public function init(){
		
		    $webname=$this->_cfg['web_name'];
			$member=$this->userinfo;
			$user_id = $member['uid'];
			$title="我的用户中心";
			//$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");
			if(!empty($member['headimg'])){
				$member['img'] = $member['headimg'];
			}else{
				$member['img'] = G_UPLOAD_PATH.'/'.$member['img'];
			}
		 //获取用户等级  用户新手  用户小将==
		  $memberdj=$this->db->GetList("select * from `@#_member_group`");
		  $jingyan=$member['jingyan'];
		  if(!empty($memberdj)){
		     foreach($memberdj as $key=>$val){
			    if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
			    		   $member['yungoudj']=$val['name'];

				}
			}
		}
		$uid=_getcookie('uid');

		 $user_folw=$this->db->GetOne("select * from `@#_member_flow` where uid=".$user_id);
		 if (empty($user_folw)) {
		 	$user_folw['flow'] = 0;
		 }
		 $member['flow'] = sprintf( "%.1f ",$user_folw['flow']);
		//var_dump($user_folw);

		$user_mobile = substr_replace($member['mobile'],'****',3,4);

		$useractivity = $this->db->GetOne("select * from `@#_new_user` where `uid` = ".$user_id );
		$zengsong = 0;
		if (!empty($useractivity)) {
			if ($useractivity['status'] == 1) {
				$zengsong = 8;
			}
		}

		require_once("system/modules/mobile/jssdk.php");

	         $wechat= $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

	        $jssdk = new JSSDK($wechat['appid'],$wechat['appsecret']);

	        $signPackage = $jssdk->GetSignPackage();

	    $vip_flag = $this->db->GetOne("SELECT * FROM `@#_member` WHERE `uid` = '$user_id'");

		include templates("mobile/user","index2");
	}
	public function init2(){
		
		    $webname=$this->_cfg['web_name'];
			$member=$this->userinfo;
			$user_id = $member['uid'];
			$title="我的用户中心";
			//$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");
			if(!empty($member['headimg'])){
				$member['img'] = $member['headimg'];
			}else{
				$member['img'] = G_UPLOAD_PATH.'/'.$member['img'];
			}
		 //获取用户等级  用户新手  用户小将==
		  $memberdj=$this->db->GetList("select * from `@#_member_group`");
		  $jingyan=$member['jingyan'];
		  if(!empty($memberdj)){
		     foreach($memberdj as $key=>$val){
			    if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
			    		   $member['yungoudj']=$val['name'];

				}
			}
		}
		$uid=_getcookie('uid');

		 $user_folw=$this->db->GetOne("select * from `@#_member_flow` where uid=".$user_id);
		 if (empty($user_folw)) {
		 	$user_folw['flow'] = 0;
		 }
		 $member['flow'] = sprintf( "%.1f ",$user_folw['flow']);
		//var_dump($user_folw);

		$user_mobile = substr_replace($member['mobile'],'****',3,4);

		$useractivity = $this->db->GetOne("select * from `@#_new_user` where `uid` = ".$user_id );
		$zengsong = 0;
		if (!empty($useractivity)) {
			if ($useractivity['status'] == 1) {
				$zengsong = 8;
			}
		}

		require_once("system/modules/mobile/jssdk.php");

	         $wechat= $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

	        $jssdk = new JSSDK($wechat['appid'],$wechat['appsecret']);

	        $signPackage = $jssdk->GetSignPackage();

		include templates("mobile/user","index2");
	}
	public function init1(){
		
	    $webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$user_id = $member['uid'];
		$title="我的用户中心";
		//$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");
		if(!empty($member['headimg'])){
			$member['img'] = $member['headimg'];
		}else{
			$member['img'] = G_UPLOAD_PATH.'/'.$member['img'];
		}
	 //获取用户等级  用户新手  用户小将==
	  $memberdj=$this->db->GetList("select * from `@#_member_group`");
	  $jingyan=$member['jingyan'];
	  if(!empty($memberdj)){
	     foreach($memberdj as $key=>$val){
		    if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
		    		   $member['yungoudj']=$val['name'];

			}
		}
	}
	$uid=_getcookie('uid');

	 $user_folw=$this->db->GetOne("select * from `@#_member_flow` where uid=".$user_id);
	 if (empty($user_folw)) {
	 	$user_folw['flow'] = 0;
	 }
	 $member['flow'] = sprintf( "%.1f ",$user_folw['flow']);
	//var_dump($user_folw);

	$user_mobile = substr_replace($member['mobile'],'****',3,4);

	$useractivity = $this->db->GetOne("select * from `@#_new_user` where `uid` = ".$user_id );
	$zengsong = 0;
	if (!empty($useractivity)) {
		if ($useractivity['status'] == 1) {
			$zengsong = 8;
		}
	}

	require_once("system/modules/mobile/jssdk.php");

         $wechat= $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'],$wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();

	include templates("mobile/user","index");
}

//流量
	public function flowshow(){
		include templates("mobile/user","flowshow");
	}

public function invite(){

        $webname=$this->_cfg['web_name'];

        $member=$this->userinfo;

        $title="我的用户中心";

        $uid=_getcookie('uid');

        //$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");

        //获取参与等级  参与新手  参与小将==

        $memberdj=$this->db->GetList("select * from `@#_member_group`");

        $wechat= $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jingyan=$member['jingyan'];

        if(!empty($memberdj)){

            foreach($memberdj as $key=>$val){

                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){

                    $member['yungoudj']=$val['name'];

                }

            }

        }

        require_once("system/modules/mobile/jssdk.php");

         $wechat= $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'],$wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        //var_dump($signPackage);
        include templates("mobile/user","invite");

    }	


	public function inviteshare(){

		$member=$this->userinfo;

		require_once("system/modules/mobile/jssdk.php");

		 $wechat= $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

		$jssdk = new JSSDK($wechat['appid'],$wechat['appsecret']);

		$signPackage = $jssdk->GetSignPackage();

		include templates("mobile/user","inviteshare");

	}

	public function shareinc(){

		$uid = empty($_POST['f']) ? 0 : $_POST['f'];

		$share=$this->db->GetList("select * from `@#_wxch_cfg`");

		if($uid<1){

			echo 5;die;

		}

		if(!$share[11]['cfg_value']){

			echo 1;die;

		}else{

			$info = $this->db->GetOne("SELECT * FROM `@#_share` WHERE `uid` ='$uid' LIMIT 1");

			if(empty($info)){

				$mon = empty($share[12]['cfg_value']) ? 0 : $share[12]['cfg_value'];

				$time = time();

				$q1 = $this->db->Query("UPDATE `@#_member` SET  `money` =`money`+$mon WHERE `uid` = $uid");

				$q2 = $this->db->Query("INSERT INTO `@#_share` SET  `time` ='$time' , `uid` ='$uid'");

				if($q1>0 && $q2>0){

					echo 2;die;

				}else{

					echo 3;die;

				}

			}else{

				echo 4;die;

			}
		}

	}

	//参与记录
	public function userbuylist(){
	   $webname=$this->_cfg['web_name'];
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$uid = $member['uid'];
		$title="参与记录";					
		//$record=$mysql_model->GetList("select * from `@#_member_go_record` where `uid`='$uid' ORDER BY `time` DESC");
		$user_dizhi = $mysql_model->GetOne("select * from `@#_member_dizhi` where `uid` = '$uid'");
		include templates("mobile/user","userbuylist");
	}

	public function myorder(){
	   $webname=$this->_cfg['web_name'];
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		if ($member['order_auth']==0) {
			_messagemobile("没有权限",WEB_PATH."/member/home",2);
			exit();
		}
		$bind_phone = $member['bind_phone'];
		$brr = explode('，', $bind_phone);
		if (!empty($_GET)) {
			$mobile = trim($_GET['mobile']);
			$bp = $mysql_model->GetOne("select * from `@#_member` where `uid` = '4'");
			$phone = $bp['bind_phone'];
			if (strpos($phone,$mobile) !== false) {
				//$limit = "0 , 9";
				$user = $mysql_model->GetList("select * from `@#_member_dizhi` where `mobile` = '$mobile' ");

				if (empty($user)) {
					_messagemobile("找不到有填写此号码的收货地址",WEB_PATH."/mobile/home/myorder",3);
					exit();
				}
				foreach ($user as $k => $v) {
					$user_record[$k] = $mysql_model->GetList("select * from `@#_member_go_record` where huode!=0 and  `dizhi_id` = ".$v['id']." order by time desc ");
				}
				$data = call_user_func_array('array_merge', $user_record);
				$data = $this->multi_array_sort($data,'dizhi_time',SORT_DESC);
				$user_record = $this->page_array(10,1,$data,0);
				//$user_record = $mysql_model->GetList("select * from `@#_member_go_record` where huode!=0 and  `uid` = ".$user['uid']." and status like '%已发货%' order by time desc limit ".$limit);
				//$user_record_count = $mysql_model->GetList("select * from `@#_member_go_record` where huode!=0 and  `uid` = ".$user['uid']." and status like '%已发货%' order by time desc ");
				$count = count($data);

				$keyword = $mobile;
				foreach ($user_record as $k => $v) {
					$list[$k]['url'] = "onclick=\"location.href= 'http://m.yyygcs.vip/index.php/mobile/user/buyDetail/".$v['shopid']."'\"";
					$list[$k]['username'] = $v['username'];
					$list[$k]['shopid'] = $v['shopid'];
					$list[$k]['shopname'] = $v['shopname'];
					$list[$k]['thumb'] = $this->get_shop_img($v['shopid']);
					$list[$k]['shopqishu'] = $v['shopqishu'];

					$list[$k]['time'] = date('Y-m-d H:i',$v['dizhi_time']);
					if (empty($v['dizhi_time'])) {
						$list[$k]['time'] = date('Y-m-d H:i',$v['time']);
					}
					
					$list[$k]['price'] = $this->get_shop_price($v['shopid']);
				}		
			}else{
				_messagemobile("您没有此号码的查询权限",WEB_PATH."/mobile/home/myorder",2);
				exit();
			}
		}
		include templates("mobile/user","userorder");
	}
	/** 
	 * 数组分页函数  核心函数  array_slice 
	 * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中 
	 * $count   每页多少条数据 
	 * $page   当前第几页 
	 * $array   查询出来的所有数组 
	 * order 0 - 不变     1- 反序 
	*/   
	function page_array($count,$page,$array,$order){  
	    global $countpage; #定全局变量  
	    $page=(empty($page))?'1':$page; #判断当前页面是否为空 如果为空就表示为第一页面   
	       $start=($page-1)*$count; #计算每次分页的开始位置  
	    if($order==1){  
	      $array=array_reverse($array);  
	    }     
	    $totals=count($array);    
	    $countpage=ceil($totals/$count); #计算总页面数  
	    $pagedata=array();  
	    $pagedata=array_slice($array,$start,$count);  
	    return $pagedata;  #返回查询数据  
	} 
	//排序方法
	function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){ 
	    if(is_array($multi_array)){ 
	        foreach ($multi_array as $row_array){ 
	            if(is_array($row_array)){ 
	                $key_array[] = $row_array[$sort_key]; 
	            }else{ 
	                return false; 
	            } 
	        } 
	    }else{ 
	        return false; 
	    } 
	    //sort , SORT_DESC	SORT_ASC
	    array_multisort($key_array,$sort,$multi_array); 
	    return $multi_array; 
	} 
	public function get_shop_img($id){
		$mysql_model=System::load_sys_class('model');
		$shop = $mysql_model->GetOne("select * from `@#_shoplist` where `id` = '$id'");
		return "/statics/uploads/".$shop['thumb'];
	}
	public function get_shop_price($id){
		$mysql_model=System::load_sys_class('model');
		$shop = $mysql_model->GetOne("select * from `@#_shoplist` where `id` = '$id'");
		return $shop['money'];
	}
	public function getorder(){
		$member=$this->userinfo;
		$mysql_model=System::load_sys_class('model');
		if ($member['order_auth']==0) {
			_messagemobile("没有权限",WEB_PATH."/member/home",2);
			exit();
		}
		$mobile = trim($_GET['mobile']);
		$p = trim($_GET['p']);
		$bp = $mysql_model->GetOne("select * from `@#_member` where `uid` = '4'");
		$phone = $bp['bind_phone'];
		if (strpos($phone,$mobile) !== false) {
			
		if (!empty($mobile)) {
			$user = $mysql_model->GetList("select * from `@#_member_dizhi` where `mobile` = '$mobile'");

			if (empty($user)) {
				echo 0;
				exit();
			}
			foreach ($user as $k => $v) {
				$user_record[$k] = $mysql_model->GetList("select * from `@#_member_go_record` where huode!=0 and  `dizhi_id` = ".$v['id']." order by time desc ");
			}
			$data = call_user_func_array('array_merge', $user_record);
			$data = $this->multi_array_sort($data,'dizhi_time',SORT_DESC);
			$user_record = $this->page_array(10,$p,$data,0);
			//$user_record = $mysql_model->GetList("select * from `@#_member_go_record` where huode!=0 and  `uid` = ".$user['uid']." and status like '%已发货%' order by time desc limit ".$limit);
			foreach ($user_record as $k => $v) {
				$list[$k]['url'] = "onclick=\"location.href= 'http://m.yyygcs.vip/index.php/mobile/user/buyDetail/".$v['shopid']."'\"";
				$list[$k]['username'] = $v['username'];
				$list[$k]['shopid'] = $v['shopid'];
				$list[$k]['shopname'] = $v['shopname'];
				$list[$k]['thumb'] = $this->get_shop_img($v['shopid']);
				$list[$k]['shopqishu'] = $v['shopqishu'];
				$list[$k]['time'] = date('Y-m-d H:i',$v['dizhi_time']);
				if (empty($v['dizhi_time'])) {
					$list[$k]['time'] = date('Y-m-d H:i',$v['time']);
				}
				$list[$k]['price'] = $this->get_shop_price($v['shopid']);
			}
			$rs['code'] = 1;
			$rs['data'] = $list;
		}else{
			$rs['code'] = 0;
			$rs['data'] = '';			
		}
						
		}else{
			$rs['code'] = 0;
			$rs['data'] = '';			
		}
		echo json_encode($rs);
		exit();
	}
	//参与记录详细

	public function userbuydetail(){

	    $webname=$this->_cfg['web_name'];

		$mysql_model=System::load_sys_class('model');

		$member=$this->userinfo;

		$title="参与详情";

		$crodid=intval($this->segment(4));

		$record=$mysql_model->GetOne("select * from `@#_member_go_record` where `id`='$crodid' and `uid`='$member[uid]' LIMIT 1");		

		if($crodid>0){

			include templates("member","userbuydetail");

		}else{

			echo _messagemobile("页面错误",WEB_PATH."/member/home/userbuylist",3);

		}

	}

	//获得的商品

	public function orderlist(){

	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="获得的商品";

		//$record=$this->db->GetList("select * from `@#_member_go_record` where `uid`='".$member['uid']."' and `huode`>'10000000' ORDER BY id DESC");				

		include templates("mobile/user","orderlist");

	}
	//订单详情
	public function orderdetail1(){
		$webname=$this->_cfg['web_name'];
		if(!$member=$this->userinfo){
		  header("location: ".WEB_PATH."/mobile/user/login");
		}
		$oid=intval($this->segment(4));
		if($oid==0 || !$oid) {echo '无此信息！';}
		else{
		   $mysql_model=System::load_sys_class('model');

		   $record=$mysql_model->GetOne("select * FROM  `@#_member_go_record` where id='$oid' limit 1");

		   $info=$mysql_model->GetOne("select * FROM  `@#_shoplist` where id=".$record['shopid']);


		   $record['thumb'] = $info['thumb'];

          $record['q_end_time']=microt($info['q_end_time']);
		  $dizhi=$mysql_model->GetList("select * from `@#_member_dizhi` where `uid`='$member[uid]' order by `default` desc,`time` desc ");
		}
		$place =  $this->db->GetOne("SELECT * FROM `@#_member_dizhi` where  `id` = ".$record['dizhi_id']);

		include templates("mobile/user","orderdetail");
	}
	public function orderdetail(){
		$webname=$this->_cfg['web_name'];
		if(!$member=$this->userinfo){
		  header("location: ".WEB_PATH."/mobile/user/login");
		}
		$oid=intval($this->segment(4));
		if($oid==0 || !$oid) {echo '无此信息！';}
		else{
		   $mysql_model=System::load_sys_class('model');

		   $record=$mysql_model->GetOne("select * FROM  `@#_member_go_record` where id='$oid' limit 1");

		   $info=$mysql_model->GetOne("select * FROM  `@#_shoplist` where id=".$record['shopid']);


		   $record['thumb'] = $info['thumb'];

          $record['q_end_time']=microt($info['q_end_time']);
		  $dizhi=$mysql_model->GetList("select * from `@#_member_dizhi` where `uid`='$member[uid]' and is_delete = '0'  order by `default` desc,`time` desc ");

		  
		  if ($record['company'] == '京东快递') {
		  	if (!empty($record['company_code'])) {
		  		$wuliu = $this->jingdong('JD',trim($record['company_code']));
		  	}	  
		  }else{
		  	if (!empty($record['company_code'])) {
		  	  	$wuliu = $this->express('',trim($record['company_code']));
		  	}
		  	//$wuliu = $this->express('3831443509873');
		  }
		  
			
		}
		$place =  $this->db->GetOne("SELECT * FROM `@#_member_dizhi` where  `id` = ".$record['dizhi_id']);

		include templates("mobile/user","orderdetail1");
	}
	public function jingdong($code){
		return '';
	}
	public function express($type,$code){
    	$key = '69265c2bf0dfc8f79388efacf421fe3f';
    	$code = $code;
    	if (empty($type)) {
    		$type = 'auto';
    	}
    	$api = 'https://way.jd.com/jisuapi/query?type='.$type.'&number='.$code.'&appkey='.$key;
    	$data = $this->send_get($api);
    	$rs = json_decode($data,true);
    	$info = '';
    	if ($rs['result']['status'] == 0) {
    		$info = $rs['result']['result']['list'];
    		for ($i=0; $i < count($info); $i++) { 
    			$info[$i]['id'] = $i;
    		}
    	}
    	return $info;
    }

    //get请求数据
    public function send_get($url) {  
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出,参数为0表示不带头文件，为1表示带头文件
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        return $data;
    }
	public function do_dizhi(){
		$sid = intval($_POST['sid']);
		$did = intval($_POST['did']);
		if (!empty($_POST)) {
			$timed = time();

			$check = $this->db->Getone("select * from `@#_member_go_record` WHERE `id` = $sid");

			if (!empty($check['dizhi_id'])) {
				echo 0;
				exit();
			}

			$q = $this->db->Query("UPDATE `@#_member_go_record` SET `dizhi_id` = $did,`dizhi_time` = $timed WHERE `id` = $sid");

			$data1 =  $this->db->GetOne("SELECT * FROM `@#_member_dizhi` where  `id` = ".$did);
			$data2 =  $this->db->GetOne("SELECT * FROM `@#_member` where  `uid` = '4'");
			
			if (!empty($data2['bind_phone'])) {
				if (strpos($data2['bind_phone'],$data1['mobile']) !== false) {
<<<<<<< HEAD
					$this->db->Query("UPDATE `@#_member_go_record` SET `status` = '已付款,已发货,未完成' ,`company` = '潮尚配送' WHERE `id` = $sid");
=======
<<<<<<< HEAD
					$this->db->Query("UPDATE `@#_member_go_record` SET `status` = '已付款,已发货,未完成' ,`company` = '潮尚配送' WHERE `id` = $sid");
=======
<<<<<<< HEAD
					$this->db->Query("UPDATE `@#_member_go_record` SET `status` = '已付款,已发货,未完成' ,`company` = '潮尚配送' WHERE `id` = $sid");
=======
					$time = explode ( " ", microtime () );
					$time = $time[0] * 1000;  
					$time2 = explode (".", $time );  
					$time = $time2[0];
					$company_code = date("YmdHis",time()).$time;
					$this->db->Query("UPDATE `@#_member_go_record` SET `status` = '已付款,已发货,未完成' ,`company` = '潮尚配送',`company_code` = '$company_code' WHERE `id` = $sid");
>>>>>>> 5.12
>>>>>>> four
>>>>>>> xxxx
				}
			}
			

			if ($q) {
				echo 1;
			}else{
				echo 0;
			}
		}
		
	}
	public function do_shouhuo(){
		$s = intval($_POST['s']);
		$did = intval($_POST['did']);

		if ($s == 2) {
			$status = "已付款,已发货,已完成";
		}
		if (!empty($_POST)) {
			$q = $this->db->Query("UPDATE `@#_member_go_record` SET `status` = '$status' WHERE `id` = $did");
			if ($q) {
				echo 1;
			}else{
				echo 0;
			}
		}
		
	}
	//账户管理

	public function userbalance(){

	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户记录";

		$account=$this->db->GetList("select * from `@#_member_account` where `uid`='$member[uid]' and `pay` = '账户' ORDER BY time DESC");

         		$czsum=0;

         		$xfsum=0;

		if(!empty($account)){ 

			foreach($account as $key=>$val){

			  if($val['type']==1){

				$czsum+=$val['money'];		  

			  }else{

				$xfsum+=$val['money'];		  

			  }		

			} 		

		}

		

		include templates("mobile/user","userbalance");

	}

	

	 

	public function userrecharge(){

	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");
<<<<<<< HEAD
		//$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where pay_id=9");
		
=======
<<<<<<< HEAD
		//$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where pay_id=9");
		
=======
<<<<<<< HEAD
		//$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where pay_id=9");
		
=======

		// $wxpay = $this->db->GetOne("SELECT * FROM `@#_pay` where pay_id=9");
		// if ($wxpay['pay_start']==1) {
		// 	header("Location:/mobile/home/userrecharge88");
		// 	exit();
		// }

>>>>>>> 5.12
>>>>>>> four
>>>>>>> xxxx
		$wxpay = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id`='7'");
		if ($wxpay['open']==1) {
			header("Location:/api/wxorder3");
			exit();
		}

		include templates("mobile/user","recharge");
		if (empty($paylist)) {
		  	header("Location:/mobile/home/userrecharge1");
		}
		
	}
	public function userrecharge3(){

	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		

		include templates("mobile/user","recharge3");		
	}
	public function userrecharge4(){
		session_start();
		// $img = $_SESSION['img'];
		// var_dump($img);
		if (empty($_SESSION['img'])) {
			header('Location:/api/wxorder');
			exit();
		}
	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		$_SESSION['wxtoken'] = $member['uid'];

		include templates("mobile/user","recharge4");		
	}
	public function userrecharge6(){
		session_start();
		// $img = $_SESSION['img'];
		// var_dump($img);
		if (empty($_SESSION['img'])) {
			header('Location:/api/wxorder1');
			exit();
		}
	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		$_SESSION['wxtoken'] = $member['uid'];

		include templates("mobile/user","recharge6");		
	}
	public function userrecharge7(){
		session_start();
		// $img = $_SESSION['img'];
		// var_dump($img);
		if (empty($_SESSION['img'])) {
			header('Location:/api/wxorder2');
			exit();
		}
	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		$_SESSION['wxtoken'] = $member['uid'];

		include templates("mobile/user","recharge7");		
	}
	public function userrecharge8(){
		session_start();
		// $img = $_SESSION['img'];
		// var_dump($img);
		if (empty($_SESSION['img'])) {
			header('Location:/api/wxorder3');
			exit();
		}
	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		$_SESSION['wxtoken'] = $member['uid'];

		$zhuanobj = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id` = '34' ");
		$zhuan = $zhuanobj['img'];

		include templates("mobile/user","recharge8");		
	}
	public function userrecharge88(){
		session_start();
		// $img = $_SESSION['img'];
		// var_dump($img);
		if (empty($_SESSION['img'])) {
			header('Location:/api/wxorder3');
			exit();
		}
	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		$_SESSION['wxtoken'] = $member['uid'];

		$zhuanobj = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id` = '34' ");
		$zhuan = $zhuanobj['img'];

		include templates("mobile/user","recharge88");		
	}
	public function bandalipay(){
		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;
		$uid = $member['uid'];

		if (!$member) {
			header("Location:/mobile/home");
			exit();
		}
		$alipay = $this->db->GetOne("SELECT * FROM `@#_user_alipay` where  `uid` = $uid order by id desc limit 1");
		$account = $alipay['alipay_account'];
		$name = $alipay['alipay_name'];
		include templates("mobile/user","bandalipay");
	}
	public function save_alipay(){
		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;


		if (!$member) {
			//header("Location:/");
			exit();
		}

		if (empty($_POST)) {
			//header("Location:/mobile/home/bandalipay");
			exit();
		}
		$alipay_account = $_POST['username'];
		$alipay_name = $_POST['qianming'];
		$uid = $member['uid'];
		$timed = time();
		
		$alipay = $this->db->GetOne("SELECT * FROM `@#_user_alipay` where  `uid` = $uid order by id desc limit 1");
		if (empty($alipay)) {
			$this->db->Query("INSERT INTO `@#_user_alipay` SET `uid` = $uid,`alipay_account` = '$alipay_account', `alipay_name` = '$alipay_name',`create_time` = $timed ");
			echo 1;
			exit();
		}else{
			$this->db->Query("UPDATE `@#_user_alipay` SET `alipay_account` = '$alipay_account', `alipay_name` = '$alipay_name' WHERE `uid` = $uid");
			echo 1;
			exit();
		}
		echo 0;
	}
	//支付宝
	public function userrecharge9(){
	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$uid = $member['uid'];

		$wxpay = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id`='27'");
		if ($wxpay['open']==0) {
			header("Location:/mobile/home/userrecharge3");
			exit();
		}

		$alipay = $this->db->GetOne("SELECT * FROM `@#_user_alipay` where  `uid` = $uid order by id desc limit 1");
		if (empty($alipay)) {
			header("Location:/mobile/home/bandalipay");
			exit();
		}

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		include templates("mobile/user","recharge9");		
	}
	//支付宝
	public function userrecharge99(){
	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$uid = $member['uid'];

		$wxpay = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id`='27'");
		if ($wxpay['open']==0) {
			header("Location:/mobile/home/userrecharge3");
			exit();
		}

		$alipay = $this->db->GetOne("SELECT * FROM `@#_user_alipay` where  `uid` = $uid order by id desc limit 1");
		if (empty($alipay)) {
			header("Location:/mobile/home/bandalipay");
			exit();
		}

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		include templates("mobile/user","recharge99");		
	}
	public function userrecharge999(){
		$m = htmlspecialchars($this->segment(4));
		include templates("mobile/user","recharge999");	
	}
	public function wxpay_locat2(){
		session_start();

		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="支付中";

		if (!$member) {
			//header("Location:/");
			exit();
		}

		//$money = $this->segment(4);

		$money = $_POST['money'];

		$array = array('10','20','30','50','100','200','300','500','1000','2000','5000');

		if(!in_array($money,$array)){
		    //header("Location:/index.php/mobile/home/userrecharge");
	        exit();
		} 


		//var_dump($money);
		$timed = time();

		$uid = $member['uid'];
		$username = $member['username'];
		$mobile = $member['mobile'];

		if (empty($_SESSION['wxtoken'])) {
			//header("Location:/");
			exit();
		}

		$img = $_SESSION['img'];

		$pay = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat` where  `uid` = $uid order by id desc limit 1");

		if ($money == 10) {
			$wid = 1;
		}elseif ($money == 50) {
			$wid = 2;
		}elseif ($money == 100) {
			$wid = 3;
		}elseif ($money == 500) {
			$wid = 4;
		}elseif ($money == 1000) {
			$wid = 5;
		}elseif ($money == 2000) {
			$wid = 6;
		}elseif ($money == 200) {
			$wid = 8;
		}elseif ($money == 300) {
			$wid = 9;
		}elseif ($money == 5000) {
			$wid = 10;
		}elseif ($money == 20) {
			$wid = 19;
		}elseif ($money == 30) {
			$wid = 20;
		}else{
			$wid = 1;
		}

		$pay_qrcode = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where  `id` = ".$wid);
		$pay_qrcode_img = $pay_qrcode['img'];
		// var_dump($pay);
		// exit();
		if(empty($pay)){
			//$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = $img, `username` = $username,`mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = '$img', `username` = '$username', `mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			//include templates("mobile/user","wxpay_locat");

			echo '/statics/templates/yungou/images/mobile/money_qrcode/'.$money.'.jpg';

			exit();
		}else{
			$miao = $timed - $pay['create_time']; 
			if ($miao < 3) {
				//_messagemobile("操作过快，请稍后再试",WEB_PATH."/mobile/home/userrecharge4");
				echo '';
				exit();
			}else{
				$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = '$img', `username` = '$username', `mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
				//include templates("mobile/user","wxpay_locat");
				echo '/statics/templates/yungou/images/mobile/money_qrcode/'.$money.'.jpg';
				exit();
			}
		}
	}
	public function wxpay_locat3(){

		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="支付中";

		if (!$member) {
			//header("Location:/");
			exit();
		}

		//$money = $this->segment(4);

		$money = $_POST['money'];

		$array = array('10','20','30','50','100','200','300','500','1000','2000','5000');

		if(!in_array($money,$array)){
		    //header("Location:/index.php/mobile/home/userrecharge");
	        exit();
		} 


		//var_dump($money);
		$timed = time();

		$uid = $member['uid'];
		$username = $member['username'];
		$mobile = $member['mobile'];

		$alipay = $this->db->GetOne("SELECT * FROM `@#_user_alipay` where  `uid` = $uid order by id desc limit 1");
		if (empty($alipay)) {
			header("Location:/mobile/home/bandalipay");
			exit();
		}

		$account = $alipay['alipay_account'];
		$name = $alipay['alipay_name'];

		$pay = $this->db->GetOne("SELECT * FROM `@#_alipay_locat` where  `uid` = $uid order by id desc limit 1");

		if ($money == 10) {
			$wid = 1;
		}elseif ($money == 50) {
			$wid = 2;
		}elseif ($money == 100) {
			$wid = 3;
		}elseif ($money == 500) {
			$wid = 4;
		}elseif ($money == 1000) {
			$wid = 5;
		}elseif ($money == 2000) {
			$wid = 6;
		}elseif ($money == 200) {
			$wid = 8;
		}elseif ($money == 300) {
			$wid = 9;
		}elseif ($money == 5000) {
			$wid = 10;
		}elseif ($money == 20) {
			$wid = 19;
		}elseif ($money == 30) {
			$wid = 20;
		}else{
			$wid = 1;
		}

		$pay_qrcode = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where  `canshu` = 'alipaycode' and `open` = '1' ");
		$pay_id = $pay_qrcode['zhi'];
		$pay_qrcode_img = $this->db->GetOne("SELECT * FROM `@#_alipay_qrcode` where  `zhi` = '$pay_id' and `num` = '$money' ");
		// var_dump($pay);
		// exit();
		if(empty($pay)){
			//$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = $img, `username` = $username,`mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			$this->db->Query("INSERT INTO `@#_alipay_locat` SET `uid` = $uid,`alipay_account` = '$account',`alipay_name` = '$name',`username` = '$username', `mobile` = '$mobile',`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			//include templates("mobile/user","wxpay_locat");
			//echo json_encode($name);exit();
			//echo '/statics/templates/yungou/images/mobile/money_qrcode/'.$money.'.jpg';

			//echo '/statics/uploads/linkimg/'.$pay_qrcode_img['img'];
			$info=$this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id`='31' ");
			echo $info['values'];
			exit();
		}else{
			$miao = $timed - $pay['create_time']; 
			if ($miao < 3) {
				//_messagemobile("操作过快，请稍后再试",WEB_PATH."/mobile/home/userrecharge4");
				echo '';
				exit();
			}else{
				$this->db->Query("INSERT INTO `@#_alipay_locat` SET `uid` = $uid,`alipay_account` = '$account', `alipay_name` = '$name', `username` = '$username', `mobile` = '$mobile',`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
				//include templates("mobile/user","wxpay_locat");
				//echo '/statics/templates/yungou/images/mobile/money_qrcode/'.$money.'.jpg';

				//echo '/statics/uploads/linkimg/'.$pay_qrcode_img['img'];
				$info=$this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id`='31' ");
				echo $info['values'];
				exit();
			}
		}
	}
	public function wxpay_locat1(){
		session_start();

		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="支付中";

		if (!$member) {
			//header("Location:/");
			exit();
		}

		//$money = $this->segment(4);

		$money = $_POST['money'];

		$array = array('10','20','30','50','100','200','300','500','1000','2000','5000');

		if(!in_array($money,$array)){
		    //header("Location:/index.php/mobile/home/userrecharge");
	        exit();
		} 


		//var_dump($money);
		$timed = time();

		$uid = $member['uid'];
		$username = $member['username'];
		$mobile = $member['mobile'];

		if (empty($_SESSION['wxtoken'])) {
			//header("Location:/");
			exit();
		}

		$img = $_SESSION['img'];
		$openid = $_SESSION['openid'];

		$pay = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat` where  `uid` = $uid order by id desc limit 1");

		if ($money == 10) {
			$wid = 1;
		}elseif ($money == 50) {
			$wid = 2;
		}elseif ($money == 100) {
			$wid = 3;
		}elseif ($money == 500) {
			$wid = 4;
		}elseif ($money == 1000) {
			$wid = 5;
		}elseif ($money == 2000) {
			$wid = 6;
		}elseif ($money == 200) {
			$wid = 8;
		}elseif ($money == 300) {
			$wid = 9;
		}elseif ($money == 5000) {
			$wid = 10;
		}elseif ($money == 20) {
			$wid = 19;
		}elseif ($money == 30) {
			$wid = 20;
		}else{
			$wid = 1;
		}

		$pay_qrcode = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where  `canshu` = 'paycode' and `open` = '1' ");
		$pay_id = $pay_qrcode['zhi'];
		$pay_qrcode_img = $this->db->GetOne("SELECT * FROM `@#_wxpay_qrcode` where  `zhi` = '$pay_id' and `num` = '$money' ");
		// var_dump($pay);
		// exit();
		if(empty($pay)){
			//$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = $img, `username` = $username,`mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = '$img',`openid` = '$openid', `username` = '$username', `mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			//include templates("mobile/user","wxpay_locat");

			//echo '/statics/templates/yungou/images/mobile/money_qrcode/'.$money.'.jpg';

			echo '/statics/uploads/linkimg/'.$pay_qrcode_img['img'];
			exit();
		}else{
			$miao = $timed - $pay['create_time']; 
			if ($miao < 3) {
				//_messagemobile("操作过快，请稍后再试",WEB_PATH."/mobile/home/userrecharge4");
				echo '';
				exit();
			}else{
				$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = '$img', `openid` = '$openid', `username` = '$username', `mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
				//include templates("mobile/user","wxpay_locat");
				//echo '/statics/templates/yungou/images/mobile/money_qrcode/'.$money.'.jpg';
				echo '/statics/uploads/linkimg/'.$pay_qrcode_img['img'];
				exit();
			}
		}
	}
	public function wxpay_locat(){
		session_start();

		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="支付中";

		if (!$member) {
			header("Location:/");
			exit();
		}

		$money = $this->segment(4);

		$array = array('10','20','30','50','100','200','300','500','1000','2000','5000');

		if(!in_array($money,$array)){
		    header("Location:/index.php/mobile/home/userrecharge");
	        exit();
		} 


		//var_dump($money);
		$timed = time();

		$uid = $member['uid'];
		$username = $member['username'];
		$mobile = $member['mobile'];

		if (empty($_SESSION['wxtoken'])) {
			header("Location:/");
		}

		$img = $_SESSION['img'];

		$pay = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat` where  `uid` = $uid order by id desc limit 1");

		if ($money == 10) {
			$wid = 1;
		}elseif ($money == 50) {
			$wid = 2;
		}elseif ($money == 100) {
			$wid = 3;
		}elseif ($money == 500) {
			$wid = 4;
		}elseif ($money == 1000) {
			$wid = 5;
		}elseif ($money == 2000) {
			$wid = 6;
		}elseif ($money == 200) {
			$wid = 8;
		}elseif ($money == 300) {
			$wid = 9;
		}elseif ($money == 5000) {
			$wid = 10;
		}elseif ($money == 20) {
			$wid = 19;
		}elseif ($money == 30) {
			$wid = 20;
		}else{
			$wid = 1;
		}

		$pay_qrcode = $this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where  `id` = ".$wid);
		$pay_qrcode_img = $pay_qrcode['img'];
		// var_dump($pay);
		// exit();
		if(empty($pay)){
			//$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = $img, `username` = $username,`mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = '$img', `username` = '$username', `mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
			include templates("mobile/user","wxpay_locat");
			exit();
		}else{
			$miao = $timed - $pay['create_time']; 
			if ($miao < 3) {
				_messagemobile("操作过快，请稍后再试",WEB_PATH."/mobile/home/userrecharge4");
			}else{
				$this->db->Query("INSERT INTO `@#_wxpay_locat` SET `uid` = $uid,`img` = '$img', `username` = '$username', `mobile` = $mobile,`money` = $money,`create_time` = $timed ,`update_time` = 0,`status` = 0,`aduser` = 0");
				include templates("mobile/user","wxpay_locat");
				exit();
			}
		}
	}


	public function userrecharge1(){

	    $webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1' AND pay_mobile = 1 order by `pay_id` desc");

		

		include templates("mobile/user","recharge1");

	}
	public function userqiandao(){

		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$uid = $member['uid'];

		$qiandao = $this->db->GetOne("SELECT * FROM `@#_qiandao` where  `uid` = $uid");

		include templates("mobile/user","userqiandao");

	}



	public function qiandao(){

		$member=$this->userinfo;

		$uid = $member['uid'];

		$t = time();

		$start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));

		$end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));

		//查询上次签到时间信息

		$qiandao = $this->db->GetOne("SELECT * FROM `@#_qiandao` where  `uid` = $uid");

		if(empty($qiandao)){

			$this->db->Query("INSERT INTO `@#_qiandao` SET `time` = $t, `uid` = $uid,`sum` = 1, `lianxu` = 1");

			//签到送100福分，同时送1元钱

			$this->db->Query("UPDATE `@#_member` SET `score` = `score`+100, `money` =`money`+0 WHERE `uid` = $uid");

			_messagemobile("签到成功，初次签到，系统会赠送您100福分！同时积分还可以兑换现金哦",WEB_PATH."/mobile/home/userqiandao");

		}

		if($qiandao['time']>0){

			if($qiandao['time']>$start && $qiandao['time']<$end){

				_messagemobile("今天已经签到过了",WEB_PATH."/mobile/home/userqiandao");

			}else{

				$this->db->Query("UPDATE `@#_qiandao` SET `time` = $t, `uid` =$uid, `sum` =`sum`+1  where uid=$uid");

				$this->db->Query("UPDATE `@#_member` SET `score` = `score`+100 WHERE `uid` = $uid");

				//判断是否连续

				if($t - $qiandao['time']>2 && $t - $qiandao['time']<172798 &&  $qiandao['time']>($start-86400)){

					$this->db->Query("UPDATE `@#_qiandao` SET `lianxu`  =`lianxu` +1 where `uid` = $uid");

				}else{

					$this->db->Query("UPDATE `@#_qiandao` SET `lianxu` = 1 where `uid`= $uid");

				}
				_messagemobile("签到成功，坚持签到有积分赠送的哦！同时积分还可以兑换现金哦",WEB_PATH."/mobile/home/userqiandao");
			}
		}else{
			_messagemobile("签到错误",WEB_PATH."/mobile/home/userqiandao");
		}
	}

	public function useraddress(){
		$webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$uid = $member['uid'];
		$t = time();
		if($_POST['submit']){
			extract($_POST);
			if(empty($sheng) || empty($shi) || empty($xian)){
				_messagemobile("地市信息不能为空",WEB_PATH."/mobile/home/address");
			}
			$jiedao1 = preg_replace( "@<script(.*?)</script>@is", "", $jiedao );
			$jiedao = $jiedao1;
			if(empty($jiedao)){
				_messagemobile("街道地址包含特殊字符",WEB_PATH."/mobile/home/address");
			}
			if(empty($shouhuoren) || empty($mobile)){
				_messagemobile("收货人 电话 不能为空",WEB_PATH."/mobile/home/address");
			}

			$q1 = $this->db->Query("INSERT INTO `@#_member_dizhi` SET `time` = $t, `uid` = $uid, `sheng` = '$sheng', `shi` = '$shi', `xian` = '$xian', `jiedao` = '$jiedao', `shouhuoren`= '$shouhuoren', `mobile`= '$mobile' ");
			if($q1){
				_messagemobile("地址添加成功",WEB_PATH."/mobile/home/address");
			}else{
				_messagemobile("地址添加失败",WEB_PATH."/mobile/home/address");
			}
		}else{
			_messagemobile("添加失败",WEB_PATH."/mobile/home/address");
		}
	}



	public function address(){
		$webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$uid = $member['uid'];
		$dizhi = $this->db->GetList("SELECT * FROM `@#_member_dizhi` where `is_delete`= '0' and `uid` = $uid");
		include templates("mobile/user","address");

	}
	public function updateddress(){
		$id=intval($this->segment(4));

		$t = time();

		if($id){

			extract($_POST);

			if(empty($sheng) || empty($shi) || empty($xian)){

				_messagemobile("地市信息不能为空",WEB_PATH."/mobile/home/address");

			}

			$jiedao1 = preg_replace( "@<script(.*?)</script>@is", "", $jiedao );
			$jiedao = $jiedao1;
			if(empty($jiedao)){
				_messagemobile("街道地址包含特殊字符",WEB_PATH."/mobile/home/address");
			}

			if( empty($shouhuoren) || empty($mobile)){

				_messagemobile("收货人 电话 不能为空",WEB_PATH."/mobile/home/address");
			}

			$q1 = $this->db->Query("UPDATE `@#_member_dizhi` SET `time` = $t, `sheng` = '$sheng', `shi` = '$shi', `xian` = '$xian', `jiedao` = '$jiedao', `shouhuoren`= '$shouhuoren', `mobile`= '$mobile' WHERE `id`= $id");

			if($q1){

				_messagemobile("地址修改成功",WEB_PATH."/mobile/home/address");

			}else{

				_messagemobile("地址修改失败",WEB_PATH."/mobile/home/address");

			}

			



		}else{

			_messagemobile("修改失败",WEB_PATH."/mobile/home/address");

		}

	}



	public function deladdress(){

		$id=intval($this->segment(4));

		if($id){

			//$q1 = $this->db->Query("DELETE FROM `@#_member_dizhi`  WHERE `id`= $id");
			$q1 = $this->db->Query("UPDATE `@#_member_dizhi` SET `is_delete` = '1' WHERE `id`= $id");
			
			if($q1){

				_messagemobile("删除成功",WEB_PATH."/mobile/home/address");

			}else{

				_messagemobile("删除失败",WEB_PATH."/mobile/home/address");

			}

		}else{

			_messagemobile("删除失败",WEB_PATH."/mobile/home/address");

		}

	}



	//设为默认

	public function setaddress(){

		$id=intval($this->segment(4));

		if($id){

			$q1 = $this->db->Query("UPDATE `@#_member_dizhi` SET `default` = 'Y' WHERE `id`= $id");

			$q2 = $this->db->Query("UPDATE `@#_member_dizhi` SET `default` = 'N' WHERE `id` != $id");

			if($q1 && $q2){

				_messagemobile("设置成功",WEB_PATH."/mobile/home/address");

			}else{

				_messagemobile("设置失败",WEB_PATH."/mobile/home/address");

			}

		}else{
			_messagemobile("设置失败",WEB_PATH."/mobile/home/address");
		}

	}
	public function zhuanzhang(){
		$webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$uid = $member['uid'];

		if ($member['zhuan_status'] == 0) {
			_messagemobile("没有权限",WEB_PATH."/mobile/home/zhuanzhang");
			exit();
		}
		$t = time();
		//查询用户余额
		$info= $this->db->GetOne("SELECT `money` FROM `@#_member` where  `uid` = $uid");
		if($_POST['submit1']){
			// if($_POST['txtBankName'] != $_POST['txtBankName1']){
			// 	_messagemobile("两次的用户信息不一致，请重新输入！",WEB_PATH."/mobile/home/zhuanzhang");
			// }
			if($info['money']< $_POST['money']){
				_messagemobile("账户余额超过转账金额了！",WEB_PATH."/mobile/home/zhuanzhang");
			}
			if(empty($_POST['money']) || $_POST['money']<1){
				_messagemobile("请输入转账金额，且不能小于1元",WEB_PATH."/mobile/home/zhuanzhang");
			}
			// 查询数据库中用户信息
			$to_user = $_POST['txtBankName'];
			$to_info= $this->db->GetOne("SELECT * FROM `@#_member` where  `mobile` = '{$to_user}' OR `email` = '{$to_user}'");
			$cash = $_POST['money'];
			if(empty($to_info)){
				_messagemobile("用户不存在！请核对后在操作",WEB_PATH."/mobile/home/zhuanzhang");
			}
			$this->db->Autocommit_start();
				$up_q1= $this->db->Query("UPDATE `@#_member` SET `money` = `money`- {$_POST['money']}  where  `uid` = $uid");
				$up_q2= $this->db->Query("UPDATE `@#_member` SET `money` = `money`+{$_POST['money']}  where  `uid` = {$to_info['uid']}");
				$up_q3= $this->db->Query("INSERT INTO `@#_member_account`  SET `uid`= $uid, `type` = -1, `pay`= '账户', `content`= '给用户{$to_info['mobile']}转账{$cash}元', `money` = $cash ,`time` = $t");
				$up_q4= $this->db->Query("INSERT INTO `@#_member_account`  SET `uid`= {$to_info['uid']}, `type` = -1, `pay`= '账户', `content`= '获得用户{$member['mobile']}转账{$cash}元', `money` = $cash ,`time` = $t");
			if($up_q1 && $up_q2 && $up_q3 && $up_q4){
				$this->db->Autocommit_commit();
				_messagemobile("转账成功",WEB_PATH."/mobile/home/zhuanzhang");
			}else{
				$this->db->Autocommit_rollback();
				$this->error = true;
				_messagemobile("转账失败",WEB_PATH."/mobile/home/zhuanzhang");
			}	
		}
		include templates("mobile/user","zhuanzhang");
	}
	/**
	 * 转盘抽奖
	 */
	public function choujiang(){
		$webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$uid = $member['uid'];
		$name = $member['username'];
		include templates("mobile/user","choujiang");
	}
	public function submit(){
		$webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$uid = $member['uid'];
		$row =  $this->db->GetOne("SELECT * FROM `@#_member`  WHERE  `uid` = $uid");
		$score = $row['score'];
		if(intval($score/200)<1){
			$res = array(
				'ok' => true,
				'round'=>0,
				'left' => 0,
				'desc' =>'您的抽奖次数已经使用完！',
			);
			echo json_encode($res);die;	
		}else{
			//扣除积分
			$q1= $this->db->Query("UPDATE `@#_member` SET `score` = `score`- 200  where  `uid` = $uid");
			$lefts = $score - 200;
			if($q1){
				$left = intval($score/200)-1;
				$res = array(
					'ok' => true,
					'round'=>0,
					'left' => $left,
					'desc' =>'真遗憾，您没有中奖哦！剩余福分'.$lefts,
				);
			echo json_encode($res);die;	
			}else{
				$left = intval($score/200);
				$res = array(
					'ok' => true,
					'round'=>0,
					'left' => $left,
					'desc' =>'抽奖出错！请联系管理员',
			);
			echo json_encode($res);die;
			}
		}
	}
	/**
	 * 摇一摇红包
	 */
	public function yaohongbao(){
		$webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$uid = $member['uid'];
		$name = $member['username'];
		include templates("mobile/user","yaohongbao");
	}
	//晒单
	public function singlelist(){
		 $webname=$this->_cfg['web_name'];
		include templates("mobile/user","singlelist");
	}	
	//添加晒单
	public function postsinglebk(){
		$member=$this->userinfo;
		$uid=_getcookie('uid');
		$ushell=_getcookie('ushell');
		$title="添加晒单";		
		if(isset($_POST['submit'])){
			if($_POST['title']==null) _messagemobile("标题不能为空");	
			if($_POST['content']==null) _messagemobile("内容不能为空");	
			if(empty($_POST['file_up'])){
				_messagemobile("图片不能为空");
			}
			$pic=$_POST['file_up'];
			$pics = explode(';',$pic);
			$src=trim($pics[0]);
			$size=getimagesize(G_UPLOAD_PATH."/".$src);
			$width=220;
			$height=$size[1]*($width/$size[0]);
			$thumbs=tubimg($src,$width,$height);				
			$uid=$this->userinfo;
			$sd_userid=$uid['uid'];
			$sd_shopid=$_POST['shopid'];
			$sd_title=$_POST['title'];
			$sd_thumbs="shaidan/".$thumbs;
			$sd_content=$_POST['content'];
			$sd_photolist=$pic;
			$sd_time=time();			
			$sd_ip = _get_ip_dizhi();						
			$qishu= $this->db->GetOne("select `qishu`, `id` from `@#_shoplist` where `id`='$sd_shopid'");
			$qs = $qishu['qishu'];
			$ids = $qishu['id'];
			$this->db->Query("INSERT INTO `@#_shaidan`(`sd_userid`,`sd_shopid`,`sd_qishu`,`sd_ip`,`sd_title`,`sd_thumbs`,`sd_content`,`sd_photolist`,`sd_time`)VALUES ('$sd_userid','$ids','$qs','$sd_ip','$sd_title','$sd_thumbs','$sd_content','$sd_photolist','$sd_time')");
			_messagemobile("晒单分享成功",WEB_PATH."/mobile/home/singlelist");
		}
		$recordid=intval($this->segment(4));
		if($recordid>0){
			$shaidan=$this->db->GetOne("select * from `@#_member_go_record` where `id`='$recordid'");	
			$shopid=$shaidan['id'];
			include templates("mobile/user","postsingle");
		}else{
			_messagemobile("页面错误");
		}
	}

	public function postsingle(){
		$member=$this->userinfo;
		$uid=$member['uid'];
		$title="添加晒单";
		$recordid=intval($this->segment(4));
		$shaidan=$this->db->GetOne("select * from `@#_member_go_record` where `shopid`='$recordid' and `uid` = '$member[uid]'");
		if(!$shaidan){
			_messagemobile("该商品您不可晒单!");
		}
		$ginfo=$this->db->GetOne("select * from `@#_shoplist` where `id`='$shaidan[shopid]' LIMIT 1");
		if(!$ginfo){
			_messagemobile("该商品已不存在!");
		}
		$shaidanyn=$this->db->GetOne("select sd_id from `@#_shaidan` where `sd_shopid`='{$ginfo['id']}' and `sd_userid` = '$member[uid]'");
		if($shaidanyn){
			_messagemobile("不可重复晒单!");
		}
		if($_POST){

			if($_POST['title']==null)_messagemobile("标题不能为空");
			if($_POST['content']==null)_messagemobile("内容不能为空");
			if(!isset($_POST['fileurl_tmp'])){
				_messagemobile("图片不能为空");
			}
			System::load_sys_class('upload','sys','no');
			$img=explode(';', $_POST['fileurl_tmp']);
			$num=count($img);
			$pic="";
			for($i=0;$i<$num;$i++){
				$img[$i] = str_replace('http://', '', $img[$i]);
				$img[$i] = str_replace($_SERVER['HTTP_HOST'], '', $img[$i]);
				$img[$i] = str_replace('/statics/uploads/', '', $img[$i]);
				$pic.=trim($img[$i]).";";
			}

			$src=trim($img[0]);
			$size=getimagesize(G_UPLOAD_PATH."/".$src);
			$width=220;
			$height=$size[1]*($width/$size[0]);
			$thumbs=tubimg($src,$width,$height);
			$sd_userid=$uid;
			$sd_shopid=intval($ginfo['id']);
			$sd_title=safe_replace($_POST['title']);
			$sd_thumbs=$src;
			$sd_content=safe_replace($_POST['content']);
			$sd_photolist=$pic;
			$sd_time=time();
			$this->db->Query("INSERT INTO `@#_shaidan`(`sd_userid`,`sd_shopid`,`sd_title`,`sd_thumbs`,`sd_content`,`sd_photolist`,`sd_time`)VALUES
			('$sd_userid','$sd_shopid','$sd_title','$sd_thumbs','$sd_content','$sd_photolist','$sd_time')");
			header("Location:".WEB_PATH."/mobile/home/singlelist");
		}

		if($recordid>0){
			$shaidan=$this->db->GetOne("select * from `@#_member_go_record` where `id`='$recordid'");
			$shopid=$shaidan['shopid'];
			include templates("mobile/user","postsingle");
		}else{
			_messagemobile("页面错误");
		}
	}
	// 晒单上传图片方法
	public function mupload(){
		$uploadDir =$_SERVER['DOCUMENT_ROOT'].'/statics/uploads/shaidan/'.date('Ymd',time()).'/';
		if(!is_dir($uploadDir)){
		 	mkdir($uploadDir,0777);				
		}
		$rand=rand(10,99).substr(microtime(),2,6).substr(time(),4,6);
		$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); 
		if (!empty($_FILES)) {
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$filetype = strtolower($fileParts['extension']);
			$tempFile   = $_FILES['Filedata']['tmp_name'];
			$targetFilename = $rand.'.'.$filetype;
			if (in_array($filetype, $fileTypes)) {
				move_uploaded_file($tempFile, $uploadDir.$targetFilename);
				echo 'shaidan/'.date('Ymd',time()).'/'.$targetFilename;
			} else {
				echo 'Invalid file type.';
			}
		}
	}
	//检查图片存在否
	public function check_exists(){
		$fileurl = $_SERVER['DOCUMENT_ROOT'].'/statics/uploads/shaidan/'.date('Ymd',time()).'/'.$_POST['filename'];
		if (file_exists($fileurl)){
			echo 1;
		}else{
			echo 0;
		}
	}
	public function file_upload(){
		ini_set('display_errors', 0);
		// error_reporting(E_ALL);
		include dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."UploadHandler.php";
		$upload_handler = new UploadHandler();
	}
	public function singoldimg(){
		if($_POST['action']=='del'){
			$sd_id=$_POST['sd_id'];
			$oldimg=$_POST['oldimg'];
			$shaidan=$this->db->GetOne("select * from `@#_shaidan` where `sd_id`='$sd_id'");
			$sd_photolist=str_replace($oldimg.";","",$shaidan['sd_photolist']);
			$this->db->Query("UPDATE `@#_shaidan` SET sd_photolist='".$sd_photolist."' where sd_id='".$sd_id."'");
		}
	}
	public function singphotoup(){
		$mysql_model=System::load_sys_class('model');
		if(!empty($_FILES)){
			$uid=isset($_POST['uid']) ? $_POST['uid'] : NULL;
			$ushell=isset($_POST['ushell']) ? $_POST['ushell'] : NULL;
			$login=$this->checkuser($uid,$ushell);
			if(!$login){_messagemobile("上传出错");}
			System::load_sys_class('upload','sys','no');
			upload::upload_config(array('png','jpg','jpeg','gif'),1000000,'shaidan');
			upload::go_upload($_FILES['Filedata']);
			if(!upload::$ok){
				echo _messagemobile(upload::$error,null,3);
			}else{
				$img=upload::$filedir."/".upload::$filename;
				$size=getimagesize(G_UPLOAD_PATH."/shaidan/".$img);
				$max=700;$w=$size[0];$h=$size[1];
				if($w>700){
					$w2=$max;
					$h2=$h*($max/$w);
					upload::thumbs($w2,$h2,1);
				}

				echo trim("shaidan/".$img);
			}
		}
	}
	public function singdel(){
		$action=isset($_GET['action']) ? $_GET['action'] : null;
		$filename=isset($_GET['filename']) ? $_GET['filename'] : null;
		if($action=='del' && !empty($filename)){
			$filename=G_UPLOAD_PATH.'shaidan/'.$filename;
			$size=getimagesize($filename);
			$filetype=explode('/',$size['mime']);
			if($filetype[0]!='image'){
				return false;
				exit;
			}
			unlink($filename);
			exit;
		}
	}
	//晒单删除
	public function shaidandel(){
		_messagemobile("不可以删除!");
		$member=$this->userinfo;
		//$id=isset($_GET['id']) ? $_GET['id'] : "";
		$id=$this->segment(4);
		$id=intval($id);
		$shaidan=$this->db->Getone("select * from `@#_shaidan` where `sd_userid`='$member[uid]' and `sd_id`='$id'");
		if($shaidan){
			$this->db->Query("DELETE FROM `@#_shaidan` WHERE `sd_userid`='$member[uid]' and `sd_id`='$id'");
			_messagemobile("删除成功",WEB_PATH."/mobile/home/singlelist");
		}else{
			_messagemobile("删除失败",WEB_PATH."/mobile/home/singlelist");
		}
	}

	//银行充值
	public function bank_recharge(){
		$member=$this->userinfo;
		$uid=$member['uid'];
		$data = $this->db->GetOne("SELECT * FROM `@#_member` WHERE `uid` = '$uid'");
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======

		$str = $this->db->GetOne("SELECT `name`,`card_number`,`bank_from` FROM `@#_bank_bind` WHERE `status` = '1'");

>>>>>>> 5.12
>>>>>>> four
>>>>>>> xxxx
		$bound_bank_name = $data['bound_bank_name'];
		if($this->segment(4)){
			$bound_bank_name = trim($this->segment(4));
			$arr = $this->db->Query("UPDATE `@#_member` SET `bound_bank_name` = '$bound_bank_name' WHERE `uid` = '$uid'");
			if($arr){
				_messagemobile("绑定成功",WEB_PATH."/mobile/home/bank_recharge");exit;
			}else{
				_messagemobile("绑定失败",WEB_PATH."/mobile/home/bank_recharge");exit;
			}
		}
		include templates("mobile/user","bank_recharge");
	}

	//更换银行卡绑定姓名
	public function edit_bank_name(){
		$member=$this->userinfo;
		$uid=$member['uid'];
		$data = $this->db->GetOne("SELECT * FROM `@#_member` WHERE `uid` = '$uid'");
		$bound_bank_name = $data['bound_bank_name'];
		if(empty($bound_bank_name)){
			header("Location: ".WEB_PATH."/mobile/home/bank_recharge");exit;
		}
		if($this->segment(4)){
			$bound_bank_name = trim($this->segment(4));
			$arr = $this->db->Query("UPDATE `@#_member` SET `bound_bank_name` = '$bound_bank_name' WHERE `uid` = '$uid'");
			if($arr){
				_messagemobile("绑定成功",WEB_PATH."/mobile/home/bank_recharge");exit;
			}else{
				_messagemobile("绑定失败",WEB_PATH."/mobile/home/bank_recharge");exit;
			}
		}
		include templates("mobile/user","edit_bank_name");
	}

	//添加银行充值
	public function add_bank_money(){
		$member=$this->userinfo;
		$uid=$member['uid'];
		$money = intval($_POST['money']);
		$time = time();
<<<<<<< HEAD
		$data = $this->db->Query("INSERT INTO `@#_bank_locat` (`uid`,`money`,`create_time`)VALUES('$uid','$money','$time')");
=======
<<<<<<< HEAD
		$data = $this->db->Query("INSERT INTO `@#_bank_locat` (`uid`,`money`,`create_time`)VALUES('$uid','$money','$time')");
=======
<<<<<<< HEAD
		$data = $this->db->Query("INSERT INTO `@#_bank_locat` (`uid`,`money`,`create_time`)VALUES('$uid','$money','$time')");
=======
		$str = $this->db->GetOne("SELECT `bound_bank_name` FROM `@#_member` WHERE `uid` = '$uid'");
		$bind_name = $str['bound_bank_name'];

		$data = $this->db->Query("INSERT INTO `@#_bank_locat` (`uid`,`money`,`bind_name`,`create_time`)VALUES('$uid','$money','$bind_name','$time')");
>>>>>>> 5.12
>>>>>>> four
>>>>>>> xxxx
		if($data){
			echo 1;
		}else{
			echo 2;
		}
	}
<<<<<<< HEAD
}
=======
<<<<<<< HEAD
}
=======
<<<<<<< HEAD
}
=======

	public function recharge_wxpay(){
		$webname=$this->_cfg['web_name'];

		$member=$this->userinfo;

		$title="账户充值";

		include templates("mobile/user","recharge_wxpay");
	}

	public function wxpay_ajax(){
		$uid = $_GET['uid'];
		$mn = intval($_GET['mn']/100);
		$time = time();
		if(empty($uid) || empty($mn)){
			_messagemobile("充值失败,请截图并及时联系客服",WEB_PATH."/mobile/home/recharge_wxpay",15);exit;
		}
		$this->db->Autocommit_start();
		$data = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + $mn WHERE `uid` = $uid");
		$str = $this->db->Query("INSERT INTO `@#_member_account` (`uid`,`type`,`pay`,`content`,`money`,`time`)VALUES($uid,'1','账户','通过微信公众号充值',$mn,$time)");
		if($data && $str){
			$this->db->Autocommit_commit();
			_messagemobile("充值成功",WEB_PATH."/mobile/home/recharge_wxpay");exit;
		}else{
			$this->db->Autocommit_rollback();
			_messagemobile("充值失败,请截图并及时联系客服",WEB_PATH."/mobile/home/recharge_wxpay",15);exit;
		}
	}
}

>>>>>>> 5.12
>>>>>>> four
>>>>>>> xxxx
