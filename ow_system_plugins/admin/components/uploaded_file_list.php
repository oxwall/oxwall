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
    /**
     * @var string
     */
    protected $item = "";
    /**
     * @var string
     */
    protected $itemMenu = "";
    /**
     * @var string
     */
    protected $bulkOptions = "";
    /**
     * @var string
     */
    protected $filter = "";
    /**
     * @var string
     */
    protected $uploadButton = "";
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param string $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @param string $itemMenu
     */
    public function setItemMenu($itemMenu)
    {
        $this->itemMenu = $itemMenu;
    }

    /**
     * @param string $bulkOptions
     */
    public function setBulkOptions($bulkOptions)
    {
        $this->bulkOptions = $bulkOptions;
    }

    /**
     * @param string $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param string $uploadButton
     */
    public function setUploadButton($uploadButton)
    {
        $this->uploadButton = $uploadButton;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    public function __construct()
    {
        parent::__construct();
        $this->setItem("ADMIN_CMP_UploadedItem");
        $this->setItemMenu("ADMIN_CMP_UploadedItemMenu");
        $this->setBulkOptions("ADMIN_CMP_UploadedFilesBulkOptions");
        $this->setFilter("ADMIN_CMP_UploadedFilesFilter");
        $this->setUploadButton("BASE_CMP_AjaxFileUploadButton");
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $this->assign('items', $this->items);
        $this->assign('item', $this->item);
        $this->assign('itemMenu', $this->itemMenu);
        $this->assign('filter', $this->filter);
        $this->assign('bulkOptions', $this->bulkOptions);
        $this->assign('uploadButton', $this->uploadButton);

        $hasSideBar = OW::getThemeManager()->getCurrentTheme()->getDto()->getSidebarPosition() != 'none';
        $photoParams = array(
            'classicMode' => (bool)OW::getConfig()->getValue('photo', 'photo_list_view_classic')
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

        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ZeroClipboard.js');
        $swfPath = OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ZeroClipboard.swf';
        OW::getDocument()->addOnloadScript("
        ;ZeroClipboard.config( { swfPath: '{$swfPath}' } );
        window.zeroClipboardFixCss = function(id){
            var \$element = $('#' + id);
            \$element.off('mouseenter').mouseenter(function(e){
                var target = $(e.currentTarget);
                target.css('background-color', 'rgb(142, 142, 142)');
                target.parents('.ow_tooltip').css('display', 'block');
                target.parents('.ow_photo_context_action').css('opacity', '1');
            }).off('mouseleave').mouseleave(function(e){
                var target = $(e.currentTarget);
                target.css('background-color', '');
                target.parents('.ow_tooltip').css('display', '');
                target.parents('.ow_photo_context_action').css('opacity', '');
            });
        };
        OW.bind('photo.photoItemRendered', function(item){
            var src = $(item).find('img').attr('src');
            $(item).find('.zero-clipboard-button').attr('data-clipboard-text', src);

            var elementId = 'zero_' + $(item).attr('id');
            $(item).find('.zero-clipboard-button').attr('id', elementId);

            zeroClipboardFixCss(elementId);
            var client = new ZeroClipboard(document.getElementById(elementId));
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
                array('action' => null, 'name' => 'TODO: Copy Url', 'liClass' => 'menuItem', 'aClass' => 'zero-clipboard-button'),
                array('action' => 'deleteImage', 'name' => 'TODO: Delete Image'),
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

    }
}
