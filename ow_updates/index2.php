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
$updater = new UPDATE_UpdateExecutor();

$status = "";
$message = "";

if ( empty($_GET[UPDATE_UpdateExecutor::URI_VAR_ACTION]) )
{
    $status = UPDATE_UpdateExecutor::STATUS_EMPTY_ACTION;
    $message = "Error! Action not provided.";
}
else
{
    switch ( trim($_GET[UPDATE_UpdateExecutor::URI_VAR_ACTION]) )
    {
        case UPDATE_UpdateExecutor::URI_VAR_ACTION_VAL_UPDATE_PLUGIN:

            if ( !empty($_GET[UPDATE_UpdateExecutor::URI_VAR_PLUGIN_KEY]) )
            {
                $pluginKey = trim($_GET[UPDATE_UpdateExecutor::URI_VAR_PLUGIN_KEY]);

                try
                {
                    $pluginArr = $updater->updateSinglePlugin($pluginKey);
                    $status = UPDATE_UpdateExecutor::STATUS_SUCCESS;
                    $message = "Update Complete! Plugin '<b>{$pluginArr["key"]}</b>' successfully updated.";
                }
                catch ( LogicUpToDateException $ex )
                {
                    $status = UPDATE_UpdateExecutor::STATUS_UP_TO_DATE;
                    $message = "Error! Plugin '<b>" . htmlspecialchars($pluginKey) . "</b>' is up to date.";
                }
                catch ( LogicException $ex )
                {
                    $status = UPDATE_UpdateExecutor::STATUS_FAIL;
                    $message = "Error! Plugin '<b>" . htmlspecialchars($pluginKey) . "</b>' not found.";
                }
            }
            else
            {
                $status = UPDATE_UpdateExecutor::STATUS_FAIL;
                $message = "Error! Empty plugin key.";
            }

            break;

        case UPDATE_UpdateExecutor::URI_VAR_ACTION_VAL_UPDATE_ALL_PLUGINS:

            try
            {
                $count = $updater->updateAllPlugins();
                $status = UPDATE_UpdateExecutor::STATUS_SUCCESS;
                $message = "Update Complete! {$count} plugins successfully updated.";
            }
            catch ( LogicUpToDateException $ex )
            {
                $status = UPDATE_UpdateExecutor::STATUS_UP_TO_DATE;
                $message = "Error! All plugins are up to date.";
            }
            catch ( LogicException $ex )
            {
                $status = UPDATE_UpdateExecutor::STATUS_FAIL;
                $message = "Error! No plugins for update.";
            }

            break;

        case UPDATE_UpdateExecutor::URI_VAR_ACTION_VAL_UPDATE_PLATFORM:
            //TODO implement
            break;

        default :
            $message = "Error! Action is not defined.";
            $status = UPDATE_UpdateExecutor::STATUS_FAIL;
    }
}

if ( !empty($_GET[UPDATE_UpdateExecutor::URI_VAR_BACK_URI]) )
{
    $url = build_url_query_string(OW_URL_HOME . urldecode(trim($_GET[UPDATE_UpdateExecutor::URI_VAR_BACK_URI])),
        array_merge($_GET, array("mode" => $status)));
    Header("HTTP/1.1 301 Moved Permanently");
    Header("Location: {$url}");
    exit;
}

echo '
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
  <html>
  <head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body style="font:18px Tahoma;">
  <div style="width:400px;margin:300px auto 0;font:14px Tahoma;">
  <h3 style="color:#CF3513;font:bold 20px Tahoma;">Update Request</h3>
  ' . $message . ' <br />
  Go to <a style="color:#3366CC;" href="' . OW_URL_HOME . '">Index Page</a>&nbsp; or &nbsp;<a style="color:#3366CC;" href="' . OW_URL_HOME . 'admin">Admin Panel</a>
  </div>
  </body>
  </html>
  ';

//http://site.com/ow_updates/index2.php?task=update-all-plugins

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
