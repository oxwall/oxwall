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

namespace Oxwall\Core;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class OW
{
    const CONTEXT_MOBILE = Application::CONTEXT_MOBILE;
    const CONTEXT_DESKTOP = Application::CONTEXT_DESKTOP;
    const CONTEXT_API = Application::CONTEXT_API;
    const CONTEXT_CLI = Application::CONTEXT_CLI;

    private static $context;

    private static function detectContext()
    {
        if ( self::$context !== null )
        {
            return;
        }

        if ( defined("OW_USE_CONTEXT") )
        {
            switch ( true )
            {
                case OW_USE_CONTEXT == 1:
                    self::$context = self::CONTEXT_DESKTOP;
                    return;

                case OW_USE_CONTEXT == 1 << 1:
                    self::$context = self::CONTEXT_MOBILE;
                    return;

                case OW_USE_CONTEXT == 1 << 2:
                    self::$context = self::CONTEXT_API;
                    return;

                case OW_USE_CONTEXT == 1 << 3:
                    self::$context = self::CONTEXT_CLI;
                    return;
            }
        }


        $context = self::CONTEXT_DESKTOP;

        try
        {
            $isSmart = \Oxwall\Utilities\Browser::isSmartphone();
        }
        catch ( \Exception $e )
        {
            return;
        }

        if ( defined("OW_CRON") )
        {
            $context = self::CONTEXT_DESKTOP;
        }
        else if ( self::getSession()->isKeySet(Application::CONTEXT_NAME) )
        {
            $context = self::getSession()->get(Application::CONTEXT_NAME);
        }
        else if ( $isSmart )
        {
            $context = self::CONTEXT_MOBILE;
        }

        if ( defined("OW_USE_CONTEXT") )
        {
            if ( (OW_USE_CONTEXT & 1 << 1) == 0 && $context == self::CONTEXT_MOBILE )
            {
                $context = self::CONTEXT_DESKTOP;
            }

            if ( (OW_USE_CONTEXT & 1 << 2) == 0 && $context == self::CONTEXT_API )
            {
                $context = self::CONTEXT_DESKTOP;
            }
        }

        if ( (bool) self::getConfig()->getValue("base", "disable_mobile_context") && $context == self::CONTEXT_MOBILE )
        {
            $context = self::CONTEXT_DESKTOP;
        }

        //temp API context detection
        //TODO remake
        $uri = \Oxwall\Utilities\Url::getRealRequestUri(self::getRouter()->getBaseUrl(), $_SERVER["REQUEST_URI"]);


        if ( mb_strstr($uri, "/") )
        {
            if ( trim(mb_substr($uri, 0, mb_strpos($uri, "/"))) == "api" )
            {
                $context = self::CONTEXT_API;
            }
        }
        else
        {
            if ( trim($uri) == "api" )
            {
                $context = self::CONTEXT_API;
            }
        }

        self::$context = $context;
    }

    /**
     * Returns autoloader object.
     *
     * @return Autoload
     */
    public static function getAutoloader()
    {
        return Autoload::getInstance();
    }

    /**
     * Returns front controller object.
     *
     * @return Application
     */
    public static function getApplication()
    {
        self::detectContext();

        switch ( self::$context )
        {
            case self::CONTEXT_MOBILE:
                return MobileApplication::getInstance();

            case self::CONTEXT_API:
                return ApiApplication::getInstance();

            case self::CONTEXT_CLI:
                return CliApplication::getInstance();

            default:
                return Application::getInstance();
        }
    }

    /**
     * Returns global config object.
     *
     * @return Config
     */
    public static function getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Returns session object.
     *
     * @return Session
     */
    public static function getSession()
    {
        return Session::getInstance();
    }

    /**
     * Returns current web user object.
     *
     * @return User
     */
    public static function getUser()
    {
        return User::getInstance();
    }
    /**
     * Database object instance.
     *
     * @var Database
     */
    private static $dboInstance;

    /**
     * Returns DB access object with default connection.
     *
     * @return OW_Database
     */
    public static function getDbo()
    {
        if ( self::$dboInstance === null )
        {
            $params = array(
                "host" => OW_DB_HOST,
                "username" => OW_DB_USER,
                "password" => OW_DB_PASSWORD,
                "dbname" => OW_DB_NAME
            );
            if ( defined("OW_DB_PORT") && (OW_DB_PORT !== null) )
            {
                $params["port"] = OW_DB_PORT;
            }
            if ( defined("OW_DB_SOCKET") )
            {
                $params["socket"] = OW_DB_SOCKET;
            }

            if ( OW_DEV_MODE || OW_PROFILER_ENABLE )
            {
                $params["profilerEnable"] = true;
            }

            if ( OW_DEBUG_MODE )
            {
                $params["debugMode"] = true;
            }

            self::$dboInstance = Database::getInstance($params);
        }
        return self::$dboInstance;
    }

