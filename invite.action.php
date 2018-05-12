<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
System::load_app_class('base','member','no');
System::load_app_fun('my','go');
System::load_app_fun('user','go');
System::load_sys_fun('send');
System::load_sys_fun('user');
class invite extends base {
	public function __construct(){ 
		parent::__construct();
		if(ROUTE_A!='userphotoup' and ROUTE_A!='singphotoup' && $_GET['is_debug'] != 'yes'){
			if(!$this->userinfo)_message("请登录",WEB_PATH."/mobile/user/login",3);
		}		
		$this->db = System::load_sys_class('model');
	}

    public function friends(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }
        if(empty($member['yungoudj'])){
            $member['yungoudj'] = "将军";
        }

        $mysql_model=System::load_sys_class('model');
        $uid=_getcookie('uid');
        $notinvolvednum=0;  //未参加参与的人数
        $involvednum=0;     //参加预购的人数
        $involvedtotal=0;   //邀请人数


        //查询邀请好友信息
        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");
        $involvedtotal=count($invifriends);


        //var_dump($invifriends);

        for($i=0;$i<count($invifriends);$i++){
            $sqluid=$invifriends[$i]['uid'];
            $sqname=get_user_name($invifriends[$i]);
            $invifriends[$i]['sqlname']=	 $sqname;

            //查询邀请好友的消费明细
            $accounts[$sqluid]=$mysql_model->GetList("select * from `@#_member_account` where `uid`='$sqluid'  ORDER BY `time` DESC");


            //判断哪个好友有消费
            if(empty($accounts[$sqluid])){
                $notinvolvednum +=1;
                $records[$sqluid]='未参与参与';
            }else{
                $involvednum +=1;
                $records[$sqluid]='已参与参与';
            }


        }

        $invifriends = $this->multi_array_sort($invifriends,'time',SORT_DESC);
        
