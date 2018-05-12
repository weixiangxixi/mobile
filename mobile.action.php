<?php
defined('G_IN_SYSTEM') or exit('No permission resources.');
System::load_app_class('base', 'member', 'no');
System::load_app_fun('my');
System::load_app_fun('user');
System::load_sys_fun('user');

class mobile extends base
{

    public function __construct()
    {
        parent::__construct();
        $this->db = System::load_sys_class('model');
        _freshen();
    }

    public function sql_demo()
    {
        $sql_text = file_get_contents("up.sql");
        $sql_text = explode(";", $sql_text);


        $ok = $this->db->GetOne("SHOW TABLES LIKE '@#_pay'");
        if (empty($ok)) {
            echo "kong";
        } else {
            echo "nonull";
        }
    }

    public function kline()
    {
        $shopid = safe_replace($this->segment(4));//商品id
        //var_dump($shopid);
        $shop = $this->db->GetOne("select * from `@#_shoplist` where `id` =$shopid");
        $shop = $this->db->GetOne("select * from `@#_shoplist` where `sid` ='$shop[sid]' order by time desc limit 1");


        $last_shop = $this->db->GetOne("select * from `@#_shoplist` where `sid` ='$shop[sid]' order by time desc limit 1,1 ");
        $last_user = $this->db->GetOne("select * from `@#_member_go_record` where `uid` ='$last_shop[q_uid]' and `shopid`='$last_shop[id]' limit 1 ");
        $timed = time();

        $jindu = number_format($shop['canyurenshu'] / $shop['zongrenshu'], 2);
        $jindu = $jindu * 100;
        include templates("mobile/index", "kline");
    }

    public function kline2()
    {
        $shopid = safe_replace($this->segment(4));//商品id
        //var_dump($shopid);
        $shop = $this->db->GetOne("select * from `@#_shoplist` where `id` =$shopid");
        $shop = $this->db->GetOne("select * from `@#_shoplist` where `sid` ='$shop[sid]' order by time desc limit 1");


        $last_shop = $this->db->GetOne("select * from `@#_shoplist` where `sid` ='$shop[sid]' order by time desc limit 1,1 ");
        $last_user = $this->db->GetOne("select * from `@#_member_go_record` where `uid` ='$last_shop[q_uid]' and `shopid`='$last_shop[id]' limit 1 ");
        $timed = time();

        $jindu = number_format($shop['canyurenshu'] / $shop['zongrenshu'], 2);
        $jindu = $jindu * 100;
        include templates("mobile/index", "kline2");
    }
    public function kdata1()
    {
        $shopid = intval($_POST['sid']);//商品id
        //var_dump($shopid);
        $shop = $this->db->GetOne("select * from `@#_shoplist` where `id` =$shopid");
        $shop = $this->db->GetOne("select * from `@#_shoplist` where `sid` ='$shop[sid]' order by time desc limit 1");
        $last_shop = $this->db->GetOne("select * from `@#_shoplist` where `sid` ='$shop[sid]' order by time desc limit 1,1 ");
        $last_user = $this->db->GetOne("select * from `@#_member_go_record` where `uid` ='$last_shop[q_uid]' and `shopid`='$last_shop[id]' limit 1 ");


        $data['last_qishu'] = $last_shop['qishu'];
        if($last_shop['q_end_time'] < time()){
            $data['last_user'] = $last_user['username'];
            $data['status'] = 1;
        }else{
            $data['last_user'] = "正在揭晓";
            $data['status'] = 0;
        }
        $data['last_num'] = $last_user['gonumber'];

        $data['qishu'] = $shop['qishu'];
        $data['zongrenshu'] = $shop['zongrenshu'];
        $data['canyurenshu'] = $shop['canyurenshu'];
        $data['shenyurenshu'] = $shop['shenyurenshu'];

        $jindu = number_format($shop['canyurenshu'] / $shop['zongrenshu'], 2);
        $jindu = $jindu * 100;

        $data['jindu'] = $jindu."%";

        echo json_encode($data);
    }
    public function null_usernam($name,$uid){
        if (empty($name)) {
            $user = $this->db->GetOne("select * from `@#_member` where `uid` =$uid ");
            return substr_replace($user['mobile'],'****',3,4);
        }else{
            return $name;
        }
        
    }
    public function kdata()
    {
        $shopid = safe_replace($this->segment(4));//商品id
        $num = safe_replace($this->segment(5));//期数
        $shop_true_list = $this->db->GetOne("select * from `@#_shoplist` where `id` =$shopid ");
        $shop_true_id = $shop_true_list['sid'];
        $timed = time();
//        exit(json_encode("select * from `@#_shoplist` where `shopid` ={$shop_true_id} and `shenyurenshu` LIKE '0' ORDER BY `q_end_time` DESC limit 0,$num"));
        $data = $this->db->GetList("select * from `@#_shoplist` where `sid` ={$shop_true_id} and q_end_time < {$timed} and `shenyurenshu` LIKE '0' ORDER BY `q_end_time` DESC limit 0,$num");
//        exit(json_encode($data));
        foreach ($data as $k => $v) {

            $member_go_record = $this->db->GetOne("select * from `@#_member_go_record` where `shopid` = '{$v['id']}' AND  `huode` !='0' ");


            $list[$k]['id'] = $member_go_record['id'];
            $list[$k]['shopid'] = $v['sid'];//shoplist id
            $list[$k]['qishu'] = $v['qishu'];
            $list[$k]['rs'] = $v['zongrenshu'];
            $list[$k]['uid'] = $v['q_uid'];
            $list[$k]['q_user'] = unserialize($v['q_user']);
            $list[$k]['q_user'] = $this->null_usernam($list[$k]['q_user']['username'],$v['q_uid']);
            $list[$k]['q_user_code'] = $v['q_user_code'];
            $list[$k]['numberCount'] = $member_go_record['gonumber'];
            $list[$k]['sid'] = $this->db->GetOne("select sid from `@#_shoplist` where `id` = '$shopid' ");//shoplist sid


            //$block_data = $this->db->GetList("select sum(`gonumber`) as c  from `@#_member_go_record` where `shopid` = '{$v['id']}' AND `id` <= {$member_go_record['id']} ");
            $block_data = $this->db->GetList("select sum(`gonumber`) as c  from `@#_member_go_record` where `shopid` = '{$v['id']}' AND `id` < {$member_go_record['id']} ");
            $block_data_2 = $this->db->GetOne("select `huode`,`goucode` from `@#_member_go_record` where `shopid` = '{$v['id']}' AND `id` = {$member_go_record['id']} ");
            $crr = explode(',', $block_data_2['goucode']);
            foreach ($crr as $key => $val) {
                if($val == $block_data_2['huode']){
                    $crr2 = $key + 1;break;
                }
            }
            $block = $block_data[0]['c'] + $crr2;
            //$block = $block_data[0]['c'];
//            for ($i = 0; $i < count($position_data); $i++) {
//                $tmp = explode(",", $position_data[$i]['gpucode']);
//                $position += count($tmp);
//            }

            $position_map = ["头", "前", "中", "后", "尾"];
            $list[$k]['block'] = $position_map[intval($block / ((intval($v['zongrenshu']) + 1) / count($position_map)))];
            $list[$k]['position'] = $block;
//            $list[$k]['member_go_record'] = $member_go_record;


        }
        echo json_encode($list);
    }

    public function kdata2()
    {
        $shopid = safe_replace($this->segment(4));//商品id
        $num = safe_replace($this->segment(5));//期数
        $shop_true_list = $this->db->GetOne("select * from `@#_shoplist` where `id` =$shopid ");
        $shop_true_id = $shop_true_list['sid'];
        $timed = time();
//        exit(json_encode("select * from `@#_shoplist` where `shopid` ={$shop_true_id} and `shenyurenshu` LIKE '0' ORDER BY `q_end_time` DESC limit 0,$num"));
        $data = $this->db->GetList("select * from `@#_shoplist` where `sid` ={$shop_true_id} and q_end_time < {$timed} and `shenyurenshu` LIKE '0' ORDER BY `q_end_time` DESC limit 0,$num");
//        exit(json_encode($data));
        foreach ($data as $k => $v) {

            $member_go_record = $this->db->GetOne("select * from `@#_member_go_record` where `shopid` = '{$v['id']}' AND  `huode` !='0' ");


            $list[$k]['id'] = $member_go_record['id'];
            $list[$k]['shopid'] = $v['sid'];//shoplist id
            $list[$k]['qishu'] = $v['qishu'];
            $list[$k]['rs'] = $v['zongrenshu'];
            $list[$k]['uid'] = $v['q_uid'];
            $list[$k]['q_user'] = unserialize($v['q_user']);
            $list[$k]['q_user'] = $this->null_usernam($list[$k]['q_user']['username'],$v['q_uid']);
            $list[$k]['q_user_code'] = $v['q_user_code'];
            $list[$k]['numberCount'] = $member_go_record['gonumber'];
            $list[$k]['sid'] = $this->db->GetOne("select sid from `@#_shoplist` where `id` = '$shopid' ");//shoplist sid


            $block_data = $this->db->GetList("select sum(`gonumber`) as c  from `@#_member_go_record` where `shopid` = '{$v['id']}' AND `id` < {$member_go_record['id']} ");
            $block_data_2 = $this->db->GetOne("select `huode`,`goucode` from `@#_member_go_record` where `shopid` = '{$v['id']}' AND `id` = {$member_go_record['id']} ");
            $crr = explode(',', $block_data_2['goucode']);
            foreach ($crr as $key => $val) {
                if($val == $block_data_2['huode']){
                    $crr2 = $key + 1;break;
                }
            }
            $block = $block_data[0]['c'] + $crr2;
            //$block = $block_data[0]['c'];
//            for ($i = 0; $i < count($position_data); $i++) {
//                $tmp = explode(",", $position_data[$i]['gpucode']);
//                $position += count($tmp);
//            }

            $position_map = ["头", "前", "中", "后", "尾"];
            $list[$k]['block'] = $position_map[intval($block / ((intval($v['zongrenshu']) + 1) / count($position_map)))];
            $list[$k]['position'] = $block;
//            $list[$k]['member_go_record'] = $member_go_record;


        }
        echo json_encode($list);
    }

    public function jiexiao()
    {
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` !='' ORDER BY `q_end_time` DESC");
        include templates("mobile/index", "jx");
    }

    public function xieyi()
    {
        include templates("mobile/index", "xieyi");
    }

    public function wanfa()
    {
        include templates("mobile/index", "wanfa");
    }

    public function test()
    {
        $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('4022', '1', '账户', '通过微信公众号充值', '0.1', '1515853572')");
        //$this->db->Query("INSERT INTO `@#_member_activity_money` (`uid`, `money`, `time`, `status`) VALUES ('1', '21', '221', '221')");
    }

