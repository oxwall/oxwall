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
 * User avatar widget
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_UserAvatarWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $avatarService = BOL_AvatarService::getInstance();

        $viewerId = OW::getUser()->getId();

        $userId = $paramObj->additionalParamList['entityId'];

        $owner = false;
        
        if ( $viewerId == $userId )
        {
            $owner = true;
            

            $label = OW::getLanguage()->text('base', 'avatar_change');

            $script =
            '$("#avatar-change").click(function(){
                document.avatarFloatBox = OW.ajaxFloatBox(
                    "BASE_CMP_AvatarChange",
                    { params : { step : 1 } },
                    { width : 749, title: ' . json_encode($label) . '}
                );
            });

            OW.bind("base.avatar_cropped", function(data){
                if ( data.bigUrl != undefined ) {
                    $("#avatar_console_image").css({ "background-image" : "url(" + data.bigUrl + ")" });
                }

                if ( data.modearationStatus )
                {
                    if ( data.modearationStatus != "active" )
                    {
                        $(".ow_avatar_pending_approval").show();
                    }
                    else 
                    {
                        $(".ow_avatar_pending_approval").hide();
                    }
                }
            });
            ';

            OW::getDocument()->addOnloadScript($script);
        }
        
        $isModerator = (OW::getUser()->isAuthorized('base') || OW::getUser()->isAdmin());
        
        $this->assign('owner', $owner);
        $this->assign('isModerator', $isModerator);
        
        $avatarDto = $avatarService->findByUserId($userId);
        
        $this->assign('hasAvatar', !empty($avatarDto));
        $moderation = false;

        // approve button
        if ( $isModerator && !empty($avatarDto) && $avatarDto->status == BOL_ContentService::STATUS_APPROVAL )
        {
            $moderation = true;
            
            $script = ' window.avartar_arrove_request = false;
            $("#avatar-approve").click(function(){
            
                if ( window.avartar_arrove_request == true )
                {
                    return;
                }
                
                window.avartar_arrove_request = true;
                
                $.ajax({
                    "type": "POST",
                    "url": '.json_encode(OW::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder')).',
                    "data": {
                        \'ajaxFunc\' : \'ajaxAvatarApprove\',
                        \'avatarId\' : '.((int)$avatarDto->id).'
                    },
                    "success": function(data){
                        if ( data.result == true )
                        {
                            if ( data.message )
                            {
                                OW.info(data.message);
                            }
                            else
                            {
                                OW.info('.json_encode(OW::getLanguage()->text('base', 'avatar_has_been_approved')).');
                            }
                            
                            $("#avatar-approve").remove();
                            $(".ow_avatar_pending_approval").hide();
                        }
                        else
                        {
                            if ( data.error )
                            {
                                OW.info(data.error);
                            }
                        }
                    },
                    "complete": function(){
                        window.avartar_arrove_request = false;
                    },
                    "dataType": "json"
                });
            }); ';

            OW::getDocument()->addOnloadScript($script);
        }
        
        $avatar = $avatarService->getAvatarUrl($userId, 2, null, false, !($moderation || $owner));
        $this->assign('avatar', $avatar ? $avatar : $avatarService->getDefaultAvatarUrl(2));
        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $userService = BOL_UserService::getInstance();

        $showPresence = true;
        // Check privacy permissions 
        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            $showPresence = false;
        }

        $this->assign('isUserOnline', ($userService->findOnlineUserById($userId) && $showPresence));
        $this->assign('userId', $userId);

        $this->assign('avatarSize', OW::getConfig()->getValue('base', 'avatar_big_size'));
        
        $this->assign('moderation', $moderation);
        $this->assign('avatarDto', $avatarDto);
        
        OW::getLanguage()->addKeyForJs('base', 'avatar_has_been_approved');
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'avatar_widget'),
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => self::ICON_PICTURE,
            self::SETTING_FREEZE => true
        );
    }
}