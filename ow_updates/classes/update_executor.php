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
 * @since 1.8.3
 */
class UPDATE_UpdateExecutor
{
    /**
     * @var OW_Database
     */
    private $db;

    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * @var string
     */
    private $heading;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $resultCode;

    /**
     * @var array
     */
    private $params;

    public function __construct( array $params )
    {
        $this->db = Updater::getDbo();
        $this->dbPrefix = OW_DB_PREFIX;
        $this->params = $params;
    }

    public function runTask()
    {
        if ( empty($this->params["task"]) )
        {
            return;
        }

        $task = trim($this->params["task"]);

        if ( $task == "updatePlugin" )
        {
            if ( empty($this->params["pluginKey"]) )
            {
                $this->message = "Error! Empty plugin key.";
                $this->resultCode = "plugin_empty";
                return;
            }

            $this->updateSinglePlugin($this->params["pluginKey"]);
        }

        if ( $task == "update-all-plugins" )
        {
            $this->updateAllPlugins();
        }

        if ( $task == "platform" )
        {
            $this->updatePlatform();
        }
    }

    public function updateSinglePlugin( $pluginKey )
    {
        if ( empty($this->params["plugin"]) )
        {
            $this->message = "Error! Invalid plugin key.";
            $this->resultCode = "plugin_empty";
            return;
        }

        $pluginKey = htmlspecialchars(trim($this->params["plugin"]));
        $result = $this->db->queryForRow("SELECT * FROM `{$this->dbPrefix}base_plugin` WHERE `key` = :key AND `update` = 2",
            array("key" => $pluginKey));

        // plugin not found
        if ( empty($result) )
        {
            $this->message = "Error! Plugin `<b>{$pluginKey}</b>` not found.";
            $this->resultCode = "plugin_empty";
            return;
        }

        $this->setMaintenance(true);

        if ( !$this->updatePlugin($result) )
        {
            $this->message = "Error! Plugin '<b>{$pluginArr['key']}</b>' is up to date.";
            $this->resultCode = "plugin_up_to_date";
            $this->setDevMode();
        }
        else
        {
            $this->message = "Update Complete! Plugin '<b>{$pluginArr['key']}</b>' successfully updated.";
            $this->resultCode = "plugin_update_success";
        }

        $this->setMaintenance(false);
    }

    public function updateAllPlugins()
    {
        $result = $this->db->queryForList("SELECT * FROM `{$this->dbPrefix}base_plugin` WHERE `update` = 2");

        // plugin not found
        if ( empty($result) )
        {
            $this->message = "Error! No plugins for update.";
            $this->resultCode = "plugin_empty";
            return;
        }

        $this->setMaintenance(true);

        $successCount = 0;
        $failCount = 0;

        foreach ( $result as $plugin )
        {
            if ( !$this->updatePlugin($plugin) )
            {
                $failCount++;
            }
            else
            {
                $successCount++;
            }
        }

        $this->setDevMode();
        $this->setMaintenance(false);

        if ( $successCount > 0 )
        {
            $this->resultCode = "plugin_update_success";
            $this->message = "Update Complete! {$successCount} plugins successfully updated.";
        }
        else
        {
            $this->message = "Error! All plugins are up to date.";
            $this->resultCode = "plugin_up_to_date";
        }
    }

    public function updateSingleTheme()
    {
        
    }

    public function updateAllThemes()
    {
        
    }

    public function updatePlatform()
    {
        $currentBuild = (int) $this->db->queryForColumn("SELECT `value` FROM `{$this->dbPrefix}base_config` WHERE `key` = 'base' AND `name` = 'soft_build'");

        $currentXmlInfo = (array) simplexml_load_file(OW_DIR_ROOT . "ow_version.xml");

        if ( (int) $currentXmlInfo["build"] > $currentBuild )
        {
            $this->setMaintenance(true);

            $owpUpdateDir = UPDATE_DIR_ROOT . "updates" . DS;

            $updateDirList = array();

            $handle = opendir($owpUpdateDir);

            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === "." || $item === ".." )
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
                    $this->includeScript($owpUpdateDir . $item . DS . "update.php");
                }

