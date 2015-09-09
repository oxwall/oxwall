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
class ADMIN_CMP_UploadedFilesFloatbox extends OW_Component
{
    public function __construct( $layout )
    {
        parent::__construct();

        $saveImageDataUrl = OW::getRouter()->urlFor('ADMIN_CTRL_Theme', 'ajaxResponder');

        $jsString = ";$('.image_save_data').click(function(e){
            e.preventDefault();
            var floatbox = $('.floatbox_container');
            var title = $('.ow_photoview_title input', floatbox).val();
            var imageId = $('.ow_photoview_id', floatbox).val();
            var data = {'entityId': imageId, 'title': title, 'ajaxFunc': 'ajaxSaveImageData'};
            $('.image_save_data').attr('disabled', 'disabled');
            $('.image_save_data').addClass('ow_inprogress');
            $.ajax({
                url: '{$saveImageDataUrl}',
                data: data,
                method: 'POST',
                success: function(data){
                    $('.image_save_data').removeAttr('disabled');
                    $('.image_save_data').removeClass('ow_inprogress');
                    photoView.unsetCache(data.imageId);
                    OW.info('All changes saved');
                }
            });
        });
        ";
        OW::getDocument()->addOnloadScript($jsString);
    }
}
