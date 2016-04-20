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
use GuzzleHttp\RequestOptions;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_utilities
 * @since 1.8.1
 */
class UTIL_HttpClientParams
{
    /**
     * @var array
     */
    private $options = array("params" => array());

    public function __construct()
    {
        
    }

    /**
     * @param bool $allowRedirects
     */
    public function setAllowRedirects( $allowRedirects )
    {
        $this->options[RequestOptions::ALLOW_REDIRECTS] = (bool) $allowRedirects;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout( $timeout )
    {
        $this->options[RequestOptions::CONNECT_TIMEOUT] = (int) $timeout;
    }

    /**
     * @param string $headerName
     * @param string $headerVal
     */
    public function setHeader( $headerName, $headerVal )
    {
        if ( array_key_exists(RequestOptions::HEADERS, $this->options) )
        {
            $this->options[RequestOptions::HEADERS] = array();
        }

        $this->options[RequestOptions::HEADERS][trim($headerName)] = trim($headerVal);
    }

    /**
     * @param array $headers
     */
    public function setHeaders( array $headers )
    {
        foreach ( $headers as $name => $val )
        {
            $this->setHeader($name, $val);
        }
    }

    /**
     * @param string $body
     */
    public function setBody( $body )
    {
        $this->options[RequestOptions::BODY] = trim($body);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param string $val
     */
    public function addParam( $name, $val )
    {
        $this->options["params"][trim($name)] = trim($val);
    }

    /**
     * @param array $params
     */
    public function addParams( array $params )
    {
        foreach ( $params as $name => $val )
        {
            $this->addParam($name, $val);
        }
    }
}
