<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BIRTHDAYS_CMP_FriendBirthdaysWidget extends BASE_CMP_UsersWidget
{
    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        if( !OW::getUser()->isAuthenticated() || !OW::getEventManager()->call('plugin.friends') )
        {
            $this->setVisible(false);
            return array();
        }
        
        $count = (int)$params->customParamList['count'];

        $language = OW::getLanguage();
        $service = BIRTHDAYS_BOL_Service::getInstance();

        $friendsIdList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => OW::getUser()->getId()));
        $users = $service->findListByBirthdayPeriod(date('Y-m-d'), date('Y-m-d', strtotime('+7 day')), 0, $count, $friendsIdList, array('everybody','friends_only'));
        
        if ( (!$params->customizeMode && empty($users) ) )
        {
            $this->setVisible(false);
        }        

        return array(
            'birthdays_this_week' => array(
                'menu-label' => "",//$language->text('birthdays', 'user_list_menu_item_birthdays'),
                'userIds' => array( 'key' => 'birthdays_this_week', 'list' => $this->getIdList($users) ),
                'toolbar' => false, //TODO complete
                'menu_active' => true
            )
        );
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => 'Count',
            'value' => '9'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('birthdays', 'friends_widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
    
    protected function getUsersCmp( $list )
    {
        $key = !empty($list['key']) ? $list['key'] : null;
        $idList = !empty($list['list']) ? $list['list'] : array();
        
        return new BIRTHDAYS_CMP_AvatarUserList($idList, $key);
    }
}