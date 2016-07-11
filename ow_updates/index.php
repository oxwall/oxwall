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
define('OW_DIR_ROOT', dirname(dirname(__FILE__)) . DS);
define('UPDATE_DIR_ROOT', OW_DIR_ROOT . 'ow_updates' . DS);

require_once OW_DIR_ROOT . 'ow_includes/config.php';
require_once OW_DIR_ROOT . 'ow_includes/define.php';
require_once OW_DIR_UTIL . 'debug.php';
require_once OW_DIR_UTIL . 'string.php';
require_once OW_DIR_UTIL . 'file.php';
require_once UPDATE_DIR_ROOT . 'classes' . DS . 'autoload.php';
require_once UPDATE_DIR_ROOT . 'classes' . DS . 'error_manager.php';
require_once UPDATE_DIR_ROOT . 'classes' . DS . 'updater.php';
require_once OW_DIR_CORE . 'ow.php';
require_once OW_DIR_CORE . 'plugin.php';

spl_autoload_register(array('UPDATE_Autoload', 'autoload'));

UPDATE_ErrorManager::getInstance(true);

$autoloader = UPDATE_Autoload::getInstance();
$autoloader->addPackagePointer('BOL', OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'bol' . DS);
$autoloader->addPackagePointer('BASE_CLASS', OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS);
$autoloader->addPackagePointer('OW', OW_DIR_CORE);
$autoloader->addPackagePointer('UTIL', OW_DIR_UTIL);
$autoloader->addPackagePointer('UPDATE', UPDATE_DIR_ROOT . 'classes' . DS);

$db = Updater::getDbo();

OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());

/* ------------------- CORE UPDATE  ------------------------ */

$currentBuild = (int) $db->queryForColumn("SELECT `value` FROM `" . OW_DB_PREFIX . "base_config` WHERE `key` = 'base' AND `name` = 'soft_build'");

$currentXmlInfo = (array) simplexml_load_file(OW_DIR_ROOT . 'ow_version.xml');

if ( (int) $currentXmlInfo['build'] > $currentBuild )
{
    $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 1 WHERE `key` = 'base' AND `name` = 'maintenance'");

    $owpUpdateDir = UPDATE_DIR_ROOT . 'updates' . DS;

    $updateDirList = array();

    $handle = opendir($owpUpdateDir);

    while ( ($item = readdir($handle)) !== false )
    {
        if ( $item === '.' || $item === '..' )
        {
            continue;
        }

        $dirPath = $owpUpdateDir . ((int) $item);

        if ( file_exists($dirPath) && is_dir($dirPath) )
        {
            $updateDirList[] = (int) $item;
        }
    }

    sort($updateDirList);

    foreach ( $updateDirList as $item )
    {
        if ( $item > $currentBuild )
        {
            include($owpUpdateDir . $item . DS . 'update.php');
        }

//        $updateXmlInfo = (array) simplexml_load_file($owpUpdateDir . $item . DS . 'update.xml');

        $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = :build WHERE `key` = 'base' AND `name` = 'soft_build'", array('build' => $currentXmlInfo['build']));
        $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = :version WHERE `key` = 'base' AND `name` = 'soft_version'", array('version' => $currentXmlInfo['version']));
    }

    $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 0 WHERE `key` = 'base' AND `name` = 'update_soft'");
    $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 0 WHERE `key` = 'base' AND `name` = 'maintenance'");
    $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 1 WHERE `key` = 'base' AND `name` = 'dev_mode'");

    $entries = UPDATER::getLogger()->getEntries();

    if ( !empty($entries) )
    {
        $query = "INSERT INTO `" . OW_DB_PREFIX . "base_log` (`message`, `type`, `key`, `timeStamp`) VALUES (:message, 'ow_update', 'core', :time)";
        try
        {
            $db->query($query, array('message' => json_encode($entries), 'time' => time()));
        }
        catch ( Exception $e )
        {

        }
    }
}

/* ----------------- CORE UPDATE END ------------------------ */

/* ----------------- PLUGIN UPDATE ------------------------ */

