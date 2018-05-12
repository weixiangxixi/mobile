<?php
defined('G_IN_SYSTEM') or exit('No permission resources.');
System::load_app_class('base', 'member', 'no');
System::load_app_fun('my');
System::load_app_fun('user');
System::load_sys_fun('user');
System::load_sys_fun("send");

class wabao extends base
{

    public function __construct()
    {
        parent::__construct();
        $this->db = System::load_sys_class('model');
    }

    public function init(){
    	include templates("mobile/wabao","index");
    } 
}