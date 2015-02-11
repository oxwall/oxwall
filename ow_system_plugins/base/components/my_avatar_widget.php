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
 * About Me widget
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_MyAvatarWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $avatarService = BOL_AvatarService::getInstance();
        $userId = OW::getUser()->getId();

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('avatar', $avatars[$userId]);

        $event = new BASE_CLASS_EventCollector('base.on_avatar_toolbar_collect', array(
            'userId' => $userId
        ));

        OW::getEventManager()->trigger($event);

        $toolbarItems = $event->getData();
        $tplToolbarItems = array();
        foreach ( $toolbarItems as $item )
        {
            if ( empty($item['title']) || empty($item['url']) || empty($item['iconClass']) )
            {
                continue;
            }

            $order = empty($item['order']) ? count($tplToolbarItems) + 1 : (int) $item['order'];

            if ( !empty($tplToolbarItems[$order]) )
            {
                $order = count($tplToolbarItems) + 1;
            }

            $tplToolbarItems[$order] = $item;
        }

        ksort($tplToolbarItems);

        $this->assign('toolbarItems', array_values($tplToolbarItems));
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_AVALIABLE_SECTIONS => array(BOL_ComponentService::SECTION_SIDEBAR),
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'my_avatar_widget'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => 'ow_ic_user'
        );
    }
}