if ( !empty($_GET['plugin']) )
{
    $query = "SELECT * FROM `" . OW_DB_PREFIX . "base_plugin` WHERE `key` = :key";
    $result = $db->queryForRow($query, array('key' => trim($_GET['plugin'])));

    // plugin not found
    if ( empty($result) )
    {
        $mode = 'plugin_empty';
        $hcMessage = "Error! Plugin '<b>" . htmlspecialchars($_GET['plugin']) . "</b>' not found.";
    }
    else
    {
        $xmlInfoArray = (array) simplexml_load_file(OW_DIR_ROOT . 'ow_plugins' . DS . $result['module'] . DS . 'plugin.xml');

        if ( (int) $xmlInfoArray['build'] > (int) $result['build'] )
        {
            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 1 WHERE `key` = 'base' AND `name` = 'maintenance'");

            $owpUpdateDir = OW_DIR_ROOT . 'ow_plugins' . DS . $result['module'] . DS . 'update' . DS;

            $updateDirList = array();

            if ( file_exists($owpUpdateDir) )
            {
                $handle = opendir($owpUpdateDir);

                while ( ($item = readdir($handle)) !== false )
                {
                    if ( $item === '.' || $item === '..' )
                    {
                        continue;
                    }

                    if ( file_exists($owpUpdateDir . ((int) $item)) && is_dir($owpUpdateDir . ((int) $item)) )
                    {
                        $updateDirList[] = (int) $item;
                    }
                }

                sort($updateDirList);

                foreach ( $updateDirList as $item )
                {
                    if ( (int) $item > (int) $result['build'] )
                    {
                        include($owpUpdateDir . $item . DS . 'update.php');
                        $query = "UPDATE `" . OW_DB_PREFIX . "base_plugin` SET `build` = :build, `update` = 0 WHERE `key` = :key";
                        $db->query($query, array('build' => (int) $item, 'key' => $result['key']));
                    }
                }
            }

            $entries = UPDATER::getLogger()->getEntries();

            if ( !empty($entries) )
            {
                $query = "INSERT INTO `" . OW_DB_PREFIX . "base_log` (`message`, `type`, `key`, `timeStamp`) VALUES (:message, 'ow_update', :key, :time)";
                try
                {
                    $db->query($query, array('message' => json_encode($entries), 'key' => $result['key'], 'time' => time()));
                }
                catch ( Exception $e )
                {
                    
                }
            }

            $query = "UPDATE `" . OW_DB_PREFIX . "base_plugin` SET `build` = :build, `update` = 0, `title` = :title, `description` = :desc WHERE `key` = :key";
            $db->query($query, array('build' => (int) $xmlInfoArray['build'], 'key' => $result['key'], 'title' => $xmlInfoArray['name'], 'desc' => $xmlInfoArray['description']));

            $mode = 'plugin_update_success';
            $hcMessage = "Update Complete! Plugin '<b>" . $result['key'] . "</b>' successfully updated.";

            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 0 WHERE `key` = 'base' AND `name` = 'maintenance'");
            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 59 WHERE `key` = 'base' AND `name` = 'dev_mode'");
        }
        else
        {
            $db->query("UPDATE `" . OW_DB_PREFIX . "base_plugin` SET `update` = 0 WHERE `key` = :key", array('key' => $result['key']));
            $mode = 'plugin_up_to_date';
            $hcMessage = "Error! Plugin '<b>" . $result['key'] . "</b>' is up to date.";
        }
    }

    // update result actions
    if ( !empty($_GET['back-uri']) )
    {
        $url = build_url_query_string(OW_URL_HOME . urldecode($_GET['back-uri']), array('plugin' => $_GET['plugin'], 'mode' => $mode));
        Header("HTTP/1.1 301 Moved Permanently");
        Header("Location: " . $url);
        exit;
    }
    else
    {
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
                    ' . $hcMessage . ' <br />
                    Go to <a style="color:#3366CC;" href="' . OW_URL_HOME . '">Index Page</a>&nbsp; or &nbsp;<a style="color:#3366CC;" href="' . OW_URL_HOME . 'admin">Admin Panel</a>
                </div>
              </body>
            </html>
            ';
        exit;
    }
}

/* ------------------ PLUGIN UPDATE END -------------------- */


