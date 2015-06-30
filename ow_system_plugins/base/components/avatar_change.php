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
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.2
 */
class BASE_CMP_AvatarChange extends OW_Component
{
    public function __construct( array $params = null )
    {
        parent::__construct();

        $step = !empty($params['step']) && in_array($params['step'], array(1,2)) ? $params['step'] : 1;
        $inputId = !empty($params['inputId']) ? $params['inputId'] : null;
        $entityType = !empty($params['entityType']) ? $params['entityType'] : null;
        $entityId = !empty($params['entityId']) ? $params['entityId'] : null;
        $id = !empty($params['id']) ? $params['id'] : null;
        $changeUserAvatar = isset($params['changeUserAvatar']) && $params['changeUserAvatar'] == false ? false : true;

        $hideSteps = !empty($params['hideSteps']) ? $params['hideSteps'] : false;
        $displayPreloader = !empty($params['displayPreloader']) ? $params['displayPreloader'] : false;

        $avatarService = BOL_AvatarService::getInstance();
        $lang = OW::getLanguage();

        $library = $avatarService->collectAvatarChangeSections();

        $minSize = OW::getConfig()->getValue('base', 'avatar_big_size');

        $this->assign('limit', BOL_AvatarService::AVATAR_CHANGE_GALLERY_LIMIT);
        $this->assign('library', $library);
        $this->assign('step', $step);
        $this->assign('minSize', $minSize);
        $this->assign('hideSteps', $hideSteps);
        $this->assign('displayPreloader', $displayPreloader);

        $avatarService->setAvatarChangeSessionKey();

        $lang->addKeyForJs('base', 'avatar_image_too_small');
        $lang->addKeyForJs('base', 'avatar_drop_single_image');
        $lang->addKeyForJs('base', 'drag_image_or_browse');
        $lang->addKeyForJs('base', 'drop_image_here');
        $lang->addKeyForJs('base', 'not_valid_image');
        $lang->addKeyForJs('base', 'avatar_crop');
        $lang->addKeyForJs('base', 'avatar_changed');
        $lang->addKeyForJs('base', 'avatar_select_image');
        $lang->addKeyForJs('base', 'crop_avatar_failed');
        $lang->addKeyForJs('base', 'avatar_change');
        

        $staticJsUrl = OW::getPluginManager()->getPlugin('base')->getStaticJsUrl();
        $staticCssUrl = OW::getPluginManager()->getPlugin('base')->getStaticCssUrl();

        OW::getDocument()->addStyleSheet($staticCssUrl . 'jquery.Jcrop.min.css');
        OW::getDocument()->addScript($staticJsUrl . 'jquery.Jcrop.min.js');
        OW::getDocument()->addScript($staticJsUrl . 'avatar_change.js');

        $objParams = array(
            'ajaxResponder' => OW::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder'),
            'step' => $step,
            'limit' => BOL_AvatarService::AVATAR_CHANGE_GALLERY_LIMIT,
            'inputId' => $inputId,
            'minCropSize' => $minSize,
            'changeUserAvatar' => $changeUserAvatar
        );

        if ( $library && $entityType && $id )
        {
            $item = $avatarService->getAvatarChangeGalleryItem($entityType, $entityId, $id);
            if ( $item && !empty($item['url']) )
            {
                $objParams['url'] = $item['url'];
                $objParams['entityType'] = $entityType;
                $objParams['entityId'] = $entityId;
                $objParams['id'] = $id;
            }
        }

        $script = "
            var avatar = new avatarChange(" . json_encode($objParams) . ");
        ";

        if ( $library )
        {
            $script .= "OW.addScroll($('.ow_photo_library_wrap'));";
        }

        OW::getDocument()->addOnloadScript($script);
    }
}