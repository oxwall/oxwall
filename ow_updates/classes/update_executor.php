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
    const URI_VAR_ACTION = "action";
    const URI_VAR_ACTION_VAL_UPDATE_PLUGIN = "update-plugin";
    const URI_VAR_ACTION_VAL_UPDATE_ALL_PLUGINS = "update-all-plugins";
    const URI_VAR_ACTION_VAL_UPDATE_PLATFORM = "update-platform";
    const URI_VAR_BACK_URI = BOL_StorageService::URI_VAR_BACK_URI;
    const URI_VAR_PLUGIN_KEY = BOL_StorageService::URI_VAR_KEY;
    const STATUS_EMPTY_ACTION = "status_success";
    const STATUS_UP_TO_DATE = "status_up_to_date";
    const STATUS_SUCCESS = "status_success";
    const STATUS_FAIL = "status_success";

    /**
     * @var OW_Database
     */
    private $db;

    /**
     * @var string
     */
    private $dbPrefix;

    public function __construct()
    {
        $this->db = Updater::getDbo();
        $this->dbPrefix = OW_DB_PREFIX;
    }

    /**
     * Updates single plugin
     * 
     * @param string $pluginKey
     * @throws LogicException
     * @throws LogicUpToDateException
     * 
     * @return array
     */
    public function updateSinglePlugin( $pluginKey )
    {
        $pluginData = $this->db->queryForRow("SELECT * FROM `{$this->dbPrefix}base_plugin` WHERE `key` = :key AND `update` = :statusVal",
            array("key" => $pluginKey, "statusVal" => BOL_PluginDao::UPDATE_VAL_MANUAL_UPDATE));

        // plugin not found
        if ( empty($pluginData) )
        {
            throw new LogicException("Plugin not found");
        }

        $this->setMaintenance(true);

        if ( !$this->updatePlugin($pluginData) )
        {
            throw new LogicUpToDateException("Plugin is up to date");
        }

        $this->setDevMode();
        $this->setMaintenance(false);

        return $pluginData;
    }

    /**
     * Updates all plugins
     * 
     * @return int
     * @throws LogicException
     * @throws LogicUpToDateException
     */
    public function updateAllPlugins()
    {
        $result = $this->db->queryForList("SELECT * FROM `{$this->dbPrefix}base_plugin` WHERE `update` = :statusVal",
            array("statusVal" => BOL_PluginDao::UPDATE_VAL_MANUAL_UPDATE));

        // plugin not found
        if ( empty($result) )
        {
            throw new LogicException("No plugins for update");
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

        $this->setMaintenance(false);

        if ( $successCount <= 0 )
        {
            throw new LogicUpToDateException("All plugins are up to date");
        }

        $this->setDevMode();

        return $successCount;
    }

//    public function updatePlatform()
//    {
//        $currentBuild = (int) $this->db->queryForColumn("SELECT `value` FROM `{$this->dbPrefix}base_config` WHERE `key` = 'base' AND `name` = 'soft_build'");
//
//        $currentXmlInfo = (array) simplexml_load_file(OW_DIR_ROOT . "ow_version.xml");
//
//        if ( (int) $currentXmlInfo["build"] > $currentBuild )
//        {
//            $this->setMaintenance(true);
//
//            $owpUpdateDir = UPDATE_DIR_ROOT . "updates" . DS;
//
//            $updateDirList = array();
//
//            $handle = opendir($owpUpdateDir);
//
//            while ( ($item = readdir($handle)) !== false )
//            {
//                if ( $item === "." || $item === ".." )
//                {
//                    continue;
//                }
//
//                $dirPath = $owpUpdateDir . ((int) $item);
//
//                if ( file_exists($dirPath) && is_dir($dirPath) )
//                {
//                    $updateDirList[] = (int) $item;
//                }
//            }
//
//            sort($updateDirList);
//
//            foreach ( $updateDirList as $item )
//            {
//                if ( $item > $currentBuild )
//                {
//                    $this->includeScript($owpUpdateDir . $item . DS . "update.php");
//                }
//
//                $this->db->query("UPDATE `{$this->dbPrefix}base_config` SET `value` = :build WHERE `key` = 'base' AND `name` = 'soft_build'",
//                    array("build" => $currentXmlInfo["build"]));
//                $this->db->query("UPDATE `{$this->dbPrefix}base_config` SET `value` = :version WHERE `key` = 'base' AND `name` = 'soft_version'",
//                    array("version" => $currentXmlInfo["version"]));
//            }
//
//            $db->query("UPDATE `" . OW_DB_PREFIX . "base_config` SET `value` = 0 WHERE `key` = 'base' AND `name` = 'update_soft'");
//
//            $this->setMaintenance(false);
//            $this->setDevMode();
//            $this->writeLog();
//        }
//    }
    /* -------------------------------------------------------------------------------------------------------------- */

    private function updatePlugin( array $pluginArr )
    {
        $pluginRootPath = OW_DIR_PLUGIN . $pluginArr["module"] . DS;

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

                $this->writeLog($pluginArr["key"]);
            }

            $query = "UPDATE `{$this->dbPrefix}base_plugin` SET `build` = :build, `update` = :updateVal, `title` = :title, `description` = :desc WHERE `key` = :key";

            $this->db->query($query,
                array(
                "build" => (int) $xmlInfoArray["build"],
                "key" => $pluginArr["key"],
                "title" => $xmlInfoArray["name"],
                "desc" => $xmlInfoArray["description"],
                "updateVal" => BOL_PluginDao::UPDATE_VAL_UP_TO_DATE)
            );

            return true;
        }

        $query = "UPDATE `{$this->dbPrefix}base_plugin` SET `update` = :updateVal WHERE `key` = :key";

        $this->db->query($query,
            array(
            "key" => $pluginArr["key"],
            "updateVal" => BOL_PluginDao::UPDATE_VAL_UP_TO_DATE)
        );

        return false;
    }

    private function writeLog( $key )
    {
        $entries = UPDATER::getLogger()->getEntries();

        if ( !empty($entries) )
        {
            $query = "INSERT INTO `{$this->dbPrefix}base_log` (`message`, `type`, `key`, `timeStamp`) VALUES (:message, 'ow_update', :key, :time)";
            try
            {
                $this->db->query($query, array("message" => json_encode($entries), "key" => $key, "time" => time()));
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

class LogicUpToDateException extends LogicException
{
    
}
