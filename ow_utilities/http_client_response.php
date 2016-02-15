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
class UTIL_HttpClientResponse
{
    private $resultBody;
    private $headers;

    /**
     * 
     * @param string
     */
    public function __construct( $response, $headers )
    {
        $this->resultBody = $response;

        if ( $headers && is_array($headers) )
        {
            $this->headers = $this->parseHeaders($headers);
        }
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeader( $name )
    {
        if ( !empty($this->headers[$name]) )
        {
            return $this->headers[$name];
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader( $name )
    {
        return !empty($this->headers[$name]);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->headers["reponse_code"];
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->resultBody;
    }

    private function parseHeaders( $headers )
    {
        $head = array();
        foreach ( $headers as $k => $v )
        {
            $t = explode(':', $v, 2);
            if ( isset($t[1]) )
                $head[trim($t[0])] = trim($t[1]);
            else
            {
                $head[] = $v;
                if ( preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out) )
                    $head['reponse_code'] = intval($out[1]);
            }
        }
        return $head;
    }
}
