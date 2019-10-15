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

class BIRTHDAYS_CLASS_EventHandler
{

    public function __construct()
    {
        
    }

    public function addUserlistData( BASE_CLASS_EventCollector $event )
    {
        $event->add(
            array(
                'label' => OW::getLanguage()->text('base', 'user_list_menu_item_birthdays'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'birthdays')),
                'iconClass' => 'ow_ic_calendar',
                'key' => 'birthdays',
                'order' => 5,
                'dataProvider' => array(BIRTHDAYS_BOL_Service::getInstance(), 'getUserListData')
            )
        );
    }

    public function privacyAddAction( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => 'birthdays_view_my_birthdays',
            'pluginKey' => 'birthdays',
            'label' => $language->text('birthdays', 'privacy_action_view_my_birthday'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    public function onTodayBirthday( OW_Event $e )
    {
        $params = $e->getParams();
        $userIds = $params['userIdList'];
        $usersData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);

        $actionParams = array(
            'entityType' => 'birthday',
            'pluginKey' => 'birthdays',
            'replace' => true
        );
        $actionData = array(
            'time' => time(),     
        );
        
        $birthdays = BOL_QuestionService::getInstance()->getQuestionData($userIds, array('birthdate'));
            
        foreach ( $userIds as $userId )
        {
            $userEmbed = '<a href="' . $usersData[$userId]['url'] . '">' . $usersData[$userId]['title'] . '</a>';
            $actionParams['userId'] = $userId;
            $actionParams['entityId'] = $userId;
            $actionData['line'] = array('key' => "birthdays+feed_item_line", 'vars' => array('user' => $userEmbed)); 
            $actionData['content'] = '<div class="ow_user_list_picture">' .OW::getThemeManager()->processDecorator('avatar_item', $usersData[$userId]) . '</div>';
            $actionData['view'] = array( 'iconClass' => 'ow_ic_birthday' );
            
            if ( !empty($birthdays[$userId]['birthdate']) )
            {
                $actionData['birthdate'] = $birthdays[$userId]['birthdate'];
                $actionData['userData'] = $usersData[$userId];
            }
            
            $event = new OW_Event('feed.action', $actionParams, $actionData);

            OW::getEventManager()->trigger($event);

            BOL_AuthorizationService::getInstance()->trackActionForUser($userId, 'birthdays', 'birthday');
        }
    }

    public function onNewsfeedItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $content = $event->getData();

        
        if ( !empty($params['action']['entityType']) && !empty($params['action']['pluginKey']) && $params['action']['entityType'] == 'birthday' && $params['action']['pluginKey'] == 'birthdays' )
        {
            $html = '<div class="ow_user_list_data"></div>';
            
            if ( !empty($content['birthdate']) && !empty($content['userData']) )
            {
                $date = UTIL_DateTime::parseDate($content['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $birthdate = UTIL_DateTime::formatBirthdate($date['year'], $date['month'], $date['day']);
                
                if ( $date['month'] == intval(date('m')) )
                {
                    if ( intval(date('d')) + 1 == intval($date['day']) )
                    {
                        $birthdate = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . OW::getLanguage()->text('base', 'date_time_tomorrow') . '</a>';
                    }
                    else if ( intval(date('d')) == intval($date['day']) )
                    {
                        $birthdate = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . OW::getLanguage()->text('base', 'date_time_today') . '</span>';
                    }
                }
                
                $html = '<div class="ow_user_list_data">
                            <a href="'.$content['userData']["url"].'">'.$content['userData']["title"].'</a><br><span style="font-weight:normal;" class="ow_small">'. OW::getLanguage()->text('birthdays', 'birthday') . ' '. $birthdate . '</span>                
                         </div>';
            }
            
            $userId = $params['action']['entityId'];
            $usersData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
            $content['content'] = '<div class="ow_user_list_picture">' .OW::getThemeManager()->processDecorator('avatar_item', $usersData[$userId]) . '</div>';
            $content['content'] .= $html;
            $content['content'] = '<div class="clearfix">'.$content['content'].'</div>';
            $content['view'] = array( 'iconClass' => 'ow_ic_birthday' );
                        
            $event->setData($content);
        }
    }
    
    public function onChangePrivacy( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];

        $actionList = $params['actionList'];

        if ( empty($actionList['birthdays_view_my_birthdays']) )
        {
            return;
        }

        $privacyDto = BIRTHDAYS_BOL_Service::getInstance()->findBirthdayPrivacyByUserId($userId);

        if ( empty($privacyDto) )
        {
            $privacyDto = new BIRTHDAYS_BOL_Privacy();
            $privacyDto->userId = $userId;
        }

        $privacyDto->privacy = $actionList['birthdays_view_my_birthdays'];

        BIRTHDAYS_BOL_Service::getInstance()->saveBirthdayPrivacy($privacyDto);
    }

    public function onUserUnregister( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];
        BIRTHDAYS_BOL_Service::getInstance()->deleteBirthdayPrivacyByUserId($userId);
    }

    public function feedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('birthdays', 'feed_content_label'),
            'activity' => '*:birthday'
        ));
    }

    public function feedComment( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != 'birthday' )
        {
            return;
        }

        $userId = (int) $params['entityId'];

        if ( $userId == $params['userId'] )
        {
            $string = OW::getLanguage()->text('birthdays', 'feed_activity_self_birthday_string');
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            $string = OW::getLanguage()->text('birthdays', 'feed_activity_birthday_string', array(
                    'user' => $userEmbed
                ));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'comment',
                'activityId' => $params['commentId'],
                'entityId' => $userId,
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'birthdays'
                ), array(
                'string' => $string,
                'line' => null
            )));

        if ( $userId != $params['userId'] )
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($params['userId']);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($params['userId']);
            
            $urlContent = OW::getEventManager()->call('feed.get_item_permalink', array('entityId' => $userId, 'entityType' => $params['entityType']));
            
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']), true, true, false, false);
            $contentImage = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']), true, true, false, false);
            $avatar = $avatars[$params['userId']];

            $event = new OW_Event('notifications.add', array(
                    'pluginKey' => 'birtdays',
                    'entityType' => $params['entityType'],
                    'entityId' => $params['entityId'],
                    'action' => 'comment',
                    'userId' => $params['entityId'],
                    'time' => time()
                    ), array(
                    'avatar' => $avatar,
                    'string' => array(
                        'key' => 'birthdays+console_notification_comment',
                        'vars' => array(
                            'userName' => $userName,
                            'userUrl' => $userUrl
                        )
                    ),
                    'content' => strip_tags($data['message']),
                    'url' => $urlContent
                ));



            OW::getEventManager()->trigger($event);
        }
    }

    public function feedLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'birthday' )
        {
            return;
        }

        $userId = (int) $params['entityId'];

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        $string = OW::getLanguage()->text('birthdays', 'feed_activity_birthday_string_like', array('user' => $userEmbed));

        if ( $userId == OW::getUser()->getId() )
        {
            $string = OW::getLanguage()->text('birthdays', 'feed_activity_birthday_string_like_own', array('user' => $userEmbed));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'like',
                'activityId' => $params['userId'],
                'entityId' => $userId,
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'birthdays'
                ), array(
                'string' => $string,
                'line' => null
            )));

        if ( $userId != OW::getUser()->getId() )
        {
            $userName = BOL_UserService::getInstance()->getDisplayName(OW::getUser()->getId());
            $userUrl = BOL_UserService::getInstance()->getUserUrl(OW::getUser()->getId());

            $contentUrl = OW::getEventManager()->call('feed.get_item_permalink', array('entityId' => $userId, 'entityType' => $params['entityType']));
            
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']), true, true, false, false);
            $avatar = $avatars[$params['userId']];

            $event = new OW_Event('notifications.add', array(
                    'pluginKey' => 'birtdays',
                    'entityType' => $params['entityType'],
                    'entityId' => $params['entityId'],
                    'action' => 'like',
                    'userId' => $params['entityId'],
                    'time' => time()
                    ), array(
                    'avatar' => $avatar,
                    'string' => array(
                        'key' => 'birthdays+console_notification_like',
                        'vars' => array(
                            'userName' => $userName,
                            'userUrl' => $userUrl
                        )
                    ),
                    'url' => $contentUrl,
                    //'contentImage' => $contentImage
                ));

            OW::getEventManager()->trigger($event);
        }
    }

    public function notificationActions( OW_Event $event )
    {
        $event->add(array(
            'section' => 'birthdays',
            'action' => 'comment',
            'sectionIcon' => 'ow_ic_calendar',
            'sectionLabel' => OW::getLanguage()->text('birthdays', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('birthdays', 'email_notifications_setting_status_comment'),
            'selected' => true
        ));

        $event->add(array(
            'section' => 'birthdays',
            'action' => 'like',
            'sectionIcon' => 'ow_ic_calendar',
            'sectionLabel' => OW::getLanguage()->text('birthdays', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('birthdays', 'email_notifications_setting_status_like'),
            'selected' => true
        ));
    }    

    public function genericInit()
    {
        OW::getEventManager()->bind('base.add_user_list', array($this, 'addUserlistData'));
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'privacyAddAction'));
        OW::getEventManager()->bind('birthdays.today_birthday_user_list', array($this, 'onTodayBirthday'));
        OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($this, 'onChangePrivacy'));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($this, 'feedCollectConfigurableActivity'));
        OW::getEventManager()->bind('feed.after_comment_add', array($this, 'feedComment'));
        OW::getEventManager()->bind('feed.after_like_added', array($this, 'feedLike'));
        OW::getEventManager()->bind('notifications.collect_actions', array($this, 'notificationActions'));
        //OW::getEventManager()->bind('base.after_avatar_update', array($this, 'onAfterAvatarUpdate'));

        $credits = new BIRTHDAYS_CLASS_Credits();
        OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
        
        OW::getEventManager()->bind('feed.on_item_render', array($this, "onNewsfeedItemRender"));
    }

    public function init()
    {
        
    }
}
