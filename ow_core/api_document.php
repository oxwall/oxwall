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
 * Base class for HTML document object,
 * handles rendered output context (html document includes, headers, styles, etc).
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.ow_core
 * @since 1.0
 */
class OW_ApiDocument extends OW_Document
{
    public function  __construct()
    {
        $this->type = OW_Document::JSON;
    }

    private $body;

    public function getBody()
    {
        return $this->body;
    }

    public function setBody( $body )
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function render()
    {
        if( $this->type == OW_Document::JSON )
        {
            return $this->renderJson();
        }
    }

    private function renderJson()
    {
        OW::getResponse()->setHeader(OW_Response::HD_CNT_TYPE, "application/json");

        $body = $this->getBody();
        
        $apiResponse = array(
            "type" => "success",
            "data" => empty($body) ? new stdClass() : $body
        );
        
        return json_encode($apiResponse);
    }
}