<?php
defined('G_IN_SYSTEM') or exit('No permission resources.');
System::load_app_class('base', 'member', 'no');
System::load_app_fun('my');
System::load_app_fun('user');
System::load_sys_fun('user');
System::load_sys_fun("send");

class my extends base
{

    public function __construct()
    {
        parent::__construct();
        $this->db = System::load_sys_class('model');
        //_freshen();
    }

    public function clean1(){
        $now = time();
        $to = strtotime(date('Y-m-d',time())) + 60 * 60 * 12;  
        
        $list = $this->db->GetList("select * from `@#_appoint` where `status`= 0");
        if (empty($list)) {
            exit();
        }

        if ($now == $to) {
            $rs = $this->db->Query("delete FROM `@#_appoint` where `status`= 0 ");
            if ($rs) {
                echo 'success\n';
            }else{
                echo 'error\n';
            }
        }else{
            echo 'doing\n';
        }
        exit();
    }

    public function test(){
        $sid = htmlspecialchars($this->segment(4));
        $k = 0;
        
        $shoplist = $this->db->GetList("select * from `@#_member_go_record` where `shopid`=".$sid);
        
        foreach ($shoplist as $k => $v) {
            $user = $this->db->GetOne("select * from `@#_member` where `uid`=".$v['uid']);
            echo $user['auto_user']."<br>";
        }
        
    }
    
    public function sb(){
        include("/www/wwwroot/m.yyygcs.vip/system/modules/mobile/page.class.php");
        $page = new Page(100,10);
        echo $page->fpage();
    }
}