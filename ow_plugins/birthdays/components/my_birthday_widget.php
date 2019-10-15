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
class BIRTHDAYS_CMP_MyBirthdayWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = BIRTHDAYS_BOL_Service::getInstance();
        $user = BOL_UserService::getInstance()->findUserById($params->additionalParamList['entityId']);
        
        if( $user === null )
        {
            $this->setVisible(false);
            return;
        }

        $eventParams =  array(
                'action' => 'birthdays_view_my_birthdays',
                'ownerId' => $user->getId(),
                'viewerId' => OW::getUser()->getId()
            );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {
            $this->setVisible(false);
            return;
        }
        
        $result = $service->findListByBirthdayPeriod(date('Y-m-d'), date('Y-m-d', strtotime('+7 day')), 0, 1, array( $user->getId()));
        $isComingSoon = !empty($result);
        $this->assign('ballonGreenSrc', OW::getPluginManager()->getPlugin('birthdays')->getStaticUrl().'img/' . 'ballon-lime-green.png');
        $data = BOL_QuestionService::getInstance()->getQuestionData(array( $user->getId() ), array('birthdate'));        

        if ( (!$isComingSoon && !$params->customizeMode) || !array_key_exists('birthdate', $data[$user->getId()]) )
        {
            $this->setVisible(false);
            return;
        }        
        
        $birtdate = $data[$user->getId()]['birthdate']; 
        $dateInfo = UTIL_DateTime::parseDate($birtdate, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
        $label = '';

        if ( $dateInfo['day'] == date('d') )
        {
            $label = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . OW::getLanguage()->text('base', 'date_time_today') . '</span> ';
        }
        else if ( $dateInfo['day'] == date('d') + 1 )
        {
            $label = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . OW::getLanguage()->text('base', 'date_time_tomorrow') . '</span> ';
        }
        else
        {
            $label = '<span class="ow_small">' . UTIL_DateTime::formatBirthdate($dateInfo['year'], $dateInfo['month'], $dateInfo['day']) . '</span>';
        }
        
        $this->assign('label', $label);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('birthdays', 'my_widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_FREEZE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}