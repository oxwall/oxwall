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
    /* list of plugin scripts */
    const SCRIPT_INIT = 'init.php';
    const SCRIPT_INSTALL = 'install.php';
    const SCRIPT_UNINSTALL = 'uninstall.php';
    const SCRIPT_ACTIVATE = 'activate.php';
    const SCRIPT_DEACTIVATE = 'deactivate.php';
    const PLUGIN_INFO_XML = "plugin.xml";

    /**
     * @var BOL_PluginDao
     */
    private $pluginDao;

    /**
     * @var array
     */
    private $pluginDaoCache;

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
        $this->readPluginsList();
    }

    public function readPluginsList()
    {
        $this->pluginDaoCache = $this->pluginDao->findAll();
    }

    /**
     * Saves and updates plugin items.
     *
     * @param BOL_Plugin $pluginItem
     */
    public function savePlugin( BOL_Plugin $pluginItem )
    {
        $this->pluginDao->save($pluginItem);
        $this->pluginDaoCache = $this->pluginDao->findAll();
    }

    /**
     * Removes plugin entry in DB.
     *
     * @param integer $id
     */
    public function deletePluginById( $id )
    {
        $this->pluginDao->deleteById($id);
        $this->pluginDaoCache = $this->pluginDao->findAll();
    }

    /**
     * Returns all installed plugins.
     *
     * @return array<BOL_Plugin>
     */
    public function findAllPlugins()
    {
        return $this->pluginDaoCache;
    }

    /**
     * Finds plugin item for provided key.
     *
     * @param string $key
     * @return BOL_Plugin
     */
    public function findPluginByKey( $key, $developerKey = null )
    {
        /* @var $plugin BOL_Plugin */
        foreach ( $this->pluginDaoCache as $plugin )
        {
            if ( $developerKey !== null )
            {
                if ( $plugin->getKey() == $key && $plugin->getDeveloperKey() == $developerKey )
                {
                    return $plugin;
                }
            }
            else if ( $plugin->getKey() == $key )
            {
                return $plugin;
            }
        }
    }

    /**
     * Returns list of active plugins.
     *
     * @return array
     */
    public function findActivePlugins()
    {
        $activePlugins = array();

        /* @var $plugin BOL_Plugin */
        foreach ( $this->pluginDaoCache as $plugin )
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
        $dbPlugins = $this->pluginDaoCache;
        $dbPluginsArray = array();

        /* @var $plugin BOL_Plugin */
        foreach ( $dbPlugins as $plugin )
        {
            $dbPluginsArray[] = $plugin->getKey();
        }

        $xmlPlugins = $this->getPluginsXmlInfo();

        foreach ( $xmlPlugins as $key => $plugin )
        {
            if ( !in_array($plugin['key'], $dbPluginsArray) )
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

        $xmlFiles = UTIL_File::findFiles(OW_DIR_PLUGIN, array('xml'), 1);

        foreach ( $xmlFiles as $pluginXml )
        {
            if ( basename($pluginXml) === 'plugin.xml' )
            {
                $pluginInfo = $this->readPluginXmlInfo($pluginXml);
                if ( $pluginInfo !== null )
                {
                    $resultArray[$pluginInfo['key']] = $pluginInfo;
                }
            }
        }

        return $resultArray;
    }

    public function updatePluginsXmlInfo()
    {
        $info = $this->getPluginsXmlInfo();

        foreach ( $info as $key => $pluginInfo )
        {
            $dto = $this->pluginDao->findPluginByKey($key);

            if ( $dto !== null )
            {
                $dto->setTitle($pluginInfo['title']);
                $dto->setDescription($pluginInfo['description']);
                $dto->setDeveloperKey($pluginInfo['developerKey']);
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
        $xml = (array) simplexml_load_file($pluginXmlPath);

        if ( empty($xml['key']) || empty($xml['name']) || empty($xml['description']) || empty($xml['license']) ||
            empty($xml['author']) || empty($xml['build']) || empty($xml['copyright']) || empty($xml['licenseUrl']) )
        {
            return null;
        }

        $xml['title'] = $xml['name'];
        unset($xml['name']);
        $xml['path'] = dirname($pluginXmlPath);

        return $xml;
    }

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
        foreach ( $this->pluginDaoCache as $plugin )
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
     *
     * @param string $key
     */
    public function install( $key, $generateCache = true )
    {
        $availablePlugins = $this->getAvailablePluginsList();

        if ( empty($key) || !array_key_exists($key, $availablePlugins) )
        {
            throw new LogicException('Invalid plugin key - `' . $key . '` provided!');
        }

        $pluginInfo = $availablePlugins[$key];
        $dirArray = explode(DS, $pluginInfo['path']);
        $moduleName = array_pop($dirArray);

        // add DB entry
        $pluginDto = new BOL_Plugin();
        $pluginDto->setTitle((!empty($pluginInfo['title']) ? trim($pluginInfo['title']) : 'No Title'));
        $pluginDto->setDescription((!empty($pluginInfo['description']) ? trim($pluginInfo['description']) : 'No Description'));
        $pluginDto->setKey(trim($pluginInfo['key']));
        $pluginDto->setModule($moduleName);
        $pluginDto->setIsActive(true);
        $pluginDto->setIsSystem(false);
        $pluginDto->setBuild((int) $pluginInfo['build']);

        if ( !empty($pluginInfo['developerKey']) )
        {
            $pluginDto->setDeveloperKey(trim($pluginInfo['developerKey']));
        }

        $this->pluginDao->save($pluginDto);

        $this->readPluginsList();
        OW::getPluginManager()->readPluginsList();

        // copy static dir
        $pluginStaticDir = OW_DIR_PLUGIN . $pluginDto->getModule() . DS . 'static' . DS;

        if ( !defined('OW_PLUGIN_XP') && file_exists($pluginStaticDir) )
        {
            $staticDir = OW_DIR_STATIC_PLUGIN . $pluginDto->getModule() . DS;

            if ( !file_exists($staticDir) )
            {
                mkdir($staticDir);
                chmod($staticDir, 0777);
            }

            UTIL_File::copyDir($pluginStaticDir, $staticDir);
        }

        // create dir in pluginfiles
        $pluginfilesDir = OW_DIR_PLUGINFILES . $pluginDto->getModule();

        if ( !file_exists($pluginfilesDir) )
        {
            mkdir($pluginfilesDir);
            chmod($pluginfilesDir, 0777);
        }

        // create dir in userfiles
        $userfilesDir = OW_DIR_PLUGIN_USERFILES . $pluginDto->getModule();

        if ( !file_exists($userfilesDir) )
        {
            mkdir($userfilesDir);
            chmod($userfilesDir, 0777);
        }

        include_once OW_DIR_PLUGIN . $pluginDto->getModule() . DS . 'install.php';
        include_once OW_DIR_PLUGIN . $pluginDto->getModule() . DS . 'activate.php';

        if ( $generateCache )
        {
            BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
        }

        // trigger event
        $event = new OW_Event(OW_EventManager::ON_AFTER_PLUGIN_INSTALL, array('pluginKey' => $pluginDto->getKey()));
        OW::getEventManager()->trigger($event);

        return $pluginDto;
    }

    /**
     * Uninstalls plugin.
     *
     * @param string $key
     */
    public function uninstall( $key )
    {
        if ( empty($key) )
        {
            throw new LogicException('');
        }

        $pluginDto = $this->findPluginByKey(trim($key));

        if ( $pluginDto === null )
        {
            throw new LogicException('');
        }

        // trigger event
        $event = new OW_Event(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array('pluginKey' => $pluginDto->getKey()));
        OW::getEventManager()->trigger($event);

        include OW_DIR_PLUGIN . $pluginDto->getModule() . DS . 'deactivate.php';

        // include plugin custom uninstall script
        include OW_DIR_PLUGIN . $pluginDto->getModule() . DS . 'uninstall.php';

        // delete plugin work dirs
        $dirsToRemove = array(
            OW_DIR_PLUGINFILES . $pluginDto->getModule(),
            OW_DIR_PLUGIN_USERFILES . $pluginDto->getModule()
        );

        if ( !defined('OW_PLUGIN_XP') )
        {
            $dirsToRemove[] = OW_DIR_STATIC_PLUGIN . $pluginDto->getModule();
        }

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
    }

    public function activate( $key )
    {
        $pluginDto = $this->pluginDao->findPluginByKey($key);
        $pluginDto->setIsActive(true);
        $this->pluginDao->save($pluginDto);
        OW::getPluginManager()->addPackagePointers($pluginDto);
        include OW_DIR_PLUGIN . $pluginDto->getModule() . DS . 'activate.php';
    }

    public function deactivate( $key )
    {
        $pluginDto = $this->pluginDao->findPluginByKey($key);

        $pluginDto->setIsActive(false);
        $this->pluginDao->save($pluginDto);
        include OW::getPluginManager()->getPlugin($pluginDto->getKey())->getRootDir() . 'deactivate.php';
    }

    public function getPluginsToUpdateCount()
    {
        return $this->pluginDao->findPluginsForUpdateCount();
    }

    public function checkManualUpdates()
    {
        $timestamp = OW::getConfig()->getValue('base', 'check_mupdates_ts');

        if ( ( time() - (int) $timestamp ) < 30 )
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
                $plugin->setUpdate(2);
                $this->pluginDao->save($plugin);
            }
        }

        OW::getConfig()->saveConfig('base', 'check_mupdates_ts', time());
    }

    /**
     * @return BOL_Plugin
     */
    public function findNextManualUpdatePlugin()
    {
        return $this->pluginDao->findPluginForManualUpdate();
    }

    /**
     * Returns a list of plugins with unverified 
     * 
     * @return array<BOL_Plugin>
     */
    public function getListOfUnverifiedPlugins()
    {
        //TODO implement
        return array();
    }

    /**
     * @param BOL_Plugin $dto
     * @return OW_Plugin
     */
    public function getPluginObject( BOL_Plugin $dto )
    {
        return $dto->isSystem ?
            new OW_SystemPlugin(array('dir_name' => $dto->getModule(), 'key' => $dto->getKey(), 'active' => $dto->isActive(), 'dto' => $dto)) :
            new OW_Plugin(array('dir_name' => $dto->getModule(), 'key' => $dto->getKey(), 'active' => $dto->isActive(), 'dto' => $dto));
    }
}