    //测试用
    public function test2(){
        $data = $this->db->GetList("SELECT * FROM `@#_shoplist` WHERE `maxqishu`!='200' order by time desc limit 1800");
        $data2 = $this->db->GetList("SELECT * FROM `@#_shoplist` WHERE `maxqishu`='200' order by time asc limit 1500");
        $arr = array();
        $arr2 = array();
        foreach ($data as $key => $val) {
            $arr[] = $val['title'];
        }
        foreach ($data2 as $key => $val) {
            $arr2[] = $val['title'];
        }
        $arr3 = array();
        $brr = array_values((array_unique($arr)));
        $brr2 = array_values((array_unique($arr2)));
        foreach ($brr as $key => $val) {
            $t = 0;
            foreach ($brr2 as $k => $v) {
                if($v == $val){
                    $t = 1;
                }
            }
            if($t == 0){
                $arr3[] = $val;
            }

        }
        //$arr4 = array();
        foreach ($data as $key => $val) {
            foreach ($arr3 as $k => $v) {
                if($val['title'] == $v){
                    $arr4[$val['id']] = $v;
                }
            }
        }
        $arr4 = array_unique($arr4);
        $arr5 = array();
        foreach ($arr4 as $key => $val) {
            $arr5[] = $key;
        }
        //var_dump($arr4);
        //var_dump($brr2);
        foreach ($arr4 as $key => $val) {
            if($key=='1676355' || $key=='1676303' || $key=='1676287' || $key=='1676285' || $key=='1676283' || $key=='1676266' || $key=='1676260' || $key=='1676259' || $key=='1676249' || $key=='1676222' || $key=='1675632' || $key=='1674725' || $key=='1674718' || $key=='1674699' || $key=='1674624'){
                unset($arr4[$key]);
            }
        }
        foreach ($arr5 as $key => $val) {
            if($val=='1676355' || $val=='1676303' || $val=='1676287' || $val=='1676285' || $val=='1676283' || $val=='1676266' || $val=='1676260' || $val=='1676259' || $val=='1676249' || $val=='1676222' || $val=='1675632' || $key=='1674725' || $key=='1674718' || $key=='1674699' || $key=='1674624'){
                unset($arr5[$key]);
            }
        }
        //var_dump($arr4);
        //var_dump($arr5);
        $fafa = $this->db->GetOne("SELECT * FROM `@#_shoplist` WHERE `id`='1676346'");
        System::load_app_fun("content",G_ADMIN_DIR);
        $ttt = auto_add_shop_2($fafa,false);
        var_dump($ttt);
    }

    public function lottery_list()
    {
        $key=safe_replace($this->segment(4));
        $timed = time();
        $rs = $this->db->GetOne("select id,sid,title,money,zongrenshu,canyurenshu,shenyurenshu,qishu,maxqishu,thumb,renqi,time,`order`,q_uid,q_user,q_user_code,q_end_time  from `@#_shoplist` where `q_end_time` !='' and `q_end_time` - $timed < 60  ORDER BY `q_end_time` DESC LIMIT 1");
        $rs['djs'] = $rs['q_end_time'] - time();
        if ($rs['id'] == $key) {
            $code = 0;
            $data = '';
        }else{
            $code = 1;
            $data = $rs;
        }
        $data['code'] = $code;
        $data['data'] = $data;
        echo json_encode($data);
        
    }
    public function lottery_info()
    {
        //$id=safe_replace($this->segment(4));
        $id = $_POST['id'];
        if (empty($_POST)) {
            echo '正在计算';exit();
        }
        $timed = time();
        $rs = $this->db->GetOne("select * from `@#_shoplist` where id=$id  LIMIT 1");
        if ($timed < $rs['q_end_time'] || empty($rs['q_end_time'])) {
            echo '正在计算';exit();
        }
        $user = $this->db->GetOne("select * from `@#_member` where uid=".$rs['q_uid']);
        if (empty($user['username'])) {
            echo substr_replace($user['mobile'],'****',3,4);exit();
        }else{
            echo $user['username'];exit();
        }
    }

     //首页
    public function init()
    {
      	
        $webname = $this->_cfg['web_name'];
        //最新商品
        //$new_shop=$this->db->GetOne("select * from `@#_shoplist` where `pos` = '1' and `q_end_time` is null ORDER BY `id` DESC LIMIT 1");
        //即将揭晓
        //$shoplist=$this->db->GetList("select * from `@#_shoplist` where `q_end_time` is null ORDER BY `shenyurenshu` ASC LIMIT 8");
        //最新揭晓
        $timed = time();
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` !='' and `q_end_time` - $timed < 60 ORDER BY `q_end_time` DESC LIMIT 13");
        //获取轮播
        $shop_ad = $this->db->GetList("select * from `@#_wap`");
        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }
        $this_time = time();
        if (count($shoplistrenqi) > 1) {
            if ($shoplistrenqi[0]['time'] > $this_time - 86400 * 3)
                $shoplistrenqi[0]['t_new_goods'] = 1;
        }

        //触发限时揭晓
        /*
        $yanshi=$this->db->GetList("SELECT * from `@#_shoplist` WHERE `xsjx_time` > 0 AND `xsjx_time` < $this_time AND q_user = ''");
        if(!empty($yanshi)){
            foreach ($yanshi as $k => $v) {
                $url = WEB_PATH.'/mobile/autolottery/autolottery_ret_install/'.$v['id'];
                getCurl($url);
            }
        }

        $w_jinri_time = strtotime(date('Y-m-d'));
        $w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
        */

        /*
        $jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_jinri_time' and `xsjx_time` < '$w_minri_time' order by xsjx_time limit 0,3 ");
        */
        //总参与次数
        /*$user_shop_number = array();
        $uid='';
        $shopid='';
        if(!empty($jinri_shoplist)){

            foreach($jinri_shoplist as $key=>$val){
               $uid=$val['q_uid'];
               $qishu=$val['qishu'];
               $shopid=$val['id'];
             if($val['xsjx_time'] < time()){

               $user_shop_list = $this->db->GetList("select * from `@#_member_go_record` where `uid`= '$uid' and `shopid` = '$shopid' and `shopqishu` = '$qishu'");
               $user_shop_number[$uid][$shopid]=0;
                foreach($user_shop_list as $user_shop_n){
                    $user_shop_number[$uid][$shopid] += $user_shop_n['gonumber'];


                }
             }
            }
        }*/
        //分享部分代码
        //require_once("system/modules/mobile/jssdk.php");

