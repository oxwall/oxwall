<?php

class BASE_MCMP_ConnectButtonList extends BASE_CMP_ConnectButtonList
{
    public function __construct() 
    {
        parent::__construct();
        
        $tpl = OW::getPluginManager()->getPlugin("base")->getMobileCmpViewDir() . "connect_button_list.html";
        $this->setTemplate($tpl);
    }
}