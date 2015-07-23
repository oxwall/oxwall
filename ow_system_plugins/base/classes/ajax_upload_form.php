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
 * 
 *
 * @author Sergei Kiselev <arrserg@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.7.5
 */
class BASE_CLASS_AjaxUploadForm extends Form
{
    public function __construct( $entityType, $entityId, $albumId = null, $albumName = null, $albumDescription = null, $url = null )
    {
        parent::__construct('ajax-upload');
        
        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->setAction(OW::getRouter()->urlForRoute('admin.ajax_upload_submit'));
        $this->bindJsFunction('success', 
            UTIL_JsGenerator::composeJsString('function( data )
            {
                if ( data )
                {
                    if ( !data.result )
                    {
                        if ( data.msg )
                        {
                            OW.error(data.msg);
                        }
                        else
                        {
                            OW.getLanguageText("admin", "photo_upload_error");
                        }
                    }
                    else
                    {
                        var url = {$url};
                        
                        if ( url )
                        {
                            window.location.href = url;
                        }
                        else if ( data.url )
                        {
                            window.location.href = data.url;
                        }
                    }
                }
                else
                {
                    OW.error("Server error");
                }
            }', array(
                'url' => $url
            ))
        );

        $submit = new Submit('submit');
        $submit->addAttribute('class', 'ow_ic_submit ow_positive');
        $this->addElement($submit);
    }
}