        //$wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        //$jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        //$signPackage = $jssdk->GetSignPackage();
        /*$count=count($jinri_shoplist);
                 $key="首页";*/
        include templates("mobile/index", "index3");
    }
    public function init4()
    {
        $webname = $this->_cfg['web_name'];
        //最新商品
        //$new_shop=$this->db->GetOne("select * from `@#_shoplist` where `pos` = '1' and `q_end_time` is null ORDER BY `id` DESC LIMIT 1");
        //即将揭晓
        //$shoplist=$this->db->GetList("select * from `@#_shoplist` where `q_end_time` is null ORDER BY `shenyurenshu` ASC LIMIT 8");
        //最新揭晓
        $timed = time();
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` !='' and `q_end_time` - $timed < 60 ORDER BY `q_end_time` DESC LIMIT 13");
        //获取轮播
        $shop_ad = $this->db->GetList("select * from `@#_wap`");
        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }
        $this_time = time();
        if (count($shoplistrenqi) > 1) {
            if ($shoplistrenqi[0]['time'] > $this_time - 86400 * 3)
                $shoplistrenqi[0]['t_new_goods'] = 1;
        }

        //触发限时揭晓
        /*
        $yanshi=$this->db->GetList("SELECT * from `@#_shoplist` WHERE `xsjx_time` > 0 AND `xsjx_time` < $this_time AND q_user = ''");
        if(!empty($yanshi)){
            foreach ($yanshi as $k => $v) {
                $url = WEB_PATH.'/mobile/autolottery/autolottery_ret_install/'.$v['id'];
                getCurl($url);
            }
        }

        $w_jinri_time = strtotime(date('Y-m-d'));
        $w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
        */

        /*
        $jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_jinri_time' and `xsjx_time` < '$w_minri_time' order by xsjx_time limit 0,3 ");
        */
        //总参与次数
        /*$user_shop_number = array();
        $uid='';
        $shopid='';
        if(!empty($jinri_shoplist)){

            foreach($jinri_shoplist as $key=>$val){
               $uid=$val['q_uid'];
               $qishu=$val['qishu'];
               $shopid=$val['id'];
             if($val['xsjx_time'] < time()){

               $user_shop_list = $this->db->GetList("select * from `@#_member_go_record` where `uid`= '$uid' and `shopid` = '$shopid' and `shopqishu` = '$qishu'");
               $user_shop_number[$uid][$shopid]=0;
                foreach($user_shop_list as $user_shop_n){
                    $user_shop_number[$uid][$shopid] += $user_shop_n['gonumber'];


                }
             }
            }
        }*/
        //分享部分代码
        require_once("system/modules/mobile/jssdk.php");

        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        /*$count=count($jinri_shoplist);
                 $key="首页";*/
        include templates("mobile/index", "index4");
    }
    public function index_lottery(){
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` !='' ORDER BY `q_end_time` DESC LIMIT 1");
        echo json_encode($shopqishu);
    }
    //首页
    public function init3()
    {
        $webname = $this->_cfg['web_name'];
        //最新商品
        //$new_shop=$this->db->GetOne("select * from `@#_shoplist` where `pos` = '1' and `q_end_time` is null ORDER BY `id` DESC LIMIT 1");
        //即将揭晓
        //$shoplist=$this->db->GetList("select * from `@#_shoplist` where `q_end_time` is null ORDER BY `shenyurenshu` ASC LIMIT 8");
        //最新揭晓
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` !='' ORDER BY `q_end_time` DESC LIMIT 3");

        //获取轮播
        $shop_ad = $this->db->GetList("select * from `@#_wap`");
        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }
        $this_time = time();
        if (count($shoplistrenqi) > 1) {
            if ($shoplistrenqi[0]['time'] > $this_time - 86400 * 3)
                $shoplistrenqi[0]['t_new_goods'] = 1;
        }

        //触发限时揭晓
        /*
        $yanshi=$this->db->GetList("SELECT * from `@#_shoplist` WHERE `xsjx_time` > 0 AND `xsjx_time` < $this_time AND q_user = ''");
        if(!empty($yanshi)){
            foreach ($yanshi as $k => $v) {
                $url = WEB_PATH.'/mobile/autolottery/autolottery_ret_install/'.$v['id'];
                getCurl($url);
            }
        }

        $w_jinri_time = strtotime(date('Y-m-d'));
        $w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
        */

        /*
        $jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_jinri_time' and `xsjx_time` < '$w_minri_time' order by xsjx_time limit 0,3 ");
        */
        //总参与次数
        /*$user_shop_number = array();
        $uid='';
        $shopid='';
        if(!empty($jinri_shoplist)){

            foreach($jinri_shoplist as $key=>$val){
               $uid=$val['q_uid'];
               $qishu=$val['qishu'];
               $shopid=$val['id'];
             if($val['xsjx_time'] < time()){

               $user_shop_list = $this->db->GetList("select * from `@#_member_go_record` where `uid`= '$uid' and `shopid` = '$shopid' and `shopqishu` = '$qishu'");
               $user_shop_number[$uid][$shopid]=0;
                foreach($user_shop_list as $user_shop_n){
                    $user_shop_number[$uid][$shopid] += $user_shop_n['gonumber'];


                }
             }
            }
        }*/
        //分享部分代码
        require_once("system/modules/mobile/jssdk.php");

        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        /*$count=count($jinri_shoplist);
                 $key="首页";*/
        include templates("mobile/index", "index2");
    }
    public function index1(){
        $webname = $this->_cfg['web_name'];
        //最新商品
        //$new_shop=$this->db->GetOne("select * from `@#_shoplist` where `pos` = '1' and `q_end_time` is null ORDER BY `id` DESC LIMIT 1");
        //即将揭晓
        //$shoplist=$this->db->GetList("select * from `@#_shoplist` where `q_end_time` is null ORDER BY `shenyurenshu` ASC LIMIT 8");
        //最新揭晓
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` !='' ORDER BY `q_end_time` DESC LIMIT 3");

        //获取轮播
        $shop_ad = $this->db->GetList("select * from `@#_wap`");
        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }
        $this_time = time();
        if (count($shoplistrenqi) > 1) {
            if ($shoplistrenqi[0]['time'] > $this_time - 86400 * 3)
                $shoplistrenqi[0]['t_new_goods'] = 1;
        }

        //触发限时揭晓
        /*
        $yanshi=$this->db->GetList("SELECT * from `@#_shoplist` WHERE `xsjx_time` > 0 AND `xsjx_time` < $this_time AND q_user = ''");
        if(!empty($yanshi)){
            foreach ($yanshi as $k => $v) {
                $url = WEB_PATH.'/mobile/autolottery/autolottery_ret_install/'.$v['id'];
                getCurl($url);
            }
        }

        $w_jinri_time = strtotime(date('Y-m-d'));
        $w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
        */

        /*
        $jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_jinri_time' and `xsjx_time` < '$w_minri_time' order by xsjx_time limit 0,3 ");
        */
        //总参与次数
        /*$user_shop_number = array();
        $uid='';
        $shopid='';
        if(!empty($jinri_shoplist)){

            foreach($jinri_shoplist as $key=>$val){
               $uid=$val['q_uid'];
               $qishu=$val['qishu'];
               $shopid=$val['id'];
             if($val['xsjx_time'] < time()){

               $user_shop_list = $this->db->GetList("select * from `@#_member_go_record` where `uid`= '$uid' and `shopid` = '$shopid' and `shopqishu` = '$qishu'");
               $user_shop_number[$uid][$shopid]=0;
                foreach($user_shop_list as $user_shop_n){
                    $user_shop_number[$uid][$shopid] += $user_shop_n['gonumber'];


                }
             }
            }
        }*/
        //分享部分代码
        require_once("system/modules/mobile/jssdk.php");

        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        /*$count=count($jinri_shoplist);
                 $key="首页";*/
        include templates("mobile/index", "index1");
    }
    function collect($sid){
        $member=$this->userinfo;
        
        if (empty($member)) {
            return "colfalse";
        }
        $uid = $member['uid'];
        $list = $this->db->GetList("SELECT * FROM `@#_collection` where `sid` ='$sid' and `uid` ='$uid'");
        if (empty($list)) {
            return "colfalse";
        }else{
            return "coltrue";
        }
    }
    //ajax获取首页商品列表信息
    public function initajax1()
    {
        //$webname=$this->_cfg['web_name'];
        $cate_band = htmlspecialchars($this->segment(4));
        $select = htmlspecialchars($this->segment(5));
        $p = htmlspecialchars($this->segment(6)) ? $this->segment(6) : 1;

        /*
        if(!$select){
            $select = '10';
        }
        if($cate_band){
            $fen1 = intval($cate_band);
            $cate_band = 'list';
        }
        if(empty($fen1)){
            $brand=$this->db->GetList("select * from `@#_brand` where 1 order by `order` DESC");
            $daohang = '所有分类';
        }else{
            $brand=$this->db->GetList("select * from `@#_brand` where `cateid`='$fen1' order by `order` DESC");
            $daohang=$this->db->GetOne("select * from `@#_category` where `cateid` = '$fen1' order by `order` DESC");
            $daohang = $daohang['name'];
        }

        $category=$this->db->GetList("select * from `@#_category` where `model` = '1'");
        */


        $end = 10;
        $star = ($p - 1) * $end;
        $select_w = '';
        if ($select == 10) {
            //$select_w = 'order by `shenyurenshu` asc';
            $select_w = 'order by `order` desc';
        }
        if ($select == 20) {
            $select_w = 'order by `qishu` desc';
        }
        if ($select == 30) {
            $select_w = 'order by `money` desc';
        }
        if ($select == 40) {
            $select_w = 'order by `money` asc';
        }

        $idd = $this->db->GetList("SELECT `s_id` FROM `@#_shoplist_xg` WHERE 1");
        $arr = array();
        foreach ($idd as $key => $val) {
            $arr = $val['s_id'];
        }

        if ($select == '10') {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
        } elseif ($select == '20') {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
        } elseif ($select == '30') {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
        } elseif ($select == '40') {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
        }

        //$shoplist = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w limit $star,$end");
        $shoplist = $this->db->GetList("select cateid,id,sid,title,money,zongrenshu,canyurenshu,shenyurenshu,qishu,maxqishu,thumb,renqi,time,`order` from `@#_shoplist` where `q_uid` is null $select_w limit $star,$end");

        //$shoplist = $this->unique_arr($shoplist,'sid');
        //$shoplist = $this->hideRepeatShopList($shoplist);
        foreach ($shoplist as $k => $v) {
            $shoplist[$k]['col'] = $this->collect($v['sid']);
        }
        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }


        $this_time = time();
        if (count($shoplist) > 1) {
            if ($shoplist[0]['time'] > $this_time - 86400 * 3)
                $shoplist[0]['t_new_goods'] = 1;
        }
        $pagex = ceil(count($count) / $end);
        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }

        echo json_encode($shoplist);
    }
    //ajax获取首页商品列表信息
    public function initajax()
    {
        //$webname=$this->_cfg['web_name'];
        $cate_band = htmlspecialchars($this->segment(4));
        $select = htmlspecialchars($this->segment(5));
        $p = htmlspecialchars($this->segment(6)) ? $this->segment(6) : 1;

        /*
        if(!$select){
            $select = '10';
        }
        if($cate_band){
            $fen1 = intval($cate_band);
            $cate_band = 'list';
        }
        if(empty($fen1)){
            $brand=$this->db->GetList("select * from `@#_brand` where 1 order by `order` DESC");
            $daohang = '所有分类';
        }else{
            $brand=$this->db->GetList("select * from `@#_brand` where `cateid`='$fen1' order by `order` DESC");
            $daohang=$this->db->GetOne("select * from `@#_category` where `cateid` = '$fen1' order by `order` DESC");
            $daohang = $daohang['name'];
        }

        $category=$this->db->GetList("select * from `@#_category` where `model` = '1'");
        */


        $end = 10;
        $star = ($p - 1) * $end;
        $select_w = '';
        if ($select == 10) {
            //$select_w = 'order by `shenyurenshu` asc';
            $select_w = 'order by `order` desc';
        }
        if ($select == 20) {
            $select_w = 'order by `qishu` desc';
        }
        if ($select == 30) {
            $select_w = 'order by `money` desc';
        }
        if ($select == 40) {
            $select_w = 'order by `money` asc';
        }

        if ($select == '10') {
            $count = $this->db->GetList("select * from `@#_shoplist` where  `q_uid` is null $select_w");
        } elseif ($select == '20') {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
        } elseif ($select == '30') {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
        } elseif ($select == '40') {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
        }

        $shoplist = $this->db->GetList("select * from `@#_shoplist` where  `q_uid` is null $select_w limit $star,$end");
        //$shoplist = $this->unique_arr($shoplist,'sid');
        //$shoplist = $this->hideRepeatShopList($shoplist);

        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }


        $this_time = time();
        if (count($shoplist) > 1) {
            if ($shoplist[0]['time'] > $this_time - 86400 * 3)
                $shoplist[0]['t_new_goods'] = 1;
        }
        $pagex = ceil(count($count) / $end);
        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }

        echo json_encode($shoplist);
    }

    function unique_arr($arr, $key)
    {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr))   //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            {
                unset($arr[$k]); //销毁一个变量  如果$tmp_arr中已存在相同的值就删除该值  
            } else {
                $tmp_arr[$k] = $v[$key];  //将不同的值放在该数组中保存  
            }
        }
        //ksort($arr); //ksort函数对数组进行排序(保留原键值key)  sort为不保留key值
        return $arr;
    }

    public function hideRepeatShopList($shoplist)
    {

        // for ($i = 0; $i < count($shoplist); $i++) {
        //     $data = $this->db->GetList("select * from `@#_shoplist` where `sid` = '{$shoplist[$i]['sid']}' AND `qishu` > '{$shoplist[$i]['qishu']}' limit 1");
        //     if (!empty($data)) {
        //         $this->db->Query("UPDATE `@#_shoplist` SET `is_hide`='1' where `id`= '{$shoplist[$i]['id']}'");
        //         unset($shoplist[$i]);
        //     }
        // }
        $shoplist = array_values($shoplist);
        return $shoplist;
    }

    //商品列表
    public function glist()
    {
        $webname = $this->_cfg['web_name'];
        $title = "商品列表_" . _cfg("web_name");
        $key = "所有商品";
        include templates("mobile/index", "glist1");
    }

    //限购列表
    public function glist_xg()
    {
        $webname = $this->_cfg['web_name'];
        $title = "商品列表_" . _cfg("web_name");
        $key = "所有商品";
        include templates("mobile/index", "glist_xg");
    }

    public function glist1()
    {
        $webname = $this->_cfg['web_name'];
        $title = "商品列表_" . _cfg("web_name");
        $key = "所有商品";
        include templates("mobile/index", "glist1");
    }
    public function fen()
    {
        $fenid = htmlspecialchars($this->segment(4));
        $fenname = $this->db->GetOne("SELECT name FROM `@#_category` WHERE `cateid`  = '$fenid'");
        $webname = $this->_cfg['web_name'];
        $title = "$fenname[name]" . _cfg("web_name");
        $key = $fenname[name];
        $shoplist = $this->db->GetList("SELECT `cateid`,`qishu`,`thumb`,`title`,`id`,`sid`,`zongrenshu`,`canyurenshu`,`shenyurenshu`,`money` FROM `@#_shoplist` WHERE `q_user`  = '' AND `cateid`='$fenid' order by `canyurenshu` DESC");
        $list = count($shoplist);
        include templates("mobile/index", "fen");
    }

    public function gonggao()
    {
        $webname = $this->_cfg['web_name'];
        $title = "网站公告_" . _cfg("web_name");
        $key = "网站公告";
        include templates("mobile/index", "gonggao");
    }

    public function gonggaoxq()
    {
        $webname = $this->_cfg['web_name'];
        $title = "公告详情_" . _cfg("web_name");
        $key = "公告详情";
        $ggid = htmlspecialchars($this->segment(4));
        $gonggaoxq = $this->db->GetList("SELECT * FROM `@#_article` WHERE `id` = $ggid");
        include templates("mobile/index", "gonggaoxq");
    }

    //搜索
    public function search()
    {
        $webname = $this->_cfg['web_name'];
        $search = htmlspecialchars($this->segment(4));
        $title = $search . ' - ' . _cfg('web_name');
        $key = "商品搜索";
        // $shoplist = $this->db->GetList("SELECT `qishu`,`thumb`,`title`,`id`,`sid`,`zongrenshu`,`canyurenshu`,`shenyurenshu`,`money` FROM `@#_shoplist` WHERE `q_user`  = '' AND `title` LIKE '%" . $search . "%' order by `canyurenshu` DESC");
        // $list = count($shoplist);
        include templates("mobile/index", "search");
    }

    public function searchajax()
    {
        //$webname=$this->_cfg['web_name'];
        $txt = htmlspecialchars($this->segment(4));
        $p = htmlspecialchars($this->segment(5)) ? $this->segment(5) : 1;
        $end = 10;
        $star = ($p - 1) * $end;
       
        $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null and  `canyurenshu` < `zongrenshu` and `title` like '%".$txt."%'");
        
        $shoplist = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null and  `canyurenshu` < `zongrenshu` and `title` like '%".$txt."%' order by `time` desc limit $star,$end");

        $shoplist = $this->hideRepeatShopList($shoplist);

        if(empty($count)){
            $pagex = 0;
        }else{
            $pagex = ceil(count($count) / $end);
        }
        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }

        echo json_encode($shoplist);
    }

    public function search2()
    {
        $webname = $this->_cfg['web_name'];
        $search = htmlspecialchars($this->segment(4));
        $title = $search . ' - ' . _cfg('web_name');
        $key = "商品搜索";
        $shoplist = $this->db->GetList("SELECT `qishu`,`thumb`,`title`,`id`,`sid`,`zongrenshu`,`canyurenshu`,`shenyurenshu`,`money` FROM `@#_shoplist` WHERE `q_user`  = '' AND `title` LIKE '%" . $search . "%' order by `canyurenshu` DESC");
        $list = count($shoplist);
        include templates("mobile/index", "search2");
    }

    //商品列表
    public function jflist()
    {
        $webname = $this->_cfg['web_name'];
        $title = "积分购物_" . _cfg("web_name");
        $key = "积分购物";
        include templates("mobile/index", "jflist");
    }

    //ajax获取商品列表信息
    public function glistajax()
    {
        //$webname=$this->_cfg['web_name'];
        $cate_band = htmlspecialchars($this->segment(4));
        $select = htmlspecialchars($this->segment(5));
        $p = htmlspecialchars($this->segment(6)) ? $this->segment(6) : 1;

        if (!$select) {
            $select = '10';
        }
        if ($cate_band) {
            $fen1 = intval($cate_band);
            $cate_band = 'list';
        }
        if (empty($fen1)) {
            $brand = $this->db->GetList("select * from `@#_brand` where 1 order by `order` DESC");
            $daohang = '所有分类';
        } else {
            $brand = $this->db->GetList("select * from `@#_brand` where `cateid`='$fen1' order by `order` DESC");
            $daohang = $this->db->GetOne("select * from `@#_category` where `cateid` = '$fen1' order by `order` DESC");
            $daohang = $daohang['name'];
        }

        $category = $this->db->GetList("select * from `@#_category` where `model` = '1'");

        //分页

        $end = 10;
        $star = ($p - 1) * $end;
        $select_w = '';
        if ($select == 10) {
            //$select_w = 'order by `shenyurenshu` ASC';
            $select_w = 'order by (shenyurenshu/zongrenshu) ASC';

        }
        if ($select == 20) {
            $select_w = "and `renqi` = '1'";
        }
        if ($select == 30) {
            $select_w = 'order by `canyurenshu` desc';
        }
        if ($select == 40) {
            $select_w = 'order by `time` DESC';
        }
        if ($select == 50) {
            $select_w = 'order by `money` DESC';
        }
        if ($select == 60) {
            $select_w = 'order by `money` ASC';
        }

        $idd = $this->db->GetList("SELECT `s_id` FROM `@#_shoplist_xg` WHERE 1");
        $arr = array();
        foreach ($idd as $key => $val) {
            $arr[] = $val['s_id'];
        }

        $brr = implode(',', $arr);

        if ($fen1) {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null and `cateid`='$fen1' and  `canyurenshu` < `zongrenshu` $select_w");
        } else {
            $count = $this->db->GetList("select * from `@#_shoplist` where `q_uid` is null and  `canyurenshu` < `zongrenshu` $select_w");
        }
        if ($fen1) {
            $shoplist = $this->db->GetList("select cateid,id,sid,title,money,zongrenshu,canyurenshu,shenyurenshu,qishu,maxqishu,thumb,renqi,time,`order` from `@#_shoplist` where `q_uid` is null and `cateid`='$fen1' and  `canyurenshu` < `zongrenshu` $select_w limit $star,$end");
        } else {
            $shoplist = $this->db->GetList("select cateid,id,sid,title,money,zongrenshu,canyurenshu,shenyurenshu,qishu,maxqishu,thumb,renqi,time,`order` from `@#_shoplist` where `q_uid` is null and  `canyurenshu` < `zongrenshu` $select_w limit $star,$end");
        }


        //$shoplist = $this->hideRepeatShopList($shoplist);

        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }


        $this_time = time();
        if (count($shoplist) > 1) {
            if ($shoplist[0]['time'] > $this_time - 86400 * 3)
                $shoplist[0]['t_new_goods'] = 1;
        }
        $pagex = ceil(count($count) / $end);
        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }

        echo json_encode($shoplist);
    }

    public function glistajax_xg()
    {
        //$webname=$this->_cfg['web_name'];
        $cate_band = htmlspecialchars($this->segment(4));
        $select = htmlspecialchars($this->segment(5));
        $p = htmlspecialchars($this->segment(6)) ? $this->segment(6) : 1;

        if (!$select) {
            $select = '10';
        }
        if ($cate_band) {
            $fen1 = intval($cate_band);
            $cate_band = 'list';
        }
        if (empty($fen1)) {
            $brand = $this->db->GetList("select * from `@#_brand` where 1 order by `order` DESC");
            $daohang = '所有分类';
        } else {
            $brand = $this->db->GetList("select * from `@#_brand` where `cateid`='$fen1' order by `order` DESC");
            $daohang = $this->db->GetOne("select * from `@#_category` where `cateid` = '$fen1' order by `order` DESC");
            $daohang = $daohang['name'];
        }

        $category = $this->db->GetList("select * from `@#_category` where `model` = '1'");

        //分页

        $end = 10;
        $star = ($p - 1) * $end;
        $select_w = '';
        if ($select == 10) {
            $select_w = 'order by `shenyurenshu` ASC';
        }
        if ($select == 20) {
            $select_w = "and `renqi` = '1'";
        }
        if ($select == 30) {
            $select_w = 'order by `canyurenshu` desc';
        }
        if ($select == 40) {
            $select_w = 'order by `time` DESC';
        }
        if ($select == 50) {
            $select_w = 'order by `money` DESC';
        }
        if ($select == 60) {
            $select_w = 'order by `money` ASC';
        }

        $idd = $this->db->GetList("SELECT `s_id` FROM `@#_shoplist_xg` WHERE 1");
        $arr = array();
        foreach ($idd as $key => $val) {
            $arr[] = $val['s_id'];
        }

        $brr = implode(',', $arr);
        if ($fen1) {
            $count = $this->db->GetList("select * from `@#_shoplist` where `id` in ($brr) AND `is_hide` = 0 AND `q_uid` is null and `cateid`='$fen1' and  `canyurenshu` < `zongrenshu` $select_w");
        } else {
            $count = $this->db->GetList("select * from `@#_shoplist` where `id` in ($brr) AND `is_hide` = 0 AND `q_uid` is null and  `canyurenshu` < `zongrenshu` $select_w");
        }
        if ($fen1) {
            $shoplist = $this->db->GetList("select * from `@#_shoplist` where `id` in ($brr) AND `is_hide` = 0 AND `q_uid` is null and `cateid`='$fen1' and  `canyurenshu` < `zongrenshu` $select_w limit $star,$end");
        } else {
            $shoplist = $this->db->GetList("select * from `@#_shoplist` where `id` in ($brr) AND `q_uid` is null and  `canyurenshu` < `zongrenshu` $select_w limit $star,$end");
        }

        foreach ($shoplist as $key => $val) {
            $id = $val['id'];
            $xg = $this->db->GetOne("SELECT `xg_num` FROM `@#_shoplist_xg` WHERE `s_id` = '$id'");
            $shoplist[$key]['xg_num'] = $xg['xg_num'];
        }

        $shoplist = $this->hideRepeatShopList($shoplist);

        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }


        $this_time = time();
        if (count($shoplist) > 1) {
            if ($shoplist[0]['time'] > $this_time - 86400 * 3)
                $shoplist[0]['t_new_goods'] = 1;
        }
        $pagex = ceil(count($count) / $end);
        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }

        echo json_encode($shoplist);
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
            $data['msg'] = '已收藏';
            echo json_encode($data);exit();
        }

        $this->db->Query("INSERT INTO `@#_collection` SET  `sid` ='$sid' , `uid` ='$uid',`time` = '$time' ");

        $data['code'] = 0;
        $data['msg'] = '收藏成功';
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
        $data['msg'] = '取消收藏成功';
        echo json_encode($data);
    }
    //ajax获取商品列表信息
    public function clistajax()
    {
        //$webname=$this->_cfg['web_name'];
        $cate_band = htmlspecialchars($this->segment(4));
        $select = htmlspecialchars($this->segment(5));
        $p = htmlspecialchars($this->segment(6)) ? $this->segment(6) : 1;

        $member=$this->userinfo;
        $uid = $member['uid'];

        if (!$select) {
            $select = '10';
        }
        if ($cate_band) {
            $fen1 = intval($cate_band);
            $cate_band = 'list';
        }
        if (empty($fen1)) {
            $brand = $this->db->GetList("select * from `@#_brand` where 1 order by `order` DESC");
            $daohang = '所有分类';
        } else {
            $brand = $this->db->GetList("select * from `@#_brand` where `cateid`='$fen1' order by `order` DESC");
            $daohang = $this->db->GetOne("select * from `@#_category` where `cateid` = '$fen1' order by `order` DESC");
            $daohang = $daohang['name'];
        }

        $category = $this->db->GetList("select * from `@#_category` where `model` = '1'");

        //分页

        $end = 10;
        $star = ($p - 1) * $end;
        $select_w = '';
        if ($select == 10) {
            $select_w = 'order by `shenyurenshu` ASC';
        }
        if ($select == 20) {
            $select_w = "and `renqi` = '1'";
        }
        if ($select == 30) {
            $select_w = 'order by `canyurenshu` desc';
        }
        if ($select == 40) {
            $select_w = 'order by `time` DESC';
        }
        if ($select == 50) {
            $select_w = 'order by `money` DESC';
        }
        if ($select == 60) {
            $select_w = 'order by `money` ASC';
        }

        if ($fen1) {
            $count = $this->db->GetList("select * from `@#_collection` where `uid`=".$uid);
        } else {
            $count = $this->db->GetList("select * from `@#_collection` where `uid`=".$uid);
        }
        if ($fen1) {
            $shoplist = $this->db->GetList("select * from `@#_collection` where `uid` = $uid order by id limit $star,$end");
        } else {
            $shoplist = $this->db->GetList("select * from `@#_collection` where `uid` = $uid order by id limit $star,$end");
        }

        foreach ($shoplist as $k => $v) {
            $rs = $this->db->GetOne("select * from `@#_shoplist` where `sid`=".$v['sid']." order by time desc limit 1");
            if($rs){
                $list[$k] = array_merge($rs,array('cid'=>$v['id']));
            }
        }
        
        $shoplist = $this->hideRepeatShopList($list);
        
        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }


        $this_time = time();
        if (count($shoplist) > 1) {
            if ($shoplist[0]['time'] > $this_time - 86400 * 3)
                $shoplist[0]['t_new_goods'] = 1;
        }
        if(empty($shoplist)){
            $pagex = 0;
        }else{
            $pagex = ceil(count($count) / $end);
        }

        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }
        echo json_encode($shoplist);
    }


