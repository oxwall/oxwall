<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_UserListWidget extends BASE_CMP_UsersWidget
{
    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $count = (int) $params->customParamList['count'];
        $language = OW::getLanguage();
        $userService = BOL_UserService::getInstance();

        $toolbar = array(
            'latest' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest'))
            ),
            'online' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online'))
            ),
            'featured' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured'))
            )
        );

        $latestUsersCount = $userService->count();

        if ( $latestUsersCount > $count )
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar['latest']));
        }

        $resultList = array(
            'latest' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_latest'),
                'menu_active' => true,
                'userIds' => $this->getIdList($userService->findList(0, $count)),
                'toolbar' => ( $latestUsersCount > $count ? array($toolbar['latest']) : false ),
            ),
            'online' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_online'),
                'userIds' => $this->getIdList($userService->findOnlineList(0, $count)),
                'toolbar' => ( $userService->countOnline() > $count ? array($toolbar['online']) : false ),
            ));

        $featuredIdLIst = $this->getIdList($userService->findFeaturedList(0, $count));

        if ( !empty($featuredIdLIst) )
        {
            $resultList['featured'] = array(
                    'menu-label' => $language->text('base', 'user_list_menu_item_featured'),
                    'userIds' => $featuredIdLIst,
                    'toolbar' => ( $userService->countFeatured() > $count ? array($toolbar['featured']) : false ),
                );
        }

        $event = new OW_Event('base.userList.onToolbarReady', array(), $resultList);
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('base', 'user_list_widget_settings_count'),
            'value' => '9'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'user_list_widget_settings_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}