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
 * @package ow.ow_system_plugins.base.comments
 * @since 1.0
 */
class BASE_CMP_Comments extends OW_Component
{
    /**
     * @var BASE_CommentsParams
     */
    protected $params;
    protected $batchData;
    protected $staticData;
    protected $id;
    protected $cmpContextId;
    //protected $formName;
    protected $isAuthorized;

    /**
     * Constructor.
     *
     * @param BASE_CommentsParams $params
     */
    public function __construct( BASE_CommentsParams $params )
    {
        parent::__construct();
        $this->params = $params;
        $this->batchData = $params->getBatchData();
        $this->staticData = empty($this->batchData['_static']) ? array() : $this->batchData['_static'];
        $this->batchData = isset($this->batchData[$params->getEntityType()][$params->getEntityId()]) ? $this->batchData[$params->getEntityType()][$params->getEntityId()] : array();

        srand(time());
        $this->id = $params->getEntityType() . $params->getEntityId() . rand(1, 10000);
        $this->cmpContextId = "comments-$this->id";
        $this->assign('cmpContext', $this->cmpContextId);
        $this->assign('wrapInBox', $params->getWrapInBox());
        $this->assign('topList', in_array($params->getDisplayType(), array(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST, BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)));
        $this->assign('bottomList', $params->getDisplayType() == BASE_CommentsParams::DISPLAY_TYPE_WITH_PAGING);
        $this->assign('mini', $params->getDisplayType() == BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI);

        $this->isAuthorized = OW::getUser()->isAuthorized($params->getPluginKey(), 'add_comment') && $params->getAddComment();

        if ( !$this->isAuthorized )
        {
            $errorMessage = $params->getErrorMessage();

            if ( empty($errorMessage) )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_comment');
                $errorMessage = OW::getUser()->isAuthenticated() ? $status['msg'] : OW::getLanguage()->text('base', 'comments_add_login_message');
            }

            $this->assign('authErrorMessage', $errorMessage);
        }

        $this->initForm();
    }

    public function initForm()
    {
        $jsParams = array(
            'entityType' => $this->params->getEntityType(),
            'entityId' => $this->params->getEntityId(),
            'pluginKey' => $this->params->getPluginKey(),
            'contextId' => $this->cmpContextId,
            'userAuthorized' => $this->isAuthorized,
            'customId' => $this->params->getCustomId()
        );

        if ( $this->isAuthorized )
        {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.autosize.js');
            $taId = 'cta' . $this->id;
            $attchId = 'attch' . $this->id;
            $attchUid = BOL_CommentService::getInstance()->generateAttachmentUid($this->params->getEntityType(), $this->params->getEntityId());

            $jsParams['ownerId'] = $this->params->getOwnerId();
            $jsParams['cCount'] = isset($this->batchData['countOnPage']) ? $this->batchData['countOnPage'] : $this->params->getCommentCountOnPage();
            $jsParams['initialCount'] = $this->params->getInitialCommentsCount();
            $jsParams['loadMoreCount'] = $this->params->getLoadMoreCount();
            $jsParams['countOnPage'] = $this->params->getCommentCountOnPage();
            $jsParams['uid'] = $this->id;
            $jsParams['addUrl'] = OW::getRouter()->urlFor('BASE_CTRL_Comments', 'addComment');
            $jsParams['displayType'] = $this->params->getDisplayType();
            $jsParams['textAreaId'] = $taId;
            $jsParams['attchId'] = $attchId;
            $jsParams['attchUid'] = $attchUid;
            $jsParams['enableSubmit'] = true;
            $jsParams['mediaAllowed'] = BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed();
            $jsParams['labels'] = array(
                'emptyCommentMsg' => OW::getLanguage()->text('base', 'empty_comment_error_msg'),
                'disabledSubmit' => OW::getLanguage()->text('base', 'submit_disabled_error_msg'),
                'attachmentLoading' => OW::getLanguage()->text('base', 'submit_attachment_not_loaded'),
            );

            if ( !empty($this->staticData['currentUserInfo']) )
            {
                $userInfoToAssign = $this->staticData['currentUserInfo'];
            }
            else
            {
                $currentUserInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
                $userInfoToAssign = $currentUserInfo[OW::getUser()->getId()];
            }

            $buttonContId = 'bCcont' . $this->id;

            if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() )
            {
                $this->addComponent('attch', new BASE_CLASS_Attachment($this->params->getPluginKey(), $attchUid, $buttonContId));
            }

            $this->assign('buttonContId', $buttonContId);
            $this->assign('currentUserInfo', $userInfoToAssign);
            $this->assign('formCmp', true);
            $this->assign('taId', $taId);
            $this->assign('attchId', $attchId);
        }

        OW::getDocument()->addOnloadScript("new OwComments(" . json_encode($jsParams) . ");");

        $this->assign('displayType', $this->params->getDisplayType());

        // add comment list cmp
        $this->addComponent('commentList', new BASE_CMP_CommentsList($this->params, $this->id));
    }
}

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.ow_system_plugins.base.comments
 * @since 1.0
 */
