<?php

class BASE_CMP_InvitationItem extends BASE_CMP_ConsoleListIpcItem
{
    public function __construct()
    {
        parent::__construct();

        $plugin = OW::getPluginManager()->getPlugin('BASE');
        $this->setTemplate($plugin->getCmpViewDir() . 'console_list_ipc_item.html');

        $this->addClass('ow_invitation_item ow_cursor_default');
    }
}