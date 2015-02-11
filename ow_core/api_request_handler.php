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
 * Dispatcher handles request after routing process,
 * i.e. creates instance of controller and calls action using provided params.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
final class OW_ApiRequestHandler extends OW_RequestHandler
{
    /**
     * Constructor.
     */
    private function __construct()
    {
        
    }
    /**
     * Singleton instance.
     *
     * @var OW_ApiRequestHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_ApiRequestHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
//    protected function processCatchAllRequestsAttrs()
//    {
//        return null;
//    }
    
    /**
     * @param array $dispatchAttributes
     */
    public function dispatch()
    {
        // check if controller class contains package pointer with plugin key
        if ( empty($this->handlerAttributes[self::ATTRS_KEY_CTRL]) || !mb_strstr($this->handlerAttributes[self::ATTRS_KEY_CTRL], '_') )
        {
            throw new InvalidArgumentException("Can't dispatch request! Empty or invalid controller class provided!");
        }
        
        // set uri params in request object
        if ( !empty($this->handlerAttributes[self::ATTRS_KEY_VARLIST]) )
        {
            OW::getRequest()->setUriParams($this->handlerAttributes[self::ATTRS_KEY_VARLIST]);
        }
        
        $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey($this->handlerAttributes[self::ATTRS_KEY_CTRL]));
        
        $catchAllRequests = $this->processCatchAllRequestsAttrs();
        
        if ( $catchAllRequests !== null )
        {
            $this->handlerAttributes = $catchAllRequests;
        }
        
        try
        {
            $reflectionClass = new ReflectionClass($this->handlerAttributes[self::ATTRS_KEY_CTRL]);
        }
        catch ( ReflectionException $e )
        {
            throw new Redirect404Exception();
        }
        
        /* @var $controller OW_ActionController */
        $controller = $reflectionClass->newInstance();

        // check if controller exists and is instance of base action controller class
        if ( $controller === null || !$controller instanceof OW_ApiActionController )
        {
            throw new LogicException("Can't dispatch request! Please provide valid controller class!");
        }
        
        // redirect to page 404 if plugin is inactive and isn't instance of admin controller class
        if ( !$plugin->isActive() && !$controller instanceof ADMIN_CTRL_Abstract )
        {
            throw new Redirect404Exception();
        }

        // call optional init method
        $controller->init();

        if ( empty($this->handlerAttributes[self::ATTRS_KEY_ACTION]) )
        {
            $this->handlerAttributes[self::ATTRS_KEY_ACTION] = $controller->getDefaultAction();
        }

        try
        {
            $action = $reflectionClass->getMethod($this->handlerAttributes[self::ATTRS_KEY_ACTION]);
        }
        catch ( Exception $e )
        {
            throw new Redirect404Exception();
        }

        $args = array();
        
        $args[] = $_POST;
        $args[] = empty($this->handlerAttributes[self::ATTRS_KEY_VARLIST]) ? array() : $this->handlerAttributes[self::ATTRS_KEY_VARLIST];
        
        $action->invokeArgs($controller, $args);

        OW::getDocument()->setBody($controller->render());
    }
}
