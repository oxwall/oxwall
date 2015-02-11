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
 * @author Aybat Duyshokov <duyshokov@gmail.com>, Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
abstract class BASE_CMP_UsersWidget extends BASE_CLASS_Widget
{
    protected $forceDisplayMenu = false;
    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'users_widget.html');
        $randId = UTIL_HtmlTag::generateAutoId('base_users_widget');
        $this->assign('widgetId', $randId);

        $data = $this->getData($params);
        $menuItems = array();
        $dataToAssign = array();

        if ( !empty($data) )
        {
            foreach ( $data as $key => $item )
            {
                $contId = "{$randId}_users_widget_{$key}";
                $toolbarId = (!empty($item['toolbar']) ? "{$randId}_toolbar_{$key}" : false );

                $menuItems[$key] = array(
                    'label' => $item['menu-label'],
                    'id' => "{$randId}_users_widget_menu_{$key}",
                    'contId' => $contId,
                    'active' => !empty($item['menu_active']),
                    'toolbarId' => $toolbarId
                );

                $usersCmp = $this->getUsersCmp($item['userIds']);

                $dataToAssign[$key] = array(
                    'users' => $usersCmp->render(),
                    'active' => !empty($item['menu_active']),
                    'toolbar' => (!empty($item['toolbar']) ? $item['toolbar'] : array() ),
                    'toolbarId' => $toolbarId,
                    'contId' => $contId
                );
            }
        }
        $this->assign('data', $dataToAssign);

        $displayMenu = true;

        if( count($data) == 1 && !$this->forceDisplayMenu )
        {
            $displayMenu = false;
        }

        if ( !$params->customizeMode && ( count($data) != 1 || $this->forceDisplayMenu ) )
        {
            $menu = $this->getMenuCmp($menuItems);

            if ( !empty($menu) )
            {
                $this->addComponent('menu', $menu);
            }
        }
    }

    abstract public function getData( BASE_CLASS_WidgetParameter $params );

    protected function getIdList( $users )
    {
        $resultArray = array();

        if ( $users )
        {
            foreach ( $users as $user )
            {
                $resultArray[] = $user->getId();
            }
        }

        return $resultArray;
    }

    protected function forceDisplayMenu( $value )
    {
        $this->forceDisplayMenu = (boolean) $value;
    }

    protected function getUsersCmp( $list )
    {
        return new BASE_CMP_AvatarUserList($list);
    }

    protected function getMenuCmp( $menuItems )
    {
        return new BASE_CMP_WidgetMenu($menuItems);
    }
}