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
 * Class is responsible for...
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_ViewRenderer
{
    /**
     * @var OW_Smarty
     */
    private $smarty;

    /**
     * Singleton instance.
     *
     * @var OW_ViewRenderer
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_ViewRenderer
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->smarty = new OW_Smarty();
    }

    /**
     * Assigns list of values to template vars by reference.
     *
     * @param array $vars
     */
    public function assignVars( $vars )
    {
        foreach ( $vars as $key => $value )
        {
            $this->smarty->assignByRef($key, $vars[$key]);
        }
    }

    /**
     * Assigns value to template var by reference.
     *
     * @param string $key
     * @param mixed $value
     */
    public function assignVar( $key, $value )
    {
        $this->smarty->assignByRef($key, $value);
    }

    /**
     * Renders template using assigned vars and returns generated markup.
     *
     * @param string $template
     * @return string
     */
    public function renderTemplate( $template )
    {
        return $this->smarty->fetch($template);
    }

    /**
     * Returns assigned var value for provided var name.
     *
     * @param string $varName
     * @return mixed
     */
    public function getAssignedVar( $varName )
    {
        return $this->smarty->getTemplateVars($varName);
    }

    /**
     * Returns list of assigned var values.
     *
     * @return array
     */
    public function getAllAssignedVars()
    {
        return $this->smarty->getTemplateVars();
    }

    /**
     * Deletes all assigned template vars.
     */
    public function clearAssignedVars()
    {
        $this->smarty->clearAllAssign();
    }

    /**
     *
     * @param string $varName
     */
    public function clearAssignedVar( $varName )
    {
        $this->smarty->clearAssign($varName);
    }

    /**
     * Adds custom function for template.
     *
     * @param string $name
     * @param callback $callback
     */
    public function registerFunction( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['function'][$name]) )
        {
            $this->smarty->registerPlugin('function', $name, $callback);
        }
    }

    /**
     * Removes custom function.
     *
     * @param string $name
     */
    public function unregisterFunction( $name )
    {
        $this->smarty->unregisterPlugin('function', $name);
    }

    /**
     * Adds custom block function for template.
     *
     * @param string $name
     * @param callback $callback
     */
    public function registerBlock( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['block'][$name]) )
        {
            $this->smarty->registerPlugin('block', $name, $callback);
        }
    }

    /**
     * Removes block function.
     *
     * @param string $name
     */
    public function unregisterBlock( $name )
    {
        $this->smarty->unregisterPlugin('block', $name);
    }

    /**
     * Adds custom template modifier.
     * 
     * @param string $name
     * @param string $callback 
     */
    public function registerModifier( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['modifier'][$name]) )
        {
            $this->smarty->registerPlugin('modifier', $name, $callback);
        }
    }

    /**
     * Remopves template modifier.
     * 
     * @param string $name 
     */
    public function unregisterModifier( $name )
    {
        $this->smarty->unregisterPlugin('modifier', $name);
    }

    /**
     * Clears compiled templates.
     */
    public function clearCompiledTpl()
    {
        $this->smarty->clearCompiledTemplate();
    }
}