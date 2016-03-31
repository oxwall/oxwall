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
define("_OW_", true);
define("DS", DIRECTORY_SEPARATOR);
define("OW_DIR_ROOT", dirname(dirname(__FILE__)) . DS);
define("UPDATE_DIR_ROOT", OW_DIR_ROOT . "ow_updates" . DS);

require_once OW_DIR_ROOT . "ow_includes/config.php";
require_once OW_DIR_ROOT . "ow_includes/define.php";
require_once OW_DIR_UTIL . "debug.php";
require_once OW_DIR_UTIL . "string.php";
require_once OW_DIR_UTIL . "file.php";
require_once UPDATE_DIR_ROOT . "classes" . DS . "autoload.php";
require_once UPDATE_DIR_ROOT . "classes" . DS . "error_manager.php";
require_once UPDATE_DIR_ROOT . "classes" . DS . "updater.php";
require_once UPDATE_DIR_ROOT . "classes" . DS . "update_executor.php";
require_once OW_DIR_CORE . "ow.php";
require_once OW_DIR_CORE . "plugin.php";

spl_autoload_register(array("UPDATE_Autoload", "autoload"));

UPDATE_ErrorManager::getInstance(true);

$autoloader = UPDATE_Autoload::getInstance();
$autoloader->addPackagePointer("BOL", OW_DIR_SYSTEM_PLUGIN . "base" . DS . "bol" . DS);
$autoloader->addPackagePointer("BASE_CLASS", OW_DIR_SYSTEM_PLUGIN . "base" . DS . "classes" . DS);
$autoloader->addPackagePointer("OW", OW_DIR_CORE);
$autoloader->addPackagePointer("UTIL", OW_DIR_UTIL);
$autoloader->addPackagePointer("UPDATE", UPDATE_DIR_ROOT . "classes" . DS);

$db = Updater::getDbo();
$dbPrefix = OW_DB_PREFIX;

//TODO check what for we need authentificator
OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());

$updateExec = new UPDATE_UpdateExecutor($_GET);
$updateExec->runTask();
$updateExec->processResult();

//http://gskl/ow_updates/index2.php?task=update-all-plugins



/* functions */

function build_url_query_string( $url, array $paramsToUpdate = array(), $anchor = null )
{
    $requestUrlArray = parse_url($url);

    $currentParams = array();

    if ( isset($requestUrlArray['query']) )
    {
        parse_str($requestUrlArray['query'], $currentParams);
    }

    $currentParams = array_merge($currentParams, $paramsToUpdate);

    return $requestUrlArray['scheme'] . '://' . $requestUrlArray['host'] . $requestUrlArray['path'] . '?' . http_build_query($currentParams) . ( $anchor === null ? '' : '#' . trim($anchor) );
}

function printVar( $var )
{
    UTIL_Debug::varDump($var);
}
