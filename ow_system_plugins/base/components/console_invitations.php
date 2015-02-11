<?php

class BASE_CMP_ConsoleInvitations extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        $label = OW::getLanguage()->text('base', 'console_item_invitations_label');

        parent::__construct( $label, 'invitation' );

        $this->addClass('ow_invitation_list');
    }

    public function initJs()
    {
        parent::initJs();

        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('OW.Invitation = new OW_Invitation({$key}, {$params});', array(
            'key' => $this->getKey(),
            'params' => array(
                'rsp' => OW::getRouter()->urlFor('BASE_CTRL_Invitation', 'ajax')
            )
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
}