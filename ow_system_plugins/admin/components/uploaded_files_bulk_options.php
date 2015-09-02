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
class ADMIN_CMP_UploadedFilesBulkOptions extends OW_Component
{

    public function __construct()
    {
        parent::__construct();

    }

    private function assignUniqidVar($name)
    {
        $showId = uniqid($name);
        $this->assign($name, $showId);
        return $showId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $showId = $this->assignUniqidVar('showId');
        $deleteId = $this->assignUniqidVar('deleteId');
        $backId = $this->assignUniqidVar('backId');
        $containerId = $this->assignUniqidVar('containerId');

        OW::getDocument()->addOnloadScript("
            ;function exitBulkOptions(){
                $('#{$containerId}').fadeOut(function(){
                    $('#{$showId}').parent().parent().fadeIn();
                    $(this).parents('.ow_fw_menu').find('.ow_admin_date_filter').fadeIn();
                    $('.ow_photo_context_action').show();
                    $('.ow_photo_item .ow_photo_chekbox_area').hide();
                });
            }
            $('#{$deleteId}').click(function(){
                var deleteIds = [];

                $('.ow_photo_item.ow_photo_item_checked').each(function(){
                    deleteIds.push($(this).closest('.ow_photo_item_wrap').data('photoId'));
                });
                photoContextAction.deleteImages(deleteIds);
                exitBulkOptions();
            });
            $('#{$showId}').click(function(){
                $('.ow_photo_item.ow_photo_item_checked').toggleClass('ow_photo_item_checked');
                $(this).parents('.ow_fw_menu').find('.ow_admin_date_filter').fadeOut();
                $('#{$showId}').parent().parent().fadeOut(function(){
                    $('#{$containerId}').fadeIn();
                    $('.ow_photo_context_action').hide();
                    $('.ow_photo_item .ow_photo_chekbox_area').show();
                });
            });
            $('#{$backId}').click(function(){
                exitBulkOptions();
            });
            $('.ow_photo_list').on('click', '.ow_photo_checkbox, .ow_photo_chekbox_area', function(e){
                e.stopPropagation();
                $(this).parents('.ow_photo_item').toggleClass('ow_photo_item_checked');
            });"

        );
    }
}
