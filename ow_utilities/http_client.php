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
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\RequestOptions;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_utilities
 * @since 1.8.1
 */
class UTIL_HttpClient
{
    const HTTP_STATUS_OK = 200;
    const CONNECTION_TIMEOUT = 5;

    /**
     * @var GuzzleHttp\Client
     */
    private static $client;

    /**
     * @param string $url
     * @param UTIL_HttpClientParams $params
     * @return UTIL_HttpClientResponse
     */
    public static function get( $url, UTIL_HttpClientParams $params = null )
    {
        $options = $params ? $params->getOptions() : array();

        if ( !empty($options["params"]) )
        {
            $options[RequestOptions::QUERY] = $options["params"];
        }

        return self::request("GET", $url, $options);
    }

    /**
     * @param string $url
     * @param UTIL_HttpClientParams $params
     * @return UTIL_HttpClientResponse
     */
    public static function post( $url, UTIL_HttpClientParams $params = null )
    {
        $options = $params ? $params->getOptions() : array();

        if ( !empty($options["params"]) )
        {
            $options[RequestOptions::FORM_PARAMS] = $options["params"];
        }

        return self::request("POST", $url, $options);
    }
    /* --------------------------------------------------------------------- */

    private static function getClient()
    {
        if ( self::$client == null )
        {
            $handler = function_exists("curl_version") ? new CurlHandler() : new StreamHandler();

            self::$client = new Client(array(
                "request.options" => array(
                    "exceptions" => false,
                ),
                "handler" => HandlerStack::create($handler)
            ));
        }

        return self::$client;
    }

    private static function request( $method, $url, array $options )
    {
        $options[RequestOptions::VERIFY] = false;
        $options[RequestOptions::CONNECT_TIMEOUT] = self::CONNECTION_TIMEOUT;

        try
        {
            $response = self::getClient()->request($method, $url, $options);
        }
        catch ( Exception $e )
        {
            return null;
        }

        return new UTIL_HttpClientResponse($response);
    }
}
