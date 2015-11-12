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
 * @since 1.8.1
 */
class BOL_StorageService
{
    const UPDATE_SERVER = "https://storage.oxwall.org";
    /* ---------------------------------------------------------------------- */
    const URI_CHECK_ITEMS_FOR_UPDATE = "get-items-update-info";
    const URI_GET_ITEM_INFO = "get-item-info";
    const URI_GET_PLATFORM_INFO = "platform-info";
    const URI_DOWNLOAD_PLATFORM_ARCHIVE = "download-platform";
    const URI_DOWNLOAD_ITEM = "get-item";
    const URI_CHECK_LECENSE_KEY = "check-license-key";
    /* ---------------------------------------------------------------------- */
    const URI_VAR_KEY = "key";
    const URI_VAR_DEV_KEY = "developerKey";
    const URI_VAR_BUILD = "build";
    const URI_VAR_LICENSE_KEY = "licenseKey";
    const URI_VAR_ITEM_TYPE = "type";
    const URI_VAR_BACK_URI = "back-uri";
    const URI_VAR_LICENSE_CHECK_COMPLETE = "license-check-complete";
    const URI_VAR_LICENSE_CHECK_RESULT = "license-check-result";
    const URI_VAR_FREEWARE = "freeware";
    const URI_VAR_NOT_FOUND = "notFound";
    /* ---------------------------------------------------------------------- */
    const URI_VAR_ITEM_TYPE_VAL_PLUGIN = "plugin";
    const URI_VAR_ITEM_TYPE_VAL_THEME = "theme";
    /* ---------------------------------------------------------------------- */
    const EVENT_ON_STORAGE_INTERECT = "base.on_plugin_info_update";
    const OXWALL_STORE_DEV_KEY = "e547ebcf734341ec11911209d93a1054";
    const ITEM_DEACTIVATE_TIMEOUT_IN_DAYS = 5;

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
        $requestArray = array("platform" => array(self::URI_VAR_BUILD => OW::getConfig()->getValue("base", "soft_build")), "items" => array());

