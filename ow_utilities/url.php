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
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_Url
{
    private static $redirectCodes = array(
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    );

    /**
     * Makes search engines friendly redirect to provided URL.
     * 
     * @param string $url
     * @param integer $redirectCode
     */
    public static function redirect( $url, $redirectCode = null )
    {
        $redirectCode = array_key_exists((int) $redirectCode, self::$redirectCodes) ? (int) $redirectCode : 301;

        header(self::$redirectCodes[$redirectCode]);
        header("Location: " . $url);
        exit();
    }

    /**
     * Removes site installation subfolder from request URI
     * 
     * @param string $urlHome
     * @param string $requestUri
     * @return string
     */
    public static function getRealRequestUri( $urlHome, $requestUri )
    {
        $urlArray = parse_url($urlHome);

        $originalUri = UTIL_String::removeFirstAndLastSlashes($requestUri);
        $originalPath = UTIL_String::removeFirstAndLastSlashes($urlArray['path']);

        if ( $originalPath === '' )
        {
            return $originalUri;
        }

        $uri = mb_substr($originalUri, (mb_strpos($originalUri, $originalPath) + mb_strlen($originalPath)));
        $uri = trim(UTIL_String::removeFirstAndLastSlashes($uri));

        return $uri ? self::secureUri($uri) : '';
    }

   /**
    * Secure uri
    *
    * @param string $uri
    * @return string
    */
    public static function secureUri( $uri )
    {
        // remove posible native uri encoding
        $uriInfo = parse_url(urldecode($uri));

        if ( $uriInfo )
        {
            $processedUri = '';

            // process uri path
            if ( !empty($uriInfo['path']) ) 
            {
                $processedUri = implode('/', array_map('urlencode', explode('/', $uriInfo['path'])));
            }

            // process uri params
            if ( !empty($uriInfo['query']) )
            {
                // parse uri params
                $uriParams = array();
                parse_str($uriInfo['query'], $uriParams);

                $processedUri .= '?' . http_build_query($uriParams);
            }

            if ( !empty($uriInfo['fragment']) )
            {
                $processedUri .= '#' . urlencode($uriInfo['fragment']);
            }

            return $processedUri;
        }
    }

    public static function selfUrl()
    {
        $s = (!empty($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on") ) ? 's' : '';
        $serverProtocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($serverProtocol, 0, strpos($serverProtocol, '/')) . $s;

        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);

        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . self::secureUri($_SERVER['REQUEST_URI']);
    }

    public static function getLocalPath( $uri )
    {
        $userFilesUrl = OW::getStorage()->getFileUrl(OW_DIR_USERFILES);
        $path = null;

        if ( stripos($uri, OW_URL_HOME) !== false )
        {
            $path = str_replace(OW_URL_HOME, OW_DIR_ROOT, $uri);
            $path = str_replace('/', DS, $path);
        }
        else if ( stripos($uri, $userFilesUrl) !== false )
        {
            $path = str_replace($userFilesUrl, OW_DIR_USERFILES, $uri);
            $path = str_replace('/', DS, $path);
        }

        return $path;
    }
}