        include templates("mobile/invite","friends");
    }

    




        // 邀请好友
        public function friends1(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }

        $mysql_model=System::load_sys_class('model');
        $member=$this->userinfo;
        $uid=_getcookie('uid');
        $notinvolvednum=0;  //未参加参与的人数
        $involvednum=0;     //参加预购的人数
        $involvedtotal=0;   //邀请人数


        //查询邀请好友信息
        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");
        $involvedtotal=count($invifriends);


        //var_dump($invifriends);

        for($i=0;$i<count($invifriends);$i++){
            $sqluid=$invifriends[$i]['uid'];
            $sqname=get_user_name($invifriends[$i]);
            $invifriends[$i]['sqlname']=	 $sqname;

            //查询邀请好友的消费明细
            $accounts[$sqluid]=$mysql_model->GetList("select * from `@#_member_account` where `uid`='$sqluid'  ORDER BY `time` DESC");


            //判断哪个好友有消费
            if(empty($accounts[$sqluid])){
                $notinvolvednum +=1;
                $records[$sqluid]='未参与参与';
            }else{
                $involvednum +=1;
                $records[$sqluid]='已参与参与';
            }


        }
        include templates("mobile/invite","friends1");
    }

	 public function address(){
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$title="收货地址";
		$member_dizhi=$mysql_model->Getlist("select * from `@#_member_dizhi` where uid='".$member['uid']."' limit 5");
		foreach($member_dizhi as $k=>$v){		
			$member_dizhi[$k] = _htmtocode($v);
		}
		$count=count($member_dizhi);
		include templates("mobile/invite","address");
            }
        

	public function usertransfer(){
		$member=$this->userinfo;
		$title="转帐";
	
		if(isset($_POST['submit1'])){
			
			$tmoney=$_POST[money];
			$tuser=$_POST[txtBankName];
			if($member[score]<1000)
				_messagemobile("帐户云积分不得小与1000",null,3);
		if($_POST[money]<1000)
				_messagemobile("转帐云积分不得小于1000",null,3);
			if(empty($tmoney)||empty($tuser))
				_messagemobile("转入用户和金额不得为空",null,3);
			if($tmoney>$member[score])
				_messagemobile("转入云积分不得大于帐户云积分",null,3);
			$user= $this->db->GetOne("SELECT * FROM `@#_member` where `email` = '$tuser' limit 1");	
			if(empty($user))
				$user= $this->db->GetOne("SELECT * FROM `@#_member` where `mobile` = '$tuser' limit 1");	
			if(empty($user))
					_messagemobile("转入用户不存在",null,3);
			$uid=$member[uid];
			$tuid=$user[uid];
		if($uid==$tuid)
					_messagemobile("不能给自己转帐",null,3);
			$time=time();
			$cmoney=$member[score]-$tmoney;
			$ctmoney=$user[score]+$tmoney;
			$name=get_user_name($uid,'username','all');
			$tname=get_user_name($tuid,'username','all');
                                    $this->db->Query("update `@#_member` SET `score`='$cmoney' WHERE `uid`='$uid'");
                                    $this->db->Query("update `@#_member` SET `score`='$ctmoney' WHERE `uid`='$tuid'");
                                    $this->db->Query("insert into `@#_member_op_record` (`uid`,`username`,`deltamoney`,`premoney`,`money`,`time`) values ('$uid','$name','-$tmoney','$member[money]','$cmoney','$time')");
                                    $this->db->Query("insert into `@#_member_op_record` (`uid`,`username`,`deltamoney`,`premoney`,`money`,`time`) values ('$tuid','$tname','$tmoney','$user[money]','$ctmoney','$time')");
                                    $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '转出到<$tname>', '$tmoney', '$time')");
                                    $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$tuid', '1', '账户', '由<$name>转入', '$tmoney', '$time')");		
		                               _message("给".$tname."的".$tmoney."云积分冲值成功!",null,3);		
		              }
		              include templates("mobile/invite","usertransfer");
                  }

    //排序方法
    public function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){ 
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
        //sort , SORT_DESC  SORT_ASC
        array_multisort($key_array,$sort,$multi_array); 
        return $multi_array; 
    }  
    public function commissions_test(){
        $webname=$this->_cfg['web_name'];
        $member=$this->db->GetOne("select * from `@#_member` where `uid`='9261'");
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }

        $mysql_model=System::load_sys_class('model');
          $member=$this->db->GetOne("select * from `@#_member` where `uid`='9261'");
        $uid = $member['uid'];
        $recodetotal=0;   // 判断是否为空
        $shourutotal=0;
        $zhichutotal=0;

        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");


        //查询佣金表
        for($i=0;$i<count($invifriends);$i++){
            $sqluid=$invifriends[$i]['uid'];

            //查询邀请好友给我反馈的佣金
            $recodes[$sqluid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC");

            $user[$sqluid]['username']= get_user_name($invifriends[$i]);

        }
        //自己提现或充值
        $recodes[$uid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$uid' and `type`!=1 ORDER BY `time` DESC");
        $user[$uid]['username']= get_user_name($member);


        $recodearr='';
        $i=0;
        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                $username[$key]=$user[$key]['username'];
                foreach($val as $key2=>$val2){
                    $recodearr[$i]=$val2;
                    $i++;
                }
            }
        }

        $recodearr = $this->multi_array_sort($recodearr,'time',SORT_DESC);

        $recodetotal=count($recodes);


        //查询   累计收入：元    累计(提现/充值)：元    佣金余额：元

        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                if($uid==$key){
                    // foreach($val as $key2=>$val2){

                    //     $zhichutotal+=$val2['money'];     //总佣金支出

                    // }
                }else{
                    foreach($val as $key3=>$val3){

                        $shourutotal+=$val3['money'];    //总佣金收入
                    }

                }
            }

        }


        //$total=$shourutotal-$zhichutotal;  //计算佣金余额
        
        //自己提现或充值
        $recodes_t=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`=".$member['uid']." and `type`!=1 ORDER BY `time` DESC");
        if(!empty($recodes_t)){
            foreach($recodes_t as $key=>$val){
                 $zhichutotal+=$val['money'];    //总佣金支出

            }
        }
        $total=$shourutotal-$zhichutotal;  //计算佣金余额
        $shourutotal = sprintf("%.2f",$shourutotal);
        $total= number_format($total, 2);
        include templates("mobile/invite","commissions");
    }             
    public function commissions_backup(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }

        $mysql_model=System::load_sys_class('model');
        $member=$this->userinfo;
        $uid = $member['uid'];
        $recodetotal=0;   // 判断是否为空
        $shourutotal=0;
        $zhichutotal=0;

        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");


        //查询佣金表
        for($i=0;$i<count($invifriends);$i++){
            $sqluid=$invifriends[$i]['uid'];

            //查询邀请好友给我反馈的佣金
            $recodes[$sqluid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC");

            $user[$sqluid]['username']=	get_user_name($invifriends[$i]);

        }
        //自己提现或充值
        $recodes[$uid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$uid' and `type`!=1 ORDER BY `time` DESC");
        $user[$uid]['username']= get_user_name($member);


        $recodearr='';
        $i=0;
        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                $username[$key]=$user[$key]['username'];
                foreach($val as $key2=>$val2){
                    $recodearr[$i]=$val2;
                    $i++;
                }
            }
        }

        $recodearr = $this->multi_array_sort($recodearr,'time',SORT_DESC);

        $recodetotal=count($recodes);


        //查询   累计收入：元    累计(提现/充值)：元    佣金余额：元

        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                if($uid==$key){
                    // foreach($val as $key2=>$val2){

                    //     $zhichutotal+=$val2['money'];	 //总佣金支出

                    // }
                }else{
                    foreach($val as $key3=>$val3){

                        $shourutotal+=$val3['money'];	 //总佣金收入
                    }

                }
            }

        }


        //$total=$shourutotal-$zhichutotal;  //计算佣金余额
        
        //自己提现或充值
        $recodes_t=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`=".$member['uid']." and `type`!=1 ORDER BY `time` DESC");
        if(!empty($recodes_t)){
            foreach($recodes_t as $key=>$val){
                 $zhichutotal+=$val['money'];    //总佣金支出

            }
        }
        $total=$shourutotal-$zhichutotal;  //计算佣金余额
      	$shourutotal = sprintf("%.2f",$shourutotal);
        $total= number_format($total, 2);
        include templates("mobile/invite","commissions");
    }
    public function commissions3(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }

        $mysql_model=System::load_sys_class('model');
        $member=$this->userinfo;
        $uid = $member['uid'];
        $recodetotal=0;   // 判断是否为空
        $shourutotal=0;
        $zhichutotal=0;

        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");


        //查询佣金表
        for($i=0;$i<count($invifriends);$i++){
            $sqluid=$invifriends[$i]['uid'];

            //查询邀请好友给我反馈的佣金
            $recodes[$sqluid]=$mysql_model->GetList("select uid,content,time from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC ");

            $user[$sqluid]['username']= get_user_name($invifriends[$i]);

        }
        exit('1');
        //自己提现或充值
        $recodes[$uid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$uid' and `type`!=1 ORDER BY `time` DESC");
        $user[$uid]['username']= get_user_name($member);


        $recodearr='';
        $i=0;
        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                $username[$key]=$user[$key]['username'];
                foreach($val as $key2=>$val2){
                    $recodearr[$i]=$val2;
                    $i++;
                }
            }
        }

        $recodearr = array_slice($recodearr,0,20);
        $recodearr = $this->multi_array_sort($recodearr,'time',SORT_DESC);

        $recodetotal=count($recodes);


        //查询   累计收入：元    累计(提现/充值)：元    佣金余额：元

        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                if($uid==$key){
                    // foreach($val as $key2=>$val2){

                    //     $zhichutotal+=$val2['money'];     //总佣金支出

                    // }
                }else{
                    foreach($val as $key3=>$val3){

                        $shourutotal+=$val3['money'];    //总佣金收入
                    }

                }
            }

        }


        //$total=$shourutotal-$zhichutotal;  //计算佣金余额
        
        //自己提现或充值
        $recodes_t=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`=".$member['uid']." and `type`!=1 ORDER BY `time` DESC");
        if(!empty($recodes_t)){
            foreach($recodes_t as $key=>$val){
                 $zhichutotal+=$val['money'];    //总佣金支出

            }
        }

        $total=$shourutotal-$zhichutotal;  //计算佣金余额
        $shourutotal = sprintf("%.2f",$shourutotal);
        $total= number_format($total, 2);
        include templates("mobile/invite","commissions1");
    }
    public function commissions(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }
        if(empty($member['yungoudj'])){
            $member['yungoudj'] = "将军";
        }
        $mysql_model=System::load_sys_class('model');

        $uid = $member['uid'];
        $recodetotal=0;   // 判断是否为空
        $shourutotal=0;
        $zhichutotal=0;

        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");


        //查询佣金表
        // for($i=0;$i<count($invifriends);$i++){
        //     $sqluid=$invifriends[$i]['uid'];

        //     //查询邀请好友给我反馈的佣金
        //     //$recodes[$sqluid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC");
        //     $recodes[$sqluid]=$mysql_model->GetList("select uid,content,time,money,ygmoney from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC ");
        //     $user[$sqluid]['username']= get_user_name($invifriends[$i]);

        // }
        //自己提现或充值
        // $recodes2=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$uid' and `type`!=1 ORDER BY `time` DESC");
        // $user[$uid]['username']= get_user_name($member);

        foreach ($invifriends as $key => $val) {
            $crr[] = $val['uid'];
        }
        $crr = array_values(array_unique($crr));
        $drr = implode(',', $crr);
        $recodes=$mysql_model->GetList("select uid,content,time,money,ygmoney from `@#_member_recodes` where (`uid` in($drr) and `type`=1) or (`uid`='$uid' and `type`!=1) ORDER BY `time` DESC limit 500");

        $recodearr='';
        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                $recodearr[]=$val;
                $username[$val['uid']]= get_user_name($val['uid']);
            }
        }
        
        // if(!empty($recodes)){
        //     foreach($recodes as $key=>$val){
        //         $username[$key]=$user[$key]['username'];
        //         foreach($val as $key2=>$val2){
        //             $recodearr[$i]=$val2;
        //             $i++;
        //         }
        //     }
        // }
        //$recodearr = array_slice($recodearr,0,2000);

        //$recodearr = $this->multi_array_sort($recodearr,'time',SORT_DESC);
        $recodes2=$mysql_model->GetList("select money from `@#_member_recodes` where `uid` in($drr) and `type`=1 ORDER BY `time` DESC");
        $recodetotal=count($recodes2);


        // //查询   累计收入：元    累计(提现/充值)：元    佣金余额：元

        if(!empty($recodes2)){
            foreach($recodes2 as $key=>$val){

                $shourutotal+=$val['money'];    //总佣金收入

            }

        }


        //$total=$shourutotal-$zhichutotal;  //计算佣金余额
        
        //自己提现或充值
        $recodes_t=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`=".$member['uid']." and `type`!=1 ORDER BY `time` DESC");
        if(!empty($recodes_t)){
            foreach($recodes_t as $key=>$val){
                 $zhichutotal+=$val['money'];    //总佣金支出

            }
        }
        $total=$shourutotal-$zhichutotal;  //计算佣金余额
        $shourutotal = sprintf("%.2f",$shourutotal);
        $total= number_format($total, 2);
        include templates("mobile/invite","commissions1");
    }
  
   public function commissions_rs(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }
        if(empty($member['yungoudj'])){
            $member['yungoudj'] = "将军";
        }
        $mysql_model=System::load_sys_class('model');

        $uid = $member['uid'];
        $recodetotal=0;   // 判断是否为空
        $shourutotal=0;
        $zhichutotal=0;

        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");
		 

        //查询佣金表
        for($i=0;$i<count($invifriends);$i++){
            $sqluid=$invifriends[$i]['uid'];

            //查询邀请好友给我反馈的佣金
            //$recodes[$sqluid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC");
            $recodes[$sqluid]=$mysql_model->GetList("select uid,content,time,money,ygmoney from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC ");
            $user[$sqluid]['username']= get_user_name($invifriends[$i]);

            echo count($recodes[$sqluid])." \n";
          	ob_flush();
          	flush();
          
        }
        //自己提现或充值
        $recodes[$uid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$uid' and `type`!=1 ORDER BY `time` DESC");
        $user[$uid]['username']= get_user_name($member);

        $recodearr='';
        $i=0;
        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                $username[$key]=$user[$key]['username'];
                foreach($val as $key2=>$val2){
                    $recodearr[$i]=$val2;
                    $i++;
                }
            }
        }
        $recodearr = array_slice($recodearr,0,20);
        $recodearr = $this->multi_array_sort($recodearr,'time',SORT_DESC);

        $recodetotal=count($recodes);


        //查询   累计收入：元    累计(提现/充值)：元    佣金余额：元

        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                if($uid==$key){
                    // foreach($val as $key2=>$val2){

                    //     $zhichutotal+=$val2['money'];     //总佣金支出

                    // }
                }else{
                    foreach($val as $key3=>$val3){

                        $shourutotal+=$val3['money'];    //总佣金收入
                    }

                }
            }

        }


        //$total=$shourutotal-$zhichutotal;  //计算佣金余额
        
        //自己提现或充值
        $recodes_t=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`=".$member['uid']." and `type`!=1 ORDER BY `time` DESC");
        if(!empty($recodes_t)){
            foreach($recodes_t as $key=>$val){
                 $zhichutotal+=$val['money'];    //总佣金支出

            }
        }
        $total=$shourutotal-$zhichutotal;  //计算佣金余额
        $shourutotal = sprintf("%.2f",$shourutotal);
        $total= number_format($total, 2);
        include templates("mobile/invite","commissions1");
    }
  
    public function commissions2(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }

        $mysql_model=System::load_sys_class('model');
        $member=$this->userinfo;
        $uid = $member['uid'];
        $recodetotal=0;   // 判断是否为空
        $shourutotal=0;
        $zhichutotal=0;

        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");


        //查询佣金表
        // for($i=0;$i<count($invifriends);$i++){
        //     $sqluid=$invifriends[$i]['uid'];

        //     //查询邀请好友给我反馈的佣金
        //     //$recodes[$sqluid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC");
        //     $recodes[$sqluid]=$mysql_model->GetList("select uid,content,time,money,ygmoney from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC ");
        //     $user[$sqluid]['username']= get_user_name($invifriends[$i]);

        // }
        // //自己提现或充值
        // $recodes[$uid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$uid' and `type`!=1 ORDER BY `time` DESC");
        // $user[$uid]['username']= get_user_name($member);


        // $recodearr='';
        // $i=0;
        // if(!empty($recodes)){
        //     foreach($recodes as $key=>$val){
        //         $username[$key]=$user[$key]['username'];
        //         foreach($val as $key2=>$val2){
        //             $recodearr[$i]=$val2;
        //             $i++;
        //         }
        //     }
        // }

        foreach ($invifriends as $key => $val) {
            $crr[] = $val['uid'];
        }
        $crr = array_values(array_unique($crr));
        $drr = implode(',', $crr);
        $recodes=$mysql_model->GetList("select uid,content,time,money,ygmoney from `@#_member_recodes` where (`uid` in($drr) and `type`=1) or (`uid`='$uid' and `type`!=1) ORDER BY `time` DESC limit 5000");

        $recodearr='';
        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                $recodearr[]=$val;
                $username[$val['uid']]= get_user_name($val['uid']);
            }
        }

        //$recodearr = $this->multi_array_sort($recodearr,'time',SORT_DESC);

        $recodetotal=count($recodes);


        //查询   累计收入：元    累计(提现/充值)：元    佣金余额：元

        // if(!empty($recodes)){
        //     foreach($recodes as $key=>$val){
        //         if($uid==$key){
        //             // foreach($val as $key2=>$val2){

        //             //     $zhichutotal+=$val2['money'];     //总佣金支出

        //             // }
        //         }else{
        //             foreach($val as $key3=>$val3){

        //                 $shourutotal+=$val3['money'];    //总佣金收入
        //             }

        //         }
        //     }

        // }


        //$total=$shourutotal-$zhichutotal;  //计算佣金余额
        
        //自己提现或充值
        // $recodes_t=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`=".$member['uid']." and `type`!=1 ORDER BY `time` DESC");
        // if(!empty($recodes_t)){
        //     foreach($recodes_t as $key=>$val){
        //          $zhichutotal+=$val['money'];    //总佣金支出

        //     }
        // }
        // $total=$shourutotal-$zhichutotal;  //计算佣金余额
        // $shourutotal = sprintf("%.2f",$shourutotal);
        // $total= number_format($total, 2);
        include templates("mobile/invite","commissions2");
    }
    function test(){
        //13553778989
        $mysql_model=System::load_sys_class('model');
        $recodes=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='1346' and `type`!=1 ORDER BY `time` DESC");
        foreach ($recodes as $k => $v) {
            $recodes[$k]['content'] = $v['content'];
            $recodes[$k]['time'] = date('Y-m-d H:i',$v['time']);
            $recodes[$k]['money'] = $v['money'];
        }
        var_dump($recodes);
    }
    
    function cashout(){

        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }
        if(empty($member['yungoudj'])){
            $member['yungoudj'] = "将军";
        }
        
        $mysql_model=System::load_sys_class('model');
        $uid = $member['uid'];
        $total=0;
        $shourutotal=0;
        $zhichutotal=0;
        $cashoutdjtotal=0;
        $cashouthdtotal=0;
        //查询邀请好友id
        $invifriends=$mysql_model->GetList("select * from `@#_member` where `yaoqing`='$member[uid]' ORDER BY `time` DESC");

        //查询佣金收入
        // for($i=0;$i<count($invifriends);$i++){
        //     $sqluid=$invifriends[$i]['uid'];
        //     //查询邀请好友给我反馈的佣金
        //     //$recodes[$sqluid]=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC");
        //     $recodes[$sqluid]=$mysql_model->GetList("select money from `@#_member_recodes` where `uid`='$sqluid' and `type`=1 ORDER BY `time` DESC");
        // }
        foreach ($invifriends as $key => $val) {
            $crr[] = $val['uid'];
        }
        $crr = array_values(array_unique($crr));
        $drr = implode(',', $crr);
        $recodes=$mysql_model->GetList("select money from `@#_member_recodes` where `uid` in($drr) and `type`=1 ORDER BY `time` DESC");

        if(!empty($recodes)){
            foreach($recodes as $key=>$val){
                
                    $shourutotal+=$val['money'];    //总佣金收入
            }
        }

        //查询佣金消费(提现,充值)
        $zhichu=$mysql_model->GetList("select * from `@#_member_recodes` where `uid`='$uid' and `type`!=1 ORDER BY `time` DESC");

        //查询被冻结金额
        $cashoutdj=$mysql_model->GetOne("select SUM(money) as summoney  from `@#_member_cashout` where `uid`='$uid' and `auditstatus`!='1' ORDER BY `time` DESC");

        // if(!empty($recodes)){
        //     foreach($recodes as $key=>$val){
        //         foreach($val as $key2=>$val2){
        //             $shourutotal+=$val2['money'];	 //总佣金收入
        //         }
        //     }
        // }
        if(!empty($zhichu)){
            foreach($zhichu as $key=>$val3){
                $zhichutotal+=$val3['money'];	//总支出的佣金
            }
        }


        $total=sprintf("%.2f",$shourutotal-$zhichutotal);  //计算佣金余额
 
        $cashoutdjtotal= sprintf("%.2f",$cashoutdj['summoney']);  //冻结佣金余额
        $cashouthdtotal= sprintf("%.2f",$total-$cashoutdj['summoney']);  //活动佣金余额

