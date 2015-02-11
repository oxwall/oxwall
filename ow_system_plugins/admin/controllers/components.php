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
class ADMIN_CTRL_Components extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_ComponentService
     *
     */
    private $componentsService;

    public function __construct()
    {
        parent::__construct();

        $this->componentsService = BOL_ComponentAdminService::getInstance();
    }

    public function init()
    {
        $basePluginDir = OW::getPluginManager()->getPlugin('BASE')->getRootDir();

        $controllersTemplate = OW::getPluginManager()->getPlugin('ADMIN')->getCtrlViewDir() . 'drag_and_drop_components.html';
        $this->setTemplate($controllersTemplate);
    }

    private function action( $place, $componentTemplate )
    {
        $dbSettings = $this->componentsService->findAllSettingList();

        $dbPositions = $this->componentsService->findAllPositionList($place);

        $dbComponents = $this->componentsService->findPlaceComponentList($place);
        $activeScheme = $this->componentsService->findSchemeByPlace($place);
        $schemeList = $this->componentsService->findSchemeList();

        if ( empty($activeScheme) && !empty($schemeList) )
        {
            $activeScheme = reset($schemeList);
        }

        $componentPanel = new ADMIN_CMP_DragAndDropAdminPanel($place, $dbComponents, $componentTemplate);
        $componentPanel->setPositionList($dbPositions);
        $componentPanel->setSettingList($dbSettings);
        $componentPanel->setSchemeList($schemeList);
        if ( !empty($activeScheme) )
        {
            $componentPanel->setScheme($activeScheme);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }

    public function dashboard()
    {
        $this->setPageHeading(OW::getLanguage()->text('base', 'widgets_admin_dashboard_heading'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = BOL_ComponentAdminService::PLACE_DASHBOARD;
        $this->action($place, 'drag_and_drop_panel');
    }

    public function profile()
    {
        $this->setPageHeading(OW::getLanguage()->text('base', 'widgets_admin_profile_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $place = BOL_ComponentAdminService::PLACE_PROFILE;
        $this->action($place, 'drag_and_drop_panel');
    }
}