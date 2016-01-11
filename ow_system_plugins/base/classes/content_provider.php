<?php

class BASE_CLASS_ContentProvider
{
    const ENTITY_TYPE_PROFILE = "user_join";
    const ENTITY_TYPE_COMMENT = "comment";
    const ENTITY_TYPE_AVATAR = "avatar-change";
    
    /**
     * Singleton instance.
     *
     * @var BASE_CLASS_ContentProvider
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BASE_CLASS_ContentProvider
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    
    }
    
    public function onCollectTypes( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "pluginKey" => "base",
            "authorizationGroup" => "base",
            "group" => "profiles",
            "entityType" => self::ENTITY_TYPE_PROFILE,
            
            "groupLabel" => OW::getLanguage()->text("base", "content_profiles_label"),
            "entityLabel" => OW::getLanguage()->text("base", "content_profile_label"),
            "displayFormat" => "empty"
        ));
        
        $event->add(array(
            "pluginKey" => "base",
            "authorizationGroup" => "base",
            "group" => "comments",
            "entityType" => self::ENTITY_TYPE_COMMENT,
            
            "groupLabel" => OW::getLanguage()->text("base", "content_comments_label"),
            "entityLabel" => OW::getLanguage()->text("base", "content_comment_label"),
            "moderation" => array(BOL_ContentService::MODERATION_TOOL_FLAG)
        ));
        
        $event->add(array(
            "pluginKey" => "base",
            "authorizationGroup" => "base",
            "group" => "avatars",
            "entityType" => self::ENTITY_TYPE_AVATAR,
            
            "groupLabel" => OW::getLanguage()->text("base", "content_avatars_label"),
            "entityLabel" => OW::getLanguage()->text("base", "content_avatar_label")
        ));
    }
    
    public function onGetInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = null;
        
        switch ($params["entityType"])
        {
            case self::ENTITY_TYPE_PROFILE:
                $data = $this->getProfileInfo($params["entityIds"]);

                break;
            
            case self::ENTITY_TYPE_COMMENT:
                $data = $this->getCommentInfo($params["entityIds"]);
                
                break;
            
            case self::ENTITY_TYPE_AVATAR:
                $data = $this->getAvatarInfo($params["entityIds"]);
                
                break;
            
            default:
                return;
        }
        
        $event->setData($data);
        
        return $data;
    }
    
    private function getAvatarInfo( $entityIds )
    {
        $out = array();
        
        if ( empty($entityIds) )
        {
            return $out;
        }

        $avatarList = BOL_AvatarService::getInstance()->findAvatarByIdList($entityIds);
        
        if ( empty($avatarList) )
        {
            return $out;
        }
        
        foreach ( $avatarList as $avatar )
        {
            $info = array();

            $info["id"] = $avatar->id;
            $info["userId"] = $avatar->userId;
            $info["timeStamp"] = $avatar->hash;
            $info["status"] = $avatar->status;
            
            $fullSize = BOL_AvatarService::getInstance()->getAvatarUrl($avatar->userId, 3, $avatar->hash, true, false);
            $info["image"] = array( "thumbnail" => BOL_AvatarService::getInstance()->getAvatarUrl($avatar->userId, 1, $avatar->hash, true, false),
                                    "preview" => BOL_AvatarService::getInstance()->getAvatarUrl($avatar->userId, 2, $avatar->hash, true, false),
                                    "view" => $fullSize,
                                    "fullsize" => $fullSize );
            
            $out[$avatar->id] = $info;
        }
        
        return $out;
    }
    
    private function getProfileInfo( $userIds )
    {
        foreach ( $userIds as $userId )
        {
            $user = BOL_UserService::getInstance()->findUserById($userId);
            
            $info = array();

            $info["id"] = $user->id;
            $info["userId"] = $user->id;
            $info["timeStamp"] = $user->joinStamp;
            $info["joinIP"] = $user->joinIp;
            $info["activityStamp"] = $user->activityStamp;
            $info["email"] = $user->email;
            
            $out[$userId] = $info;
        }
        
        return $out;
    }
    
    private function getCommentInfo( $commentIds )
    {
        $out = array();

        $comments = BOL_CommentService::getInstance()->findCommentListByIds($commentIds);
        
        foreach ( $comments as $comment )
        {
            $info = array();

            $info["id"] = $comment->id;
            $info["userId"] = $comment->userId;

            $info["text"] = $comment->message;
            $info["timeStamp"] = $comment->createStamp;

            $info["image"] = array();

            $attachment = empty($comment->attachment)
                    ? null
                    : json_decode($comment->attachment, true);

            if ( $attachment !== null )
            {
                if ( $attachment["type"] == "photo" )
                {
                    $info["image"]["preview"] = $attachment["url"];
                }

                $info["image"]["thumbnail"] = empty($attachment["thumbnail_url"]) 
                        ? null 
                        : $attachment["thumbnail_url"];

                $info["title"] = empty($attachment["title"]) ? null : $attachment["title"];
                $info["description"] = empty($attachment["description"]) ? null : $attachment["description"];
                $info["url"] = empty($attachment["url"]) ? null : $attachment["url"];
            }
            
            $commentEntity = BOL_CommentService::getInstance()->findCommentEntityById($comment->commentEntityId);
            $contentInfo = BOL_ContentService::getInstance()->getContent($commentEntity->entityType, $commentEntity->entityId);
            
            if ( !empty($contentInfo) )
            {
                $label = strtolower($contentInfo["label"]);
                $contentEmbed = $contentInfo["url"] 
                        ? '<a href="' . $contentInfo["url"] . '">' . $label . '</a>' 
                        : $label;

                $info["label"] = OW::getLanguage()->text("base", "comment_content_label", array(
                    "content" => $contentEmbed
                ));
            }
                        
            $out[$comment->id] = $info;
        }
                
        return $out;
    }
    
    public function onUpdateInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        switch ($params["entityType"])
        {
            case self::ENTITY_TYPE_PROFILE:
                $this->updateProfiles($data);

                break;
            
            case self::ENTITY_TYPE_COMMENT:
                $data = $this->updateComments($data);
                
                break;
            
            case self::ENTITY_TYPE_AVATAR:
                $this->updateAvatar($data);
                
                break;
            
            default:
                return;
        }
    }
    
    private function updateProfiles( $data )
    {
        foreach ( $data as $userId => $info )
        {
            $isApproved = BOL_UserService::getInstance()->isApproved($userId);
            $isSuspended = BOL_UserService::getInstance()->isSuspended($userId);
            
            if ( $info["status"] == BOL_ContentService::STATUS_ACTIVE && !$isApproved )
            {
                BOL_UserService::getInstance()->approve($userId);
                BOL_UserService::getInstance()->sendApprovalNotification($userId);
            }
            
            if ( $info["status"] == BOL_ContentService::STATUS_APPROVAL && $isApproved )
            {
                BOL_UserService::getInstance()->disapprove($userId);
            }
            
            if ( $info["status"] == BOL_ContentService::STATUS_SUSPENDED && !$isSuspended )
            {
                BOL_UserService::getInstance()->disapprove($userId);
            }
        }
    }
    
    private function updateComments( $data )
    {
        foreach ( $data as $commentId => $info )
        {
            // TODO
        }
    }
    
    private function updateAvatar( $data )
    {
        foreach ( $data as $avatarId => $info )
        {
            $avatar = BOL_AvatarService::getInstance()->findAvatarById($avatarId);
            
            if ( $avatar->status != $info['status'] )
            {
                $avatar->status = $info['status'];
                BOL_AvatarService::getInstance()->updateAvatar($avatar);
                
                $params = array(
                    'avatarId' => $avatar->id, 
                    'userId' => $avatar->userId, 
                    'trackAction' => false,
                    'isModerable' => true
                );
        
                $event = new OW_Event('base.after_avatar_update', $params);
                OW::getEventManager()->trigger($event);
            }
        }
    }
    
    public function onDelete( OW_Event $event )
    {
        $params = $event->getParams();
        
        switch ($params["entityType"])
        {
            case self::ENTITY_TYPE_PROFILE:
                $this->deleteProfiles($params["entityIds"]);

                break;
            
            case self::ENTITY_TYPE_COMMENT:
                $this->deleteComments($params["entityIds"]);
                
                break;
            
            case self::ENTITY_TYPE_AVATAR:
                $this->deleteAvatar($params["entityIds"]);
                
                break;
            
            default:
                return;
        }
    }
    
    public function deleteProfiles( $userIds )
    {
        foreach ( $userIds as $userId )
        {
            BOL_UserService::getInstance()->deleteUser($userId);
        }
    }
    
    public function deleteComments( $commentIds )
    {
        BOL_CommentService::getInstance()->deleteCommentListByIds($commentIds);
    }
    
    private function deleteAvatar( $avatarIds )
    {
        foreach ( $avatarIds as $id )
        {
            BOL_AvatarService::getInstance()->deleteAvatarById($id);
        }
    }
    
    
    public function onCommentAdd( OW_Event $event )
    {
        $params = $event->getParams();
               
        $contentInfo = BOL_ContentService::getInstance()->getContent($params["entityType"], $params["entityId"]);
        $label = strtolower($contentInfo["label"]);
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_ADD, array(
            "entityType" => self::ENTITY_TYPE_COMMENT,
            "entityId" => $params["commentId"]
        ), array(
            "string" => array("key" => "base+comment_added_string", "vars" => array(
                "content" => $contentInfo["url"] 
                    ? '<a href="' . $contentInfo["url"] . '">' . $label . '</a>' 
                    : $label
            ))
        )));
    }
    
    public function onUserJoin( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_ADD, array(
            "entityType" => self::ENTITY_TYPE_PROFILE,
            "entityId" => $userId
        ), array(
            "string" => array('key' => 'base+feed_user_join')
        )));
    }
    
    public function afterUserEdit( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = !empty($params["userId"]) ? $params["userId"] : 0 ;

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( empty($user) )
        {
            return;
        }

        $isModerate = !empty($params["moderate"]) ? $params["moderate"] : false;

        if ( $isModerate ) {
            $url = new BASE_CLASS_LanguageParamsUrl();
            $url->setRoute('base_edit_user_datails', array( 'userId' => $userId ));

            OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_CHANGE, array(
                "entityType" => self::ENTITY_TYPE_PROFILE,
                "entityId" => $userId
            ), array(
                "string" => array('key' => 'base+moderation_user_update', "vars" => array('profileUrl' => UTIL_Serialize::serialize($url)))
            )));
        }
    }
    
    public function onUserDeleted( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
            "entityType" => self::ENTITY_TYPE_PROFILE,
            "entityId" => $userId
        )));
    }
    
    public function onUserApprove( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = $params["userId"];
        
        OW::getEventManager()->trigger(new OW_Event("moderation.approve", array(
            "entityType" => self::ENTITY_TYPE_PROFILE,
            "entityId" => $userId
        )));
    }
    
    
    public function onAvatarChange( OW_Event $event )
    {
        $params = $event->getParams();
        $avatarId = $params["avatarId"];
        
        if ( isset($params["isModerable"]) && $params["isModerable"] == false )
        {
            return;
        }
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_CHANGE, array(
            "entityType" => self::ENTITY_TYPE_AVATAR,
            "entityId" => $avatarId
        ), array(
            "string" => array("key" => "base+avatar_update_string")
        )));
    }
    
    public function onAvatarDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $avatarId = $params["avatarId"];
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
            "entityType" => self::ENTITY_TYPE_AVATAR,
            "entityId" => $avatarId
        ), array()));
    }
    
    
    public function init()
    {
        OW::getEventManager()->bind('base.after_avatar_update', array($this, "onAvatarChange"), 10000);
        OW::getEventManager()->bind('base.before_user_avatar_delete', array($this, "onAvatarDelete"));
        
        OW::getEventManager()->bind(OW_EventManager::ON_USER_APPROVE, array($this, "onUserApprove"));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_EDIT, array($this, "afterUserEdit"));
        
        OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($this, "onUserJoin"));
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, "onUserDeleted"));
        OW::getEventManager()->bind("base_add_comment", array($this, "onCommentAdd"));
        
        OW::getEventManager()->bind(BOL_ContentService::EVENT_COLLECT_TYPES, array($this, "onCollectTypes"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_GET_INFO, array($this, "onGetInfo"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_UPDATE_INFO, array($this, "onUpdateInfo"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_DELETE, array($this, "onDelete"));
    }
}