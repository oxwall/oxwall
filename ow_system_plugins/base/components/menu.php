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
 * Base menu component class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Menu extends OW_Component
{
    /**
     * @var array
     */
    protected $menuItems = array();
    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param array $menuItems
     * @param string $template
     */
    public function __construct( $menuItems = array() )
    {
        parent::__construct();

        $this->setMenuItems($menuItems);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'menu.html');
    }

    /**
     * @return array
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * @param array $menuItems
     */
    public function setMenuItems( $menuItems )
    {
        if ( empty($menuItems) )
        {
            return;
        }
        
        foreach ( $menuItems as $item )
        {
            $this->addElement($item);
        }
    }

    /**
     * Adds menu item.
     *
     * @param BASE_MenuItem $menuItem
     */
    public function addElement( BASE_MenuItem $menuItem )
    {
        $this->menuItems[] = $menuItem;
    }

    /**
     * Returns menu item for provided key.
     *
     * @param string $prefix
     * @param string $key
     * @return BASE_MenuItem
     */
    public function getElement( $key, $prefix = null )
    {
        /* @var $value BASE_MenuItem */
        foreach ( $this->menuItems as $value )
        {
            if ( $value->getKey() === trim($key) && ( $prefix === null || $value->getPrefix() === trim($prefix) ) )
            {
                return $value;
            }
        }

        return null;
    }

    /**
     * Deletes menu element by key.
     *
     * @param string $prefix
     * @param string $key
     */
    public function removeElement( $key, $prefix = null )
    {
        /* @var $value BASE_MenuItem */
        foreach ( $this->menuItems as $itemKey => $value )
        {
            if ( $value->getKey() === trim($key) && ( $prefix === null || $value->getPrefix() === trim($prefix) ) )
            {
                unset($this->menuItems[$itemKey]);
            }
        }
    }

    /**
     * Deactivates all menu elements.
     */
    public function deactivateElements()
    {
        /* @var $value BASE_MenuItem */
        foreach ( $this->menuItems as $itemKey => $value )
        {
            $value->setActive(false);
        }
    }

    protected function getItemViewData( BASE_MenuItem $menuItem )
    {
        return array(
            'label' => $menuItem->getLabel(),
            'url' => $menuItem->getUrl(),
            'class' => $menuItem->getPrefix() . '_' . $menuItem->getKey(),
            'iconClass' => $menuItem->getIconClass(),
            'active' => $menuItem->isActive(),
            'new_window' => $menuItem->getNewWindow(),
            'prefix' => $menuItem->getPrefix(),
            'key' => $menuItem->getKey()
        );
    }


    /**
     * @see OW_Renderable::onBeforeRender()
     *
     */
    public function onBeforeRender()
    {
        $arrayToAssign = array();

        usort($this->menuItems, array(BOL_NavigationService::getInstance(), 'sortObjectListByAsc'));

        /* @var $menuItem BASE_MenuItem */
        foreach ( $this->menuItems as $menuItem )
        {
            $menuItem->activate(OW::getRouter()->getBaseUrl() . OW::getRequest()->getRequestUri());
            $arrayToAssign[] = $this->getItemViewData($menuItem);
        }

        $this->assign('class', 'ow_' . OW_Autoload::getInstance()->classToFilename(get_class($this), false));
        $this->assign('data', $arrayToAssign);
    }
}

/**
 * Base menu element class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MenuItem
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var string
     */
    private $key;
    /**
     * @var integer
     */
    private $order;
    /**
     * @var boolean
     */
    private $newWindow;
    /**
     * @var string
     */
    private $iconClass;
    /**
     * @var boolean
     */
    private $active = false;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @param string $iconClass
     * @return BASE_MenuItem
     */
    public function setIconClass( $iconClass )
    {
        $this->iconClass = $iconClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return $this->iconClass;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $key
     * @return BASE_MenuItem
     */
    public function setKey( $key )
    {
        $this->key = trim($key);
        return $this;
    }

    /**
     * @param string $label
     * @return BASE_MenuItem
     */
    public function setLabel( $label )
    {
        $this->label = trim($label);
        return $this;
    }

    /**
     * @param integer $order
     * @return BASE_MenuItem
     */
    public function setOrder( $order )
    {
        $this->order = (int) $order;
        return $this;
    }

    /**
     * @param string $url
     * @return BASE_MenuItem
     */
    public function setUrl( $url )
    {
        $this->url = trim($url);
        return $this;
    }

    /**
     * @return boolean
     */
    public function getNewWindow()
    {
        return $this->newWindow;
    }

    /**
     * @param boolean $newWindow
     * @return BASE_MenuItem
     */
    public function setNewWindow( $newWindow )
    {
        $this->newWindow = $newWindow;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string  $prefix
     * @return BASE_MenuItem
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setActive( $active )
    {
        $this->active = (bool) $active;
        return $this;
    }

    /**
     * @param string $url
     * @return boolean
     */
    public function activate( $url )
    {
        if ( UTIL_String::removeFirstAndLastSlashes($this->url) === UTIL_String::removeFirstAndLastSlashes($url) )
        {
            $this->setActive(true);
        }
    }
}