    /**
     * Returns system mailer object.
     *
     * 	@return Mailer
     */
    public static function getMailer()
    {
        return Mailer::getInstance();
    }

    /**
     * Returns responded HTML document object.
     *
     * @return HtmlDocument
     */
    public static function getDocument()
    {
        return Response::getInstance()->getDocument();
    }

    /**
     * Returns global request object.
     *
     * @return Request
     */
    public static function getRequest()
    {
        return Request::getInstance();
    }

    /**
     * Returns global response object.
     *
     * @return Response
     */
    public static function getResponse()
    {
        return Response::getInstance();
    }

    /**
     * Returns language object.
     *
     * @return Language
     */
    public static function getLanguage()
    {
        return Language::getInstance();
    }

    /**
     * Returns system router object.
     *
     * @return Router
     */
    public static function getRouter()
    {
        return Router::getInstance();
    }

    /**
     * Returns system plugin manager object.
     *
     * @return PluginManager
     */
    public static function getPluginManager()
    {
        return PluginManager::getInstance();
    }

    /**
     * Returns system theme manager object.
     *
     * @return ThemeManager
     */
    public static function getThemeManager()
    {
        return ThemeManager::getInstance();
    }

    /**
     * Returns system event manager object.
     *
     * @return EventManager
     */
    public static function getEventManager()
    {
        return EventManager::getInstance();
    }

    /**
     * @return Registry
     */
    public static function getRegistry()
    {
        return Registry::getInstance();
    }

    /**
     * Returns global feedback object.
     *
     * @return Feedback
     */
    public static function getFeedback()
    {
        return Feedback::getInstance();
    }

    /**
     * Returns global navigation object.
     *
     * @return Navigation
     */
    public static function getNavigation()
    {
        return Navigation::getInstance();
    }

    /**
     * @return RequestHandler
     */
    public static function getRequestHandler()
    {
        self::detectContext();

        switch ( self::$context )
        {
            case self::CONTEXT_API:
                return ApiRequestHandler::getInstance();

            default:
                return RequestHandler::getInstance();
        }
    }

    /**
     *
     * @return CacheService
     */
    public static function getCacheService()
    {
        return \BOL_DbCacheService::getInstance(); //TODO make configurable
    }
    private static $storage;

    /**
     *
     * @return OW_Storage
     */
    public static function getStorage()
    {
        if ( self::$storage === null )
        {
            self::$storage = self::getEventManager()->call("core.get_storage");

            if ( self::$storage === null )
            {
                switch ( true )
                {
                    case defined("OW_USE_AMAZON_S3_CLOUDFILES") && OW_USE_AMAZON_S3_CLOUDFILES :
                        self::$storage = new \BASE_CLASS_AmazonCloudStorage();
                        break;

                    case defined("OW_USE_CLOUDFILES") && OW_USE_CLOUDFILES :
                        self::$storage = new \BASE_CLASS_CloudStorage();
                        break;

                    default :
                        self::$storage = new \BASE_CLASS_FileStorage();
                        break;
                }
            }
        }

        return self::$storage;
    }

    public static function getLogger( $logType = "ow" )
    {
        return Log::getInstance($logType);
    }

    /**
     * @return Authorization
     */
    public static function getAuthorization()
    {
        return Authorization::getInstance();
    }

    /**
     * @return CacheManager
     */
    public static function getCacheManager()
    {
        return CacheManager::getInstance();
    }

    public static function getClassInstance( $className, $arguments = null )
    {
        $args = func_get_args();
        $constuctorArgs = array_splice($args, 1);

        return self::getClassInstanceArray($className, $constuctorArgs);
    }

    public static function getClassInstanceArray( $className, array $arguments = array() )
    {
        $params = array(
            "className" => $className,
            "arguments" => $arguments
        );

        $eventManager = self::getEventManager();
        $eventManager->trigger(new \OW_Event("core.performance_test", array("key" => "component_construct.start", "params" => $params)));

        $event = new \OW_Event("class.get_instance." . $className, $params);
        $eventManager->trigger($event);
        $instance = $event->getData();

        if ( $instance !== null )
        {
            $eventManager->trigger(new \OW_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
            return $instance;
        }

        $event = new \OW_Event("class.get_instance", $params);

        $eventManager->trigger($event);
        $instance = $event->getData();

        if ( $instance !== null )
        {
            $eventManager->trigger(new \OW_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
            return $instance;
        }

        $rClass = new \ReflectionClass($className);
        $eventManager->trigger(new \OW_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
        return $rClass->newInstanceArgs($arguments);
    }

    /**
     * Returns text search manager object.
     *
     * @return TextSearchManager
     */
    public static function getTextSearchManager()
    {
        return TextSearchManager::getInstance();
    }
}