/* ----------------- THEME UPDATE ------------------------ */

if ( !empty($_GET['theme']) )
{
    $query = "SELECT * FROM `" . OW_DB_PREFIX . "base_theme` WHERE `key` = :key";
    $result = $db->queryForRow($query, array('key' => trim($_GET['theme'])));

    // theme not found
    if ( empty($result) )
    {
        $mode = 'theme_empty';
        $hcMessage = "Error! Theme '<b>" . htmlspecialchars($_GET['theme']) . "</b>' not found.";
    }
    else
    {
        $xmlInfoArray = (array) simplexml_load_file(OW_DIR_ROOT . 'ow_themes' . DS . $result['key'] . DS . 'theme.xml');

        if ( (int) $xmlInfoArray['build'] > (int) $result['build'] )
        {
            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 1 WHERE `key` = 'base' AND `name` = 'maintenance'");

            $entries = UPDATER::getLogger()->getEntries();

            if ( !empty($entries) )
            {
                $query = "INSERT INTO `" . OW_DB_PREFIX . "base_log` (`message`, `type`, `key`, `timeStamp`) VALUES (:message, 'ow_update', :key, :time)";
                try
                {
                    $db->query($query, array('message' => json_encode($entries), 'key' => $result['key'], 'time' => time()));
                }
                catch ( Exception $e )
                {

                }
            }

            $query = "UPDATE `" . OW_DB_PREFIX . "base_theme` SET `update` = 0 WHERE `key` = :key";
            $db->query($query, array('key' => $result['key']));

            BOL_ThemeService::getInstance()->updateThemeInfo($result['key'], true);

            $mode = 'theme_update_success';
            $hcMessage = "Update Complete! Theme '<b>" . $result['title'] . "</b>' successfully updated.";

            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 0 WHERE `key` = 'base' AND `name` = 'maintenance'");
            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 1 WHERE `key` = 'base' AND `name` = 'dev_mode'");
        }
        else
        {
            $db->query("UPDATE `" . OW_DB_PREFIX . "base_theme` SET `update` = 0 WHERE `key` = :key", array('key' => $result['key']));
            $mode = 'theme_up_to_date';
            $hcMessage = "Error! Theme '<b>" . $result['title'] . "</b>' is up to date.";
        }
    }

    // update result actions
    if ( !empty($_GET['back-uri']) )
    {
        $url = build_url_query_string(OW_URL_HOME . urldecode($_GET['back-uri']), array('theme' => $_GET['theme'], 'mode' => $mode));
        Header("HTTP/1.1 301 Moved Permanently");
        Header("Location: " . $url);
        exit;
    }
    else
    {
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
                    ' . $hcMessage . ' <br />
                    Go to <a style="color:#3366CC;" href="' . OW_URL_HOME . '">Index Page</a>&nbsp; or &nbsp;<a style="color:#3366CC;" href="' . OW_URL_HOME . 'admin">Admin Panel</a>
                </div>
              </body>
            </html>
            ';
        exit;
    }
}

/* ------------------ THEME UPDATE END -------------------- */

if ( !empty($owpUpdateDir) )
{
    echo '
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
  <html>
  <head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body>
  <div style="width:400px;margin:300px auto 0;font:14px Tahoma;">
  <h3 style="color:#CF3513;font:bold 20px Tahoma;">Update Complete</h3>
  Your version has been successfully updated to <b>' . $currentXmlInfo['version'] . '</b>!<br />
  Go to <a style="color:#3366CC;" href="' . OW_URL_HOME . '">Index Page</a>&nbsp; or &nbsp;<a style="color:#3366CC;" href="' . OW_URL_HOME . 'admin">Admin Panel</a>
  </div>
  </body>
  </html>
  ';
}
else
{
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
  Your version is up to date. <br />
  Go to <a style="color:#3366CC;" href="' . OW_URL_HOME . '">Index Page</a>&nbsp; or &nbsp;<a style="color:#3366CC;" href="' . OW_URL_HOME . 'admin">Admin Panel</a>
  </div>
  </body>
  </html>
  ';
}

/* ------------------- CORE UPDATE END ------------------------ */

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
