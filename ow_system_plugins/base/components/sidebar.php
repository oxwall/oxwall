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
 * Page Sidebar
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Sidebar extends OW_Component
{
    private $componentList = array();
    private $settingList = array();
    private $positionList = array();

    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $service;

	public function __construct()
	{
            parent::__construct();

            $this->service = BOL_ComponentAdminService::getInstance();
            $this->fetchFromCache();

            OW_ViewRenderer::getInstance()->registerFunction('sb_component', array($this, 'tplComponent'));
	}

	private function fetchFromCache()
	{
            $place = BOL_ComponentAdminService::PLACE_INDEX;

	    $state = $this->service->findCache($place);

            if ( empty($state) )
            {
                $this->componentList = $this->service->findSectionComponentList($place, 'sidebar');
                $this->positionList = $this->service->findSectionPositionList($place, 'sidebar');
                $this->settingList = $this->service->findSettingListByComponentPlaceList($this->componentList);

                return;
            }

	    foreach ( $state['defaultPositions'] as $key => $item )
	    {
	        if ($item['section'] == 'sidebar')
	        {
                $this->positionList[$key] = $item;
                $this->componentList[$key] = $state['defaultComponents'][$key];
                if( !empty($state['defaultSettings'][$key]) )
                {
                    $this->settingList[$key] = $state['defaultSettings'][$key];
                }
	        }
	    }
	}

	public function render()
	{
        $tplComponentList = array();
        foreach ( $this->componentList as $item )
        {
            $position = $this->positionList[$item['uniqName']];
            $tplComponentList[$position['order']] = $item;
        }

        ksort($tplComponentList);

        $this->assign('componentList', $tplComponentList);

        return parent::render();
	}

    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];

        $componentPlace = $this->componentList[$uniqName];

        $viewInstance = new BASE_CMP_DragAndDropItem($uniqName);
        $viewInstance->setSettingList( empty( $this->settingList[$uniqName] ) ? array() : $this->settingList[$uniqName] );
        $viewInstance->setContentComponentClass( $componentPlace['className'] );

        return $viewInstance->renderView();
    }
}