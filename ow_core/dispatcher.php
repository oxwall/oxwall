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
 * @method static OW_Dispatcher getInstance()
 * @since 1.0
 */
class OW_Dispatcher
{
    const ATTRS_KEY_CTRL = 'controller';
    const ATTRS_KEY_ACTION = 'action';
    const ATTRS_KEY_VARLIST = 'params';

    const CATCH_ALL_REQUEST_KEY_CTRL = 'controller';
    const CATCH_ALL_REQUEST_KEY_ACTION = 'action';
    const CATCH_ALL_REQUEST_KEY_REDIRECT = 'redirect';
    const CATCH_ALL_REQUEST_KEY_JS = 'js';
    const CATCH_ALL_REQUEST_KEY_ROUTE = 'route';
    const CATCH_ALL_REQUEST_KEY_PARAMS = 'params';

    use OW_Singleton;
    
    /**
     * @var array
     */
    private $dispatchAttributes;
    /**
     * @var array
     */
    private $indexPageAttributes;
    /**
     * @var array
     */
    private $staticPageAttributes;
    /**
     * @var array
     */
    private $catchAllRequestsAttributes = array();
    /**
     * @var array
     */
    private $catchAllRequestsExcludes = array();

    /**
     * @param string $key
     * @return array
     */
    public function getCatchAllRequestsAttributes( $key )
    {
        return!empty($this->catchAllRequestsAttributes[$key]) ? $this->catchAllRequestsAttributes[$key] : null;
    }

    /**
     * <controller> <action> <params> <route> <redirect> <js>
     *
     * @param string $key
     * @param array $attributes
     */
    public function setCatchAllRequestsAttributes( $key, array $attributes )
    {
        $this->catchAllRequestsAttributes[$key] = $attributes;

        $this->addCatchAllRequestsExclude($key, $attributes[self::ATTRS_KEY_CTRL], $attributes[self::ATTRS_KEY_ACTION]);
    }