                $this->db->query("UPDATE `{$this->dbPrefix}base_config` SET `value` = :build WHERE `key` = 'base' AND `name` = 'soft_build'",
                    array("build" => $currentXmlInfo["build"]));
                $this->db->query("UPDATE `{$this->dbPrefix}base_config` SET `value` = :version WHERE `key` = 'base' AND `name` = 'soft_version'",
                    array("version" => $currentXmlInfo["version"]));
            }

            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 0 WHERE `key` = 'base' AND `name` = 'update_soft'");

            $this->setMaintenance(false);
            $this->setDevMode();
            $this->writeLog();
        }
    }
    /* -------------------------------------------------------------------------------------------------------------- */

    public function processResult()
    {
        if ( !empty($this->params["back-uri"]) )
        {
            $url = build_url_query_string(OW_URL_HOME . urldecode($this->params["back-uri"]),
                array_merge($this->params, array("mode" => $this->resultCode)));
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
  <h3 style="color:#CF3513;font:bold 20px Tahoma;">' . ($this->heading ? $this->heading : "Update Request") . '</h3>
  ' . $this->message . ' <br />
  Go to <a style="color:#3366CC;" href="' . OW_URL_HOME . '">Index Page</a>&nbsp; or &nbsp;<a style="color:#3366CC;" href="' . OW_URL_HOME . 'admin">Admin Panel</a>
  </div>
  </body>
  </html>
  ';
    }

    private function updateTheme( array $themeArr )
    {
        //NO need to update themes in this script
    }

    private function updatePlugin( array $pluginArr )
    {
        $pluginRootPath = OW_DIR_PLUGIN . $pluginArr["module"] . DS;
        $result = false;

        $xmlInfoArray = (array) simplexml_load_file("{$pluginRootPath}plugin.xml");

        if ( !$xmlInfoArray )
        {
            return false;
        }

        if ( (int) $xmlInfoArray["build"] > (int) $pluginArr["build"] )
        {
            $owpUpdateDir = $pluginRootPath . "update" . DS;

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

                    $updateItemPath = $owpUpdateDir . intval($item);

                    if ( file_exists($updateItemPath) && is_dir($updateItemPath) )
                    {
                        $updateDirList[] = intval($item);
                    }
                }

                sort($updateDirList);

                foreach ( $updateDirList as $item )
                {
                    $scriptPath = $owpUpdateDir . $item . DS . "update.php";

                    if ( intval($item) > intval($pluginArr["build"]) && file_exists($scriptPath) )
                    {
                        $this->includeScript($scriptPath);
                        $query = "UPDATE `{$this->dbPrefix}base_plugin` SET `build` = :build, `update` = 0 WHERE `key` = :key";
                        $this->db->query($query, array("build" => (int) $item, "key" => $pluginArr["key"]));
                    }
                }

                $this->writeLog();
            }

            $query = "UPDATE `{$this->dbPrefix}base_plugin` SET `build` = :build, `update` = 0, `title` = :title, `description` = :desc WHERE `key` = :key";

            $this->db->query($query,
                array(
                "build" => (int) $xmlInfoArray["build"],
                "key" => $pluginArr["key"],
                "title" => $xmlInfoArray["name"],
                "desc" => $xmlInfoArray["description"])
            );

            return true;
        }

        return false;
    }

    private function writeLog()
    {
        $entries = UPDATER::getLogger()->getEntries();

        if ( !empty($entries) )
        {
            $query = "INSERT INTO `{$this->dbPrefix}base_log` (`message`, `type`, `key`, `timeStamp`) VALUES (:message, 'ow_update', :key, :time)";
            try
            {
                $this->db->query($query,
                    array("message" => json_encode($entries), "key" => $pluginArr["key"], "time" => time()));
            }
            catch ( Exception $e )
            {
                
            }
        }
    }

    private function includeScript( $path )
    {
        include($path);
    }

    private function setMaintenance( $mode )
    {
        $mode = $mode ? 1 : 0;

        $this->db->query("UPDATE `{$this->dbPrefix}base_config` SET `value` = :mode WHERE `key` = 'base' AND `name` = 'maintenance'",
            array("mode" => $mode));
    }

    private function setDevMode( $mode = null )
    {
        $mode = $mode ? intval($mode) : 1;

        $this->db->query("UPDATE `{$this->dbPrefix}base_config` SET `value` = 1 WHERE `key` = 'base' AND `name` = 'dev_mode'",
            array("mode" => $mode));
    }
}
