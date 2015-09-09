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
 * @author Sergei Kiselev <arrserg@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.5
 */
class BASE_CMP_AjaxFileUpload extends OW_Component
{
    public function __construct( $url = null )
    {
        $userId = OW::getUser()->getId();

        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin('base');
        $document->addScript($plugin->getStaticJsUrl() . 'jQueryRotate.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'codemirror.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'upload.js');
        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.ajaxFileUploadParams = {};
                Object.defineProperties(ajaxFileUploadParams, {
                    actionUrl: {
                        value: {$url},
                        writable: false,
                        enumerable: true
                    },
                    maxFileSize: {
                        value: {$size},
                        writable: false,
                        enumerable: true
                    },
                    deleteAction: {
                        value: {$deleteAction},
                        writable: false,
                        enumerable: true
                    }
                });',
                array(
                    'url' => OW::getRouter()->urlForRoute('admin.ajax_upload'),
                    'size' => BOL_FileService::getInstance()->getUploadMaxFilesizeBytes(),
                    'deleteAction' => OW::getRouter()->urlForRoute('admin.ajax_upload_delete')
                )
            )
        );
        $document->addOnloadScript(';window.ajaxFileUploader.init();');

        BOL_FileTemporaryService::getInstance()->deleteUserTemporaryFiles($userId);

        $form = new BASE_CLASS_AjaxUploadForm('user', $userId, $url);
        $this->addForm($form);

        $language = OW::getLanguage();
        $language->addKeyForJs('admin', 'not_all_photos_uploaded');
        $language->addKeyForJs('admin', 'size_limit');
        $language->addKeyForJs('admin', 'type_error');
        $language->addKeyForJs('admin', 'dnd_support');
        $language->addKeyForJs('admin', 'dnd_not_support');
        $language->addKeyForJs('admin', 'drop_here');
        $language->addKeyForJs('admin', 'please_wait');
        $language->addKeyForJs('admin', 'describe_photo');
        $language->addKeyForJs('admin', 'photo_upload_error');
    }
}
