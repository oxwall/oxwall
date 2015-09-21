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
    const UPDATE_SERVER = 'https://storage.oxwall.org/';
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
     * Returns all installed plugins
     *
     * @return array<BOL_Plugin>
     */
    public function findAllPlugins()
    {
        return $this->getPluginListCache();
    }

    /**
     * Finds plugin item for provided key
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
     * Returns list of active plugins
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
     * Returns list of plugins available for installation
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
            if ( !in_array($plugin['key'], $dbPluginsArray) )
            {
                $availPlugins[$key] = $plugin;
            }
        }

        return $availPlugins;
    }

    /**
     * Returns all plugins XML info
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

    /**
     * Synchronizes plugins DB data with xml info
     */
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
     * Reads provided XML file and returns plugin info array
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

    /**
     * Returns the count of plugins ready for update
     * 
     * @return int
     */
    public function findPluginsForUpdateCount()
    {
        return $this->pluginDao->findPluginsForUpdateCount();
    }

    /**
     * Cron function. Requests info from update server 
     * 
     */
    public function checkUpdates()
    {
        if ( defined('OW_PLUGIN_XP') )
        {
            return;
        }

        $pluginsRequestArray = array(
            array('key' => 'core', 'developerKey' => 'ow', 'build' => OW::getConfig()->getValue('base', 'soft_build'))
        );

        $plugins = $this->pluginDao->findRegularPlugins();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            $pluginsRequestArray[] = array('key' => $plugin->getKey(), 'developerKey' => $plugin->getDeveloperKey(), 'build' => $plugin->getBuild());
        }

        $themeService = BOL_ThemeService::getInstance();
        //check all manual updates before reading builds in DB
        $themeService->checkManualUpdates();

        $themesRequestArray = array();
        $themes = $themeService->findAllThemes();

        /* @var $theme BOL_Theme */
        foreach ( $themes as $theme )
        {
            $themesRequestArray[] = array('key' => $theme->getName(), 'developerKey' => $theme->getDeveloperKey(), 'build' => $theme->getBuild());
        }

        $event = new OW_Event('base.on_plugin_info_update');
        OW::getEventManager()->trigger($event);

        $data = $event->getData();
        if ( empty($data) )
        {
            $data = array();
        }

        //TODO add request url to class constants list
        $requestUrl = OW::getRequest()->buildUrlQueryString(self::UPDATE_SERVER . 'get-items-update-info/');

        $data['plugins'] = urlencode(json_encode($pluginsRequestArray));
        $data['themes'] = urlencode(json_encode($themesRequestArray));

        $postdata = http_build_query($data);

        $options = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context = stream_context_create($options);

        $resultArray = json_decode(file_get_contents($requestUrl, false, $context), true);

        if ( empty($resultArray) || !is_array($resultArray) )
        {
            return;
        }

        if ( !empty($resultArray['plugins']) && is_array($resultArray['plugins']) )
        {
            foreach ( $plugins as $plugin )
            {
                if ( in_array($plugin->getKey(), $resultArray['plugins']) && (int) $plugin->getUpdate() === 0 )
                {
                    $plugin->setUpdate(1);
                    $this->pluginDao->save($plugin);
                }
            }

            if ( in_array('core', $resultArray['plugins']) )
            {
                OW::getConfig()->saveConfig('base', 'update_soft', 1);
            }
        }

        if ( !empty($resultArray['themes']) && is_array($resultArray['themes']) )
        {
            foreach ( $themes as $theme )
            {
                if ( in_array($theme->getName(), $resultArray['themes']) && (int) $theme->getUpdate() === 0 )
                {
                    $theme->setUpdate(1);
                    $themeService->saveTheme($theme);
                }
            }
        }
    }

    /**
     * Return item (plugin/theme) update info
     * 
     * @param string $key
     * @param string $devKey
     * @return array
     */
    public function getItemInfoForUpdate( $key, $devKey )
    {
        $params = array('key' => trim($key), 'developerKey' => $devKey);
        $event = new OW_Event('base.on_plugin_info_update', $params);
        OW::getEventManager()->trigger($event);

        $data = $event->getData();
        if ( empty($data) )
        {
            $data = $params;
        }

        //TODO add request url to class constants list
        $requestUrl = OW::getRequest()->buildUrlQueryString(self::UPDATE_SERVER . 'get-item-info', $data);

        return json_decode((file_get_contents($requestUrl)), true);
    }

    /**
     * Returns platform update info
     * 
     * @return array
     */
    public function getPlatformInfoForUpdate()
    {
        $event = new OW_Event('base.on_plugin_info_update');
        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( empty($data) )
        {
            $data = array();
        }

        //TODO add request url to class constants list
        $requestUrl = OW::getRequest()->buildUrlQueryString(self::UPDATE_SERVER . 'platform-info', $data);

        return json_decode((file_get_contents($requestUrl)), true);
    }

    /**
     * Downloads platform archive and moves it to provided location
     * 
     * @param string $archivePath
     */
    public function downloadPlatform( $archivePath )
    {
        $params = array(
            'platform-version' => OW::getConfig()->getValue('base', 'soft_version'),
            'platform-build' => OW::getConfig()->getValue('base', 'soft_build')
        );
        $event = new OW_Event('base.on_plugin_info_update', $params);
        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( empty($data) )
        {
            $data = $params;
        }

        //TODO add request url to class constants list
        $requestUrl = OW::getRequest()->buildUrlQueryString(self::UPDATE_SERVER . 'download-platform', $data);

        $fileContents = file_get_contents($requestUrl);
        file_put_contents($archivePath, $fileContents);
    }

    /**
     * Downloads plugin archive and returns archive path
     * 
     * @param string $key
     * @param string $devKey
     * @param string $licenseKey
     * @return string
     * @throws LogicException
     */
    public function downloadItem( $key, $devKey, $licenseKey = null )
    {
        $params = array('key' => trim($key), 'developerKey' => trim($devKey), 'licenseKey' => $licenseKey);
        $event = new OW_Event('base.on_plugin_info_update', $params);
        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( empty($data) )
        {
            $data = $params;
        }

        //TODO add request url to class constants list
        $requestUrl = OW::getRequest()->buildUrlQueryString(self::UPDATE_SERVER . 'get-item', $data);

        $fileContents = file_get_contents($requestUrl);

        if ( empty($fileContents) )
        {
            throw new LogicException("Can't download file! Server returned empty file!");
        }

        $filePath = OW_DIR_PLUGINFILES . 'ow' . DS . 'temp' . rand(1, 1000000) . '.zip';

        file_put_contents($filePath, $fileContents);

        return $filePath;
    }

    /**
     * Checks license key for provided plugin
     * 
     * @param string $key
     * @param string $developerKey
     * @param string $licenseKey
     * @return bool
     */
    public function checkLicenseKey( $key, $developerKey, $licenseKey )
    {
        $params = array('key' => trim($key), 'licenseKey' => $licenseKey, 'developerKey' => $developerKey);
        $event = new OW_Event('base.on_plugin_info_update', $params);
        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( empty($data) )
        {
            $data = $params;
        }

        $requestUrl = OW::getRequest()->buildUrlQueryString(self::UPDATE_SERVER . 'check-license-key', $data);

        return (bool) json_decode((file_get_contents($requestUrl)));
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
            $pluginDto->setTitle((!empty($pluginInfo['title']) ? trim($pluginInfo['title']) : 'No Title'));
            $pluginDto->setDescription((!empty($pluginInfo['description']) ? trim($pluginInfo['description']) : 'No Description'));
            $pluginDto->setKey(trim($pluginInfo['key']));
            $pluginDto->setModule($pluginInfo["dir_name"]);
            $pluginDto->setIsActive(true);
            $pluginDto->setIsSystem(true);
            $pluginDto->setBuild((int) $pluginInfo['build']);

            if ( !empty($pluginInfo['developerKey']) )
            {
                $pluginDto->setDeveloperKey(trim($pluginInfo['developerKey']));
            }

            $this->pluginListCache[$pluginDto->getKey()] = $pluginDto;

            $plugin = new OW_Plugin($pluginDto);

            if ( file_exists($plugin->getRootDir() . self::SCRIPT_INSTALL) )
            {
                include_once $plugin->getRootDir() . self::SCRIPT_INSTALL;
            }

            $this->pluginDao->save($pluginDto);
            $this->updatePluginListCache();
        }
    }

    /**
     * Installs plugin
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
        $this->updatePluginListCache();

        include_once OW_DIR_PLUGIN . $pluginDto->getModule() . DS . self::SCRIPT_INSTALL;
        include_once OW_DIR_PLUGIN . $pluginDto->getModule() . DS . self::SCRIPT_ACTIVATE;

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
     * Creates platform reserved dirs for plugin, copies all plugin static data
     * 
     * @param BOL_Plugin $pluginDto
     */
    public function addPluginDirs( BOL_Plugin $pluginDto )
    {
        $plugin = new OW_Plugin($pluginDto);

        if ( !defined('OW_PLUGIN_XP') && file_exists($plugin->getStaticDir()) )
        {
            $staticDir = OW_DIR_STATIC_PLUGIN . $pluginDto->getModule() . DS;

            if ( !file_exists($staticDir) )
            {
                mkdir($staticDir);
                chmod($staticDir, 0777);
            }

            UTIL_File::copyDir($plugin->getStaticDir(), $staticDir);
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
    }

    /**
     * Uninstalls plugin
     *
     * @param string $key
     */
    public function uninstall( $key )
    {
        if ( empty($key) )
        {
            throw new LogicException("Empty plugin key provided for uninstall");
        }

        $pluginDto = $this->findPluginByKey(trim($key));

        if ( $pluginDto === null )
        {
            throw new LogicException("Invalid plugin key - `{$key}` provided for uninstall!");
        }

        // trigger event
        $event = new OW_Event(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array("pluginKey" => $pluginDto->getKey()));
        OW::getEventManager()->trigger($event);

        include OW_DIR_PLUGIN . $pluginDto->getModule() . DS . self::SCRIPT_DEACTIVATE;

        // include plugin custom uninstall script
        include OW_DIR_PLUGIN . $pluginDto->getModule() . DS . self::SCRIPT_UNINSTALL;

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
        $this->updatePluginListCache();
    }

    /**
     * Activates plugin
     * 
     * @param string $key
     */
    public function activate( $key )
    {
        $pluginDto = $this->pluginDao->findPluginByKey($key);

        $pluginDto->setIsActive(true);
        $this->pluginDao->save($pluginDto);
        OW::getPluginManager()->addPackagePointers($pluginDto);
        include OW_DIR_PLUGIN . $pluginDto->getModule() . DS . self::SCRIPT_ACTIVATE;
        $this->updatePluginListCache();
    }

    /**
     * Deactivates plugin
     * 
     * @param sring $key
     */
    public function deactivate( $key )
    {
        $pluginDto = $this->pluginDao->findPluginByKey($key);

        $pluginDto->setIsActive(false);
        $this->pluginDao->save($pluginDto);
        include OW::getPluginManager()->getPlugin($pluginDto->getKey())->getRootDir() . self::SCRIPT_DEACTIVATE;
        $this->updatePluginListCache();
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
        if ( defined('OW_PLUGIN_XP') )
        {
            return;
        }

        $timestamp = OW::getConfig()->getValue('base', 'check_mupdates_ts');

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

        OW::getConfig()->saveConfig('base', 'check_mupdates_ts', time());
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
     * Returns inited and checked ftp connection.
     *
     * @throws LogicException
     * @return UTIL_Ftp
     */
    public function getFtpConnection()
    {
        $language = OW::getLanguage();
        $errorMessageKey = null;
        $ftp = null;

        if ( !OW::getSession()->isKeySet('ftpAttrs') || !is_array(OW::getSession()->get('ftpAttrs')) )
        {
            $errorMessageKey = 'plugins_manage_need_ftp_attrs_message';
        }
        else
        {
            $ftp = null;

            try
            {
                $ftp = UTIL_Ftp::getConnection(OW::getSession()->get('ftpAttrs'));
            }
            catch ( Exception $ex )
            {
                $errorMessageKey = $ex->getMessage();
            }

            if ( $ftp !== null )
            {
                $testDir = OW_DIR_CORE . 'test';

                $ftp->mkDir($testDir);

                if ( file_exists($testDir) )
                {
                    $ftp->rmDir($testDir);
                }
                else
                {
                    $errorMessageKey = 'plugins_manage_ftp_attrs_invalid_user';
                }
            }
        }

        if ( $errorMessageKey !== null )
        {
            throw new LogicException($language->text('admin', $errorMessageKey));
        }

        return $ftp;
    }

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
}
