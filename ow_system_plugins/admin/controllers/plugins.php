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
 * Plugins manage admin controller class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Plugins extends ADMIN_CTRL_StorageAbstract
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Default action. Shows the list of installed plugins.
     */
    public function index()
    {
        OW::getNavigation()->activateMenuItem(OW_Navigation::ADMIN_PLUGINS, "admin", "sidebar_menu_plugins_installed");

        $language = OW::getLanguage();
        $router = OW::getRouter();

        $this->setPageTitle($language->text("admin", "page_title_manage_plugins"));
        $this->setPageHeading($language->text("admin", "page_title_manage_plugins"));
        $this->setPageHeadingIconClass("ow_ic_gear_wheel");

        $this->pluginService->updatePluginsXmlInfo();
        // get plugins in DB
        $plugins = $this->pluginService->findRegularPlugins();

        usort($plugins,
            function( BOL_Plugin $a, BOL_Plugin $b )
        {
            return strcmp($a->getTitle(), $b->getTitle());
        });

        $arrayToAssign["active"] = array();
        $arrayToAssign["inactive"] = array();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            $array = array(
                "title" => $plugin->getTitle(),
                "description" => $plugin->getDescription(),
                "set_url" => ( $plugin->isActive && $plugin->getAdminSettingsRoute() !== null) ? $router->urlForRoute($plugin->adminSettingsRoute) : false,
                "update_url" => ((int) $plugin->getUpdate() == 1) ? $router->urlFor(__CLASS__, "updateRequest",
                        array("key" => $plugin->getKey())) : false,
                "un_url" => $router->urlFor(__CLASS__, "uninstallRequest", array("key" => $plugin->getKey()))
            );

            if ( $plugin->getLicenseCheckTimestamp() > 0 )
            {
                $params = array(
                    BOL_StorageService::URI_VAR_BACK_URI => urlencode($router->uriForRoute("admin_plugins_installed")),
                    BOL_StorageService::URI_VAR_KEY => $plugin->getKey(),
                    BOL_StorageService::URI_VAR_ITEM_TYPE => BOL_StorageService::URI_VAR_ITEM_TYPE_VAL_PLUGIN,
                    BOL_StorageService::URI_VAR_DEV_KEY => $plugin->getDeveloperKey(),
                    BOL_StorageService::URI_VAR_RETURN_RESULT => 0
                );
                $array["license_url"] = OW::getRequest()->buildUrlQueryString($router->urlFor("ADMIN_CTRL_Storage",
                        "checkItemLicense"), $params);
            }

            if ( $plugin->isActive() )
            {
                $array["deact_url"] = $router->urlFor(__CLASS__, "deactivate", array("key" => $plugin->getKey()));

                if ( $plugin->getUninstallRoute() !== null )
                {
                    $array["un_url"] = $router->urlForRoute($plugin->getUninstallRoute());
                }

                $arrayToAssign["active"][$plugin->getKey()] = $array;
            }
            else
            {
                $array["active_url"] = $router->urlFor(__CLASS__, "activate", array("key" => $plugin->getKey()));
                $arrayToAssign["inactive"][$plugin->getKey()] = $array;
            }
        }

        $event = new OW_Event("admin.plugins_list_view", array("ctrl" => $this, "type" => "index"), $arrayToAssign);
        OW::getEventManager()->trigger($event);
        $arrayToAssign = $event->getData();

        $this->assign("plugins", $arrayToAssign);
    }

    /**
     * Shows the list of plugins available for installation.
     */
    public function available()
    {
        OW::getNavigation()->activateMenuItem(OW_Navigation::ADMIN_PLUGINS, "admin", "sidebar_menu_plugins_available");

        $language = OW::getLanguage();
        $this->setPageTitle($language->text("admin", "page_title_available_plugins"));
        $this->setPageHeading($language->text("admin", "page_title_available_plugins"));

        // read plugins dir and find available plugins
        $arrayToAssign = $this->pluginService->getAvailablePluginsList();

        usort($arrayToAssign,
            function(array $a, array $b)
        {
            return strcmp($a["name"], $b["name"]);
        });

        /* @var $plugin BOL_Plugin */
        foreach ( $arrayToAssign as $key => $plugin )
        {
            $params = array(
                BOL_StorageService::URI_VAR_KEY => $plugin["key"],
                BOL_StorageService::URI_VAR_DEV_KEY => $plugin["developerKey"],
                BOL_StorageService::URI_VAR_ITEM_TYPE => "plugin");
            $arrayToAssign[$key]["inst_url"] = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor(__CLASS__,
                    "install"), $params);
            $arrayToAssign[$key]["del_url"] = OW::getRouter()->urlFor(__CLASS__, "delete",
                array("key" => $plugin["key"]));
        }

        $event = new OW_Event("admin.plugins_list_view", array("ctrl" => $this, "type" => "available"), $arrayToAssign);
        OW::getEventManager()->trigger($event);
        $arrayToAssign = $event->getData();
        $this->assign("plugins", $arrayToAssign);
    }

    /**
     * Uploads new plugin and extracts archive contecnts.
     */
    public function add()
    {
        OW::getNavigation()->activateMenuItem(OW_Navigation::ADMIN_PLUGINS, "admin", "sidebar_menu_plugins_add");

        $language = OW::getLanguage();
        $feedback = OW::getFeedback();

        $form = new Form("plugin-add");
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $file = new FileField("file");
        $form->addElement($file);

        $submit = new Submit("submit");
        $submit->setValue($language->text("admin", "plugins_manage_add_submit_label"));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $result = UTIL_File::checkUploadedFile($_FILES["file"]);

                if ( !$result["result"] )
                {
                    $feedback->error($result["message"]);
                    $this->redirect();
                }

                $pluginfilesDir = OW::getPluginManager()->getPlugin("base")->getPluginFilesDir();

                $tempFile = $pluginfilesDir . UTIL_String::getRandomStringWithPrefix("plugin_add") . ".zip";
                $tempDirName = UTIL_String::getRandomStringWithPrefix("plugin_add");

                if ( !move_uploaded_file($_FILES["file"]["tmp_name"], $tempFile) )
                {
                    $feedback->error($language->text("admin", "manage_plugin_add_move_file_error"));
                    $this->redirectToAction("index");
                }

                $zip = new ZipArchive();

                if ( $zip->open($tempFile) === true )
                {
                    $zip->extractTo($this->getTemDirPath() . $tempDirName);
                    $zip->close();
                }
                else
                {
                    $feedback->error($language->text("admin", "manage_plugin_add_extract_error"));
                    $this->redirectToAction("index");
                }

                unlink($tempFile);

                $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor(__CLASS__, "processAdd"),
                        array("dir" => $tempDirName)));
            }
        }
    }

    /**
     * Uploads plugin contents to plugins dir via FTP.
     */
    public function processAdd()
    {
        $language = OW::getLanguage();
        $feedback = OW::getFeedback();

        $tempDirName = empty($_GET["dir"]) ? null : str_replace(DS, "", $_GET["dir"]);

        if ( $tempDirName == null || !file_exists($this->getTemDirPath() . $tempDirName) )
        {
            $feedback->error($language->text("admin", "manage_plugins_add_ftp_move_error"));
            $this->redirectToAction("add");
        }

        $tempDirPath = $this->getTemDirPath() . $tempDirName;

        // locate plugin.xml file to find plugin source root dir
        $result = UTIL_File::findFiles($tempDirPath, array("xml"));
        $localPluginRootPath = null;

        foreach ( $result as $item )
        {
            if ( basename($item) == BOL_PluginService::PLUGIN_INFO_XML )
            {
                $localPluginRootPath = dirname($item) . DS;
            }
        }

        if ( $localPluginRootPath == null )
        {
            $feedback->error($language->text("admin", "manage_plugin_add_extract_error"));
            $this->redirectToAction('add');
        }

        //get plugin.xml info
        $pluginXmlInfo = $this->pluginService->readPluginXmlInfo($localPluginRootPath . BOL_PluginService::PLUGIN_INFO_XML);

        //check if there is a plugin with the same key
        $pluginWithSameKey = $this->pluginService->findPluginByKey($pluginXmlInfo["key"]);

        //check if the plugin is already installed
        $pluginAlreadyInstalled = $this->pluginService->findPluginByKey($pluginXmlInfo["key"],
            $pluginXmlInfo["developerKey"]);

        if ( $pluginWithSameKey !== null )
        {
            // if it's already installed need to upload source to implement manual update
            if ( $pluginAlreadyInstalled !== null )
            {
                $pluginDir = OW_DIR_PLUGIN . $pluginWithSameKey->getModule() . DS;
            }
            else
            {
                // show error, can't have 2 plugins with the same key
                OW::getFeedback()->error(OW::getLanguage()->text("admin", "manage_plugin_cant_add_duplicate_key_error"));
                $this->redirectToAction("index");
            }
        }
        else
        {
            $pluginDir = null;

            // find the plugin path to update the source if plugin is in available list
            $itemsXmlList = $this->pluginService->getPluginsXmlInfo();

            foreach ( $itemsXmlList as $xmlItem )
            {
                if ( $xmlItem["key"] == $pluginXmlInfo["key"] && $xmlItem["developerKey"] == $pluginXmlInfo["developerKey"] )
                {
                    $pluginDir = $xmlItem["path"];
                }
            }

            // make up new dir path for the plugin if it is added for the first time 
            if ( $pluginDir == null )
            {
                $pluginDir = OW_DIR_PLUGIN . basename($localPluginRootPath);

                while ( file_exists($pluginDir) )
                {
                    $pluginDir .= UTIL_String::getRandomString(3, UTIL_String::RND_STR_NUMERIC);
                }
            }
        }

        $ftp = $this->getFtpConnection();
        $ftp->uploadDir($localPluginRootPath, $pluginDir);
        UTIL_File::removeDir($tempDirPath);

        OW::getFeedback()->info($language->text("base", "manage_plugins_add_success_message"));
        $this->redirectToAction("available");
    }

    /**
     * Deactivates plugin.
     *
     * @param array $params
     */
    public function deactivate( array $params )
    {
        $pluginDto = $this->getPluginDtoByKeyInParamsArray($params);
        $language = OW::getLanguage();

        $this->pluginService->deactivate($pluginDto->getKey());

        OW::getFeedback()->info($language->text('admin', 'manage_plugins_deactivate_success_message',
                array('plugin' => $pluginDto->getTitle())));
        $this->redirectToAction('index');
    }

    /**
     * Activates plugin.
     *
     * @param array $params
     */
    public function activate( array $params )
    {
        $pluginDto = $this->getPluginDtoByKeyInParamsArray($params);
        $language = OW::getLanguage();
        $urlRedirect = OW::getRouter()->urlForRoute("admin_plugins_installed");

        $invalidItems = $this->pluginService->findPluginsWithInvalidLicense();

        /* @var $item BOL_Plugin */
        foreach ( $invalidItems as $item )
        {
            if ( $item->getKey() == $pluginDto->getKey() )
            {
                OW::getFeedback()->error($language->text("admin",
                        "manage_plugins_cant_activate_item_with_invalid_license"));
                $this->redirect($urlRedirect);
            }
        }

        $this->pluginService->activate($pluginDto->getKey());

        OW::getFeedback()->info($language->text("admin", "manage_plugins_activate_success_message",
                array("plugin" => $pluginDto->getTitle())));
        $this->redirect($urlRedirect);
    }

    /**
     * Shows confirmation page before plugin update
     * 
     * @param array $params
     * @return type
     */
    public function updateRequest( array $params )
    {
        //TODO merge method with platform update request
        $pluginDto = $this->getPluginDtoByKeyInParamsArray($params);
        $language = OW::getLanguage();
        $router = OW::getRouter();

        $remotePluginInfo = (array) $this->storageService->getItemInfoForUpdate($pluginDto->getKey(),
                $pluginDto->getDeveloperKey(), $pluginDto->getBuild());
        $this->assign("returnUrl", $router->urlForRoute("admin_plugins_installed"));
        $this->assign("changeLog", $remotePluginInfo["changeLog"]);

        if ( empty($remotePluginInfo) || !empty($remotePluginInfo["error"]) )
        {
            //TODO edit lang to "plugin not found or network error"
            $this->assign("text", $language->text("admin", "plugin_update_request_error"));
            return;
        }

        if ( $pluginDto->getBuild() == $remotePluginInfo["build"] )
        {
            $this->assign("text", $language->text("admin", "manage_plugins_up_to_date_message"));
            return;
        }

        if ( !(bool) $remotePluginInfo["freeware"] && ($pluginDto->getLicenseKey() == null || !$this->storageService->checkLicenseKey($pluginDto->getKey(),
                $pluginDto->getDeveloperKey(), $pluginDto->getLicenseKey())) )
        {
            if ( !isset($_GET[BOL_StorageService::URI_VAR_LICENSE_CHECK_COMPLETE]) )
            {
                $get = array(
                    BOL_StorageService::URI_VAR_BACK_URI => urlencode($router->uriFor(__CLASS__, "updateRequest",
                            $params)),
                    BOL_StorageService::URI_VAR_KEY => $pluginDto->getKey(),
                    BOL_StorageService::URI_VAR_ITEM_TYPE => BOL_StorageService::URI_VAR_ITEM_TYPE_VAL_PLUGIN,
                    BOL_StorageService::URI_VAR_DEV_KEY => $pluginDto->getDeveloperKey()
                );
                $this->redirect(OW::getRequest()->buildUrlQueryString($router->urlFor("ADMIN_CTRL_Storage",
                            "checkItemLicense"), $get));
            }
            else
            {
                $this->assign("text", $language->text("admin", "plugin_update_request_error"));
                return;
            }
        }

        $this->assign("text",
            $language->text("admin", "free_plugin_request_text",
                array("releaseNotes" => "", "oldVersion" => $pluginDto->getBuild(), "newVersion" => $remotePluginInfo["build"],
                "name" => $pluginDto->getTitle())));
        $this->assign("updateUrl", $router->urlFor(__CLASS__, "update", $params));

        if ( OW::getConfig()->getValue("base", "update_soft") )
        {
            $this->assign("platformUpdateAvail", true);
            $this->assign("platformUpdateUrl", OW::getRouter()->urlForRoute("admin_core_update_request"));
        }
    }

    /**
     * Executes plugin update
     * 
     * @param array $params
     */
    public function update( array $params )
    {
        $pluginDto = $this->getPluginDtoByKeyInParamsArray($params);
        $language = OW::getLanguage();
        $feedback = OW::getFeedback();
        $redirectUrl = OW::getRouter()->urlForRoute("admin_plugins_installed");

        //TODO remove hardcoded constants
        // process data returned by update script
        if ( !empty($_GET["mode"]) )
        {
            switch ( trim($_GET["mode"]) )
            {
                case "plugin_up_to_date":
                    $feedback->warning($language->text("admin", "manage_plugins_up_to_date_message"));
                    break;

                case "plugin_update_success":
                    OW::getEventManager()->trigger(new OW_Event(OW_EventManager::ON_AFTER_PLUGIN_UPDATE,
                        array('pluginKey' => $pluginDto->getKey())));
                    $feedback->info($language->text("admin", "manage_plugins_update_success_message"));
                    break;

                default :
                    $feedback->error($language->text("admin", "manage_plugins_update_process_error"));
                    break;
            }

            $this->redirect($redirectUrl);
        }

        $remotePluginInfo = (array) $this->storageService->getItemInfoForUpdate($pluginDto->getKey(),
                $pluginDto->getDeveloperKey(), $pluginDto->getBuild());

        if ( empty($remotePluginInfo) || !empty($remotePluginInfo["error"]) )
        {
            $feedback->error($language->text("admin", "manage_plugins_update_process_error"));
            $this->redirect($redirectUrl);
        }

        if ( !(bool) $remotePluginInfo["freeware"] && ($pluginDto->getLicenseKey() == null || !$this->storageService->checkLicenseKey($pluginDto->getKey(),
                $pluginDto->getDeveloperKey(), $pluginDto->getLicenseKey())) )
        {
            $feedback->error($language->text("admin", "manage_plugins_update_invalid_key_error"));
            $this->redirect($redirectUrl);
        }

        $ftp = $this->getFtpConnection();

        try
        {
            $archivePath = $this->storageService->downloadItem($pluginDto->getKey(), $pluginDto->getDeveloperKey(),
                $pluginDto->getLicenseKey());
        }
        catch ( Exception $e )
        {
            $feedback->error($e->getMessage());
            $this->redirect($redirectUrl);
        }

        if ( !file_exists($archivePath) )
        {
            $feedback->error(OW::getLanguage()->text("admin", "plugin_update_download_error"));
            $this->redirect($redirectUrl);
        }

        $zip = new ZipArchive();
        $tempDirPath = OW::getPluginManager()->getPlugin("base")->getPluginFilesDir() . "plugin_update" . UTIL_String::getRandomString(5,
                UTIL_String::RND_STR_ALPHA_NUMERIC) . DS;

        mkdir($tempDirPath);

        if ( $zip->open($archivePath) === true )
        {
            $zip->extractTo($tempDirPath);
            $zip->close();
        }
        else
        {
            $feedback->error(OW::getLanguage()->text("admin", "plugin_update_download_error"));
            $this->redirect($redirectUrl);
        }

        // locate plugin.xml file to find plugin source root dir
        $result = UTIL_File::findFiles($tempDirPath, array("xml"));
        $localPluginRootPath = null;

        foreach ( $result as $item )
        {
            if ( basename($item) == BOL_PluginService::PLUGIN_INFO_XML )
            {
                $localPluginRootPath = dirname($item) . DS;
            }
        }

        if ( $localPluginRootPath == null )
        {
            $feedback->error($language->text("admin", "manage_plugin_add_extract_error"));
            $this->redirect($redirectUrl);
        }

        try
        {
            $plugin = OW::getPluginManager()->getPlugin($pluginDto->getKey());
        }
        catch ( InvalidArgumentException $ex )
        {
            $feedback->error($language->text("admin", "manage_plugin_update_plugin_not_active_error"));
            $this->redirect($redirectUrl);
        }

        $remoteDirPath = $plugin->getRootDir();
        $ftp->uploadDir($localPluginRootPath, $remoteDirPath);
        UTIL_File::removeDir($localPluginRootPath);

        $this->pluginService->addPluginDirs($pluginDto);
        $params = array("plugin" => $pluginDto->getKey(), "back-uri" => urlencode(OW::getRequest()->getRequestUri()));
        $this->redirect(OW::getRequest()->buildUrlQueryString($this->storageService->getUpdaterUrl(), $params));
    }

    /**
     * Updates plugin DB after manual source upload
     * 
     * @param array $params
     */
    public function manualUpdateRequest( array $params )
    {
        $language = OW::getLanguage();
        $feedback = OW::getFeedback();
        $urlToRedirect = OW::getRouter()->urlForRoute("admin_plugins_installed");
        $pluginDto = null;

        // check if plugin key was provided
        if ( !empty($params["key"]) )
        {
            $pluginDto = $this->pluginService->findPluginByKey(trim($params["key"]));
        }

        // try to get item for manual update from DB
        if ( !$pluginDto )
        {
            $pluginDto = $this->pluginService->findNextManualUpdatePlugin();
        }

        if ( !empty($_GET["mode"]) )
        {
            switch ( trim($_GET["mode"]) )
            {
                case "plugin_up_to_date":
                    $feedback->warning($language->text("admin", "manage_plugins_up_to_date_message"));
                    break;

                case "plugin_update_success":

                    if ( $pluginDto !== null )
                    {
                        OW::getEventManager()->trigger(new OW_Event(OW_EventManager::ON_AFTER_PLUGIN_UPDATE,
                            array("pluginKey" => $pluginDto->getKey())));
                    }

                    $feedback->info($language->text("admin", "manage_plugins_update_success_message"));
                    break;

                default :
                    $feedback->error($language->text("admin", "manage_plugins_update_process_error"));
                    break;
            }

            $this->redirect($urlToRedirect);
        }

        // if nothing was found for update or everything is up to date
        if ( !$pluginDto || (int) $pluginDto->getUpdate() != BOL_PluginService::PLUGIN_STATUS_MANUAL_UPDATE )
        {
            $feedback->warning(OW::getLanguage()->text("admin", "no_plugins_for_manual_updates"));
            $this->redirect($urlToRedirect);
        }

        $this->assign("text",
            $language->text("admin", "manage_plugins_manual_update_request", array("name" => $pluginDto->getTitle())));
        $params = array("plugin" => $pluginDto->getKey(), "back-uri" => urlencode(OW::getRequest()->getRequestUri()), "addParam" => UTIL_String::getRandomString());
        $this->assign("redirectUrl",
            OW::getRequest()->buildUrlQueryString($this->storageService->getUpdaterUrl(), $params));
    }

    /**
     * Installs plugin.
     */
    public function install()
    {
        $params = $_GET;
        $language = OW::getLanguage();
        $router = OW::getRouter();
        $feedback = OW::getFeedback();

        //check if key and dev_key are provided
        if ( empty($params[BOL_StorageService::URI_VAR_KEY]) || empty($params[BOL_StorageService::URI_VAR_DEV_KEY]) )
        {
            if ( !empty($params) )
            {
                $feedback->error($language->text("admin", "manage_plugins_install_empty_key_error_message"));
            }

            $this->redirect($router->urlForRoute("admin_plugins_available"));
        }

        $installPlugin = false;

        // get remote info about the plugin
        $itemData = $this->storageService->getItemInfoForUpdate($params[BOL_StorageService::URI_VAR_KEY],
            $params[BOL_StorageService::URI_VAR_DEV_KEY]);

        if ( empty($itemData) )
        {
            $feedback->error($language->text("admin", "manage_plugins_server_invalid_responce_error_message"));
            $this->redirect($router->urlForRoute("admin_plugins_available"));
        }

        // check if it's free
        if ( !empty($itemData[BOL_StorageService::URI_VAR_FREEWARE]) || !empty($itemData[BOL_StorageService::URI_VAR_NOT_FOUND]) )
        {
            $installPlugin = true;
        }
        else
        {
            if ( !isset($params[BOL_StorageService::URI_VAR_LICENSE_CHECK_COMPLETE]) )
            {
                $params[BOL_StorageService::URI_VAR_BACK_URI] = urlencode(OW::getRequest()->getRequestUri());
                $params["back-button-uri"] = urlencode(OW::getRouter()->uriForRoute("admin_plugins_available"));
                $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor("ADMIN_CTRL_Storage",
                            "checkItemLicense"), $params));
            }

            if ( isset($params[BOL_StorageService::URI_VAR_LICENSE_CHECK_RESULT]) && (bool) $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_RESULT] && isset($params[BOL_StorageService::URI_VAR_LICENSE_KEY]) )
            {
                if ( $this->storageService->checkLicenseKey($params[BOL_StorageService::URI_VAR_KEY],
                        $params[BOL_StorageService::URI_VAR_DEV_KEY], $params[BOL_StorageService::URI_VAR_LICENSE_KEY]) )
                {
                    $installPlugin = true;
                }
            }
        }

        if ( $installPlugin )
        {
            try
            {
                $pluginDto = $this->pluginService->install(trim($params[BOL_StorageService::URI_VAR_KEY]));

                if ( $pluginDto != null && !empty($params[BOL_StorageService::URI_VAR_LICENSE_KEY]) )
                {
                    $pluginDto->setLicenseKey(urldecode($params[BOL_StorageService::URI_VAR_LICENSE_KEY]));
                    $this->pluginService->savePlugin($pluginDto);
                }

                $feedback->info($language->text("admin", "manage_plugins_install_success_message",
                        array("plugin" => $pluginDto->getTitle())));
            }
            catch ( LogicException $e )
            {
                OW::getLogger()->addEntry($e->getTraceAsString());

                if ( OW_DEBUG_MODE )
                {
                    throw $e;
                }

                $feedback->error($language->text("admin", "manage_plugins_install_error_message",
                        array("key" => ( empty($pluginDto) ? "_INVALID_" : $pluginDto->getKey()))));
            }
        }
        else
        {
            $feedback->error($language->text("admin", "manage_plugins_install_invalid_license_key"));
        }

        $this->redirect($router->urlForRoute("admin_plugins_available"));
    }

    /**
     * Deletes plugin.
     *
     * @param array $params
     */
    public function uninstall( array $params )
    {
        $language = OW::getLanguage();

        if ( empty($params["key"]) )
        {
            OW::getFeedback()->error($language->text('admin', 'manage_plugins_uninstall_error_message'));
            $this->redirect(OW::getRouter()->urlForRoute("admin_plugins_installed"));
        }

        $pluginDto = $this->getPluginDtoByKeyInParamsArray($params);

        if ( $pluginDto === null )
        {
            OW::getFeedback()->error($language->text('admin', 'manage_plugins_uninstall_error_message'));
            $this->redirect(OW::getRouter()->urlForRoute("admin_plugins_installed"));
        }

        if ( !$pluginDto->isActive )
        {
            $this->pluginService->activate($pluginDto->getKey());
        }

        try
        {
            $this->pluginService->uninstall($pluginDto->getKey());
        }
        catch ( Exception $e )
        {
            if ( OW_DEBUG_MODE )
            {
                throw $e;
            }
            else
            {
                OW::getLogger()->addEntry($e->getTraceAsString());
            }

            OW::getFeedback()->error($language->text("admin", "manage_plugins_uninstall_error_message"));
            $this->redirect(OW::getRouter()->urlForRoute("admin_plugins_installed"));
        }

        OW::getFeedback()->info($language->text("admin", "manage_plugins_uninstall_success_message",
                array("plugin" => $pluginDto->getTitle())));
        $this->redirect(OW::getRouter()->urlForRoute("admin_plugins_installed"));
    }

    public function uninstallRequest( array $params )
    {
        $language = OW::getLanguage();

        if ( empty($params["key"]) )
        {
            OW::getFeedback()->error($language->text("admin", "manage_plugins_uninstall_error_message"));
            $this->redirect(OW::getRouter()->urlForRoute("admin_plugins_installed"));
        }

        $pluginDto = $this->getPluginDtoByKeyInParamsArray($params);

        if ( $pluginDto === null )
        {
            OW::getFeedback()->error($language->text("admin", "manage_plugins_uninstall_error_message"));
            $this->redirect(OW::getRouter()->urlForRoute("admin_plugins_installed"));
        }

        $this->assign("text",
            $language->text("admin", "plugin_uninstall_request_text", array('name' => $pluginDto->getTitle())));
        $this->assign("redirectUrl", OW::getRouter()->urlFor("ADMIN_CTRL_Plugins", "uninstall", $params));
    }

    /**
     * Deletes plugin.
     *
     * @param array $params
     */
    public function delete( array $params )
    {
        $ftp = $this->getFtpConnection();

        $key = trim($params["key"]);
        $availablePlugins = $this->pluginService->getAvailablePluginsList();

        if ( !isset($availablePlugins[$key]) )
        {
            OW::getFeedback()->error(OW::getLanguage()->text("admin", "manage_plugins_plugin_not_found"));
            $this->redirectToAction('available');
        }

        $ftp->rmDir($availablePlugins[$key]["path"]);

        OW::getFeedback()->info(OW::getLanguage()->text("admin", "manage_plugins_delete_success_message",
                array("plugin" => $availablePlugins[$key]["title"])));
        $this->redirectToAction("available");
    }
    /* ---------------------------------------------------------------------- */

    protected function getPluginDtoByKeyInParamsArray( $params )
    {
        if ( !empty($params["key"]) )
        {
            $pluginDto = $this->pluginService->findPluginByKey(trim($params["key"]));
        }

        if ( !empty($pluginDto) )
        {
            return $pluginDto;
        }

        OW::getFeedback()->error(OW::getLanguage()->text("admin", "manage_plugins_plugin_not_found"));
        $this->redirectToAction("index");
    }
}
