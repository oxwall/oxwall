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
 * Oembed attachment
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_OembedAttachment extends OW_Component
{
    protected $uniqId, $oembed;
    
    public function __construct( array $oembed, $delete = false )
    {
        parent::__construct();

        $this->oembed = $oembed;

        $this->assign('delete', $delete);
        $this->uniqId = uniqid("oe-");
        $this->assign("uniqId", $this->uniqId);
    }

    public function setDeleteBtnClass( $class )
    {
        $this->assign('deleteClass', $class);
    }

    public function setContainerClass( $class )
    {
        $this->assign('containerClass', $class);
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($this->oembed["html"], "autoplay", 1);
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($code, "play", 1);
        
        $js->addScript('$(".ow_oembed_video_cover", "#" + {$uniqId}).click(function() { '
                . '$(".two_column", "#" + {$uniqId}).addClass("ow_video_playing"); '
                . '$(".attachment_left", "#" + {$uniqId}).html({$embed});'
                . 'OW.trigger("base.comment_video_play", {});'
                . 'return false; });', array(
            "uniqId" => $this->uniqId,
            "embed" => $code
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function render()
    {
        if ( $this->oembed["type"] == "video" && !empty($this->oembed["html"]) )
        {
            $this->initJs();
        }
        
        $this->assign('data', $this->oembed);

        return parent::render();
    }
}