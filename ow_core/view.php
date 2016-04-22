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
 * Base class for renderable elements. Allows to assign vars and compile HTML using template engine.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.8.3
 */
class OW_View
{
    /**
     * List of assigned vars
     *
     * @var array
     */
    protected $assignedVars = array();

    /**
     * Template path
     *
     * @var string
     */
    protected $template;

    /**
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var array
     */
    protected static $devInfo = array();

    /**
     * @var boolean
     */
    private static $collectDevInfo = false;

    /**
     * Getter for renderedClasses static property
     * 
     * @return array
     */
    public static function getDevInfo()
    {
        return self::$devInfo;
    }

    /**
     * Sets developer mode
     * 
     * @param boolean $collect 
     */
    public static function setCollectDevInfo( $collect )
    {
        self::$collectDevInfo = (bool) $collect;
    }

    /**
     * Sets visibility, invisible items return empty markup on render
     *
     * @param boolean $visible
     * @return OW_View
     */
    public function setVisible( $visible )
    {
        $this->visible = (bool) $visible;
        
        return $this;
    }

    /**
     * Checks if item is visible
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return OW_View
     */
    public function setTemplate( $template )
    {
        $this->template = $template;
        
        return $this;
    }

    /**
     * Assigns variable
     *
     * @param string $name
     * @param mixed $value
     * @return OW_View
     */
    public function assign( $name, $value )
    {
        $this->assignedVars[$name] = $value;
        
        return $this;
    }

    /**
     * @param string $varName
     * @return OW_View
     */
    public function clearAssign( $varName )
    {
        if ( isset($this->assignedVars[$varName]) )
        {
            unset($this->assignedVars[$varName]);
        }

        return $this;
    }

    public function onBeforeRender()
    {
        
    }

    /**
     * Returns rendered markup
     *
     * @return string
     */
    public function render()
    {
        $this->onBeforeRender();

        if ( !$this->visible )
        {
            return "";
        }

        $className = get_class($this);

        if ( $this->template === null )
        {
            throw new LogicException("No template provided for class `{$className}`");
        }

        $viewRenderer = OW_ViewRenderer::getInstance();

        $prevVars = $viewRenderer->getAllAssignedVars();

        $this->onRender();

        $viewRenderer->assignVars($this->assignedVars);

        $renderedMarkup = $viewRenderer->renderTemplate($this->template);

        $viewRenderer->clearAssignedVars();

        $viewRenderer->assignVars($prevVars);

        // TODO refactor - dirty data collect for dev tool
        if ( self::$collectDevInfo )
        {
            self::$devInfo[$className] = $this->template;
        }

        return $renderedMarkup;
    }

    protected function onRender()
    {
        
    }

    /**
     * Triggers event using base event class
     * 
     * @param string $name
     * @param array $params
     * @param mixed $data
     * @return mixed
     */
    protected function triggerEvent( $name, array $params = array(), $data = null )
    {
        return OW::getEventManager()->trigger(new OW_Event($name, $params, $data));
    }

    /**
     * @param OW_Event $event
     * @return mixed
     */
    protected function triggerEventForObject( OW_Event $event )
    {
        return OW::getEventManager()->trigger($event);
    }
}
