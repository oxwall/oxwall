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
 * @since 1.7.6
 */
class BOL_StorageService
{
    const UPDATE_SERVER = "https://storage.oxwall.org";
    /* --------------------------------------------- */
    const URI_CHECK_ITEMS_FOR_UPDATE = "get-items-update-info";
    const URI_GET_ITEM_INFO = "get-item-info";
    const URI_GET_PLATFORM_INFO = "platform-info";
    const URI_DOWNLOAD_PLATFORM_ARCHIVE = "download-platform";
    const URI_DOWNLOAD_ITEM = "get-item";
    const URI_CHECK_LECENSE_KEY = "check-license-key";
    /* --------------------------------------------- */
    const URI_VAR_KEY = "key";
    const URI_VAR_DEV_KEY = "developerKey";
    const URI_VAR_BUILD = "build";
    const URI_VAR_LICENSE_KEY = "licenseKey";
    const URI_VAR_ITEM_TYPE = "item-type";
    const URI_VAR_BACK_URI = "back-uri";
    const URI_VAR_LICENSE_CHECK_COMPLETE = "license-check-complete";
    const URI_VAR_LICENSE_CHECK_RESULT = "license-check-result";
    const URI_VAR_FREEWARE = "freeware";
    /* --------------------------------------------- */
    const STORE_ITEM_PROP_BUILD = "build";
    const STORE_ITEM_PROP_FREEWARE = "freeware";

    /* --------------------------------------------- */
    const EVENT_ON_STORAGE_INTERECT = "base.on_plugin_info_update";

    /**
     * @var BOL_ThemeService
     */
    private $themeService;

    /**
     * @var BOL_PluginService
     */
    private $pluginService;

    /**
     * Singleton instance.
     *
     * @var BOL_StorageService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_StorageService
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
        $this->pluginService = BOL_PluginService::getInstance();
        $this->themeService = BOL_ThemeService::getInstance();
    }

    /**
     * Retrieves update information for all plugins and themes. Cron function.
     */
    public function checkUpdates()
    {
        $pluginsRequestArray = array(
            array(
                self::URI_VAR_KEY => "core",
                self::URI_VAR_DEV_KEY => "ow",
                self::URI_VAR_BUILD => OW::getConfig()->getValue("base", "soft_build"))
        );

        $plugins = $this->pluginService->findRegularPlugins();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            $pluginsRequestArray[] = array(
                self::URI_VAR_KEY => $plugin->getKey(),
                self::URI_VAR_DEV_KEY => $plugin->getDeveloperKey(),
                self::URI_VAR_BUILD => $plugin->getBuild());
        }

        $themesRequestArray = array();

        //check all manual updates before reading builds in DB
        $this->themeService->checkManualUpdates();
        $themes = $this->themeService->findAllThemes();

        /* @var $theme BOL_Theme */
        foreach ( $themes as $theme )
        {
            $themesRequestArray[] = array(
                self::URI_VAR_KEY => $theme->getName(),
                self::URI_VAR_DEV_KEY => $theme->getDeveloperKey(),
                self::URI_VAR_BUILD => $theme->getBuild());
        }

