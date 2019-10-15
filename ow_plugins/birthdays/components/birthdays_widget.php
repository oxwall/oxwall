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
 * @author Aybat Duyshokov <duyshokov@gmail.com>, Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BIRTHDAYS_CMP_BirthdaysWidget extends BASE_CMP_UsersWidget
{
    const SHOW_ONLY_TODAY = 'today';
    const SHOW_TODAY_AND_THIS_WEEK = 'this_week';

    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $this->forceDisplayMenu(true);

        $count = (int) $params->customParamList['count'];
        $displayType = trim($params->customParamList['show']);

        $language = OW::getLanguage();
        $service = BIRTHDAYS_BOL_Service::getInstance();

        $toolbar = array(
            'birthdays_today' => array(
                array(
                    'label' => OW::getLanguage()->text('base', 'view_all'),
                    'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'birthdays'))
                )
            ),
            'birthdays_this_week' => array(
                array(
                    'label' => OW::getLanguage()->text('base', 'view_all'),
                    'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'birthdays'))
                )
            )
        );
        
        $dataArray = array();

        switch ( $displayType )
        {
            case self::SHOW_TODAY_AND_THIS_WEEK:

                $birthdaysCount = $service->countByBirthdayPeriod(date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+7 day')), null, array('everybody'));

                if ( $birthdaysCount > 0 )
                {
                    $dataArray = array(
                        'birthdays_this_week' => array(
                            'menu-label' => $language->text('birthdays', 'user_list_menu_item_birthdays_upcoming'),
                            'userIds' => array( 'key' => 'birthdays_this_week', 'list' => $this->getIdList($service->findListByBirthdayPeriod(date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+7 day')), 0, $count, null, array('everybody'))) ),
                            'toolbar' => ( $birthdaysCount > $count ? $toolbar['birthdays_this_week'] : false ),
                            'menu_active' => true
                        )
                    );

                    if ( $birthdaysCount > $count )
                    {
                        $this->setSettingValue(self::SETTING_TOOLBAR,$toolbar['birthdays_this_week']);
                    }
                }

            case self::SHOW_ONLY_TODAY:

                $todayBirthdaysCount = $service->countByBirthdayPeriod(date('Y-m-d'), date('Y-m-d'), null, array('everybody'));

                if ( $todayBirthdaysCount > 0 )
                {
                    $dataArray['birthdays_today'] = array(
                        'menu-label' => $language->text('birthdays', 'user_list_menu_item_birthdays_today'),
                        'userIds' => array( 'key' => 'birthdays_today', 'list' => $this->getIdList($service->findListByBirthdayPeriod(date('Y-m-d'), date('Y-m-d'), 0, $count, null, array('everybody'))) ),
                        'toolbar' => ( $todayBirthdaysCount > $count ?  $toolbar['birthdays_today'] : false ),
                        'menu_active' => true
                    );

                    if ( !empty($dataArray['birthdays_this_week']['menu_active']) )
                    {
                        $dataArray['birthdays_this_week']['menu_active'] = false;
                    }

                    $dataArray = array_reverse($dataArray);

                    if ( $todayBirthdaysCount > $count )
                    {
                        $this->setSettingValue(self::SETTING_TOOLBAR, $toolbar['birthdays_today']);
                    }
                }

                break;
        }

        if ( empty($dataArray) )
        {
            $this->setVisible(false);
        }

        return $dataArray;
    }

    //default settings
    public static function getSettingList()
    {
        $language = OW::getLanguage();

        $settingList = array(
            'count' => array(
                'presentation' => self::PRESENTATION_NUMBER,
                'label' => $language->text('birthdays', 'widget_setting_count_label'),
                'value' => '12'
            ),
            'show' => array(
                'presentation' => self::PRESENTATION_SELECT,
                'label' => $language->text('birthdays', 'widget_setting_show_label'),
                'optionList' => array(
                    self::SHOW_ONLY_TODAY => $language->text('birthdays', 'widget_setting_value_' . self::SHOW_ONLY_TODAY),
                    self::SHOW_TODAY_AND_THIS_WEEK => $language->text('birthdays', 'widget_setting_value_' . self::SHOW_TODAY_AND_THIS_WEEK)
                ),
                'value' => self::SHOW_ONLY_TODAY
            )
        );

        return $settingList;
    }

    // set title and toolbar
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('birthdays', 'widget_title'),
            self::SETTING_ICON => self::ICON_CALENDAR,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    // set who allowed to see widget
    public static function getAccess()
    {
        /*
          ACCESS_GUEST - for guests only,
          ACCESS_ALL  - everyone,
          ACCESS_MEMBER - only for registered users )
         */
        return self::ACCESS_ALL;
    }
    
    protected function getUsersCmp( $list )
    {
        $key = !empty($list['key']) ? $list['key'] : null;
        $idList = !empty($list['list']) ? $list['list'] : array();
        
        return new BIRTHDAYS_CMP_AvatarUserList($idList, $key);
    }
}