        $plugins = $this->pluginService->findRegularPlugins();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            $requestArray["items"][] = array(
                self::URI_VAR_KEY => $plugin->getKey(),
                self::URI_VAR_DEV_KEY => $plugin->getDeveloperKey(),
                self::URI_VAR_BUILD => $plugin->getBuild(),
                self::URI_VAR_LICENSE_KEY => $plugin->getLicenseKey(),
                self::URI_VAR_ITEM_TYPE => self::URI_VAR_ITEM_TYPE_VAL_PLUGIN
            );
        }

        //check all manual updates before reading builds in DB
        $this->themeService->checkManualUpdates();
        $themes = $this->themeService->findAllThemes();

        /* @var $dto BOL_Theme */
        foreach ( $themes as $dto )
        {
            $requestArray["items"][] = array(
                self::URI_VAR_KEY => $dto->getKey(),
                self::URI_VAR_DEV_KEY => $dto->getDeveloperKey(),
                self::URI_VAR_BUILD => $dto->getBuild(),
                self::URI_VAR_LICENSE_KEY => $dto->getLicenseKey(),
                self::URI_VAR_ITEM_TYPE => self::URI_VAR_ITEM_TYPE_VAL_THEME
            );
        }

        $data = $this->triggerEventBeforeRequest();
        $data["info"] = json_encode($requestArray);

        $params = new UTIL_HttpClientParams();
        $params->addParams($data);
        $response = UTIL_HttpClient::post($this->getStorageUrl(self::URI_CHECK_ITEMS_FOR_UPDATE), $params);

        if ( $response->getStatusCode() != UTIL_HttpClient::HTTP_STATUS_OK )
        {
            OW::getLogger()->addEntry(__CLASS__ . "::" . __METHOD__ . "#" . __LINE__ . " storage request status is not OK", "core.update");
            return;
        }

        $resultArray = array();

        if ( $response->getBody() )
        {
            $resultArray = json_decode($response->getBody(), true);
        }

        if ( empty($resultArray) || !is_array($resultArray) )
        {
            OW::getLogger()->addEntry(__CLASS__ . "::" . __METHOD__ . "#" . __LINE__ . " remote request returned empty result", "core.update");
            return;
        }

        if ( !empty($resultArray["update"]) )
        {
            if ( !empty($resultArray["update"]["platform"]) && (bool) $resultArray["update"]["platform"] )
            {
                OW::getConfig()->saveConfig("base", "update_soft", 1);
            }

            if ( !empty($resultArray["update"]["items"]) )
            {
                $this->updateItemsUpdateStatus($resultArray["update"]["items"]);
            }
        }

        if ( !empty($resultArray["invalidLicense"]) )
        {
            $this->updateItemsLicenseStatus($resultArray["invalidLicense"]);
        }
    }

    /**
     * Returns information from remote storage for store item.
     * 
     * @param string $key
     * @param string $devKey
     * @param int $currentBuild
     * @return array
     */
    public function getItemInfoForUpdate( $key, $devKey, $currentBuild = 0 )
    {
        $params = array(
            self::URI_VAR_KEY => trim($key),
            self::URI_VAR_DEV_KEY => trim($devKey),
            self::URI_VAR_BUILD => (int) $currentBuild
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));
        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_GET_ITEM_INFO), $data);

        return $this->requestGetResultAsJson($this->getStorageUrl(self::URI_GET_ITEM_INFO), $data);
    }

    /**
     * Returns information from remote storage for platform.
     * 
     * @return array
     */
    public function getPlatformInfoForUpdate()
    {
        $data = $this->triggerEventBeforeRequest();
        return $this->requestGetResultAsJson($this->getStorageUrl(self::URI_GET_PLATFORM_INFO), $data);
    }

    /**
     * Downloads platform update archive and puts it to the provided path.
     * 
     * @return string 
     * @throws LogicException
     */
    public function downloadPlatform()
    {
        $params = array(
            "platform-version" => OW::getConfig()->getValue("base", "soft_version"),
            "platform-build" => OW::getConfig()->getValue("base", "soft_build")
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));
        $requestUrl = OW::getRequest()->buildUrlQueryString($this->getStorageUrl(self::URI_DOWNLOAD_PLATFORM_ARCHIVE), $data);

        $paramsObj = new UTIL_HttpClientParams();
        $paramsObj->addParams($data);
        $response = UTIL_HttpClient::get($this->getStorageUrl(self::URI_DOWNLOAD_PLATFORM_ARCHIVE), $paramsObj);

        if ( $response->getStatusCode() != UTIL_HttpClient::HTTP_STATUS_OK || empty($response->getBody()) )
        {
            throw new LogicException("Can't download file. Server returned empty file.");
        }

        $fileName = UTIL_String::getRandomStringWithPrefix("platform_archive_", 8, UTIL_String::RND_STR_NUMERIC) . ".zip";
        $archivePath = OW_DIR_PLUGINFILES . DS . $fileName;
        file_put_contents($archivePath, $response->getBody());

        return $archivePath;
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
            self::URI_VAR_LICENSE_KEY => $licenseKey != null ? trim($licenseKey) : null
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));

        $paramsObj = new UTIL_HttpClientParams();
        $paramsObj->addParams($data);
        $response = UTIL_HttpClient::get($this->getStorageUrl(self::URI_DOWNLOAD_ITEM), $paramsObj);

        if ( $response->getStatusCode() != UTIL_HttpClient::HTTP_STATUS_OK || empty($response->getBody()) )
        {
            throw new LogicException("Can't download file. Server returned empty file.");
        }

        $fileName = UTIL_String::getRandomString("plugin_archive_", 8, UTIL_String::RND_STR_NUMERIC) . ".zip";
        $archivePath = OW_DIR_PLUGINFILES . DS . $fileName;
        file_put_contents($archivePath, $response->getBody());

        return $archivePath;
    }

    /**
     * Checks if license key is valid for store item.
     * 
     * @param string $key
     * @param string $developerKey
     * @param string $licenseKey
     * @return bool
     */
    public function checkLicenseKey( $key, $devKey, $licenseKey )
    {
        if ( empty($key) || empty($devKey) || empty($licenseKey) )
        {
            return null;
        }

        $params = array(
            self::URI_VAR_KEY => trim($key),
            self::URI_VAR_DEV_KEY => trim($devKey),
            self::URI_VAR_LICENSE_KEY => trim($licenseKey)
        );

        $data = array_merge($params, $this->triggerEventBeforeRequest($params));
        $result = $this->requestGetResultAsJson($this->getStorageUrl(self::URI_CHECK_LECENSE_KEY), $data);

        if ( $result === null )
        {
            return null;
        }

        return $result === true ? true : false;
    }

    /**
     * Returns platform xml info.
     * 
     * @return array
     */
    public function getPlatformXmlInfo()
    {
        $filePath = OW_DIR_ROOT . "ow_version.xml";

        if ( !file_exists($filePath) )
        {
            return null;
        }

        return (array) simplexml_load_file($filePath);
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

        if ( !OW::getSession()->isKeySet("ftpAttrs") || !is_array(OW::getSession()->get("ftpAttrs")) )
        {
            $errorMessageKey = "plugins_manage_need_ftp_attrs_message";
        }
        else
        {
            $ftp = null;

            try
            {
                $ftp = UTIL_Ftp::getConnection(OW::getSession()->get("ftpAttrs"));
            }
            catch ( Exception $ex )
            {
                $errorMessageKey = $ex->getMessage();
            }

            if ( $ftp !== null )
            {
                $testDir = OW_DIR_CORE . "test";

                $ftp->mkDir($testDir);

                if ( file_exists($testDir) )
                {
                    $ftp->rmDir($testDir);
                }
                else
                {
                    $errorMessageKey = "plugins_manage_ftp_attrs_invalid_user";
                }
            }
        }

        if ( $errorMessageKey !== null )
        {
            throw new LogicException($language->text("admin", $errorMessageKey));
        }

        return $ftp;
    }

    /**
     * Returns URL of local generic update script.
     * 
     * @return string
     */
    public function getUpdaterUrl()
    {
        return OW_URL_HOME . "ow_updates/index.php";
    }

    /**
     * Returns the list of items with invalid license.
     * 
     * @return type
     */
    public function findItemsWithInvalidLicense()
    {
        $plugins = $this->pluginService->findPluginsWithInvalidLicense();
    }
    /* ---------------------------------------------------------------------- */

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

    private function updateItemsUpdateStatus( array $items )
    {
        if ( empty($items) )
        {
            return;
        }

        foreach ( $items as $item )
        {
            if ( $item[self::URI_VAR_ITEM_TYPE] == self::URI_VAR_ITEM_TYPE_VAL_PLUGIN )
            {
                $dto = $this->pluginService->findPluginByKey($item[self::URI_VAR_KEY], $item[self::URI_VAR_DEV_KEY]);

                if ( $dto != null )
                {
                    $dto->setUpdate(BOL_PluginService::PLUGIN_STATUS_UPDATE);
                    $this->pluginService->savePlugin($dto);
                }
            }
            else if ( $item[self::URI_VAR_ITEM_TYPE] == self::URI_VAR_ITEM_TYPE_VAL_THEME )
            {
                $dto = $this->themeService->findThemeByKey($item[self::URI_VAR_KEY]);

                if ( $dto != null && $dto->getDeveloperKey() == $item[self::URI_VAR_DEV_KEY] )
                {
                    $dto->setUpdate(BOL_ThemeService::THEME_STATUS_UPDATE);
                    $this->themeService->saveTheme($dto);
                }
            }
        }
    }

    private function updateItemsLicenseStatus( array $items )
    {
        $invalidItems = array(self::URI_VAR_ITEM_TYPE_VAL_PLUGIN => array(), self::URI_VAR_ITEM_TYPE_VAL_THEME => array());

        foreach ( $items as $item )
        {
            $invalidItems[$item[self::URI_VAR_ITEM_TYPE]][$item[self::URI_VAR_KEY]] = $item[self::URI_VAR_DEV_KEY];
        }

        $licenseExpireTimeInSeconds = self::ITEM_DEACTIVATE_TIMEOUT_IN_DAYS * 24 * 5;
        $plugins = $this->pluginService->findActivePlugins();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            if ( isset($invalidItems[self::URI_VAR_ITEM_TYPE_VAL_PLUGIN][$plugin->getKey()]) && $invalidItems[self::URI_VAR_ITEM_TYPE_VAL_PLUGIN][$plugin->getKey()] )
            {
                if ( (int) $plugin->getLicenseCheckTimestamp() == 0 )
                {
                    $plugin->setLicenseCheckTimestamp(time());
                    $this->pluginService->savePlugin($plugin);
                    $this->notifyAdminAboutInvalidItem(array("name" => $plugin->getTitle()));
                }
                else if ( (intval($plugin->getLicenseCheckTimestamp()) + $licenseExpireTimeInSeconds) <= time() )
                {
                    $this->pluginService->deactivate($plugin->getKey());
                }
            }
            else if ( $plugin->getLicenseCheckTimestamp() != null && $plugin->getLicenseCheckTimestamp() > 0 )
            {
                $plugin->setLicenseCheckTimestamp(null);
                $this->pluginService->savePlugin($plugin);
            }
        }

        $themes = $this->themeService->findAllThemes();

        /* @var $theme BOL_Theme */
        foreach ( $themes as $theme )
        {
            if ( isset($invalidItems[self::URI_VAR_ITEM_TYPE_VAL_THEME][$theme->getKey()]) && $invalidItems[self::URI_VAR_ITEM_TYPE_VAL_THEME][$theme->getKey()] )
            {
                if ( (int) $theme->getLicenseCheckTimestamp() == 0 )
                {
                    $theme->setLicenseCheckTimestamp(time());
                    $this->themeService->saveTheme($theme);
                    $this->notifyAdminAboutInvalidItem(array("name" => $theme->getTitle()));
                }
                else if ( (intval($theme->getLicenseCheckTimestamp()) + $licenseExpireTimeInSeconds) <= time() && $this->themeService->getSelectedThemeName() == $theme->getKey() )
                {
                    $this->themeService->setSelectedThemeName(BOL_ThemeService::DEFAULT_THEME);
                }
            }
            else if ( $theme->getLicenseCheckTimestamp() != null && $theme->getLicenseCheckTimestamp() > 0 )
            {
                $theme->setLicenseCheckTimestamp(null);
                $this->themeService->saveTheme($theme);
            }
        }
    }

    private function notifyAdminAboutInvalidItem( array $item )
    {
        $language = OW::getLanguage();
        $mail = OW::getMailer()->createMail();
        //$mail->addRecipientEmail(OW::getConfig()->getValue("base", "site_email"));
        $mail->addRecipientEmail("madumarov@gmail.com");
        $mail->setSubject($language->text("base", "mail_template_admin_invalid_license_subject"));
        $mail->setHtmlContent($language->text("base", "mail_template_admin_invalid_license_content_html", array("itemName" => $item["name"], "siteURL" => OW_URL_HOME)));
        $mail->setTextContent($language->text("base", "mail_template_admin_invalid_license_content_text", array("itemName" => $item["name"], "siteURL" => OW_URL_HOME)));

        OW::getMailer()->send($mail);
    }

    private function requestGetResultAsJson( $url, $data )
    {
        $params = new UTIL_HttpClientParams();
        $params->addParams($data);
        $response = UTIL_HttpClient::get($url, $params);

        if ( $response->getStatusCode() != UTIL_HttpClient::HTTP_STATUS_OK || !$response->getBody() )
        {
            return null;
        }

        return json_decode($response->getBody(), true);
    }
}
