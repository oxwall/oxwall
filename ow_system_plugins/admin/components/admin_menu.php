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
 * Admin menu class. Works with all admin menu types.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_AdminMenu extends BASE_CMP_Menu
{
    /**
     * @var boolean
     */
    private $active = false;

    /**
     * Constructor.
     * 
     * @param array $itemsList
     */
    public function __construct( $itemsList )
    {
        parent::__construct();
        $this->setMenuItems(BOL_NavigationService::getInstance()->getMenuItems($itemsList));
        // set default template
        $this->setTemplate(null);
    }

    public function render($subMenuClass = null)
    {
        $this->assign('isActive', $this->active);
        $this->assign('subMenuClass', $subMenuClass);

        return parent::render();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        /* @var $menuItem BASE_MenuItem */
        foreach ( $this->menuItems as $menuItem )
        {
            if ( $menuItem->isActive() )
            {
                $this->active = true;
            }
        }
    }

    /**
     * Returns first element.
     *
     * @return BASE_MenuItem
     */
    public function getFirstElement()
    {
        usort($this->menuItems, array(BOL_NavigationService::getInstance(), 'sortObjectListByAsc'));
        return $this->menuItems[0];
    }

    /**
     * Returns menu elements count.
     *
     * @return integer
     */
    public function getElementsCount()
    {
        return count($this->menuItems);
    }

    /**
     * Checks if menu has active elements.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    public function setCategory($category)
    {
        $this->assign('category', $category);
    }
}