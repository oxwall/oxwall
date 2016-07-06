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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @method static OW_Request getInstance()
 * @since 1.0
 */
class OW_Request
{
    use OW_Singleton;
    
    /**
     * Request uri.
     *
     * @var string
     */
    private $uri;
    private $uriParams;

    /**
     * Constructor.
     */
    private function __construct()
    {
        if ( get_magic_quotes_gpc() )
        {
            $_GET = $this->stripSlashesRecursive($_GET);
            $_POST = $this->stripSlashesRecursive($_POST);
        }
    }

    /**
     * @return array
     */
    public function getUriParams()
    {
        return $this->uriParams;
    }

    /**
     * @param array $uriParams
     */
    public function setUriParams( array $uriParams )
    {
        $this->uriParams = $uriParams;
    }

    /**
     * Returns real request uri.
     *
     * @return string
     */
    public function getRequestUri()
    {
        if ( $this->uri === null )
        {
            $this->uri = UTIL_Url::getRealRequestUri(OW::getRouter()->getBaseUrl(), $_SERVER['REQUEST_URI']);
        }

        return $this->uri;
    }

    /**
     * Returns remote ip address.
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Returns request type.
     *
     * @return string
     */
    public function getRequestType()
    {
        return mb_strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }
//    public function getContentType()
//    {
//        return $_SERVER[''];
//    }

    /**
     * Indicates if request is ajax.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && mb_strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST' );
    }

    /**
     * Indicates if request is post.
     *
     * @return boolean
     */
    public function isPost()
    {
        return ( mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' );
    }

    /**
     * Returns request agent name.
     *
     * @return string
     */
    public function getUserAgentName()
    {
        return UTIL_Browser::getBrowser($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Returns user agent version;
     *
     * @return string
     */
    public function getUserAgentVersion()
    {
        return UTIL_Browser::getVersion($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Returns request agent platform.
     *
     * @return string
     */
    public function getUserAgentPlatform()
    {
        return UTIL_Browser::getPlatform($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Indicates if user agent is mobile.
     *
     * @return boolean
     */
    public function isMobileUserAgent()
    {
        return UTIL_Browser::isMobile($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Builds and updates url query string.
     *
     * @param string $url
     * @param array $paramsToUpdate
     * @param string $anchor
     * @return string
     */
    public function buildUrlQueryString( $url = null, array $paramsToUpdate = array(), $anchor = null )
    {
        $url = ( $url === null ) ? OW_URL_HOME . $this->getRequestUri() : trim($url);

        $requestUrlArray = parse_url($url);

        $currentParams = array();

        if ( isset($requestUrlArray['query']) )
        {
            parse_str($requestUrlArray['query'], $currentParams);
        }

        $currentParams = array_merge($currentParams, $paramsToUpdate);

        $scheme = empty($requestUrlArray["scheme"]) ? "" : $requestUrlArray["scheme"] . ":";
        $host = empty($requestUrlArray["host"]) ? "" : "//" . $requestUrlArray["host"];
        $port = empty($requestUrlArray["port"]) ? "" : ":" . (int) $requestUrlArray["port"];
        $path = empty($requestUrlArray["path"]) ? "" : $requestUrlArray["path"];
        $queryString = empty($currentParams) ? "" : "?" . http_build_query($currentParams);
        $anchor = ($anchor === null) ? "" : "#" . trim($anchor);

        return $scheme . $host . $port . $path . $queryString . $anchor;
    }

    /**
     * @param array $value
     * @return array
     */
    private function stripSlashesRecursive( $value )
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesRecursive'), $value) : stripslashes($value);
        return $value;
    }

    public function isSsl()
    {
        $isHttps = null;

        if ( array_key_exists("HTTPS", $_SERVER) )
        {
            $isHttps = ($_SERVER["HTTPS"] == "on");
        }
        else if ( array_key_exists("REQUEST_SCHEME", $_SERVER) )
        {
            $isHttps = (strtolower($_SERVER["REQUEST_SCHEME"]) == "https");
        }
        else if ( array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) )
        {
            $isHttps = ($_SERVER["HTTP_X_FORWARDED_PROTO"] == "https");
        }
        else if ( array_key_exists("SERVER_PORT", $_SERVER) )
        {
            $isHttps = ($_SERVER["SERVER_PORT"] == "443");
        }

        return $isHttps;
    }
}
