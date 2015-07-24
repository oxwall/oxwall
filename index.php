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
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', dirname(__FILE__) . DS);

require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');

if ( !defined('OW_ERROR_LOG_ENABLE') || (bool) OW_ERROR_LOG_ENABLE )
{
    $logFilePath = OW_DIR_LOG . 'error.log';
    $logger = OW::getLogger('ow_core_log');
    $logger->setLogWriter(new BASE_CLASS_FileLogWriter($logFilePath));
    $errorManager->setLogger($logger);
}

if ( file_exists(OW_DIR_ROOT . 'ow_install' . DS . 'install.php') )
{
    include OW_DIR_ROOT . 'ow_install' . DS . 'install.php';
}

OW::getSession()->start();

$application = OW::getApplication();

if ( OW_PROFILER_ENABLE || OW_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('before_app_init');
}

$application->init();

if ( OW_PROFILER_ENABLE || OW_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_app_init');
}

$event = new OW_Event(OW_EventManager::ON_APPLICATION_INIT);

OW::getEventManager()->trigger($event);

$application->route();

$event = new OW_Event(OW_EventManager::ON_AFTER_ROUTE);

if ( OW_PROFILER_ENABLE || OW_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_route');
}

OW::getEventManager()->trigger($event);

$application->handleRequest();

if ( OW_PROFILER_ENABLE || OW_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_controller_call');
}

$event = new OW_Event(OW_EventManager::ON_AFTER_REQUEST_HANDLE);

OW::getEventManager()->trigger($event);

$application->finalize();

if ( OW_PROFILER_ENABLE || OW_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_finalize');
}

$application->returnResponse();
