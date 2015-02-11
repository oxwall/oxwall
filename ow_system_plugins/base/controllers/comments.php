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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Comments extends OW_ActionController
{
    /**
     * @var BOL_CommentService
     */
    private $commentService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $this->commentService = BOL_CommentService::getInstance();
    }

    public function addComment()
    {
        $errorMessage = false;
        $isMobile = !empty($_POST['isMobile']) && (bool) $_POST['isMobile'];
        $params = $this->getParamsObject();

        if ( empty($_POST['commentText']) && empty($_POST['attachmentInfo']) && empty($_POST['oembedInfo']) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_required_validator_message');
        }
        else if ( !OW::getUser()->isAuthorized($params->getPluginKey(), 'add_comment') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_comment');
            $errorMessage = $status['msg'];
        }
        else if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $params->getOwnerId()) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'user_block_message');
        }

        if ( $errorMessage )
        {
            exit(json_encode(array('error' => $errorMessage)));
        }

        $commentText = empty($_POST['commentText']) ? '' : trim($_POST['commentText']);
        $attachment = null;

        if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() && !$isMobile )
        {
            if ( !empty($_POST['attachmentInfo']) )
            {
                $tempArr = json_decode($_POST['attachmentInfo'], true);
                OW::getEventManager()->call('base.attachment_save_image', array('uid' => $tempArr['uid'], 'pluginKey' => $tempArr['pluginKey']));
                $tempArr['href'] = $tempArr['url'];
                $tempArr['type'] = 'photo';
                $attachment = json_encode($tempArr);
            }
            else if ( !empty($_POST['oembedInfo']) )
            {
                $tempArr = json_decode($_POST['oembedInfo'], true);
                // add some actions
                $attachment = json_encode($tempArr);
            }
        }

        $comment = $this->commentService->addComment($params->getEntityType(), $params->getEntityId(), $params->getPluginKey(), OW::getUser()->getId(), $commentText, $attachment);

        // trigger event comment add
        $event = new OW_Event('base_add_comment', array(
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'userId' => OW::getUser()->getId(),
            'commentId' => $comment->getId(),
            'pluginKey' => $params->getPluginKey(),
            'attachment' => json_decode($attachment, true)
        ));

        OW::getEventManager()->trigger($event);

        BOL_AuthorizationService::getInstance()->trackAction($params->getPluginKey(), 'add_comment');

        if ( $isMobile )
        {
            $commentListCmp = new BASE_MCMP_CommentsList($params, $_POST['cid']);
        }
        else
        {
            $commentListCmp = new BASE_CMP_CommentsList($params, $_POST['cid']);
        }

        exit(json_encode(array(
            'newAttachUid' => $this->commentService->generateAttachmentUid($params->getEntityType(), $params->getEntityId()),
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'commentList' => $commentListCmp->render(),
            'onloadScript' => OW::getDocument()->getOnloadScript(),
            'commentCount' => $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId())
                )
            )
        );
    }

    public function getCommentList()
    {
        $params = $this->getParamsObject();

        $page = ( isset($_POST['page']) && (int) $_POST['page'] > 0) ? (int) $_POST['page'] : 1;
        $commentsList = new BASE_CMP_CommentsList($params, $_POST['cid'], $page);
        exit(json_encode(array(
            'onloadScript' => OW::getDocument()->getOnloadScript(),
            'commentList' => $commentsList->render(),
            'commentCount' => $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId())
        )));
    }

    public function getMobileCommentList()
    {
        $params = $this->getParamsObject();
        $commentsList = new BASE_MCMP_CommentsList($params, $_POST['cid']);
        exit(json_encode(array('onloadScript' => OW::getDocument()->getOnloadScript(), 'commentList' => $commentsList->render())));
    }

    public function deleteComment()
    {
        $commentArray = $this->getCommentInfoForDelete();
        $comment = $commentArray['comment'];
        $commentEntity = $commentArray['commentEntity'];
        $this->deleteAttachmentFiles($comment);
        $this->commentService->deleteComment($comment->getId());
        $commentCount = $this->commentService->findCommentCount($commentEntity->getEntityType(), $commentEntity->getEntityId());

        if ( $commentCount === 0 )
        {
            $this->commentService->deleteCommentEntity($commentEntity->getId());
        }

        $event = new OW_Event('base_delete_comment', array(
            'entityType' => $commentEntity->getEntityType(),
            'entityId' => $commentEntity->getEntityId(),
            'userId' => $comment->getUserId(),
            'commentId' => $comment->getId()
        ));

        OW::getEventManager()->trigger($event);

        $this->getCommentList();
    }

    public function deleteCommentAtatchment()
    {
        /* @var $comment BOL_Comment */
        $commentArray = $this->getCommentInfoForDelete();
        $comment = $commentArray['comment'];
        $this->deleteAttachmentFiles($comment);

        if ( !trim($comment->getMessage()) )
        {
            $this->commentService->deleteComment($comment->getId());
        }
        else
        {
            $comment->setAttachment(null);
            $this->commentService->updateComment($comment);
        }

        exit;
    }

    private function deleteAttachmentFiles( BOL_Comment $comment )
    {
        // delete attachments
        $attch = $comment->getAttachment();

        if ( $attch !== null )
        {
            $tempArr = json_decode($attch, true);

            if ( !empty($tempArr['uid']) && !empty($tempArr['pluginKey']) )
            {
                BOL_AttachmentService::getInstance()->deleteAttachmentByBundle($tempArr['pluginKey'], $tempArr['uid']);
            }
        }
    }

    private function getCommentInfoForDelete()
    {
        if ( !isset($_POST['commentId']) || (int) $_POST['commentId'] < 1 )
        {
            echo json_encode(array('error' => OW::getLanguage()->text('base', 'comment_ajax_error')));
            exit();
        }

        /* @var $comment BOL_Comment */
        $comment = $this->commentService->findComment((int) $_POST['commentId']);
        /* @var $commentEntity BOL_CommentEntity */
        $commentEntity = $this->commentService->findCommentEntityById($comment->getCommentEntityId());

        if ( $comment === null || $commentEntity === null )
        {
            echo json_encode(array('error' => OW::getLanguage()->text('base', 'comment_ajax_error')));
            exit();
        }

        $params = $this->getParamsObject();

        $isModerator = OW::getUser()->isAuthorized($params->getPluginKey());
        $isOwnerAuthorized = (OW::getUser()->isAuthenticated() && $params->getOwnerId() !== null && (int) $params->getOwnerId() === (int) OW::getUser()->getId());
        $commentOwner = ( (int) OW::getUser()->getId() === (int) $comment->getUserId() );

        if ( !$isModerator && !$isOwnerAuthorized && !$commentOwner )
        {
            echo json_encode(array('error' => OW::getLanguage()->text('base', 'auth_ajax_error')));
            exit();
        }

        return array('comment' => $comment, 'commentEntity' => $commentEntity);
    }

    private function getParamsObject()
    {
        $errorMessage = false;

        $entityType = !isset($_POST['entityType']) ? null : trim($_POST['entityType']);
        $entityId = !isset($_POST['entityId']) ? null : (int) $_POST['entityId'];
        $pluginKey = !isset($_POST['pluginKey']) ? null : trim($_POST['pluginKey']);

        if ( !$entityType || !$entityId || !$pluginKey )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_ajax_error');
        }

        $params = new BASE_CommentsParams($pluginKey, $entityType);
        $params->setEntityId($entityId);

        if ( isset($_POST['ownerId']) )
        {
            $params->setOwnerId((int) $_POST['ownerId']);
        }

        if ( isset($_POST['commentCountOnPage']) )
        {
            $params->setCommentCountOnPage((int) $_POST['commentCountOnPage']);
        }

        if ( isset($_POST['displayType']) )
        {
            $params->setDisplayType($_POST['displayType']);
        }

        if ( isset($_POST['initialCount']) )
        {
            $params->setInitialCommentsCount((int) $_POST['initialCount']);
        }

        if ( isset($_POST['loadMoreCount']) )
        {
            $params->setLoadMoreCount((int) $_POST['loadMoreCount']);
        }

        if ( $errorMessage )
        {
            echo json_encode(array(
                'error' => $errorMessage
            ));

            exit();
        }

        return $params;
    }
}
