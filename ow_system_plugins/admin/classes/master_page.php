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
 * Master page class for admin controllers.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.classes
 * @since 1.0
 */
class ADMIN_CLASS_MasterPage extends OW_MasterPage
{
    private $menuCmps = array();

    /**
     * @see OW_MasterPage::init()
     */
    protected function init()
    {
        $language = OW::getLanguage();

        OW::getThemeManager()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByKey(BOL_ThemeService::DEFAULT_THEME));

        $menuTypes = array(
            BOL_NavigationService::MENU_TYPE_ADMIN, BOL_NavigationService::MENU_TYPE_APPEARANCE,
            BOL_NavigationService::MENU_TYPE_PAGES, BOL_NavigationService::MENU_TYPE_PLUGINS, BOL_NavigationService::MENU_TYPE_SETTINGS,
            BOL_NavigationService::MENU_TYPE_USERS, BOL_NavigationService::MENU_TYPE_MOBILE
        );

        $menuItems = BOL_NavigationService::getInstance()->findMenuItemsForMenuList($menuTypes);

        if ( defined('OW_PLUGIN_XP') )
        {
            foreach ( $menuItems as $key1 => $menuType )
            {
                foreach ( $menuType as $key2 => $menuItem )
                {
                    if ( in_array($menuItem['key'], array('sidebar_menu_plugins_add', 'sidebar_menu_themes_add')) )
                    {
                        unset($menuItems[$key1][$key2]);
                    }
                }
            }
        }

        $menuDataArray = array(
            'menu_admin' => BOL_NavigationService::MENU_TYPE_ADMIN,
            'menu_users' => BOL_NavigationService::MENU_TYPE_USERS,
            'menu_settings' => BOL_NavigationService::MENU_TYPE_SETTINGS,
            'menu_appearance' => BOL_NavigationService::MENU_TYPE_APPEARANCE,
            'menu_pages' => BOL_NavigationService::MENU_TYPE_PAGES,
            'menu_plugins' => BOL_NavigationService::MENU_TYPE_PLUGINS,
            'menu_mobile' => BOL_NavigationService::MENU_TYPE_MOBILE
        );

        foreach ( $menuDataArray as $key => $value )
        {
            $this->menuCmps[$key] = new ADMIN_CMP_AdminMenu($menuItems[$value]);
            $this->addMenu($value, $this->menuCmps[$key]);
        }

        $event = new ADMIN_CLASS_NotificationCollector('admin.add_admin_notification');
        OW::getEventManager()->trigger($event);
        $this->assign('notifications', $event->getData());

        // platform info        
        $event = new OW_Event('admin.get_soft_version_text');
        OW_EventManager::getInstance()->trigger($event);

        $verString = $event->getData();

        if ( empty($verString) )
        {
            $verString = OW::getLanguage()->text('admin', 'soft_version', array('version' => OW::getConfig()->getValue('base', 'soft_version'), 'build' => OW::getConfig()->getValue('base', 'soft_build')));
        }

        $this->assign('version', OW::getConfig()->getValue('base', 'soft_version'));
        $this->assign('build', OW::getConfig()->getValue('base', 'soft_build'));
        $this->assign('softVersion', $verString);
        
        $checkUrl = OW::getRouter()->urlFor("ADMIN_CTRL_Storage", "checkUpdates");
        $params = array(BOL_StorageService::URI_VAR_BACK_URI => urlencode(OW::getRequest()->getRequestUri()));
        $this->assign("checkUpdatesUrl", OW::getRequest()->buildUrlQueryString($checkUrl, $params));
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $language = OW::getLanguage();
        OW::getDocument()->addBodyClass('ow_admin_area');
        $this->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_ADMIN));

        $arrayToAssign = array();
        srand(time());

        /* @var $value ADMIN_CMP_AdminMenu */
        foreach ( $this->menuCmps as $key => $value )
        {
            //check if there are any items in the menu
            if ( $value->getElementsCount() <= 0 )
            {
                continue;
            }

            $id = UTIL_HtmlTag::generateAutoId("mi");

            $value->setCategory($key);
            $value->onBeforeRender();

            $menuItem = $value->getFirstElement();

            $arrayToAssign[$key] = array('id' => $id, 'firstLink' => $menuItem->getUrl(), 'key' => $key, 'isActive' => $value->isActive(), 'label' => $language->text('admin', 'sidebar_' . $key), 'sub_menu' => ( $value->getElementsCount() < 2 ) ? '' : $value->render(), 'active_sub_menu' => ( $value->getElementsCount() < 2 ) ? '' : $value->render('ow_admin_submenu'));
        }

        $this->assign('menuArr', $arrayToAssign);
    }

    public function deleteMenu( $name )
    {
        if ( isset($this->menus[$name]) )
        {
            unset($this->menus[$name]);
        }

        if ( isset($this->menuCmps[$name]) )
        {
            unset($this->menuCmps[$name]);
        }
    }
}
