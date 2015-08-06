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
class ADMIN_CTRL_Storage extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_PluginService
     */
    private $pluginService;

    /**
     * @var BOL_StorageService
     */
    private $storageService;

    /**
     * @var BOL_ThemeService
     */
    private $themeService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pluginService = BOL_PluginService::getInstance();
        $this->storageService = BOL_StorageService::getInstance();
        $this->themeService = BOL_ThemeService::getInstance();
    }

    private function redirectToBackUri( $getParams )
    {
        if ( !isset($getParams[BOL_StorageService::URI_VAR_BACK_URI]) )
        {
            return;
        }

        $this->redirect(OW::getRequest()->buildUrlQueryString(OW_URL_HOME . urldecode($getParams[BOL_StorageService::URI_VAR_BACK_URI]), $getParams));
    }

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
            $errMsg = $language->text("admin", "check_license_invalid_servr_responce_err_msg");
            OW::getFeedback()->error($errMsg);
            $this->redirectToBackUri($params);
            $this->assign("message", $errMsg);
            return;
        }

        if ( (bool) $data[BOL_StorageService::STORE_ITEM_PROP_FREEWARE] )
        {
            $params[BOL_StorageService::URI_VAR_LICENSE_CHECK_RESULT] = 1;
            $params[BOL_StorageService::URI_VAR_FREEWARE] = 1;
            $this->redirectToBackUri($params);
            $this->assign("message", $language->text("admin", "check_license_item_is_free_msg"));
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

        if ( isset($params[BOL_StorageService::URI_VAR_BACK_URI]) )
        {
            $button = new Button("button");
            $button->setValue($language->text("admin", "license_form_back_label"));
            $button->addAttribute("onclick", "window.location='" . OW_URL_HOME . urldecode($params[BOL_StorageService::URI_VAR_BACK_URI]) . "'");
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
                    $pluginDto = $this->pluginService->findPluginByKey($key, $devKey);

                    if ( $pluginDto != null )
                    {
                        $pluginDto->setLicenseKey($licenseKey);
                        $this->pluginService->savePlugin($pluginDto);
                    }

                    $this->redirectToBackUri($params);
                    OW::getFeedback()->info($language->text("admin", "plugins_manage_license_key_check_success"));
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

    public function coreUpdateRequest()
    {
        if ( !(bool) OW::getConfig()->getValue('base', 'update_soft') )
        {
            throw new Redirect404Exception();
        }

        $newCoreInfo = $this->storageService->getCoreInfoForUpdate();
        $this->assign('text', OW::getLanguage()->text('admin', 'manage_plugins_core_update_request_text', array('oldVersion' => OW::getConfig()->getValue('base', 'soft_version'), 'newVersion' => $newCoreInfo['version'], 'info' => $newCoreInfo['info'])));
        $this->assign('redirectUrl', OW::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'coreUpdate'));
        $this->assign('returnUrl', OW::getRouter()->urlForRoute('admin_default'));
    }

    public function coreUpdate()
    {
        if ( !(bool) OW::getConfig()->getValue('base', 'update_soft') )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();

        $archivePath = OW_DIR_PLUGINFILES . 'ow' . DS . 'core.zip';

        $tempDir = OW_DIR_PLUGINFILES . 'ow' . DS . 'core' . DS;

        $ftp = $this->getFtpConnection();

        $errorMessage = false;

        OW::getApplication()->setMaintenanceMode(true);
        $this->storageService->downloadCore($archivePath);

        if ( !file_exists($archivePath) )
        {
            $errorMessage = $language->text('admin', 'core_update_download_error');
        }
        else
        {
            mkdir($tempDir);

            $zip = new ZipArchive();

            $zopen = $zip->open($archivePath);

            if ( $zopen === true )
            {
                $zip->extractTo($tempDir);
                $zip->close();
                $ftp->uploadDir($tempDir, OW_DIR_ROOT);
                $ftp->chmod(0777, OW_DIR_STATIC);
                $ftp->chmod(0777, OW_DIR_STATIC_PLUGIN);
            }
            else
            {
                $errorMessage = $language->text('admin', 'core_update_unzip_error');
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
            $this->redirect(OW::getRouter()->urlFor('ADMIN_CTRL_Index', 'index'));
        }

        $this->redirect(OW_URL_HOME . 'ow_updates/index.php');
    }

    /**
     * Returns ftp connection.
     *
     * @return UTIL_Ftp
     */
    private function getFtpConnection()
    {
        try
        {
            $ftp = $this->storageService->getFtpConnection();
        }
        catch ( LogicException $e )
        {
            OW::getFeedback()->error($e->getMessage());
            $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor(__CLASS__, 'ftpAttrs'), array('back_uri' => urlencode(OW::getRequest()->getRequestUri()))));
        }

        return $ftp;
    }
}
