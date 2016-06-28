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
 * @package ow_updates.classes
 * @since 1.0
 */
class Updater
{
    public static $storage = null;

    /**
     * @return OW_Database
     */
    public static function getDbo()
    {
        return OW::getDbo();
    }

    /**
     * @return UPDATE_SeoService
     */
    public static function getSeoService()
    {
        return UPDATE_SeoService::getInstance();
    }

    /**
     * @return UPDATE_LanguageService
     */
    public static function getLanguageService()
    {
        return UPDATE_LanguageService::getInstance();
    }

    /**
     * @return UPDATE_WidgetService
     */
    public static function getWidgetService()
    {
        return UPDATE_WidgetService::getInstance();
    }
    
    /**
     * @return UPDATE_WidgetService
     */
    public static function getMobileWidgeteService()
    {
        return UPDATE_MobileWidgetService::getInstance();
    }

    /**
     * @return UPDATE_ConfigService
     */
    public static function getConfigService()
    {
        return UPDATE_ConfigService::getInstance();
    }

    /**
     * @return UPDATE_NavigationService
     */
    public static function getNavigationService()
    {
        return UPDATE_NavigationService::getInstance();
    }

    /**
     * @return UPDATE_AuthorizationService
     */
    public static function getAuthorizationService()
    {
        return UPDATE_AuthorizationService::getInstance();
    }
    
    /**
     * @return UPDATE_Log
     */
    public static function getLogger()
    {
        return UPDATE_Log::getInstance();
    }

    /**
     * @return OW_Storage
     */
    public static function getStorage()
    {
        if ( self::$storage === null )
        {
            switch ( true )
            {
                case defined('OW_USE_AMAZON_S3_CLOUDFILES') && OW_USE_AMAZON_S3_CLOUDFILES :
                    self::$storage = new BASE_CLASS_AmazonCloudStorage();
                    break;

                /* case defined('OW_USE_CLOUDFILES') && OW_USE_CLOUDFILES :
                    self::$storage = new BASE_CLASS_CloudStorage();
                    break; */

                default :
                    self::$storage = new BASE_CLASS_FileStorage();
                    break;
            }
        }

        return self::$storage;
    }
}
