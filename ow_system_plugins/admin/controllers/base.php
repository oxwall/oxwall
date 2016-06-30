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
 * Admin index controller class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Base extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $this->setPageHeading(OW::getLanguage()->text('admin', 'admin_dashboard'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');
        $this->assign('version', OW::getConfig()->getValue('base', 'soft_version'));
        $this->assign('build', OW::getConfig()->getValue('base', 'soft_build'));
    }

    /**
     * Generate sitemap
     */
    public function generateSitemap()
    {
        do
        {
            BOL_SeoService::getInstance()->generateSitemap();
        }
        while ( !(int) OW::getConfig()->getValue('base', 'seo_sitemap_build_finished') );

        exit;
    }

    public function dashboard( $paramList )
    {
        $this->setPageHeading(OW::getLanguage()->text('admin', 'admin_dashboard'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = BOL_ComponentAdminService::PLASE_ADMIN_DASHBOARD;
        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';
        
        $service = BOL_ComponentAdminService::getInstance();
        $schemeList = $service->findSchemeList();
        $state = $service->findCache($place);

        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $service->findPlaceComponentList($place);
            $state['defaultPositions'] = $service->findAllPositionList($place);
            $state['defaultSettings'] = $service->findAllSettingList();
            $state['defaultScheme'] = (array) $service->findSchemeByPlace($place);

            $service->saveCache($place, $state);
        }

        if ( empty($state['defaultScheme']) && !empty($schemeList) )
        {
            $state['defaultScheme'] = reset($schemeList);
        }

        $componentPanel = new ADMIN_CMP_DashboardWidgetPage($place, $state['defaultComponents'], $customize);
        $componentPanel->allowCustomize(true);

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('admin_dashboard_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('admin_dashboard')
        );

        $componentPanel->customizeControlCunfigure($customizeUrls['customize'], $customizeUrls['normal']);

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($state['defaultPositions']);
        $componentPanel->setSettingList($state['defaultSettings']);
        $componentPanel->setScheme($state['defaultScheme']);

        $this->addComponent('componentPanel', $componentPanel);
    }
}