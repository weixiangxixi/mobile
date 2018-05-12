<?php
defined('G_IN_SYSTEM')or exit('No permission resources.');
System::load_app_class('memberbase',null,'no');
System::load_app_fun('user','go');
System::load_app_fun('my','go');
System::load_sys_fun('send');
class activity extends memberbase {
	public function __construct(){
		parent::__construct();
		$this->db = System::load_sys_class("model");
	}

	public function zhuanpan(){
		//分享部分代码
        require_once("system/modules/mobile/jssdk.php");

        $wechat = $this->db->GetOne("select * from `@#_wechat_config` where id = 1");

        $jssdk = new JSSDK($wechat['appid'], $wechat['appsecret']);

        $signPackage = $jssdk->GetSignPackage();

		include templates("mobile/activity","zhuanpan");
	}

	public function zhuanpandata(){
		// echo '{"score":[{"item":"1","name":"\u82f9\u679cX 256G","score":"0","type":"1"},{"item":"2","name":"2g\u91d1\u6761","score":"0","type":"1"},{"item":"3","name":"\u52a0\u591a\u5b9d","score":"0","type":"1"},{"item":"4","name":"688\u798f\u5206","score":"688","type":"2"},{"item":"5","name":"iPad min4","score":"0","type":"1"},{"item":"6","name":"\u5145\u7535\u5b9d","score":"0","type":"1"},{"item":"7","name":"\u798f\u4e34\u95e8\u5927\u7c73","score":"0","type":"1"},{"item":"8","name":"188\u798f\u5206","score":"188","type":"2"}]}';
		// exit();
		$shop[0]['item'] = 1;
		$shop[0]['name'] = '苹果X 256G';
		$shop[0]['score'] = 0;
		$shop[0]['type'] = 1;

		$shop[1]['item'] = 2;
		$shop[1]['name'] = '188福分';
		$shop[1]['score'] = 188;
		$shop[1]['type'] = 2;

		$shop[2]['item'] = 3;
		$shop[2]['name'] = '加多宝';
		$shop[2]['score'] = 0;
		$shop[2]['type'] = 1;

		$shop[3]['item'] = 4;
		$shop[3]['name'] = '288福分';
		$shop[3]['score'] = 288;
		$shop[3]['type'] = 2;

		$shop[4]['item'] = 5;
		$shop[4]['name'] = '福临门大米';
		$shop[4]['score'] = 0;
		$shop[4]['type'] = 1;

		$shop[5]['item'] = 6;
		$shop[5]['name'] = '388福分';
		$shop[5]['score'] = 388;
		$shop[5]['type'] = 2;


		$shop[6]['item'] = 7;
		$shop[6]['name'] = '充电宝';
		$shop[6]['score'] = 0;
		$shop[6]['type'] = 1;

		$shop[7]['item'] = 8;
		$shop[7]['name'] = '688福分';
		$shop[7]['score'] = 688;
		$shop[7]['type'] = 2;

		$list['score'] = $shop;
		echo json_encode($list);
	}

	public function zhuanpanclick(){
		//echo '{"success":false,"noCharge":1}';
		$end = 0;
		$user = $this->userinfo;

		if (!$user) {
			echo '{"success":false,"login":0}';
			exit();
		}

		if ($end == 1) {
			echo '{"success":false,"end":1}';
			exit();
		}

		$sa = 2;
		if ($sa ==0) {
			echo '{"success":false,"has":0,"tip":"抽奖次数用完"}';
			exit();
		}elseif ($sa == 1) {
			echo '{"success":false,"has":0,"tip":"请分享到朋友圈"}';
			exit();
		}

		$ch = true;
		if (!$ch) {
			echo '{"success":false,"has":0,"tip":"请先充值"}';
			exit();
		}

		echo '{"code":0,"item":"6","numcode":123,"score":388,"type":2}';
	}

	public function zhuanpanInfo(){
		echo '{"success":1,"lucky_mun":100}';
	}

	public function updateshare(){
		echo '{"share":2}';
	}

	public function getPersonprize(){
		include templates("mobile/activity","zhuanpan_huojiang");
	}
}