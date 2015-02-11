<?php

class INSTALL_CMP_FeedBack extends INSTALL_Component
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('msgs', INSTALL::getFeedback()->getMessages());
    }
}
