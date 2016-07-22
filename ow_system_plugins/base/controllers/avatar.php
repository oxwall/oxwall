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
 * Avatar action controller
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Avatar extends OW_ActionController
{
    use BASE_CLASS_UploadTmpAvatarTrait;

    /**
     * @var BOL_AvatarService
     */
    private $avatarService;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->avatarService = BOL_AvatarService::getInstance();
    }

    /**
     * Method acts as ajax responder. Calls methods using ajax
     *
     * @return string
     */
    public function ajaxResponder()
    {
        $request = $_POST;

        if ( isset($request['ajaxFunc']) && OW::getRequest()->isAjax() )
        {
            $callFunc = (string) $request['ajaxFunc'];

            $result = call_user_func(array($this, $callFunc), $request);
        }
        else
        {
            exit();
        }

        exit(json_encode($result));
    }

    public function ajaxUploadImage( $params )
    {
        if ( isset($_FILES['file']) )
        {
            return $this->uploadTmpAvatar($_FILES['file']);
        }

        return array('result' => false);
    }

    public function ajaxDeleteImage( $params )
    {
        $avatarService = BOL_AvatarService::getInstance();

        $key = $avatarService->getAvatarChangeSessionKey();
        $avatarService->deleteUserTempAvatar($key);

        return array('result' => true);
    }

    public function ajaxLoadMore( $params )
    {
        if ( isset($params['entityType']) && isset($params['entityId']) && isset($params['offset']) )
        {
            $entityType = $params['entityType'];
            $entityId = $params['entityId'];
            $offset = $params['offset'];

            $section = BOL_AvatarService::getInstance()->getAvatarChangeSection($entityType, $entityId, $offset);

            if ( $section )
            {
                $cmp = new BASE_CMP_AvatarLibrarySection($section['list'], $offset, $section['count']);
                $markup = $cmp->render();

                return array('result' => true, 'markup' => $markup, 'count' => $section['count']);
            }
        }

        return array('result' => false);
    }

    public function ajaxCropPhoto( $params )
    {
        if ( !isset($params['coords']) || !isset($params['view_size']) )
        {
            return array('result' => false, 'case' => 0);
        }

        $changeUserAvatar = isset($params['changeUserAvatar']) && (int) !$params['changeUserAvatar'] ? false : true;
        $coords = $params['coords'];
        $viewSize = $params['view_size'];
        $path = null;

        $localFile = false;

        $avatarService = BOL_AvatarService::getInstance();

        if ( !empty($params['entityType']) && !empty($params['id']) )
        {
            $item = $avatarService->getAvatarChangeGalleryItem($params['entityType'], $params['entityId'], $params['id']);
            
            if ( !$item || empty($item['path']) || !OW::getStorage()->fileExists($item['path']) )
            {
                return array('result' => false, 'case' => 1);
            }

            $path = $item['path'];
        }
        else if ( isset($params['url']) ) 
        {
            $path = UTIL_Url::getLocalPath($params['url']);
            
            if ( !OW::getStorage()->fileExists($path)  )
            {
                if ( !file_exists($path) )
                {
                    return array('result' => false, 'case' => 2);
                }
                
                $localFile = true;
            }
        }

        $userId = OW_Auth::getInstance()->getUserId();
        if ( $userId && $changeUserAvatar)
        {
            $avatar = $avatarService->findByUserId($userId);

            try
            {
                $event = new OW_Event('base.before_avatar_change', array(
                    'userId' => $userId,
                    'avatarId' => $avatar ? $avatar->id : null,
                    'upload' => false,
                    'crop' => true
                ));
                OW::getEventManager()->trigger($event);

                if ( !$avatarService->cropAvatar($userId, $path, $coords, $viewSize, array('isLocalFile' => $localFile )) )
                {
                    return array(
                        'result' => false,
                        'case' => 6
                    );
                }

                $avatar = $avatarService->findByUserId($userId, false);

                $event = new OW_Event('base.after_avatar_change', array(
                    'userId' => $userId,
                    'avatarId' => $avatar ? $avatar->id : null,
                    'upload' => false,
                    'crop' => true
                ));
                OW::getEventManager()->trigger($event);

                return array(
                    'result' => true,
                    'modearationStatus' => $avatar->status,
                    'url' => $avatarService->getAvatarUrl($userId, 1, null, false, false),
                    'bigUrl' => $avatarService->getAvatarUrl($userId, 2, null, false, false)
                );
            }
            catch ( Exception $e )
            {
                return array('result' => false, 'case' => 4);
            }
        }
        else
        {
            $key = $avatarService->getAvatarChangeSessionKey();
            $path = $avatarService->getTempAvatarPath($key, 3);
            
            if ( !file_exists($path) )
            {
                return array('result' => false, 'case' => 5);
            }
            
            $avatarService->cropTempAvatar($key, $coords, $viewSize);

            return array(
                'result' => true,
                'url' => $avatarService->getTempAvatarUrl($key, 1),
                'bigUrl' => $avatarService->getTempAvatarUrl($key, 2)
            );
        }
    }
    
    public function ajaxAvatarApprove( $params )
    {
        if ( isset($params['avatarId']) && OW::getUser()->isAuthorized('base') )
        {
            $entityId = $params['avatarId'];
            $entityType = BASE_CLASS_ContentProvider::ENTITY_TYPE_AVATAR;

            $event = new OW_Event("moderation.approve", array(
                "entityType" => $entityType,
                "entityId" => $entityId
            ));

            OW::getEventManager()->trigger($event);

            $data = $event->getData();
            
            if ( empty($data) )
            {
                return array('result' => true);
            }
            
            if ( !empty($data["message"]) )
            {
                return array('result' => true, 'message' => $data["message"]);
            }
            else
            {
                return array('result' => false, 'error' => $data["error"]);
            }
        }

        return array('result' => false);
    }
}
