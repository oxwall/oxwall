<?php

class BASE_CMP_MyProfileConsoleItem extends BASE_CMP_ConsoleDropdownMenu
{
    const KEY = "my_profile_console_item";

    protected $userName;
    protected $userId;

    public function __construct()
    {
        $this->userName = OW::getUser()->getUserObject()->getUsername();
        $this->userId = OW::getUser()->getId();

        $label = BOL_UserService::getInstance()->getDisplayName($this->userId);

        parent::__construct($label, self::KEY);

        $url = OW::getRouter()->urlForRoute('base_user_profile', array(
            'username' => $this->userName
        ));

        $this->setUrl($url);
        $this->collectItems();
    }

    protected function collectItems()
    {
        $language = OW::getLanguage();
        $router = OW::getRouter();

        $this->addItem('main', array(
            'label' => $language->text('base', 'console_item_label_profile'),
            'url' => $router->urlForRoute('base_user_profile', array(
                'username' => $this->userName
            ))
        ));

        $this->addItem('main', array(
            'label' => $language->text('base', 'edit_index'),
            'url' => $router->urlForRoute('base_edit')
        ));

        $this->addItem('main', array(
            'label' => $language->text('base', 'preference_index'),
            'url' => $router->urlForRoute('base_preference_index')
        ));

        if ( OW::getUser()->isAdmin() || BOL_AuthorizationService::getInstance()->isModerator() )
        {
            $this->addItem('main', array(
                'label' => $language->text('base', 'moderation_tools'),
                'url' => $router->urlForRoute('base.moderation_tools')
            ));
        }

        $this->addItem('foot', array(
            'label' => $language->text('base', 'console_item_label_sign_out'),
            'url' => $router->urlForRoute('base_sign_out')
        ));

        $addItemsEvent = new BASE_CLASS_EventCollector('base.add_main_console_item');
        OW::getEventManager()->trigger($addItemsEvent);

        $addItems = $addItemsEvent->getData();

        foreach ( $addItems as $addItem )
        {
            if ( !empty($addItem['label']) && !empty($addItem['url']) )
            {
                $this->addItem('main', array(
                    'label' => $addItem['label'],
                    'url' => $addItem['url'])
                );
            }
        }
    }
}