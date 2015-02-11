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
 * Widgets admin panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class BASE_MCTRL_WidgetPanel extends OW_MobileActionController
{
    private function initDragAndDrop( $place, $entityId = null, $componentTemplate = "widget_panel" )
    {
        $widgetService = BOL_MobileWidgetService::getInstance();
        
        $state = $widgetService->findCache($place);
        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $widgetService->findPlaceComponentList($place);
            $state['defaultPositions'] = $widgetService->findAllPositionList($place);
            $state['defaultSettings'] = $widgetService->findAllSettingList();

            $widgetService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];

        $componentPanel = new BASE_MCMP_WidgetPanel($place, $entityId, $defaultComponents, $componentTemplate);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);

        $this->assign('dnd', $componentPanel->render());
    }

    public function dashboard()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(OW::getLanguage()->text('base', 'dashboard_heading'));
        $this->setPageHeadingIconClass('ow_ic_house');

        $place = BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD;
        $this->initDragAndDrop($place, OW::getUser()->getId());
    }

    public function profile( $paramList )
    {
        $userService = BOL_UserService::getInstance();
        /* @var $userDao BOL_User */
        $userDto = $userService->findByUsername($paramList['username']);

        if ( $userDto === null )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthorized('base', 'view_profile') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
            $this->assign('permissionMessage', $status['msg']);
            return;
        }

        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $userDto->id,
            'viewerId' => OW::getUser()->getId()
        );
        
        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            throw new RedirectException(OW::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $displayName)));
        }

        $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)));
        $this->setPageHeadingIconClass('ow_ic_user');
        
        $profileHeader = OW::getClassInstance("BASE_MCMP_ProfileHeader", $userDto->id);
        $this->addComponent("header", $profileHeader);
        
        //Profile Info
        $displayNameQuestion = OW::getConfig()->getValue('base', 'display_name_question');
        $profileInfo = OW::getClassInstance("BASE_MCMP_ProfileInfo", $userDto->id, false, array(
            $displayNameQuestion, "birthdate"
        ));
        $this->addComponent("info", $profileInfo);
        $this->addComponent('contentMenu', OW::getClassInstance("BASE_MCMP_ProfileContentMenu", $userDto->id));
        $this->addComponent('about', OW::getClassInstance("BASE_MCMP_ProfileAbout", $userDto->id, 80));
        
        $place = BOL_MobileWidgetService::PLACE_MOBILE_PROFILE;
        $this->initDragAndDrop($place, $userDto->id);
    }
    
    public function index()
    {
        $place = BOL_MobileWidgetService::PLACE_MOBILE_INDEX;
        $this->initDragAndDrop($place);
    }
}