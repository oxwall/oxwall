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
require_once OW_DIR_LIB . 'browser' . DS . 'browser.php';

class UTIL_Browser
{
    public static function isSmartphone()
    {
        require_once OW_DIR_LIB . 'mobileesp' . DS . 'mdetect.php';
        $obj = new uagent_info();
        return (bool) $obj->DetectSmartphone();
    }

    /**
     * @param string $agentString
     * @return boolean
     */
    public static function isMobile( $agentString )
    {
        return self::getBrowserObj($agentString)->isMobile();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getBrowser( $agentString )
    {
        return self::getBrowserObj($agentString)->getBrowser();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getVersion( $agentString )
    {
        return self::getBrowserObj($agentString)->getVersion();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getPlatform( $agentString )
    {
        return self::getBrowserObj($agentString)->getPlatform();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function isRobot( $agentString )
    {
        return self::getBrowserObj($agentString)->isRobot();
    }

    /**
     * @param string $agentString
     * @return CSBrowser
     */
    private static function getBrowserObj( $agentString )
    {
        return new CSBrowser($agentString);
    }

    private static function getWurfl()
    {
        
    }
}