<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======

        //提取上次保存体现银行信息
        $bank_record = $this->db->GetOne("SELECT * FROM `@#_member_cashout` WHERE `uid` = '$uid' order by `time` desc limit 1");

>>>>>>> 5.12
>>>>>>> four
>>>>>>> xxxx
        if(isset($_POST['submit1'])){ //提现
            $money      = abs(intval($_POST['money']));
            $username   =htmlspecialchars($_POST['txtUserName']);
            $bankname   =htmlspecialchars($_POST['txtBankName']);
            $branch     =htmlspecialchars($_POST['txtSubBank']);
            $banknumber =htmlspecialchars($_POST['txtBankNo']);
            $linkphone  =htmlspecialchars($_POST['txtPhone']);
            $time       =time();
            $type       = -3;  //收取1/消费-1/充值-2/提现-3

            if($total<100){
                _messagemobile("佣金金额大于100元才能提现！");exit;
            }elseif($cashouthdtotal<$money){
                _messagemobile("输入额超出活动佣金金额！");exit;
            }elseif($total<$money ){
                _messagemobile("输入额超出总佣金金额！");exit;
            }else{

                //插入提现申请表  这里不用在佣金表中插入记录 等后台审核才插入
                $this->db->Query("INSERT INTO `@#_member_cashout`(`uid`,`money`,`username`,`bankname`,`branch`,`banknumber`,`linkphone`,`time`)VALUES
			('$uid','$money','$username','$bankname','$branch','$banknumber','$linkphone','$time')");
                _messagemobile("申请成功！请等待审核！",WEB_PATH.'/mobile/invite/cashout');
            }
        }

        if(isset($_POST['submit2'])){//充值
            //file_put_contents("/www/wwwroot/m.yyygcs.vip/sql_log/cashout.log",date("Y-m-d H:i:s", time())."-{$uid}-{$_SERVER['PHP_SELF']}:佣金余额{$total}:".json_encode($_REQUEST)."\n",FILE_APPEND);

            $money      = abs(intval($_POST['txtCZMoney']));
            $type       = 1;
            $pay        ="佣金";
            $time       =time();
            $content    ="使用佣金充值到参与账户";

            if($money <= 0 || $money > $total){
                _messagemobile("佣金金额输入不正确！");exit;
            }
            if($cashouthdtotal<$money){
                _messagemobile("输入额超出活动佣金金额！");exit;
            }

            //插入记录
            $account=$this->db->Query("INSERT INTO `@#_member_account`(`uid`,`type`,`pay`,`content`,`money`,`time`)VALUES
			('$uid','$type','$pay','$content','$money','$time')");

            //file_put_contents("/www/wwwroot/m.yyygcs.vip/sql_log/cashout-success.log",date("Y-m-d H:i:s", time())."-{$uid}-{$_SERVER['PHP_SELF']}:佣金余额{$total}:".json_encode($_REQUEST)."\n",FILE_APPEND);

            // 查询是否有该记录
            if($account){
                //修改剩余金额
                $leavemoney=$member['money']+$money;
                $mrecode=$this->db->Query("UPDATE `@#_member` SET `money`='$leavemoney' WHERE `uid`='$uid' ");
                //在佣金表中插入记录
                $recode=$this->db->Query("INSERT INTO `@#_member_recodes`(`uid`,`type`,`content`,`money`,`time`)VALUES
			('$uid','-2','$content','$money','$time')");

                _messagemobile("充值成功！",WEB_PATH.'/mobile/invite/cashout');
            }else{
                _messagemobile("充值失败！");
            }
        }

        include templates("mobile/invite","cashout");
    }
    function record(){
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的参与中心";
        $memberdj=$this->db->GetList("select * from `@#_member_group`");
        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }
        if(empty($member['yungoudj'])){
            $member['yungoudj'] = "将军";
        }
        $mysql_model=System::load_sys_class('model');
        $uid = $member['uid'];
        $recount=0;
        $fufen = System::load_app_config("user_fufen",'','member');
        //查询提现记录
        //$recordarr=$mysql_model->GetList("select * from `@#_member_recodes` a left join `@#_member_cashout` b on a.cashoutid=b.id where a.`uid`='$uid' and a.`type`='-3' ORDER BY a.`time` DESC");		$recordarr=

        $recordarr=$mysql_model->GetList("select * from  `@#_member_cashout`  where `uid`='$uid' ORDER BY `time` DESC limit 0,30");

        if(!empty($recordarr)){
            $recount=1;
        }
        include templates("mobile/invite","record");
    }

	//参与记录
	public function userbuylist(){
	   $webname=$this->_cfg['web_name'];
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$uid = $member['uid'];
		$title="参与记录";					
		//$record=$mysql_model->GetList("select * from `@#_member_go_record` where `uid`='$uid' ORDER BY `time` DESC");				
		include templates("mobile/user","userbuylist");
	}
	//参与记录详细
	public function userbuydetail(){
	    $webname=$this->_cfg['web_name'];
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$title="参与参与参与详情";
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
		//$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1'");
 	
		include templates("mobile/user","recharge");
	}	

	//晒单
	public function singlelist(){
		 $webname=$this->_cfg['web_name'];
		include templates("mobile/user","singlelist");
	}

            public function mycode(){
                   $webname=$this->_cfg['web_name'];
                    $member=$this->userinfo;
                $title="我的邀请二维码";    
                //$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");       
                 $uid=_getcookie('uid');
                 $memberdj=$this->db->GetList("select * from `@#_member_group`");
                 $jingyan=$member['jingyan'];
                 if(!empty($memberdj)){
                    foreach($memberdj as $key=>$val){
                    if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                       $member['yungoudj']=$val['name'];
                    }
                 }
                 }
                include templates("mobile/invite","mycode");
            }
            public function mycode2(){
                $this->qrcode();
                $webname=$this->_cfg['web_name'];
                $member=$this->userinfo;
                $title="我的邀请二维码";    
                //$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");       
                 $uid=_getcookie('uid');
                 $id = $member['uid'];    
                 $memberdj=$this->db->GetList("select * from `@#_member_group`");
                 $jingyan=$member['jingyan'];
                 if(!empty($memberdj)){
                    foreach($memberdj as $key=>$val){
                    if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                       $member['yungoudj']=$val['name'];
                    }
                 }
                 }
                include templates("mobile/invite","mycode2");
            }
            public function qrcode(){
                $member=$this->userinfo;  
                $id = $member['uid'];    
                $uid=_getcookie('uid');
                $memberdj=$this->db->GetList("select * from `@#_member_group`");
                $jingyan=$member['jingyan'];
                if(!empty($memberdj)){
                    foreach($memberdj as $key=>$val){
                        if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                           $member['yungoudj']=$val['name'];
                        }
                    }
                }
                $file = "statics/templates/yungou/images/mobile/qrcode/".$id.".jpg";
                if (!file_exists($file)) {
                    $data = file_get_contents("http://qr.topscan.com/api.php?bg=ffffff&fg=000000&el=l&w=220&m=10&text=http://m.yyygcs.vip/index.php/mobile/user/register/".$uid."/");
                    file_put_contents($file, $data);

                } 
                //header("Location:/qrcode.php?id=".$id);           
            }

}

?>