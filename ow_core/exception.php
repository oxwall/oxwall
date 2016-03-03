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
 * Exceptions.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package core
 * @since 1.0
 */

/**
 * Redirect exception forces 301 http redirect.
 */
class RedirectException extends Exception
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var integer
     */
    private $redirectCode;
    /**
     * @var mixed
     */
    private $data;

    /**
     * Constructor.
     *
     * @param string $url
     */
    public function __construct( $url, $code = null )
    {
        parent::__construct('', 0);
        $this->url = $url;
        $this->redirectCode = ( empty($code) ? 301 : (int) $code );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return integer
     */
    public function getRedirectCode()
    {
        return $this->redirectCode;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData( $data )
    {
        $this->data = $data;
    }
}

class InterceptException extends Exception
{
    private $handlerAttrs;

    public function __construct( $attrs )
    {
        $this->handlerAttrs = $attrs;
    }

    public function getHandlerAttrs()
    {
        return $this->handlerAttrs;
    }
}

class AuthorizationException extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct( $message = null )
    {
        $route = OW::getRouter()->getRoute('base_page_auth_failed');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'authorizationFailed') : $route->getDispatchAttrs();
        $params[OW_Route::DISPATCH_ATTRS_VARLIST]['message'] = $message;
        parent::__construct($params);
    }
}



/**
 * Page not found 404 redirect exception.
 */
class Redirect404Exception extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $route = OW::getRouter()->getRoute('base_page_404');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'page404') : $route->getDispatchAttrs();
        parent::__construct($params);
    }
}

/**
 * Internal server error redirect exception.
 */
class Redirect500Exception extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(OW_URL_HOME . '500.phtml', 500);
    }
}

/**
 * Forbidden 403 redirect exception.
 */
class Redirect403Exception extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct( $message = null )
    {
        $route = OW::getRouter()->getRoute('base_page_403');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'page403') : $route->getDispatchAttrs();
        $params[OW_Route::DISPATCH_ATTRS_VARLIST]['message'] = $message;
        parent::__construct($params);
    }
}

/**
 * Blank confirm page redirect exception.
 */
class RedirectConfirmPageException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct( $message )
    {
        parent::__construct(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_page_confirm'), array('back_uri' => urlencode(OW::getRequest()->getRequestUri()))));
        OW::getSession()->set('baseConfirmPageMessage', $message);
    }
}

/**
 * Blank message page redirect exception.
 */
class RedirectAlertPageException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct( $message )
    {
        parent::__construct(OW::getRouter()->urlForRoute('base_page_alert'));
        OW::getSession()->set('baseAlertPageMessage', $message);
    }
}

/**
 * Sign in page redirect exception.
 */
class AuthenticateException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('static_sign_in'), array('back-uri' => urlencode(OW::getRequest()->getRequestUri()))));
    }
}

class ApiResponseErrorException extends Exception
{
    public $data = array();
    
    public function __construct($data = array(), $code = 0) 
    {
        parent::__construct("", $code);
        
        $this->data = $data;
    }
}

class ApiAccessException extends ApiResponseErrorException
{
    const TYPE_NOT_AUTHENTICATED = "not_authenticated";
    const TYPE_SUSPENDED = "suspended";
    const TYPE_NOT_APPROVED = "not_approved";
    const TYPE_NOT_VERIFIED = "not_verified";

    public function __construct( $type, $userData = array() )
    {
        parent::__construct(array(
            "type" => $type,
            "data" => $userData
        ));
    }
}