    /**
     *
     * @param string $key
     * @param string $controller
     * @param string $action
     */
    public function addCatchAllRequestsExclude( $key, $controller, $action = null )
    {
        if ( empty($this->catchAllRequestsExcludes[$key]) )
        {
            $this->catchAllRequestsExcludes[$key] = array();
        }

        $this->catchAllRequestsExcludes[$key][] = array(self::CATCH_ALL_REQUEST_KEY_CTRL => $controller, self::CATCH_ALL_REQUEST_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getIndexPageAttributes()
    {
        return $this->indexPageAttributes;
    }

    /**
     * @param string $controller
     * @param string $action
     */
    public function setIndexPageAttributes( $controller, $action = 'index' )
    {
        $this->indexPageAttributes = array(self::ATTRS_KEY_CTRL => $controller, self::ATTRS_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getStaticPageAttributes()
    {
        return $this->staticPageAttributes;
    }

    /**
     * @param string $controller
     * @param string $action
     */
    public function setStaticPageAttributes( $controller, $action = 'index' )
    {
        $this->staticPageAttributes = array(self::ATTRS_KEY_CTRL => $controller, self::ATTRS_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getDispatchAttributes()
    {
        return $this->dispatchAttributes;
    }

    /**
     * @param array $attributes
     * @throws Redirect404Exception
     */
    public function setDispatchAttributes( array $attributes )
    {
        if ( empty($attributes[OW_Route::DISPATCH_ATTRS_CTRL]) )
        {
            throw new Redirect404Exception();
        }

        $this->dispatchAttributes = array(
            self::ATTRS_KEY_CTRL => trim($attributes[OW_Route::DISPATCH_ATTRS_CTRL]),
            self::ATTRS_KEY_ACTION => ( empty($attributes[OW_Route::DISPATCH_ATTRS_ACTION]) ? null : trim($attributes[OW_Route::DISPATCH_ATTRS_ACTION]) ),
            self::ATTRS_KEY_VARLIST => ( empty($attributes[OW_Route::DISPATCH_ATTRS_VARLIST]) ? array() : $attributes[OW_Route::DISPATCH_ATTRS_VARLIST])
        );
    }

    /**
     * @throws Redirect404Exception
     */
    public function dispatch()
    {
        // check if controller class contains package pointer with plugin key
        if ( empty($this->dispatchAttributes[self::ATTRS_KEY_CTRL]) || !mb_strstr($this->dispatchAttributes[self::ATTRS_KEY_CTRL], '_') )
        {
            throw new InvalidArgumentException("Can't dispatch request! Empty or invalid controller class provided!");
        }

        // set uri params in request object
        if ( !empty($this->dispatchAttributes[self::ATTRS_KEY_VARLIST]) )
        {
            OW::getRequest()->setUriParams($this->dispatchAttributes[self::ATTRS_KEY_VARLIST]);
        }

        $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey($this->dispatchAttributes[self::ATTRS_KEY_CTRL]));

        $catchAllRequests = $this->processCatchAllRequestsAttrs();

        if ( $catchAllRequests !== null )
        {
            $this->dispatchAttributes = $catchAllRequests;
        }

        /* @var OW_ActionController $controller */

        try
        {
            $controller = OW::getClassInstance($this->dispatchAttributes[self::ATTRS_KEY_CTRL]);
        }
        catch ( ReflectionException $e )
        {
            throw new Redirect404Exception();
        }
        
        // check if controller exists and is instance of base action controller class
        if ( $controller === null || !$controller instanceof OW_ActionController )
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

        if ( empty($this->dispatchAttributes[self::ATTRS_KEY_ACTION]) )
        {
            $this->dispatchAttributes[self::ATTRS_KEY_ACTION] = $controller->getDefaultAction();
        }

        try
        {
            $action = $reflectionClass->getMethod($this->dispatchAttributes[self::ATTRS_KEY_ACTION]);
        }
        catch ( Exception $e )
        {
            throw new Redirect404Exception();
        }

        $action->invokeArgs($controller, array(self::ATTRS_KEY_VARLIST => ( empty($this->dispatchAttributes[self::ATTRS_KEY_VARLIST]) ? array() : $this->dispatchAttributes[self::ATTRS_KEY_VARLIST] )));

        // set default template for controller action if template wasn't set
        if ( $controller->getTemplate() === null )
        {
            $controller->setTemplate($this->getControllerActionDefaultTemplate());
        }

        OW::getDocument()->setBody($controller->render());
    }

    /**
     * Returns template path for provided controller and action.
     *
     * @return string<path>
     */
    private function getControllerActionDefaultTemplate()
    {
        $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey($this->dispatchAttributes[self::ATTRS_KEY_CTRL]));

        $templateFilename = OW::getAutoloader()->classToFilename($this->dispatchAttributes[self::ATTRS_KEY_CTRL], false) . '_'
            . OW::getAutoloader()->classToFilename(ucfirst($this->dispatchAttributes[self::ATTRS_KEY_ACTION]), false) . '.html';

        return $plugin->getCtrlViewDir() . $templateFilename;
    }

    /**
     * Returns processed catch all requests attributes.
     *
     * @return array|null
     * @throws Exception
     */
    private function processCatchAllRequestsAttrs()
    {
        if ( empty($this->catchAllRequestsAttributes) )
        {
            return null;
        }

        $catchRequest = true;

        $lastKey = array_search(end($this->catchAllRequestsAttributes), $this->catchAllRequestsAttributes);

        foreach ( $this->catchAllRequestsExcludes[$lastKey] as $exclude )
        {
            if ( $this->dispatchAttributes[self::ATTRS_KEY_CTRL] === $exclude[self::CATCH_ALL_REQUEST_KEY_CTRL] && ( $exclude[self::CATCH_ALL_REQUEST_KEY_ACTION] === null || $this->dispatchAttributes[self::ATTRS_KEY_ACTION] === $exclude[self::CATCH_ALL_REQUEST_KEY_ACTION] ) )
            {
                $catchRequest = false;
                break;
            }
        }
        if ( $catchRequest )
        {
            if ( isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_REDIRECT]) && (bool) $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_REDIRECT] )
            {
                $route = isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ROUTE]) ? trim($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ROUTE]) : null;

                $params = isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_PARAMS]) ? $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_PARAMS] : array();

                $redirectUrl = ($route !== null) ?
                    OW::getRouter()->urlForRoute($route, $params) :
                    OW::getRouter()->urlFor($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_CTRL], $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ACTION], $params);

                $redirectUrl = OW::getRequest()->buildUrlQueryString($redirectUrl, array('back_uri' => OW::getRequest()->getRequestUri()));

                if ( isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_JS]) && (bool) $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_JS] )
                {
                    // TODO resolve hotfix
                    // hotfix for splash screen + members only case
                    if ( array_key_exists('base.members_only', $this->catchAllRequestsAttributes) )
                    {
                        if ( $this->dispatchAttributes[self::CATCH_ALL_REQUEST_KEY_CTRL] === 'BASE_CTRL_User' && $this->dispatchAttributes[self::CATCH_ALL_REQUEST_KEY_ACTION] === 'standardSignIn' )
                        {
                            $backUri = isset($_GET['back_uri']) ? $_GET['back_uri'] : OW::getRequest()->getRequestUri();
                            OW::getDocument()->addOnloadScript("window.location = '" . OW::getRequest()->buildUrlQueryString($redirectUrl, array('back_uri' => $backUri)) . "'");
                            return null;
                        }
                        else
                        {
                            $ru = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('static_sign_in'), array('back_uri' => OW::getRequest()->getRequestUri()));
                            OW::getApplication()->redirect($ru);
                        }
                    }

                    OW::getDocument()->addOnloadScript("window.location = '" . $redirectUrl . "'");
                    return null;
                }

                UTIL_Url::redirect($redirectUrl);
            }

            return $this->getCatchAllRequestsAttributes($lastKey);
        }

        return null;
    }
}