        $data = $this->triggerEventBeforeRequest();

        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_CHECK_ITEMS_FOR_UPDATE));

        $data["plugins"] = urlencode(json_encode($pluginsRequestArray));
        $data["themes"] = urlencode(json_encode($themesRequestArray));

        $postdata = http_build_query($data);

        $options = array("http" =>
            array(
                "method" => "POST",
                "header" => "Content-type: application/x-www-form-urlencoded",
                "content" => $postdata
            )
        );

        $context = stream_context_create($options);

        $resultArray = json_decode(file_get_contents($requestUrl, false, $context), true);

        if ( empty($resultArray) || !is_array($resultArray) )
        {
            return;
        }

        if ( !empty($resultArray["plugins"]) && is_array($resultArray["plugins"]) )
        {
            foreach ( $plugins as $plugin )
            {
                if ( in_array($plugin->getKey(), $resultArray["plugins"]) && (int) $plugin->getUpdate() === 0 )
                {
                    $plugin->setUpdate(1);
                    $this->pluginService->savePlugin($plugin);
                }
            }

            if ( in_array("core", $resultArray["plugins"]) )
            {
                OW::getConfig()->saveConfig("base", "update_soft", 1);
            }
        }

        if ( !empty($resultArray["themes"]) && is_array($resultArray["themes"]) )
        {
            foreach ( $themes as $theme )
            {
                if ( in_array($theme->getName(), $resultArray['themes']) && (int) $theme->getUpdate() === 0 )
                {
                    $theme->setUpdate(1);
                    $this->themeService->saveTheme($theme);
                }
            }
        }
    }

    /**
     * Returns information from remote storage for store item.
     * 
     * @param string $key
     * @param string $devKey
     * @return array
     */
    public function getItemInfoForUpdate( $key, $devKey )
    {
        $params = array(
            self::URI_VAR_KEY => trim($key),
            self::URI_VAR_DEV_KEY => trim($devKey)
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));
        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_GET_ITEM_INFO), $data);

        return (array) json_decode((file_get_contents($requestUrl)));
    }

    /**
     * Returns information from remote storage for platform.
     * 
     * @return array
     */
    public function getCoreInfoForUpdate()
    {
        $data = $this->triggerEventBeforeRequest();
        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_GET_PLATFORM_INFO), $data);
        return (array) json_decode((file_get_contents($requestUrl)));
    }

    /**
     * Downloads platform update archive and puts it to the provided path.
     * 
     * @param type $archivePath
     */
    public function downloadCore( $archivePath )
    {
        //TODO replace path in params with return value like in items
        //TODO rename to downloadPlatformUpdate
        $params = array(
            'platform-version' => OW::getConfig()->getValue('base', 'soft_version'),
            'platform-build' => OW::getConfig()->getValue('base', 'soft_build')
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));
        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_DOWNLOAD_PLATFORM_ARCHIVE), $data);

        $fileContents = file_get_contents($requestUrl);
        file_put_contents($archivePath, $fileContents);
    }

    /**
     * Downloads item archive and returns it's local path.
     * 
     * @param string $key
     * @param string $devKey
     * @param string $licenseKey
     * @return string
     * @throws LogicException
     */
    public function downloadItem( $key, $devKey, $licenseKey = null )
    {
        $params = array(
            self::URI_VAR_KEY => trim($key),
            self::URI_VAR_DEV_KEY => trim($devKey),
            self::URI_VAR_LICENSE_KEY => trim($licenseKey)
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));
        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_DOWNLOAD_ITEM), $data);
        $fileContents = file_get_contents($requestUrl);

        if ( empty($fileContents) )
        {
            throw new LogicException("Can't download file. Server returned empty file.");
        }
        //TODO replace kolhoz with util tand generator
        $filePath = OW_DIR_PLUGINFILES . 'ow' . DS . 'temp' . rand(1, 1000000) . '.zip';
        file_put_contents($filePath, $fileContents);
        return $filePath;
    }

    /**
     * Checks if license key is valid for store item.
     * 
     * @param string $key
     * @param string $developerKey
     * @param string $licenseKey
     * @return array
     */
    public function checkLicenseKey( $key, $devKey, $licenseKey )
    {
        $params = array(
            self::URI_VAR_KEY => trim($key),
            self::URI_VAR_DEV_KEY => trim($devKey),
            self::URI_VAR_LICENSE_KEY => trim($licenseKey)
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));
        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_CHECK_LECENSE_KEY), $data);

        return (bool) json_decode((file_get_contents($requestUrl)));
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
    /* -------------------------------------------------------------------------------------- */

    private function getStorageUrl( $uri )
    {
        return UTIL_String::removeFirstAndLastSlashes(self::UPDATE_SERVER) . "/" . UTIL_String::removeFirstAndLastSlashes($uri) . "/";
    }

    private function triggerEventBeforeRequest( $params = array() )
    {
        $event = OW::getEventManager()->trigger(new OW_Event('base.on_plugin_info_update', $params));
        $data = $event->getData();

        return (!empty($data) && is_array($data) ) ? $data : array();
    }
}
