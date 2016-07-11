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
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PluginService
{
    /**
     * @deprecated since version 1.8.1
     */
    const UPDATE_SERVER = BOL_StorageService::UPDATE_SERVER;

    /* list of plugin scripts */
    const SCRIPT_INIT = "init.php";
    const SCRIPT_INSTALL = "install.php";
    const SCRIPT_UNINSTALL = "uninstall.php";
    const SCRIPT_ACTIVATE = "activate.php";
    const SCRIPT_DEACTIVATE = "deactivate.php";
    const PLUGIN_INFO_XML = "plugin.xml";
    /* ---------------------------------------------------------------------- */
    const PLUGIN_STATUS_UP_TO_DATE = BOL_PluginDao::UPDATE_VAL_UP_TO_DATE;
    const PLUGIN_STATUS_UPDATE = BOL_PluginDao::UPDATE_VAL_UPDATE;
    const PLUGIN_STATUS_MANUAL_UPDATE = BOL_PluginDao::UPDATE_VAL_MANUAL_UPDATE;
    const MANUAL_UPDATES_CHECK_INTERVAL_IN_SECONDS = 30;

    /**
     * @var BOL_PluginDao
     */
    private $pluginDao;

    /**
     * @var array
     */
    private $pluginListCache;

    /**
     * Singleton instance.
     *
     * @var BOL_PluginService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PluginService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->pluginDao = BOL_PluginDao::getInstance();
    }

    /**
     * Saves and updates plugin items.
     *
     * @param BOL_Plugin $pluginItem
     */
    public function savePlugin( BOL_Plugin $pluginItem )
    {
        $this->pluginDao->save($pluginItem);
        $this->updatePluginListCache();
    }

    /**
     * Removes plugin entry in DB.
     *
     * @param integer $id
     */
    public function deletePluginById( $id )
    {
        $this->pluginDao->deleteById($id);
        $this->updatePluginListCache();
    }

    /**
     * Returns all installed plugins.
     *
     * @return array<BOL_Plugin>
     */
    public function findAllPlugins()
    {
        return $this->getPluginListCache();
    }

    /**
     * Finds plugin item for provided key.
     *
     * @param string $key
     * @return BOL_Plugin
     */
    public function findPluginByKey( $key, $developerKey = null )
    {
        $key = strtolower($key);
        $pluginList = $this->getPluginListCache();

        if ( !array_key_exists($key, $pluginList) || ( $developerKey !== null && $pluginList[$key]->getDeveloperKey() != strtolower($developerKey) ) )
        {
            return null;
        }

        return $pluginList[$key];
    }

    /**
     * Returns list of active plugins.
     *
     * @return array
     */
    public function findActivePlugins()
    {
        $activePlugins = array();
        $pluginList = $this->getPluginListCache();

        /* @var $plugin BOL_Plugin */
        foreach ( $pluginList as $plugin )
        {
            if ( $plugin->isActive() )
            {
                $activePlugins[] = $plugin;
            }
        }

        return $activePlugins;
    }

    /**
     * Returns list of plugins available for installation.
     *
     * @return array
     */
    public function getAvailablePluginsList()
    {
        $availPlugins = array();
        $dbPluginsArray = array_keys($this->getPluginListCache());

        $xmlPlugins = $this->getPluginsXmlInfo();

        foreach ( $xmlPlugins as $key => $plugin )
        {
            if ( !in_array($plugin["key"], $dbPluginsArray) )
            {
                $availPlugins[$key] = $plugin;
            }
        }

        return $availPlugins;
    }

    /**
     * Returns all plugins XML info.
     */
    public function getPluginsXmlInfo()
    {
        $resultArray = array();

        $xmlFiles = UTIL_File::findFiles(OW_DIR_PLUGIN, array("xml"), 1);

        foreach ( $xmlFiles as $pluginXml )
        {
            if ( basename($pluginXml) == self::PLUGIN_INFO_XML )
            {
                $pluginInfo = $this->readPluginXmlInfo($pluginXml);

                if ( $pluginInfo !== null )
                {
                    $resultArray[$pluginInfo["key"]] = $pluginInfo;
                }
            }
        }

        return $resultArray;
    }

    /**
     * Updates plugin meta info in DB using data in plugin.xml
     */
    public function updatePluginsXmlInfo()
    {
        $info = $this->getPluginsXmlInfo();

        foreach ( $info as $key => $pluginInfo )
        {
            $dto = $this->pluginDao->findPluginByKey($key);

            if ( $dto !== null )
            {
                $dto->setTitle($pluginInfo["title"]);
                $dto->setDescription($pluginInfo["description"]);
                $dto->setDeveloperKey($pluginInfo["developerKey"]);
                $this->pluginDao->save($dto);
            }
        }
    }

    /**
     * Reads provided XML file and returns plugin info array.
     *
     * @param string $pluginXmlPath
     * @return array|null
     */
    public function readPluginXmlInfo( $pluginXmlPath )
    {
        if ( !file_exists($pluginXmlPath) )
        {
            return null;
        }

        $propList = array("key", "name", "description", "license", "author", "build", "copyright", "licenseUrl");
        $xmlInfo = (array) simplexml_load_file($pluginXmlPath);

        if ( !$xmlInfo )
        {
            return null;
        }

        foreach ( $propList as $prop )
        {
            if ( empty($xmlInfo[$prop]) )
            {
                return null;
            }
        }

        $xmlInfo["title"] = $xmlInfo["name"];
        $xmlInfo["path"] = dirname($pluginXmlPath);
        return $xmlInfo;
    }

    /**
     * Returns the count of plugins with update available.
     * 
     * @return type
     */
    public function findPluginsForUpdateCount()
    {
        return $this->pluginDao->findPluginsForUpdateCount();
    }

    /**
     * Returns all regular (non system) plugins.
     *
     * @return array
     */
    public function findRegularPlugins()
    {
        $regularPlugins = array();

        /* @var $plugin BOL_Plugin */
        foreach ( $this->getPluginListCache() as $plugin )
        {
            if ( !$plugin->isSystem() )
            {
                $regularPlugins[] = $plugin;
            }
        }

        return $regularPlugins;
    }

    /**
     * Installs plugins.
     * Installs all available system plugins
     */
    public function installSystemPlugins()
    {
        $files = UTIL_File::findFiles(OW_DIR_SYSTEM_PLUGIN, array("xml"), 1);
        $pluginData = array();
        $tempPluginData = array();

// first element should be BASE plugin
        foreach ( $files as $file )
        {
            $tempArr = $this->readPluginXmlInfo($file);
            $pathArr = explode(DS, dirname($file));
            $tempArr["dir_name"] = array_pop($pathArr);

            if ( $tempArr["key"] == "base" )
            {
                $pluginData[$tempArr["key"]] = $tempArr;
            }
            else
            {
                $tempPluginData[$tempArr["key"]] = $tempArr;
            }
        }

        foreach ( $tempPluginData as $key => $val )
        {
            $pluginData[$key] = $val;
        }

        if ( !array_key_exists("base", $pluginData) )
        {
            throw new LogicException("Base plugin is not found in `{$basePluginRootDir}`!");
        }

// install plugins list
        foreach ( $pluginData as $pluginInfo )
        {
            $pluginDto = new BOL_Plugin();
            $pluginDto->setTitle((!empty($pluginInfo["title"]) ? trim($pluginInfo["title"]) : "No Title"));
            $pluginDto->setDescription((!empty($pluginInfo["description"]) ? trim($pluginInfo["description"]) : "No Description"));
            $pluginDto->setKey(trim($pluginInfo["key"]));
            $pluginDto->setModule($pluginInfo["dir_name"]);
            $pluginDto->setIsActive(true);
            $pluginDto->setIsSystem(true);
            $pluginDto->setBuild((int) $pluginInfo["build"]);

            if ( !empty($pluginInfo["developerKey"]) )
            {
                $pluginDto->setDeveloperKey(trim($pluginInfo["developerKey"]));
            }

            $this->pluginListCache[$pluginDto->getKey()] = $pluginDto;

            $plugin = new OW_Plugin($pluginDto);

            $this->includeScript($plugin->getRootDir() . BOL_PluginService::SCRIPT_INSTALL);

            $this->pluginDao->save($pluginDto);
            $this->updatePluginListCache();
            $this->addPluginDirs($pluginDto);
        }
    }

    /**
     * Installs plugins.
     *
     * @param string $key
     */
    public function install( $key, $generateCache = true )
    {
        $availablePlugins = $this->getAvailablePluginsList();

        if ( empty($key) || !array_key_exists($key, $availablePlugins) )
        {
            throw new LogicException("Invalid plugin key - `{$key}` provided for install!");
        }

        $pluginInfo = $availablePlugins[$key];
        $dirArray = explode(DS, $pluginInfo["path"]);
        $moduleName = array_pop($dirArray);

        // add DB entry
        $pluginDto = new BOL_Plugin();
        $pluginDto->setTitle((!empty($pluginInfo["title"]) ? trim($pluginInfo["title"]) : "No Title"));
        $pluginDto->setDescription((!empty($pluginInfo["description"]) ? trim($pluginInfo["description"]) : "No Description"));
        $pluginDto->setKey(trim($pluginInfo["key"]));
        $pluginDto->setModule($moduleName);
        $pluginDto->setIsActive(true);
        $pluginDto->setIsSystem(false);
        $pluginDto->setBuild((int) $pluginInfo["build"]);

        if ( !empty($pluginInfo["developerKey"]) )
        {
            $pluginDto->setDeveloperKey(trim($pluginInfo["developerKey"]));
        }

        $this->pluginDao->save($pluginDto);
        $this->updatePluginListCache();

        $this->addPluginDirs($pluginDto);

        $plugin = new OW_Plugin($pluginDto);

        $this->includeScript($plugin->getRootDir() . BOL_PluginService::SCRIPT_INSTALL);
        $this->includeScript($plugin->getRootDir() . BOL_PluginService::SCRIPT_ACTIVATE);

        $pluginDto = $this->findPluginByKey($pluginDto->getKey());

        if ( $generateCache )
        {
            BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
        }

        // trigger event
        OW::getEventManager()->trigger(new OW_Event(OW_EventManager::ON_AFTER_PLUGIN_INSTALL,
            array("pluginKey" => $pluginDto->getKey())));
        return $pluginDto;
    }

    /**
     * Creates platform reserved dirs for plugin, copies all plugin static data
     * 
     * @param BOL_Plugin $pluginDto
     */
    public function addPluginDirs( BOL_Plugin $pluginDto )
    {
        $plugin = new OW_Plugin($pluginDto);

        if ( file_exists($plugin->getStaticDir()) )
        {
            UTIL_File::copyDir($plugin->getStaticDir(), $plugin->getPublicStaticDir());
        }

        // create dir in pluginfiles
        if( file_exists($plugin->getInnerPluginFilesDir()) )
        {
            UTIL_File::copyDir($plugin->getInnerPluginFilesDir(), $plugin->getPluginFilesDir());
        }
        else if ( !file_exists($plugin->getPluginFilesDir()) )
        {
            mkdir($plugin->getPluginFilesDir());
            chmod($plugin->getPluginFilesDir(), 0777);
        }

        // create dir in userfiles
        if( file_exists($plugin->getInnerUserFilesDir()) )
        {
            OW::getStorage()->copyDir($plugin->getInnerUserFilesDir(), $plugin->getUserFilesDir());
        }
        else if ( !file_exists($plugin->getUserFilesDir()) )
        {
            OW::getStorage()->mkdir($plugin->getUserFilesDir());
        }
    }

    /**
     * Uninstalls plugin
     *
     * @param string $pluginKey
     */
    public function uninstall( $pluginKey )
    {
        if ( empty($pluginKey) )
        {
            throw new LogicException("Empty plugin key provided for uninstall");
        }

        $pluginDto = $this->findPluginByKey(trim($pluginKey));

        if ( $pluginDto === null )
        {
            throw new LogicException("Invalid plugin key - `{$pluginKey}` provided for uninstall!");
        }

        $plugin = new OW_Plugin($pluginDto);

        // trigger event
        OW::getEventManager()->trigger(new OW_Event(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL,
            array("pluginKey" => $pluginDto->getKey())));

        $this->includeScript($plugin->getRootDir() . BOL_PluginService::SCRIPT_DEACTIVATE);
        $this->includeScript($plugin->getRootDir() . BOL_PluginService::SCRIPT_UNINSTALL);

        // delete plugin work dirs
        $dirsToRemove = array(
            $plugin->getPluginFilesDir(),
            $plugin->getUserFilesDir(),
            $plugin->getPublicStaticDir()
        );

        foreach ( $dirsToRemove as $dir )
        {
            if ( file_exists($dir) )
            {
                UTIL_File::removeDir($dir);
            }
        }

        // remove plugin configs
        OW::getConfig()->deletePluginConfigs($pluginDto->getKey());

        // delete language prefix
        $prefixId = BOL_LanguageService::getInstance()->findPrefixId($pluginDto->getKey());

        if ( !empty($prefixId) )
        {
            BOL_LanguageService::getInstance()->deletePrefix($prefixId, true);
        }

        //delete authorization stuff
        BOL_AuthorizationService::getInstance()->deleteGroup($pluginDto->getKey());

        // drop plugin tables
        $tables = OW::getDbo()->queryForColumnList("SHOW TABLES LIKE '" . str_replace('_', '\_', OW_DB_PREFIX) . $pluginDto->getKey() . "\_%'");

        if ( !empty($tables) )
        {
            $query = "DROP TABLE ";

            foreach ( $tables as $table )
            {
                $query .= "`" . $table . "`,";
            }

            $query = substr($query, 0, -1);

            OW::getDbo()->query($query);
        }

        //remove entry in DB
        $this->deletePluginById($pluginDto->getId());
        $this->updatePluginListCache();

        // trigger event
        OW::getEventManager()->trigger(new OW_Event(OW_EventManager::ON_AFTER_PLUGIN_UNINSTALL,
            array("pluginKey" => $pluginDto->getKey())));
    }

    /**
     * Activates plugin
     * 
     * @param string $key
     */
    public function activate( $key )
    {
        $pluginDto = $this->pluginDao->findPluginByKey($key);

        if ( $pluginDto == null )
        {
            throw new LogicException("Can't activate {$key} plugin!");
        }

        $pluginDto->setIsActive(true);
        $this->pluginDao->save($pluginDto);
        OW::getPluginManager()->addPackagePointers($pluginDto);

        $this->includeScript(OW_DIR_PLUGIN . $pluginDto->getModule() . DS . self::SCRIPT_ACTIVATE);

        $this->updatePluginListCache();

        // trigger event
        $event = new OW_Event(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array("pluginKey" => $pluginDto->getKey()));
        OW::getEventManager()->trigger($event);
    }

    /**
     * Deactivates plugin
     * 
     * @param sring $key
     */
    public function deactivate( $key )
    {
        $pluginDto = $this->pluginDao->findPluginByKey($key);

        if ( $pluginDto == null )
        {
            throw new LogicException("Can't deactivate {$key} plugin!");
        }

        // trigger event
        $event = new OW_Event(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array("pluginKey" => $pluginDto->getKey()));
        OW::getEventManager()->trigger($event);

        $pluginDto->setIsActive(false);
        $this->pluginDao->save($pluginDto);

        $this->includeScript(OW::getPluginManager()->getPlugin($pluginDto->getKey())->getRootDir() . self::SCRIPT_DEACTIVATE);

        $this->updatePluginListCache();

        $event = new OW_Event(OW_EventManager::ON_AFTER_PLUGIN_DEACTIVATE, array("pluginKey" => $pluginDto->getKey()));
        OW::getEventManager()->trigger($event);
    }

    /**
     * Returns the count of plugins available for update
     * 
     * @return int
     */
    public function getPluginsToUpdateCount()
    {
        return $this->pluginDao->findPluginsForUpdateCount();
    }

    /**
     * Checks if plugin source code was updated, if yes changes the update status in DB
     * 
     * @return void
     */
    public function checkManualUpdates()
    {
        $timestamp = OW::getConfig()->getValue("base", "check_mupdates_ts");

        if ( ( time() - (int) $timestamp ) < self::MANUAL_UPDATES_CHECK_INTERVAL_IN_SECONDS )
        {
            return;
        }

        $plugins = $this->pluginDao->findAll();
        $xmlInfo = $this->getPluginsXmlInfo();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            if ( !empty($xmlInfo[$plugin->getKey()]) && (int) $plugin->getBuild() < (int) $xmlInfo[$plugin->getKey()]['build'] )
            {
                $plugin->setUpdate(BOL_PluginDao::UPDATE_VAL_MANUAL_UPDATE);
                $this->pluginDao->save($plugin);
            }
        }

        OW::getConfig()->saveConfig("base", "check_mupdates_ts", time());
    }

    /**
     * Returns next plugin for manual update if it's available
     * 
     * @return BOL_Plugin
     */
    public function findNextManualUpdatePlugin()
    {
        return $this->pluginDao->findPluginForManualUpdate();
    }

    /**
     * Returns plugins with invalid license
     * 
     * @return array
     */
    public function findPluginsWithInvalidLicense()
    {
        return $this->pluginDao->findPluginsWithInvalidLicense();
    }
    /* ---------------------------------------------------------------------- */

    private function updatePluginListCache()
    {
        $this->pluginListCache = array();
        $dbData = $this->pluginDao->findAll();

        /* @var $plugin BOL_Plugin */
        foreach ( $dbData as $plugin )
        {
            $this->pluginListCache[$plugin->getKey()] = $plugin;
        }
    }

    private function getPluginListCache()
    {
        if ( !$this->pluginListCache )
        {
            $this->updatePluginListCache();
        }

        return $this->pluginListCache;
    }

    /**
     * @param string $scriptPath
     */
    public function includeScript( $scriptPath )
    {
        if ( file_exists($scriptPath) )
        {
            include_once $scriptPath;
        }
    }

    /**
     * @deprecated since version 1.8.1
     */
    public function checkUpdates()
    {
        BOL_StorageService::getInstance()->checkUpdates();
    }
}