final class BASE_CommentsParams
{
    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_FULL_LIST = 1;

    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_TOP_FORM_WITH_PAGING = 2;

    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST = 3;

    /**
     * @deprecated since version 1.6.1
     */
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST_AND_MINI_IPC = 4;
    const DISPLAY_TYPE_WITH_PAGING = 10;
    const DISPLAY_TYPE_WITH_LOAD_LIST = 20;
    const DISPLAY_TYPE_WITH_LOAD_LIST_MINI = 30;

    private $pluginKey;
    private $entityType;
    private $entityId;
    private $ownerId;
    private $displayType;
    private $commentCountOnPage;
    private $addComment;
    private $wrapInBox;
    private $batchData;
    private $errorMessage;
    private $initialCommentsCount;
    private $loadMoreCount;
    private $showEmptyList;
    private $customId;
    private $commentPreviewMaxCharCount;

    /**
     * Constructor.
     *
     * @param string $pluginKey
     * @param string $entityType
     */
    public function __construct( $pluginKey, $entityType )
    {
        $this->pluginKey = trim($pluginKey);
        $this->entityType = trim($entityType);
        $this->entityId = 1;
        $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST;
        $this->addComment = true;
        $this->wrapInBox = true;
        $this->initialCommentsCount = 10;
        $this->loadMoreCount = 10;
        $this->commentCountOnPage = 10;
        $this->showEmptyList = true;
        $this->commentPreviewMaxCharCount = 200;
    }

    /**
     * @return string
     */
    public function getPluginKey()
    {
        return $this->pluginKey;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     *
     * @param integer $entityId
     * @return BASE_CommentsParams
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param integer $ownerId
     * @return BASE_CommentsParams
     */
    public function setOwnerId( $ownerId )
    {
        $this->ownerId = (int) $ownerId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getDisplayType()
    {
        return $this->displayType;
    }

    /**
     * @param integer $displayType
     * @return BASE_CommentsParams
     */
    public function setDisplayType( $displayType )
    {
        if ( in_array($displayType, array(self::DISPLAY_TYPE_WITH_PAGING, self::DISPLAY_TYPE_WITH_LOAD_LIST, self::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)) )
        {
            $this->displayType = (int) $displayType;
            return $this;
        }

        switch ( $displayType )
        {
            case self::DISPLAY_TYPE_BOTTOM_FORM_WITH_FULL_LIST:
            case self::DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST:
                $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST;
                break;

            case self::DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST_AND_MINI_IPC:
                $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST_MINI;
                break;

            case self::DISPLAY_TYPE_TOP_FORM_WITH_PAGING:
                $this->displayType = self::DISPLAY_TYPE_WITH_PAGING;
                break;

            default:
                $this->displayType = self::DISPLAY_TYPE_WITH_LOAD_LIST;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getCommentCountOnPage()
    {
        return $this->commentCountOnPage;
    }

    /**
     * @param integer $commentCountOnPage
     * @return BASE_CommentsParams
     */
    public function setCommentCountOnPage( $commentCountOnPage )
    {
        $this->commentCountOnPage = (int) $commentCountOnPage;
        return $this;
    }

    public function getAddComment()
    {
        return $this->addComment;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage( $errorMessage )
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function setAddComment( $addComment )
    {
        $this->addComment = (bool) $addComment;
        return $this;
    }

    public function getWrapInBox()
    {
        return $this->wrapInBox;
    }

    public function setWrapInBox( $wrapInBox )
    {
        $this->wrapInBox = (bool) $wrapInBox;
        return $this;
    }

    public function getBatchData()
    {
        return $this->batchData;
    }

    public function setBatchData( array $data )
    {
        $this->batchData = $data;
        return $this;
    }

    public function getInitialCommentsCount()
    {
        return $this->initialCommentsCount;
    }

    public function setInitialCommentsCount( $initialCommentsCount )
    {
        $this->initialCommentsCount = (int) $initialCommentsCount;
        return $this;
    }

    public function getLoadMoreCount()
    {
        return $this->loadMoreCount;
    }

    public function setLoadMoreCount( $loadMoreCount )
    {
        $this->loadMoreCount = (int) $loadMoreCount;
        return $this;
    }

    public function getShowEmptyList()
    {
        return $this->showEmptyList;
    }

    public function setShowEmptyList( $showEmptyList )
    {
        $this->showEmptyList = (bool) $showEmptyList;
        return $this;
    }

    public function getCustomId()
    {
        return $this->customId;
    }

    public function setCustomId( $customId )
    {
        $this->customId = $customId;
    }

    public function getCommentPreviewMaxCharCount()
    {
        return (int) $this->commentPreviewMaxCharCount;
    }

    public function setCommentPreviewMaxCharCount( $commentPreviewMaxCharCount )
    {
        $this->commentPreviewMaxCharCount = (int) $commentPreviewMaxCharCount;
    }
}
