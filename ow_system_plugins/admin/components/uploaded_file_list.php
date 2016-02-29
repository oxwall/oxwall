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
 * @package ow_system_plugins.admin.components
 * @since 1.7.5
 */
class ADMIN_CMP_UploadedFileList extends OW_Component
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $hasSideBar = OW::getThemeManager()->getCurrentTheme()->getDto()->getSidebarPosition() != 'none';
        $photoParams = array(
            'classicMode' => false
        );
        $photoParams[] = ($photoParams['classicMode'] ? ($hasSideBar ? 4 : 5) : 4);

        $photoDefault = array(
            'getPhotoURL' => OW::getRouter()->urlFor('ADMIN_CTRL_Theme', 'ajaxResponder'),
            'listType' => null,
            'rateUserId' => OW::getUser()->getId(),
            'urlHome' => OW_URL_HOME,
            'level' => 4
        );

        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin('base');

        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'clipboard.js');
        OW::getDocument()->addOnloadScript("
        ;var floatboxClipboard = new Clipboard('.ow_photoview_url a');

        floatboxClipboard.on('success', function(e) {
            OW.info(OW.getLanguageText('admin', 'url_copied'));
            e.clearSelection();
        });

        floatboxClipboard.on('error', function(e) {
            OW.warning(OW.getLanguageText('admin', 'press_ctrl_c'));
        });

        OW.bind('photo.photoItemRendered', function(item){
            var clipboard = new Clipboard($(item).find('.clipboard-button')[0]);

            clipboard.on('success', function(e) {
                OW.info(OW.getLanguageText('admin', 'url_copied'));
                e.clearSelection();
            });

            clipboard.on('error', function(e) {
                OW.warning(OW.getLanguageText('admin', 'press_ctrl_c'));
                var parent = $(e.trigger).parent();
                var input = parent.find('input')
                parent.addClass('ow_url_input_visible');
                input.val($(e.trigger).attr('data-clipboard-text'));
                input.get(0).setSelectionRange(0, input.get(0).value.length);
            });
        });
        ");

        $document->addScriptDeclarationBeforeIncludes(
            ';window.browsePhotoParams = ' . json_encode(array_merge($photoDefault, $photoParams)) . ';'
        );
        $document->addOnloadScript(';window.browsePhoto.init();');

        $contDefault = array(
            'downloadAccept' => (bool)OW::getConfig()->getValue('photo', 'download_accept'),
            'downloadUrl' => OW_URL_HOME . 'photo/download-photo/:id',
            'actionUrl' => $photoDefault['getPhotoURL'],
            'contextOptions' => array(
                array('action' => 'deleteImage', 'name' => OW::getLanguage()->text('admin', 'delete_image')),
            )
        );

        $document->addScriptDeclarationBeforeIncludes(
            ';window.photoContextActionParams = ' . json_encode($contDefault)
        );
        $document->addOnloadScript(';window.photoContextAction.init();');

        $document->addOnloadScript('$(document.getElementById("browse-photo")).on("click", ".ow_photo_item_wrap img", function( event )
            {
                var data = $(this).closest(".ow_photo_item_wrap").data(), _data = {};

                if ( data.dimension && data.dimension.length )
                {
                    try
                    {
                        var dimension = JSON.parse(data.dimension);

                        _data.main = dimension.main;
                    }
                    catch( e )
                    {
                        _data.main = [this.naturalWidth, this.naturalHeight];
                    }
                }
                else
                {
                    _data.main = [this.naturalWidth, this.naturalHeight];
                }

                _data.mainUrl = data.photoUrl;
                photoView.setId(data.photoId, data.listType, browsePhoto.getMoreData(), _data);
            });');

        $document->addStyleSheet($plugin->getStaticCssUrl() . 'browse_files.css');
        $document->addScript($plugin->getStaticJsUrl() . 'browse_file.js');
        OW::getLanguage()->addKeyForJs("admin", "copy_url");
        OW::getLanguage()->addKeyForJs("admin", "confirm_delete_images");
        OW::getLanguage()->addKeyForJs("admin", "no_photo_selected");
        OW::getLanguage()->addKeyForJs("admin", "no_items");
        OW::getLanguage()->addKeyForJs("admin", "dnd_support");
        OW::getLanguage()->addKeyForJs("admin", "url_copied");
        OW::getLanguage()->addKeyForJs("admin", "press_ctrl_c");
    }
}
