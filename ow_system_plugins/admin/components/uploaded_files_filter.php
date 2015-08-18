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
class ADMIN_CMP_UploadedFilesFilter extends OW_Component
{

    public function __construct($params = array())
    {
        parent::__construct();
    }

    private function getDates($images)
    {
        $dates = array();
        foreach ($images as $image)
        {
            if ( $image->addDatetime )
            {
                $dates[date('Y-m-t', $image->addDatetime)] = date('F Y', $image->addDatetime);
            }
        }
        ksort($dates);
        return array_reverse($dates, true);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $id = uniqid('filter');
        $this->assign('id', $id);
        $images = BOL_ThemeService::getInstance()->findAllCssImages();
        $this->assign('dates', $this->getDates($images));
        $jsString = ";$('#{$id} ul li a').click(function(e){
            e.preventDefault();
            window.browsePhoto.filter({'date': $(this).data('date')});
            $(this).parents('.ow_context_action').find('.ow_context_action_value span').html($(this).html());
        });
        ";
        OW::getDocument()->addOnloadScript($jsString);
    }
}
