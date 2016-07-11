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
 * Controller class to work with the remote store.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.7.7
 */
class ADMIN_CTRL_Storage extends ADMIN_CTRL_StorageAbstract
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generic action to get the license key for items.
     */
    public function checkItemLicense()
    {
        $params = $_GET;
        $language = OW::getLanguage();
        $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_COMPLETE] = 0;
        $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_RESULT] = 0;

        if ( empty($params[BOL_StorageService::URI_VAR_KEY]) || empty($params[BOL_StorageService::URI_VAR_ITEM_TYPE]) || empty($params[BOL_StorageService::URI_VAR_DEV_KEY]) )
        {
            $errMsg = $language->text("admin", "check_license_invalid_params_err_msg");
            OW::getFeedback()->error($errMsg);
            $this->redirectToBackUri($params);
            $this->assign("message", $errMsg);

            return;
        }

        $key = trim($params[BOL_StorageService::URI_VAR_KEY]);
        $devKey = trim($params[BOL_StorageService::URI_VAR_DEV_KEY]);
        $type = trim($params[BOL_StorageService::URI_VAR_ITEM_TYPE]);

        $data = $this->storageService->getItemInfoForUpdate($key, $devKey);

        if ( !$data )
        {
            $this->assign("backButton", true);
            $errMsg = $language->text("admin", "check_license_invalid_server_responce_err_msg");
            OW::getFeedback()->error($errMsg);
            $this->redirectToBackUri($params);
            $this->assign("message", $errMsg);

            return;
        }

        // if item is freeware reset check ts and redirect to back uri
        if ( (bool) $data[BOL_StorageService::URI_VAR_FREEWARE] )
        {
            $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_COMPLETE] = 1;
            $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_RESULT] = 1;
            $params[BOL_StorageService::URI_VAR_FREEWARE] = 1;
            $this->assign("message", $language->text("admin", "check_license_item_is_free_msg"));

            $dto = $this->storageService->findStoreItem($key, $devKey, $params[BOL_StorageService::URI_VAR_ITEM_TYPE]);

            if ( $dto != null )
            {
                $dto->setLicenseCheckTimestamp(null);
                $this->storageService->saveStoreItem($dto);
            }

            $this->redirectToBackUri($params);

            return;
        }

        $this->assign("text", $language->text("admin", "license_request_text", array("type" => $type, "title" => $data["title"])));

        $form = new Form("license-key");
        $licenseKey = new TextField("key");
        $licenseKey->setRequired();
        $licenseKey->setLabel($language->text("admin", "com_plugin_request_key_label"));
        $form->addElement($licenseKey);

        $submit = new Submit("submit");
        $submit->setValue($language->text("admin", "license_form_button_label"));
        $form->addElement($submit);

        if ( isset($params["back-button-uri"]) )
        {
            $button = new Button("button");
            $button->setValue($language->text("admin", "license_form_back_label"));
            $redirectUrl = UTIL_HtmlTag::escapeJs(OW_URL_HOME . urldecode($params["back-button-uri"]));
            $button->addAttribute("onclick", "window.location='{$redirectUrl}'");
            $form->addElement($button);
            $this->assign("backButton", true);
        }

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $licenseKey = $data["key"];
                $result = $this->storageService->checkLicenseKey($key, $devKey, $licenseKey);
                $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_COMPLETE] = 1;

                if ( $result )
                {
                    $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_RESULT] = 1;
                    $params[BOL_StorageService::URI_VAR_LICENSE_KEY] = urlencode($licenseKey);

                    $dto = $this->storageService->findStoreItem($key, $devKey, $params[BOL_StorageService::URI_VAR_ITEM_TYPE]);

                    if ( $dto != null )
                    {
                        $dto->setLicenseKey($licenseKey);
                        $dto->setLicenseCheckTimestamp(null);
                        $this->storageService->saveStoreItem($dto);
                    }

                    OW::getFeedback()->info($language->text("admin", "plugins_manage_license_key_check_success"));
                    $this->redirectToBackUri($params);
                    $this->redirect();
                }
                else
                {
                    OW::getFeedback()->error($language->text('admin', 'plugins_manage_invalid_license_key_error_message'));
                    $this->redirect();
                }
            }
        }
    }

    /**
     * Confirm action before platform update.
     */
    public function platformUpdateRequest()
    {
        if ( !(bool) OW::getConfig()->getValue("base", "update_soft") )
        {
            //TODO replace 404 redirect with message saying that update is not available.
            throw new Redirect404Exception();
        }

        $newPlatformInfo = $this->storageService->getPlatformInfoForUpdate();

        if ( !$newPlatformInfo )
        {
            return;
        }
//TODO check if the result is false | null
        $params = array(
            "oldVersion" => OW::getConfig()->getValue("base", "soft_version"),
            "newVersion" => $newPlatformInfo["version"],
            "info" => $newPlatformInfo["info"]
        );
        $this->assign("text", OW::getLanguage()->text("admin", "manage_plugins_core_update_request_text", $params));
        $this->assign("redirectUrl", OW::getRouter()->urlFor(__CLASS__, "platformUpdate"));
        $this->assign("returnUrl", OW::getRouter()->urlForRoute("admin_default"));
        $this->assign("changeLog", $newPlatformInfo["log"]);

        if ( !empty($newPlatformInfo["minPhpVersion"]) && version_compare(PHP_VERSION, trim($newPlatformInfo["minPhpVersion"])) < 0 )
        {
            $this->assign("phpVersionInvalidText", OW::getLanguage()->text("admin", "plugin_update_platform_invalid_php_version_msg",
                array("version" => trim($newPlatformInfo["minPhpVersion"]))));
        }
    }

    /**
     * Updates platform.
     */
    public function platformUpdate()
    {
        if ( !(bool) OW::getConfig()->getValue("base", "update_soft") )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();
        $tempDir = OW_DIR_PLUGINFILES . "ow" . DS . "core" . DS;

        $ftp = $this->getFtpConnection();

        $errorMessage = false;

        OW::getApplication()->setMaintenanceMode(true);
        $archivePath = $this->storageService->downloadPlatform();

        if ( !file_exists($archivePath) )
        {
            $errorMessage = $language->text("admin", "core_update_download_error");
        }
        else
        {
            mkdir($tempDir);
            $zip = new ZipArchive();
            $zopen = $zip->open($archivePath);

            if ( $zopen === true && file_exists($tempDir) )
            {
                $zip->extractTo($tempDir);
                $zip->close();
                $ftp->uploadDir($tempDir, OW_DIR_ROOT);
                $ftp->chmod(0777, OW_DIR_STATIC);
                $ftp->chmod(0777, OW_DIR_STATIC_PLUGIN);
            }
            else
            {
                $errorMessage = $language->text("admin", "core_update_unzip_error");
            }
        }

        if ( file_exists($tempDir) )
        {
            UTIL_File::removeDir($tempDir);
        }

        if ( file_exists($archivePath) )
        {
            unlink($archivePath);
        }

        if ( $errorMessage !== false )
        {
            OW::getApplication()->setMaintenanceMode(false);
            OW::getFeedback()->error($errorMessage);
            $this->redirect(OW::getRouter()->urlFor("ADMIN_CTRL_Index", "index"));
        }

        $this->redirect($this->storageService->getUpdaterUrl());
    }

    /**
     * Synchronizes with update server and redirects to back URI.
     */
    public function checkUpdates()
    {
        if ( $this->storageService->checkUpdates() )
        {
            OW::getFeedback()->info(OW::getLanguage()->text("admin", "check_updates_success_message"));
        }
        else
        {
            OW::getFeedback()->error(OW::getLanguage()->text("admin", "check_updates_fail_error_message"));
        }

        $backUrl = OW::getRouter()->urlForRoute("admin_default");

        if ( isset($_GET[BOL_StorageService::URI_VAR_BACK_URI]) )
        {
            $backUrl = OW_URL_HOME . urldecode($_GET[BOL_StorageService::URI_VAR_BACK_URI]);
        }

        $this->redirect($backUrl);
    }

    /**
     * Requests local FTP attributes to update items/platform source code.
     */
    public function ftpAttrs()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text("admin", "page_title_manage_plugins_ftp_info"));
        $this->setPageHeadingIconClass("ow_ic_gear_wheel");

        $form = new Form("ftp");

        $login = new TextField("host");
        $login->setValue("localhost");
        $login->setRequired(true);
        $login->setLabel($language->text("admin", "plugins_manage_ftp_form_host_label"));
        $form->addElement($login);

        $login = new TextField("login");
        $login->setHasInvitation(true);
        $login->setInvitation("login");
        $login->setRequired(true);
        $login->setLabel($language->text("admin", "plugins_manage_ftp_form_login_label"));
        $form->addElement($login);

        $password = new PasswordField("password");
        $password->setHasInvitation(true);
        $password->setInvitation("password");
        $password->setRequired(true);
        $password->setLabel($language->text("admin", "plugins_manage_ftp_form_password_label"));
        $form->addElement($password);

        $port = new TextField("port");
        $port->setValue(21);
        $port->addValidator(new IntValidator());
        $port->setLabel($language->text("admin", "plugins_manage_ftp_form_port_label"));
        $form->addElement($port);

        $submit = new Submit("submit");
        $submit->setValue($language->text("admin", "plugins_manage_ftp_form_submit_label"));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $ftpAttrs = array(
                    "host" => trim($data["host"]),
                    "login" => trim($data["login"]),
                    "password" => trim($data["password"]),
                    "port" => (int) $data["port"]);

                OW::getSession()->set("ftpAttrs", $ftpAttrs);
                $this->redirectToBackUri($_GET);
                $this->redirectToAction('index');
            }
        }
    }
}