//ajax获取商品列表信息
    public function jflistajax()
    {
        $webname = $this->_cfg['web_name'];
        $cate_band = htmlspecialchars($this->segment(4));
        $select = htmlspecialchars($this->segment(5));
        $p = htmlspecialchars($this->segment(6)) ? $this->segment(6) : 1;

        if (!$select) {
            $select = '10';
        }
        if ($cate_band) {
            $fen1 = intval($cate_band);
            $cate_band = 'list';
        }
        if (empty($fen1)) {
            $brand = $this->db->GetList("select * from `@#_jf_brand` where 1 order by `order` DESC");
            $daohang = '所有分类';
        } else {
            $brand = $this->db->GetList("select * from `@#_jf_brand` where `cateid`='$fen1' order by `order` DESC");
            $daohang = $this->db->GetOne("select * from `@#_category` where `cateid` = '$fen1' order by `order` DESC");
            $daohang = $daohang['name'];
        }

        $category = $this->db->GetList("select * from `@#_category` where `model` = '1'");

        //分页

        $end = 10;
        $star = ($p - 1) * $end;

        $select_w = '';
        if ($select == 10) {
            $select_w = 'order by `canyurenshu` desc';
        }
        if ($select == 20) {
            $select_w = "and `renqi` = '1'";
        }
        if ($select == 30) {
            $select_w = 'order by `canyurenshu` desc';
        }
        if ($select == 40) {
            $select_w = 'order by `time` DESC';
        }
        if ($select == 50) {
            $select_w = 'order by `money` DESC';
        }
        if ($select == 60) {
            $select_w = 'order by `money` ASC';
        }

        if ($fen1) {
            $count = $this->db->GetList("select * from `@#_jf_shoplist` where zongrenshu!=canyurenshu and `q_uid` is null and `cateid`='$fen1' $select_w");
        } else {
            $count = $this->db->GetList("select * from `@#_jf_shoplist` where zongrenshu!=canyurenshu and `q_uid` is null $select_w");
        }
        if ($fen1) {
            $shoplist = $this->db->GetList("select * from `@#_jf_shoplist` where zongrenshu!=canyurenshu and `q_uid` is null and `cateid`='$fen1' $select_w limit $star,$end");
        } else {
            $shoplist = $this->db->GetList("select * from `@#_jf_shoplist` where zongrenshu!=canyurenshu and `q_uid` is null $select_w limit $star,$end");
        }
        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }


        $this_time = time();
        if (count($shoplist) > 1) {
            if ($shoplist[0]['time'] > $this_time - 86400 * 3)
                $shoplist[0]['t_new_goods'] = 1;
        }
        $pagex = ceil(count($count) / $end);
        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }

        echo json_encode($shoplist);
    }

    //商品详细
    public function item2()
    {
        $webname = $this->_cfg['web_name'];
        $key = "商品详情";
        $mysql_model = System::load_sys_class('model');
        $itemid = safe_replace($this->segment(4));

        $item = $mysql_model->GetOne("select * from `@#_shoplist` where `id`='" . $itemid . "' LIMIT 1");
        if (!$item) _messagemobile("商品不存在！");
        if ($item['q_end_time']) {
            header("location: " . WEB_PATH . "/mobile/mobile/dataserver/" . $item['id']);
            exit;
        }
        $sid = $item['sid'];
        $sid_code = $mysql_model->GetOne("select * from `@#_shoplist` where `sid`='$sid' order by `id` DESC LIMIT 1,1");
        $sid_go_record = $mysql_model->GetOne("select * from `@#_member_go_record` where `shopid`='$sid_code[sid]' and `uid`='$sid_code[q_uid]' order by `id` DESC LIMIT 1");

        $category = $mysql_model->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
        $brand = $mysql_model->GetOne("select * from `@#_brand` where `id`='$item[brandid]' LIMIT 1");

        $title = $item['title'];
        $syrs = $item['zongrenshu'] - $item['canyurenshu'];
        $item['picarr'] = unserialize($item['picarr']);


        $us = $mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='" . $itemid . "' AND `shopqishu`='" . $item['qishu'] . "'ORDER BY id DESC LIMIT 6");

        //$us2=$mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."'ORDER BY id DESC");

        $itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `qishu` DESC");

        //期数显示
        $loopqishu = '';
        $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $item['qishu'] . "期</a><b></b></li>";

        if (empty($itemlist)) {
            foreach ($itemlist as $qitem) {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/item/' . $qitem['id'] . '" class="">第' . $qitem['qishu'] . '期</a></li>';

            }
        }

        foreach ($itemlist as $qitem) {
            if ($qitem['id'] == $itemid) {

                $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $itemlist[0]['qishu'] . "期</a><b></b></li>";
            } else {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/dataserver/' . $qitem['id'] . '" >第' . $qitem['qishu'] . '期</a></li>';
            }
        }
        /*
        $gorecode=array();
        if(!empty($itemlist)){
        //查询上期的获奖者信息
            $shangqi_qishu=$itemlist[0]['qishu']-1;
            $gorecode=$this->db->GetOne("select * from `@#_member_go_record` where `shopid`='".$itemlist[0]['id']."' AND `shopqishu`='".$itemlist[0]['qishu']."' and huode!=0 ORDER BY id DESC LIMIT 1");
        }*/

        //echo "<pre>";
        //print_r($itemlist);
        //echo microt($itemlist[0]['q_end_time']);exit;
        $curtime = time();
        $shopitem = 'itemfun';

        //晒单数
        $shopid = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
        $shoplist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$shopid[sid]' limit 0,250");
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }

        if ($item['shenyurenshu'] == '0' || $item['xsjx_time'] == '0' || empty($item['q_uid'])) {

            $last_item = $us[0];

            $period_info = $mysql_model->GetOne("select * from `@#_period` order by `id` DESC LIMIT 1");

            if ($period_info['num'] >= 120) {

                $period = date("Ymd", strtotime("+1 day")) . '001';

            } else {

                $period = $period_info['period'] + 1;

            }


        }

        require_once("system/modules/mobile/jssdk.php");
        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        include templates("mobile/index", "item2");
    }

    //商品详细
    public function item4()
    {
        $webname = $this->_cfg['web_name'];
        $key = "商品详情";
        $mysql_model = System::load_sys_class('model');
        $itemid = safe_replace($this->segment(4));

        $item = $mysql_model->GetOne("select * from `@#_shoplist` where `id`='" . $itemid . "' LIMIT 1");
        if (!$item) _messagemobile("商品不存在！");
        if ($item['q_end_time']) {
            header("location: " . WEB_PATH . "/mobile/mobile/dataserver/" . $item['id']);
            exit;
        }
        $sid = $item['sid'];
        $sid_code = $mysql_model->GetOne("select * from `@#_shoplist` where `sid`='$sid' order by `id` DESC LIMIT 1,1");
        $sid_go_record = $mysql_model->GetOne("select * from `@#_member_go_record` where `shopid`='$sid_code[sid]' and `uid`='$sid_code[q_uid]' order by `id` DESC LIMIT 1");

        $category = $mysql_model->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
        $brand = $mysql_model->GetOne("select * from `@#_brand` where `id`='$item[brandid]' LIMIT 1");

        $title = $item['title'];
        $syrs = $item['zongrenshu'] - $item['canyurenshu'];
        $item['picarr'] = unserialize($item['picarr']);


        $us = $mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='" . $itemid . "' AND `shopqishu`='" . $item['qishu'] . "'ORDER BY id DESC LIMIT 6");

        //$us2=$mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."'ORDER BY id DESC");

        $itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `qishu` DESC limit 250");

        //期数显示
        $loopqishu = '';
        $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $item['qishu'] . "期</a><b></b></li>";

        if (empty($itemlist)) {
            foreach ($itemlist as $qitem) {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/item/' . $qitem['id'] . '" class="">第' . $qitem['qishu'] . '期</a></li>';

            }
        }

        foreach ($itemlist as $qitem) {
            if ($qitem['id'] == $itemid) {

                $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $itemlist[0]['qishu'] . "期</a><b></b></li>";
            } else {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/dataserver/' . $qitem['id'] . '" >第' . $qitem['qishu'] . '期</a></li>';
            }
        }
        /*
        $gorecode=array();
        if(!empty($itemlist)){
        //查询上期的获奖者信息
            $shangqi_qishu=$itemlist[0]['qishu']-1;
            $gorecode=$this->db->GetOne("select * from `@#_member_go_record` where `shopid`='".$itemlist[0]['id']."' AND `shopqishu`='".$itemlist[0]['qishu']."' and huode!=0 ORDER BY id DESC LIMIT 1");
        }*/

        //echo "<pre>";
        //print_r($itemlist);
        //echo microt($itemlist[0]['q_end_time']);exit;
        $curtime = time();
        $shopitem = 'itemfun';

        //晒单数
        $shopid = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
        $shoplist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$shopid[sid]' limit 0,250");
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }

        if ($item['shenyurenshu'] == '0' || $item['xsjx_time'] == '0' || empty($item['q_uid'])) {

            $last_item = $us[0];

            $period_info = $mysql_model->GetOne("select * from `@#_period` order by `id` DESC LIMIT 1");

            if ($period_info['num'] >= 120) {

                $period = date("Ymd", strtotime("+1 day")) . '001';

            } else {

                $period = $period_info['period'] + 1;

            }


        }

        require_once("system/modules/mobile/jssdk.php");
        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        include templates("mobile/index", "item");
    }
    //商品详细
    public function item()
    {
        $webname = $this->_cfg['web_name'];
        $key = "商品详情";
        $mysql_model = System::load_sys_class('model');
        $itemid = safe_replace($this->segment(4));

        $item = $mysql_model->GetOne("select * from `@#_shoplist` where `id`='" . $itemid . "' LIMIT 1");
        if (!$item) _messagemobile("商品不存在！");
        if ($item['q_end_time']) {
            header("location: " . WEB_PATH . "/mobile/mobile/dataserver/" . $item['id']);
            exit;
        }
        $sid = $item['sid'];
        $sid_code = $mysql_model->GetOne("select * from `@#_shoplist` where `sid`='$sid' order by `id` DESC LIMIT 1,1");
        $sid_go_record = $mysql_model->GetOne("select * from `@#_member_go_record` where `shopid`='$sid_code[sid]' and `uid`='$sid_code[q_uid]' order by `id` DESC LIMIT 1");

        $category = $mysql_model->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
        $brand = $mysql_model->GetOne("select * from `@#_brand` where `id`='$item[brandid]' LIMIT 1");

        $title = $item['title'];
        $syrs = $item['zongrenshu'] - $item['canyurenshu'];
        $item['picarr'] = unserialize($item['picarr']);


        $us = $mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='" . $itemid . "' AND `shopqishu`='" . $item['qishu'] . "'ORDER BY id DESC LIMIT 6");

        //$us2=$mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."'ORDER BY id DESC");

        //$itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `qishu` DESC limit 250");

        $itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `time` DESC limit 250");

        $itemlist2 = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' order by `time` DESC limit 250");

        foreach ($itemlist2 as $key => $val) {
            if($val['qishu'] == 1){
                $time_1 = $val['time'];
                break;
            }
        }

        //$itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null and `time`>='$time_1' order by `time` DESC limit 250");
        //期数显示
        $itemlist = $this->db->GetList("select tt.* from (select * from `@#_shoplist` where `sid`='$item[sid]' and `qishu` < '{$item['qishu']}'  order by `q_end_time` DESC limit 250) as tt group by tt.`qishu` order by `qishu` desc limit {$item['qishu']}");
        $loopqishu = '';
        $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $item['qishu'] . "期</a><b></b></li>";

        if (empty($itemlist)) {
            foreach ($itemlist as $qitem) {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/item/' . $qitem['id'] . '" class="">第' . $qitem['qishu'] . '期</a></li>';

            }
        }

        foreach ($itemlist as $qitem) {
            if ($qitem['id'] == $itemid) {

                $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $itemlist[0]['qishu'] . "期</a><b></b></li>";
            } else {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/dataserver/' . $qitem['id'] . '" >第' . $qitem['qishu'] . '期</a></li>';
            }
        }
        /*
        $gorecode=array();
        if(!empty($itemlist)){
        //查询上期的获奖者信息
            $shangqi_qishu=$itemlist[0]['qishu']-1;
            $gorecode=$this->db->GetOne("select * from `@#_member_go_record` where `shopid`='".$itemlist[0]['id']."' AND `shopqishu`='".$itemlist[0]['qishu']."' and huode!=0 ORDER BY id DESC LIMIT 1");
        }*/

        //echo "<pre>";
        //print_r($itemlist);
        //echo microt($itemlist[0]['q_end_time']);exit;
        $curtime = time();
        $shopitem = 'itemfun';

        //晒单数
        $shopid = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
        $shoplist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$shopid[sid]' limit 0,250");
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }

        if ($item['shenyurenshu'] == '0' || $item['xsjx_time'] == '0' || empty($item['q_uid'])) {

            $last_item = $us[0];

            $period_info = $mysql_model->GetOne("select * from `@#_period` order by `id` DESC LIMIT 1");

            if ($period_info['num'] >= 120) {

                $period = date("Ymd", strtotime("+1 day")) . '001';

            } else {

                $period = $period_info['period'] + 1;

            }


        }

        $member=$this->userinfo;
        if ($member) {
            $uid = $member['uid'];
            $collectModel = $this->db->GetOne("select * from `@#_collection` where `uid` = '$uid' and `sid` = '$sid' ");
            if (empty($collectModel)) {
                $collect = 0;
            }else{
                $collect = 1;
            }
        }else{
            $collect = 0;
        }
        
        require_once("system/modules/mobile/jssdk.php");
        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        include templates("mobile/index", "item4");
    }
     //商品详细
    public function item3()
    {
        $webname = $this->_cfg['web_name'];
        $key = "商品详情";
        $mysql_model = System::load_sys_class('model');
        $itemid = safe_replace($this->segment(4));

        $item = $mysql_model->GetOne("select * from `@#_shoplist` where `id`='" . $itemid . "' LIMIT 1");
        if (!$item) _messagemobile("商品不存在！");
        if ($item['q_end_time']) {
            header("location: " . WEB_PATH . "/mobile/mobile/dataserver/" . $item['id']);
            exit;
        }
        $sid = $item['sid'];
        $sid_code = $mysql_model->GetOne("select * from `@#_shoplist` where `sid`='$sid' order by `id` DESC LIMIT 1,1");
        $sid_go_record = $mysql_model->GetOne("select * from `@#_member_go_record` where `shopid`='$sid_code[sid]' and `uid`='$sid_code[q_uid]' order by `id` DESC LIMIT 1");

        $category = $mysql_model->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
        $brand = $mysql_model->GetOne("select * from `@#_brand` where `id`='$item[brandid]' LIMIT 1");

        $title = $item['title'];
        $syrs = $item['zongrenshu'] - $item['canyurenshu'];
        $item['picarr'] = unserialize($item['picarr']);


        $us = $mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='" . $itemid . "' AND `shopqishu`='" . $item['qishu'] . "'ORDER BY id DESC LIMIT 6");

        //$us2=$mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."'ORDER BY id DESC");

        $itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `qishu` DESC limit 250");

        //期数显示
        $loopqishu = '';
        $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $item['qishu'] . "期</a><b></b></li>";

        if (empty($itemlist)) {
            foreach ($itemlist as $qitem) {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/item/' . $qitem['id'] . '" class="">第' . $qitem['qishu'] . '期</a></li>';

            }
        }

        foreach ($itemlist as $qitem) {
            if ($qitem['id'] == $itemid) {

                $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $itemlist[0]['qishu'] . "期</a><b></b></li>";
            } else {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/dataserver/' . $qitem['id'] . '" >第' . $qitem['qishu'] . '期</a></li>';
            }
        }
        /*
        $gorecode=array();
        if(!empty($itemlist)){
        //查询上期的获奖者信息
            $shangqi_qishu=$itemlist[0]['qishu']-1;
            $gorecode=$this->db->GetOne("select * from `@#_member_go_record` where `shopid`='".$itemlist[0]['id']."' AND `shopqishu`='".$itemlist[0]['qishu']."' and huode!=0 ORDER BY id DESC LIMIT 1");
        }*/

        //echo "<pre>";
        //print_r($itemlist);
        //echo microt($itemlist[0]['q_end_time']);exit;
        $curtime = time();
        $shopitem = 'itemfun';

        //晒单数
        $shopid = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
        $shoplist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$shopid[sid]' limit 0,250");
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }

        if ($item['shenyurenshu'] == '0' || $item['xsjx_time'] == '0' || empty($item['q_uid'])) {

            $last_item = $us[0];

            $period_info = $mysql_model->GetOne("select * from `@#_period` order by `id` DESC LIMIT 1");

            if ($period_info['num'] >= 120) {

                $period = date("Ymd", strtotime("+1 day")) . '001';

            } else {

                $period = $period_info['period'] + 1;

            }


        }

        require_once("system/modules/mobile/jssdk.php");
        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();
        include templates("mobile/index", "item3");
    }

    public function itemsajax()
    {
        exit(' ');
        //$webname=$this->_cfg['web_name'];
        $itemid = htmlspecialchars($this->segment(4));
        $cate_band = htmlspecialchars($this->segment(5));
        $select = htmlspecialchars($this->segment(6));
        $p = htmlspecialchars($this->segment(7)) ? $this->segment(7) : 1;


        //商品购买记录
        /*
    public function buyrecords_jf(){
        $webname=$this->_cfg['web_name'];
        $key="所有参与记录";
        $itemid=intval($this->segment(4));
        $cords=$this->db->GetList("select * from `@#_member_go_jf_record` where `shopid`='$itemid'");
        if(!$cords){
            _messagemobile('页面错误!');
        }
        include templates("mobile/index","buyrecords");
    }*/


        $end = 10;
        $star = ($p - 1) * $end;
        $select_w = '';
        if ($select == 10) {
            $select_w = 'order by a.`time` desc';
        }

        if ($select == '10') {
            // $count=$this->db->GetList("select * from `@#_member_go_record` where `shopid`='$itemid' $select_w");
            $count = $this->db->GetList("select *,from_unixtime(a.time) as time2,b.headimg from `@#_member_go_record` as a,`@#_member` as b where a.uid = b.uid and `shopid`='$itemid' $select_w");
        }

        // $shoplist=$this->db->GetList("select * from `@#_member_go_record` where `shopid`='$itemid' $select_w limit $star,$end");
        $shoplist = $this->db->GetList("select *,from_unixtime(a.time) as time2,b.headimg from `@#_member_go_record` as a,`@#_member` as b where a.uid = b.uid and `shopid`='$itemid' $select_w limit $star,$end");

        foreach ($shoplist as $k => $v) {
            if ($v['username'] == '') {
                $us=$this->db->GetOne("select * from `@#_member` where `uid`=".$v['uid']);
                $shoplist[$k]['username'] = substr_replace($us['mobile'],'****',3,4);
            }           
        }

        $max_renqi_qishu = 1;
        $max_renqi_qishu_id = 1;

        if (!empty($shoplistrenqi)) {
            foreach ($shoplistrenqi as $renqikey => $renqiinfo) {
                if ($renqiinfo['qishu'] >= $max_renqi_qishu) {
                    $max_renqi_qishu = $renqiinfo['qishu'];
                    $max_renqi_qishu_id = $renqikey;
                }
            }
            $shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;
        }


        $this_time = time();
        if (count($shoplist) > 1) {
            if ($shoplist[0]['time'] > $this_time - 86400 * 3)
                $shoplist[0]['t_new_goods'] = 1;
        }
        $pagex = ceil(count($count) / $end);
        if ($p <= $pagex) {
            $shoplist[0]['page'] = $p + 1;
        }
        if ($pagex > 0) {
            $shoplist[0]['sum'] = $pagex;
        } else if ($pagex == 0) {
            $shoplist[0]['sum'] = $pagex;
        }

        echo json_encode($shoplist);
    }

    function send_mobile_zj($mobile)
    {
        $db = System::load_sys_class('model');
        $template = $db->GetOne("select * from `@#_caches` where `key` = 'template_mobile_shop'");


        $content = $template['value'];

        return _sendmobile($mobile, $content);
    }

    //商品详细
    public function itemajax()
    {
        $mysql_model = System::load_sys_class('model');
        $itemid = safe_replace($this->segment(4));
        $item = $mysql_model->GetOne("select * from `@#_shoplist` where `id`='" . $itemid . "' LIMIT 1");
        $uid = $mysql_model->GetOne("select * from `@#_member` where `uid`='" . $item['q_uid'] . "' LIMIT 1");
        // if (!empty($uid['mobile'])) {
        // 	$this->send_mobile_zj($uid['mobile']);
        // }
        $mysql_model->Query("UPDATE `@#_shoplist` SET `q_showtime`='N' where `id`= $itemid");
        $temp = array();
        $temp = $item;
        $temp['user'] = empty($uid['username']) ? substr($uid['mobile'], 0, 3) . '****' . substr($uid['mobile'], 7, 4) : $uid['username'];
        $temp['pic'] = G_UPLOAD_PATH . '/' . $item['thumb'];
        echo '<div style="width:40%; float:left;"><img width="100%" src=' . $temp['pic'] . '></div><div class="txt" style="float:left"><h6>' . $temp['title'] . '</h6><div class="zj"><span style="color: #5bb6ea;">' . $temp['user'] . "</span></div></div>";
        //echo '<div><img style="width:100%;height: 82px;" src=' . $temp['pic'] . '></div><div class="zj"><span class="timeback">已揭晓</span></div>';
        exit;

    }
    //商品详细
    public function itemajax1()
    {
        $mysql_model = System::load_sys_class('model');
        $itemid = safe_replace($this->segment(4));
        $item = $mysql_model->GetOne("select * from `@#_shoplist` where `id`='" . $itemid . "' LIMIT 1");
        $uid = $mysql_model->GetOne("select * from `@#_member` where `uid`='" . $item['q_uid'] . "' LIMIT 1");
        // if (!empty($uid['mobile'])) {
        //  $this->send_mobile_zj($uid['mobile']);
        // }
        $mysql_model->Query("UPDATE `@#_shoplist` SET `q_showtime`='N' where `id`= $itemid");
        $temp = array();
        $temp = $item;
        $temp['user'] = empty($uid['username']) ? substr($uid['mobile'], 0, 3) . '****' . substr($uid['mobile'], 7, 4) : $uid['username'];
        $temp['pic'] = G_UPLOAD_PATH . '/' . $item['thumb'];
        //echo '<div style="width:40%; float:left;"><img width="100%" src=' . $temp['pic'] . '></div><div class="txt" style="float:left"><h6>' . $temp['title'] . '</h6><div class="zj"><span style="color: #5bb6ea;">' . $temp['user'] . "</span></div></div>";
        echo '<div><img style="width:100%;height: 82px;" src=' . $temp['pic'] . '></div><div class="zj" style="text-align:center;"><span class="timeback"><img style="width:20px;display:inline;position:relative;top:3px;padding-right:4px;" src="statics/templates/yungou/images/mobile/clock.png">已揭晓</span></div>';
        exit;

    }
    //商品详细
    public function jf_item()
    {
        $webname = $this->_cfg['web_name'];
        $key = "商品详情";
        $mysql_model = System::load_sys_class('model');
        $itemid = safe_replace($this->segment(4));

        $item = $mysql_model->GetOne("select * from `@#_jf_shoplist` where `id`='" . $itemid . "' LIMIT 1");
        if (!$item) _messagemobile("商品不存在！");
        if ($item['q_end_time']) {
            header("location: " . WEB_PATH . "/mobile/mobile/dataserver/" . $item['id']);
            exit;
        }
        $sid = $item['sid'];
        $sid_code = $mysql_model->GetOne("select * from `@#_jf_shoplist` where `sid`='$sid' order by `id` DESC LIMIT 1,1");
        $sid_go_record = $mysql_model->GetOne("select * from `@#_member_go_jf_record` where `shopid`='$sid_code[sid]' and `uid`='$sid_code[q_uid]' order by `id` DESC LIMIT 1");


        $category = $mysql_model->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
        $brand = $mysql_model->GetOne("select * from `@#_jf_brand` where `id`='$item[brandid]' LIMIT 1");

        $title = $item['title'];
        $syrs = $item['zongrenshu'] - $item['canyurenshu'];
        $item['picarr'] = unserialize($item['picarr']);


        $us = $mysql_model->GetList("select * from `@#_member_go_jf_record` where `shopid`='" . $itemid . "' AND `shopqishu`='" . $item['qishu'] . "'ORDER BY id DESC LIMIT 6");

        //$us2=$mysql_model->GetList("select * from `@#_member_go_jf_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."'ORDER BY id DESC");

        $itemlist = $this->db->GetList("select * from `@#_jf_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `qishu` DESC");


        //期数显示
        $loopqishu = '';
        $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $item['qishu'] . "期</a><b></b></li>";

        if (empty($itemlist)) {
            foreach ($itemlist as $qitem) {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/jf_item/' . $qitem['id'] . '" class="">第' . $qitem['qishu'] . '期</a></li>';

            }
        }

        foreach ($itemlist as $qitem) {
            if ($qitem['id'] == $itemid) {

                $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $itemlist[0]['qishu'] . "期</a><b></b></li>";
            } else {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/dataserver/' . $qitem['id'] . '" >第' . $qitem['qishu'] . '期</a></li>';
            }
        }
        $gorecode = array();
        if (!empty($itemlist)) {
            //查询上期的获奖者信息
            $gorecode = $this->db->GetOne("select * from `@#_member_go_jf_record` where `shopid`='" . $itemlist[0]['id'] . "' AND `shopqishu`='" . $itemlist[0]['qishu'] . "' and huode!=0 ORDER BY id DESC LIMIT 1");
        }

        //echo "<pre>";
        //print_r($itemlist);
        //echo microt($itemlist[0]['q_end_time']);exit;
        $curtime = time();
        $shopitem = 'itemfun';

        //晒单数
        $shopid = $this->db->GetOne("select * from `@#_jf_shoplist` where `id`='$itemid'");
        $shoplist = $this->db->GetList("select * from `@#_jf_shoplist` where `sid`='$shopid[sid]'");
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }

        include templates("mobile/index", "jf_item");
    }

    //往期商品查看
    public function dataserver()
    {
        $webname = $this->_cfg['web_name'];
        $key = "揭晓结果";
        $itemid = intval($this->segment(4));
        $item = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'  LIMIT 1");
        $sid = $item['sid'];
        if (!$item) {
            _messagemobile("商品不存在！");
        }
        if (!$item['q_end_time']) {
            header("location: " . WEB_PATH . "/mobile/mobile/item/" . $item['id']);
            die;
        }


        if (empty($item['q_user_code'])) {
            _messagemobile("该商品正在进行中...");
        }

        //$itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' order by `qishu` DESC limit 250");

        $itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `time` DESC limit 250");

        foreach ($itemlist as $key => $val) {
            if($val['qishu'] == 1){
                $time_1 = $val['time'];
                break;
            }
        }

        //$itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null and `time`>='$time_1' order by `qishu` DESC limit 250");
        $itemlist = $this->db->GetList("select tt.* from (select * from `@#_shoplist` where `sid`='$item[sid]' and `qishu` <= '{$item['qishu']}'  order by `q_end_time` DESC limit 250) as tt group by tt.`qishu` order by `qishu` desc limit {$item['qishu']}");
        
        $category = $this->db->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
        $brand = $this->db->GetOne("select * from `@#_brand` where `id` = '$item[brandid]' LIMIT 1");
        $zjtx = $this->db->GetOne("select * from `@#_member` where `uid` = '$item[q_uid]' LIMIT 1");
        //参与中奖码
        $q_user = unserialize($item['q_user']);
        $q_user_code_len = strlen($item['q_user_code']);
        $q_user_code_arr = array();
        for ($q_i = 0; $q_i < $q_user_code_len; $q_i++) {
            $q_user_code_arr[$q_i] = substr($item['q_user_code'], $q_i, 1);
        }

        //期数显示
        $loopqishu = '';
        if (empty($itemlist[0]['q_end_time'])) {
            $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/item/' . $itemlist[0]['id'] . '">' . "第" . $itemlist[0]['qishu'] . "期</a><b></b></li>";
            array_shift($itemlist);
        }

        foreach ($itemlist as $qitem) {
            if ($qitem['id'] == $itemid) {

                $loopqishu .= '<li class="cur"><a href="javascript:;">' . "第" . $qitem['qishu'] . "期</a><b></b></li>";
            } else {
                $loopqishu .= '<li><a href="' . WEB_PATH . '/mobile/mobile/dataserver/' . $qitem['id'] . '" >第' . $qitem['qishu'] . '期</a></li>';
            }
        }

        //总参与次数
        $user_shop_number = 0;
        //用户参与时间
        $user_shop_time = 0;
        //得到参与码
        $user_shop_codes = '';

        $user_shop_list = $this->db->GetList("select * from `@#_member_go_record` where `uid`= '$item[q_uid]' and `shopid` = '$itemid' and `shopqishu` = '$item[qishu]'");
        foreach ($user_shop_list as $user_shop_n) {
            $user_shop_number += $user_shop_n['gonumber'];
            if ($user_shop_n['huode']) {
                $user_shop_time = $user_shop_n['time'];
                $user_shop_codes = $user_shop_n['goucode'];
            }
        }

        $h = abs(date("H", $item['q_end_time']));
        $i = date("i", $item['q_end_time']);
        $s = date("s", $item['q_end_time']);
        $w = substr($item['q_end_time'], 11, 3);
        $user_shop_time_add = $h . $i . $s . $w;
        $user_shop_fmod = fmod($user_shop_time_add * 100, $item['canyurenshu']);

        if ($item['q_content']) {
            $item['q_content'] = unserialize($item['q_content']);
        }
        $item['picarr'] = unserialize($item['picarr']);

        //记录
        $itemzx = $this->db->GetOne("select * from `@#_shoplist` where `sid`='$item[sid]' and `qishu`>'$item[qishu]' and `q_end_time` is null order by `qishu` DESC LIMIT 1");

        $gorecode = $this->db->GetOne("select * from `@#_member_go_record` where `shopid`='" . $itemid . "' AND `shopqishu`='" . $item['qishu'] . "' and `uid`= '$item[q_uid]' and huode!=0 LIMIT 1");
        $gorecode_count = $this->db->GetOne("select sum(gonumber) as count from `@#_member_go_record` where `shopid`='" . $itemid . "' AND `shopqishu`='" . $item['qishu'] . "' and `uid`= '$item[q_uid]'");
        $gorecode_count = $gorecode_count ? $gorecode_count['count'] : 0;

        $shopitem = 'dataserverfun';
        $curtime = time();
        //晒单数
        $shopid = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
        $shoplist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$shopid[sid]' limit 0,250");
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }
        $itemxq = 0;
        if (!empty($itemzx)) {
            $itemxq = 1;
        }
        require_once("system/modules/mobile/jssdk.php");
        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();

        $member=$this->userinfo;
        if ($member) {
            $uid = $member['uid'];
            $collectModel = $this->db->GetOne("select * from `@#_collection` where `uid` = '$uid' and `sid` = '$sid' ");
            if (empty($collectModel)) {
                $collect = 0;
            }else{
                $collect = 1;
            }
        }else{
            $collect = 0;
        }

        include templates("mobile/index", "item4");
    }




    //************************************************//
    //************************************************//
    //************************************************//

    public function tenpaysuccess()
    {
        $webname = $this->_cfg['web_name'];
        $code = _getcookie('CODE');
        if (!isset($_GET['attach'])) {
            _messagemobile("页面错误!");
            exit;
        }
        if (!$code) {
            _messagemobile("页面错误!");
            exit;
        }
        $mysql_model = System::load_sys_class('model');
        $member = $this->userinfo;
        $total_fee = $_GET['total_fee'] / 100 + $member['money'];
        $attach = $_GET['attach'];
        $sign = $_GET['sign'];
        if ($pay_result < 1) {
            $mysql_model->Query("UPDATE `@#_member` SET money='" . $total_fee . "' where uid='" . $member['uid'] . "'");
            $shop = explode("&", $attach);
            gopay($member, $shop[0], $shop[1], $shop[2]);
        }
    }

    //最新揭晓
    public function lottery()
    {
        $webname = $this->_cfg['web_name'];
        //最新揭晓
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` is not null ORDER BY `q_end_time` DESC LIMIT 0,10");


        $shoplist = $this->db->GetList("select * from `@#_shoplist` where 1 ORDER BY `canyurenshu` DESC LIMIT 4");
        $member_record = $this->db->GetList("select * from `@#_member_go_record` order by id DESC limit 6");
        $key = "最新揭晓";
        include templates("mobile/index", "lottery");
    }

    public function lottery1()
    {
        $webname = $this->_cfg['web_name'];
        //最新揭晓
        $shopqishu = $this->db->GetList("select * from `@#_shoplist` where `q_end_time` is not null ORDER BY `q_end_time` DESC LIMIT 0,10");


        $shoplist = $this->db->GetList("select * from `@#_shoplist` where 1 ORDER BY `canyurenshu` DESC LIMIT 4");
        $member_record = $this->db->GetList("select * from `@#_member_go_record` order by id DESC limit 6");
        $key = "最新揭晓";
        include templates("mobile/index", "lottery1");
    }

    //商品购买记录
    public function buyrecords()
    {
        $webname = $this->_cfg['web_name'];
        $key = "所有参与记录";
        $itemid = intval($this->segment(4));
        $cords = $this->db->GetList("select * from `@#_member_go_record` where `shopid`='$itemid'order by `time` desc");
        $co = count($cords);
        include templates("mobile/index", "buyrecords");
    }

    //图文详细
    public function goodsdesc()
    {
        $webname = $this->_cfg['web_name'];
        $key = "图文详情";
        $itemid = intval($this->segment(4));
        $desc = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
        if (!$desc) {
            _messagemobile('页面错误!');
        }
        include templates("mobile/index", "goodsdesc");
    }

    //晒单评论
    public function goodspost()
    {
        $webname = $this->_cfg['web_name'];
        $key = "晒单评论";
        $itemid = intval($this->segment(4));
        $shoplist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$itemid'");
        if (!$shoplist) {
            _messagemobile('页面错误!');
        }
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id) order by `sd_id` DESC");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }
        include templates("mobile/index", "goodspost");

    }


    //商品购买记录
    public function buyrecords_jf()
    {
        $webname = $this->_cfg['web_name'];
        $key = "所有参与记录";
        $itemid = intval($this->segment(4));
        $cords = $this->db->GetList("select * from `@#_member_go_jf_record` where `shopid`='$itemid'");
        if (!$cords) {
            _messagemobile('页面错误!');
        }
        include templates("mobile/index", "buyrecords");
    }

    //图文详细
    public function goodsdesc_jf()
    {
        $webname = $this->_cfg['web_name'];
        $key = "图文详情";
        $itemid = intval($this->segment(4));
        $desc = $this->db->GetOne("select * from `@#_jf_shoplist` where `id`='$itemid'");
        if (!$desc) {
            _messagemobile('页面错误!');
        }
        include templates("mobile/index", "goodsdesc");
    }

    //晒单评论
    public function goodspost_jf()
    {
        $webname = $this->_cfg['web_name'];
        $key = "晒单评论";
        $itemid = intval($this->segment(4));
        $shoplist = $this->db->GetList("select * from `@#_jf_shoplist` where `sid`='$itemid'");
        if (!$shoplist) {
            _messagemobile('页面错误!');
        }
        $shop = '';
        foreach ($shoplist as $list) {
            $shop .= $list['id'] . ',';
        }
        $id = trim($shop, ',');
        if ($id) {
            $shaidan = $this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id) order by `sd_id` DESC");
            $sum = 0;
            foreach ($shaidan as $sd) {
                $shaidan_hueifu = $this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
                $sum = $sum + count($shaidan_hueifu);
            }
        } else {
            $shaidan = 0;
            $sum = 0;
        }
        include templates("mobile/index", "goodspost");
    }

    public function calResult()
    {
        $itemid = intval($this->segment(4));
        $item = $this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid' LIMIT 1");

        $h = abs(date("H", $item['q_end_time']));
        $i = date("i", $item['q_end_time']);
        $s = date("s", $item['q_end_time']);
        $w = substr($item['q_end_time'], 11, 3);
        $user_shop_time_add = $h . $i . $s . $w;
        $user_shop_fmod = fmod($user_shop_time_add * 100, $item['canyurenshu']);

        if ($item['q_content']) {
            $item['q_content'] = unserialize($item['q_content']);
            $user_shop_time_add = $item['q_counttime'];
            $user_shop_fmod = fmod($item['q_counttime'], $item['canyurenshu']);
        }

        $item['picarr'] = unserialize($item['picarr']);

        include templates("mobile/index", "calResult");
    }

    //新手指南
    public function about()
    {
        $webname = $this->_cfg['web_name'];
        $category = $this->db->GetOne("select * from `@#_category` where `parentid`='1' and `name`='新手指南'");

        $article = $this->db->GetList("select * from `@#_article` where `cateid`='$category[cateid]'");

        include templates("mobile/index", "about");
    }


    //用户服务协议
    public function terms()
    {
        $webname = $this->_cfg['web_name'];
        $category = $this->db->GetOne("select * from `@#_category` where `parentid`='1' and `name`='新手指南'");

        $article = $this->db->GetOne("select * from `@#_article` where `cateid`='$category[cateid]' and `title`='服务协议' ");

        include templates("mobile/system", "terms");
    }

    //访问个人主页
    public function userindex()
    {
        $webname = $this->_cfg['web_name'];
        $uid = safe_replace($this->segment(4));
        $user = $this->userinfo;
        //$uid=intval($this->segment(4))-1000000000;
        //获取个人资料
        $member = $this->db->GetOne("select * from `@#_member` where `uid`='$uid'");
        if (!empty($member['headimg'])) {
            $member['img'] = $member['headimg'];
        } else {
            $member['img'] = G_UPLOAD_PATH . '/' . $member['img'];
        }
        //获取参与等级  参与新手  参与小将==
        $memberdj = $this->db->GetList("select * from `@#_member_group`");

        $jingyan = $member['jingyan'];
        if (!empty($memberdj)) {
            foreach ($memberdj as $key => $val) {
                if ($jingyan >= $val['jingyan_start'] && $jingyan <= $val['jingyan_end']) {
                    $member['yungoudj'] = $val['name'];
                }
            }
        }
        if(empty($member['yungoudj'])){
            $member['yungoudj'] = "将军";
        }
        include templates("mobile/index", "userindex");
    }
    public function userrecharge999(){
        $m = htmlspecialchars($this->segment(4));
        include templates("mobile/user","recharge999"); 
    }
    public function alipaycode(){
        $info=$this->db->GetOne("SELECT * FROM `@#_wxpay_locat_config` where `id`='31' ");
        echo $info['values'];
    }
    /*
    //今日揭晓
    public function autolottery(){
        $w_jinri_time = strtotime(date('Y-m-d'));
        $w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));

        $jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_jinri_time' and `xsjx_time` < '$w_minri_time' order by xsjx_time limit 0,3 ");
        include templates("mobile/index","buyrecords");

    }

    //明日揭晓
    public function nextautolottery(){
        $w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
        $w_houri_time = strtotime(date('Y-m-d',strtotime("+2 day")));

        $jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_minri_time' and `xsjx_time` < '$w_houri_time' order by xsjx_time limit 0,3 ");
    }*/
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======

    public function fenlei_ajax(){
        $data = $this->db->GetList("select `cateid`,`name`,`pic_url` from `@#_category` where `model`='1' and `parentid` = '0' order by `order` desc");
        foreach ($data as $key => $val) {
            $total = $this->db->GetCount("SELECT COUNT(*) FROM `@#_shoplist` WHERE `cateid`='$val[cateid]' and `q_uid` is null");
            $data[$key]['total'] = $total;
        }
        echo json_encode($data);
    }
>>>>>>> 5.12
>>>>>>> four
>>>>>>> xxxx
}

?>