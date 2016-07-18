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
 * Widget panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_ComponentPanel extends OW_ActionController
{
    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $componentAdminService;
    /**
     *
     * @var BOL_ComponentEntityService
     */
    private $componentEntityService;

    public function __construct()
    {
        $this->componentAdminService = BOL_ComponentAdminService::getInstance();
        $this->componentEntityService = BOL_ComponentEntityService::getInstance();

        $controllersTemplate = OW::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'component_panel.html';
        $this->setTemplate($controllersTemplate);
    }

    public function render()
    {
        return parent::render();
    }

    private function action( $place, $userId, $customizeMode, $customizeRouts, $componentTemplate, $responderController = null )
    {
        $userCustomizeAllowed = (bool) $this->componentAdminService->findPlace($place)->editableByUser;

        if ( !$userCustomizeAllowed && $customizeMode )
        {
            $this->redirect($customizeRouts['normal']);
        }

        $schemeList = $this->componentAdminService->findSchemeList();

        $state = $this->componentAdminService->findCache($place);
        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $this->componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $this->componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $this->componentAdminService->findAllSettingList();
            $state['defaultScheme'] = (array) $this->componentAdminService->findSchemeByPlace($place);

            $this->componentAdminService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( $userCustomizeAllowed )
        {
            $userCache = $this->componentEntityService->findEntityCache($place, $userId);

            if ( empty($userCache) )
            {
                $userCache = array();
                $userCache['userComponents'] = $this->componentEntityService->findPlaceComponentList($place, $userId);
                $userCache['userSettings'] = $this->componentEntityService->findAllSettingList($userId);
                $userCache['userPositions'] = $this->componentEntityService->findAllPositionList($place, $userId);

                $this->componentEntityService->saveEntityCache($place, $userId, $userCache);
            }

            $userComponents = $userCache['userComponents'];
            $userSettings = $userCache['userSettings'];
            $userPositions = $userCache['userPositions'];
        }
        else
        {
            $userComponents = array();
            $userSettings = array();
            $userPositions = array();
        }

        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        $componentPanel = new BASE_CMP_DragAndDropEntityPanel($place, $userId, $defaultComponents, $customizeMode, $componentTemplate, $responderController);
        $componentPanel->setAdditionalSettingList(array(
            'entityId' => $userId,
            'entity' => 'user'
        ));

        if ( !empty($customizeRouts) )
        {
            $componentPanel->allowCustomize($userCustomizeAllowed);
            $componentPanel->customizeControlCunfigure($customizeRouts['customize'], $customizeRouts['normal']);
        }

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /*
         * This feature was disabled for users
         * if ( !empty($userScheme) )
          {
          $componentPanel->setEntityScheme($userScheme);
          } */

        if ( !empty($userComponents) )
        {
            $componentPanel->setEntityComponentList($userComponents);
        }

        if ( !empty($userPositions) )
        {
            $componentPanel->setEntityPositionList($userPositions);
        }

        if ( !empty($userSettings) )
        {
            $componentPanel->setEntitySettingList($userSettings);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }

    public function dashboard( $paramList )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(OW::getLanguage()->text('base', 'dashboard_heading'));
        $this->setPageHeadingIconClass('ow_ic_house');

        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';

        $place = BOL_ComponentService::PLACE_DASHBOARD;

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('base_member_dashboard_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('base_member_dashboard')
        );

        $userId = OW::getUser()->getId();

        $this->action($place, $userId, $customize, $customizeUrls, $template);

        $controllersTemplate = OW::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'widget_panel_dashboard.html';

        $this->setTemplate($controllersTemplate);

        $this->assign('isAdmin', OW::getUser()->isAdmin());
        $this->assign('isModerator', BOL_AuthorizationService::getInstance()->isModerator());
        
        $this->setDocumentKey('base_user_dashboard');
    }

    public function myProfile( $paramList )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName(OW::getUser()->getId());
        $this->setPageTitle(OW::getLanguage()->text('base', 'my_profile_title', array('username' => $displayName)));
        $this->setPageHeading(OW::getLanguage()->text('base', 'my_profile_heading', array('username' => $displayName)));

        $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'profile_view_description', array('username' => $displayName)));

        $event = new OW_Event('base.on_get_user_status', array('userId' => OW::getUser()->getId()));
        OW::getEventManager()->trigger($event);
        $status = $event->getData();

        if ( $status !== null )
        {
            $heading = OW::getLanguage()->text('base', 'user_page_heading_status', array('status' => $status, 'username' => $displayName));
            $this->setPageHeading($heading);
        }
        else
        {
            $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)));
        }

        $this->setPageHeadingIconClass('ow_ic_user');

        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';

        if ( $customize )
        {
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'main_menu_my_profile');
        }

        $place = BOL_ComponentService::PLACE_PROFILE;

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('base_member_profile_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('base_member_profile')
        );

        $userId = OW::getUser()->getId();

        $cmp = OW::getClassInstance("BASE_CMP_ProfileActionToolbar", $userId);
        $this->addComponent('profileActionToolbar', $cmp);

        $this->action($place, $userId, $customize, $customizeUrls, $template);
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

        if ( $userDto->id == OW::getUser()->getId() )
        {
            $this->myProfile($paramList);

            return;
        }

        if ( !OW::getUser()->isAuthorized('base', 'view_profile') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
            throw new AuthorizationException($status['msg']);
        }

        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $userDto->id,
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            $exception = new RedirectException(OW::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $userDto->username)));

            throw $exception;
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'profile_view_description', array('username' => $displayName)));

        $event = new OW_Event('base.on_get_user_status', array('userId' => $userDto->id));
        OW::getEventManager()->trigger($event);
        $status = $event->getData();

        $headingSuffix = "";
        
        if ( !BOL_UserService::getInstance()->isApproved($userDto->id) )
        {
            $headingSuffix = ' <span class="ow_remark ow_small">(' . OW::getLanguage()->text("base", "pending_approval") . ')</span>';
        }
        
        if ( $status !== null )
        {
            $heading = OW::getLanguage()->text('base', 'user_page_heading_status', array('status' => $status, 'username' => $displayName));
            $this->setPageHeading($heading . $headingSuffix);
        }
        else
        {
            $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)) . $headingSuffix);
        }

        $this->setPageHeadingIconClass('ow_ic_user');

        $this->assign('isSuspended', $userService->isSuspended($userDto->id));
        $this->assign('isAdminViewer', OW::getUser()->isAuthorized('base'));

        $place = BOL_ComponentService::PLACE_PROFILE;

        $cmp = OW::getClassInstance("BASE_CMP_ProfileActionToolbar", $userDto->id);
        $this->addComponent('profileActionToolbar', $cmp);

        $template = 'drag_and_drop_entity_panel';

        $this->action($place, $userDto->id, false, array(), $template);

        $controllersTemplate = OW::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'widget_panel_profile.html';
        $this->setTemplate($controllersTemplate);

        $this->setDocumentKey('base_profile_page');

        $vars = BOL_SeoService::getInstance()->getUserMetaInfo($userDto);

        // set meta info
        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userPage",
            "title" => "base+meta_title_user_page",
            "description" => "base+meta_desc_user_page",
            "keywords" => "base+meta_keywords_user_page",
            "vars" => $vars,
            "image" => BOL_AvatarService::getInstance()->getAvatarUrl($userDto->getId(), 2)
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function privacyMyProfileNoPermission( $params )
    {
        $username = $params['username'];

        $user = BOL_UserService::getInstance()->findByUsername($username);

        if ( $user === null )
        {
            throw new Redirect404Exception();
        }

        if ( OW::getSession()->isKeySet('privacyRedirectExceptionMessage') )
        {
            $this->assign('message', OW::getSession()->get('privacyRedirectExceptionMessage'));
        }

        $avatarService = BOL_AvatarService::getInstance();

        $viewerId = OW::getUser()->getId();

        $userId = $user->id;

        $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => BOL_UserService::getInstance()->getDisplayName($userId))));
        $this->setPageHeadingIconClass('ow_ic_user');

        $avatar = $avatarService->getAvatarUrl($userId, 2);
        $this->assign('avatar', $avatar ? $avatar : $avatarService->getDefaultAvatarUrl(2));
        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $userService = BOL_UserService::getInstance();

        $this->assign('username', $username);

        $this->assign('avatarSize', OW::getConfig()->getValue('base', 'avatar_big_size'));
        
        $cmp = OW::getClassInstance("BASE_CMP_ProfileActionToolbar", $userId);
        $this->addComponent('profileActionToolbar', $cmp);

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'user_view_privacy_no_permission.html');
    }

    public function index( $paramList )
    {
        $place = BOL_ComponentService::PLACE_INDEX;
        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';
        $allowCustomize = OW::getUser()->isAdmin();
        $template = 'drag_and_drop_index';

        if ( $customize )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                throw new AuthenticateException();
            }

            if ( !$allowCustomize )
            {
                $this->redirect(OW::getRouter()->uriForRoute('base_index'));
            }
        }

        if ( $allowCustomize )
        {
            $template = $customize ? 'drag_and_drop_index_customize' : 'drag_and_drop_index';

            if ( $customize )
            {
                OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'main_menu_index');
            }
        }

        if ( $customize )
        {
            $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('dndindex');
            OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);
            
            $this->setDocumentKey('base_index_page_customize');
        }
        else
        {
            $this->setDocumentKey('base_index_page');
        }
        
        $schemeList = $this->componentAdminService->findSchemeList();
        $state = $this->componentAdminService->findCache($place);

        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $this->componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $this->componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $this->componentAdminService->findAllSettingList();
            $state['defaultScheme'] = (array) $this->componentAdminService->findSchemeByPlace($place);

            $this->componentAdminService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        $componentPanel = new BASE_CMP_DragAndDropIndex($place, $defaultComponents, $customize, $template);
        $componentPanel->allowCustomize($allowCustomize);

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('base_index_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('base_index')
        );

        $componentPanel->customizeControlCunfigure($customizeUrls['customize'], $customizeUrls['normal']);

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /* $themeName = OW_Config::getInstance()->getValue('base', 'selectedTheme');
          $sidebarPosition = BOL_ThemeService::getInstance()->findThemeByName($themeName)->getSidebarPosition(); */

        $sidebarPosition = OW::getThemeManager()->getCurrentTheme()->getDto()->getSidebarPosition();
        $componentPanel->setSidebarPosition($sidebarPosition);

        $componentPanel->assign('adminPluginsUrl', OW::getRouter()->urlForRoute('admin_plugins_installed'));

        $this->addComponent('componentPanel', $componentPanel);

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "index",
            "title" => "base+meta_title_index",
            "description" => "base+meta_desc_index",
            "keywords" => "base+meta_keywords_index"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function ajaxSaveAboutMe()
    {

        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        echo json_encode(BASE_CMP_AboutMeWidget::processForm($_POST));

        exit();
    }
}