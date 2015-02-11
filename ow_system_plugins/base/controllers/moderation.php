<?php

class BASE_CTRL_Moderation extends OW_ActionController
{
    const ITEMS_PER_PAGE = 20;
    
    public function init()
    {
        parent::init();
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException;
        }
    }
    
    protected function onlyModerators()
    {
        $isModerator = BOL_AuthorizationService::getInstance()->isModerator();
        $isAdmin = OW::getUser()->isAdmin();
        
        if ( !$isAdmin && !$isModerator )
        {
            throw new Redirect403Exception;
        }
    }
    
    /**
     * 
     * @return BASE_CMP_ContentMenu
     */
    protected function getMenu()
    {
        $event = new BASE_CLASS_EventCollector("base.moderation_tools.collect_menu");
        OW::getEventManager()->trigger($event);
        
        $menuData = $event->getData();
        
        if ( empty($menuData) )
        {
            return null;
        }
        
        $menu = new BASE_CMP_ContentMenu();
        
        foreach ( array_reverse($menuData) as $item )
        {
            $element = new BASE_MenuItem();
            $element->setUrl($item["url"]);
            $element->setLabel($item["label"]);
            $element->setIconClass($item["iconClass"]);
            $element->setKey($item["key"]);

            $menu->addElement($element);
        }
        
        return $menu;
    }
   
    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text("base", "moderation_tools"));
        $this->setPageHeading(OW::getLanguage()->text("base", "moderation_tools"));

        $menu = $this->getMenu();
        
        if ( $menu === null )
        {
            return; // Zero situation
        }
        
        $menuItems = $menu->getMenuItems();
        $this->redirect(end($menuItems)->getUrl());
    }
    
    public function flags( $params )
    {
        $this->onlyModerators();
        
        $this->setPageTitle(OW::getLanguage()->text("base", "moderation_tools"));
        $this->setPageHeading(OW::getLanguage()->text("base", "moderation_tools"));
        
        $menu = $this->getMenu();
        if ( $menu === null )
        {
            $this->redirect(OW::getRouter()->urlForRoute("base.moderation_tools"));
        }
        
        $menu->deactivateElements();
        
        $menuItem = $menu->getElement("flags");
        if ( $menuItem )
        {
            $menuItem->setActive(true);
        }
        
        $this->addComponent("menu", $menu);
        
        $groups = BOL_FlagService::getInstance()->getContentGroupsWithCount();
        
        if ( !empty($params["group"]) && empty($groups[$params["group"]]) )
        {
            $this->redirect(OW::getRouter()->urlForRoute("base.moderation_flags_index"));
        }
        
        $currentGroup = empty($params["group"])
                ? reset($groups)
                : $groups[$params["group"]];
        
        if ( empty($currentGroup) )
        {
            $this->redirect(OW::getRouter()->urlForRoute("base.moderation_tools"));
        }
                
        $contentMenu = new BASE_CMP_VerticalMenu();
        
        $sideMenuOrder = 1;
        foreach ( $groups as $groupKey => $group )
        {
            $item = new BASE_VerticalMenuItem();
            $item->setKey($groupKey);
            $item->setUrl($group["url"]);
            $item->setNumber($group["count"]);
            $item->setLabel($group["label"]);
            $item->setActive($currentGroup["name"] == $group["name"]);
            $item->setOrder($sideMenuOrder++);
            
            $contentMenu->addElement($item);
        }
        
        $this->addComponent("contentMenu", $contentMenu);        
        
        // Paging
        $page = (isset($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
        $perPage = self::ITEMS_PER_PAGE;
        $limit = array(
            ($page - 1) * $perPage,
            $perPage
        );
        
        $this->addComponent("paging", new BASE_CMP_Paging($page, ceil($currentGroup["count"] / $perPage), 5));
        
        // List
        
        $flags = BOL_FlagService::getInstance()->findFlagsByEntityTypeList($currentGroup["entityTypes"], $limit);
        $entityList = array();
        $userIds = array();
        $reporterIds = array();
        
        foreach ( $flags as $flag )
        {
            $entityList[$flag->entityType] = empty($entityList[$flag->entityType])
                    ? array()
                    : $entityList[$flag->entityType];
            
            $entityList[$flag->entityType][] = $flag->entityId;
            $reporterIds[$flag->userId] = $flag->userId;
        }
        
        $contentData = array();
        foreach ( $entityList as $entityType => $entityIds )
        {
            $infoList = BOL_ContentService::getInstance()->getContentList($entityType, $entityIds);
            foreach ( $infoList as $entityId => $info )
            {
                $userIds[] = $info["userId"];
                $contentData[$entityType . ':' . $entityId] = $info;
            }
        }
            
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);
        $reporterUrls = BOL_UserService::getInstance()->getUserUrlsForList($reporterIds);
        $reporterNames = BOL_UserService::getInstance()->getDisplayNamesForList($reporterIds);
        
        $tplFlags = array();
        
        foreach ( $flags as $flag )
        {
            $content = $contentData[$flag->entityType . ":" . $flag->entityId];
            $contentPresenter = new BASE_CMP_ContentPresenter($content);
            
            $userName = $avatarData[$content["userId"]]["title"];
            $userUrl = $avatarData[$content["userId"]]["url"];
            
            $label = empty($content["label"]) ? $content["typeInfo"]["entityLabel"] : $content["label"];
            
            $tplFlags[] = array(
                "content" => $contentPresenter->render(),
                "avatar" => $avatarData[$content["userId"]],
                "string" => OW::getLanguage()->text("base", "moderation_flags_item_string", array(
                    "userName" => $userName,
                    "userUrl" => $userUrl,
                    "content" => strtolower($label)
                )),
                
                "contentLabel" => strtolower($label),
                
                "entityType" => $flag->entityType,
                "entityId" => $flag->entityId,
                "time" => UTIL_DateTime::formatDate($flag->timeStamp),
                
                "reason" => $flag->reason,
                "reporter" => array(
                    "url" => $reporterUrls[$flag->userId],
                    "name" => $reporterNames[$flag->userId]
                )
            );
        }
        
        $uniqId = uniqid("m-");
        $this->assign("uniqId", $uniqId);
        
        $this->assign("flags", $tplFlags);
        $this->assign("group", $currentGroup);
                
        $this->assign("responderUrl", OW::getRouter()->urlFor(__CLASS__, "flagsResponder", array(
            "group" => $currentGroup["name"]
        )));
        
        OW::getLanguage()->addKeyForJs("base", "are_you_sure");
        OW::getLanguage()->addKeyForJs("base", "moderation_delete_confirmation");
        OW::getLanguage()->addKeyForJs("base", "moderation_delete_multiple_confirmation");
        OW::getLanguage()->addKeyForJs("base", "moderation_no_items_warning");
        
        $options = array(
            "groupLabel" => strtolower($currentGroup["label"])
        );
        
        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction("MODERATION_FlagsInit", array(
            $uniqId, $options
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function flagsResponder( $params )
    {
        if ( !OW::getRequest()->isPost() || 
                !(OW::getUser()->isAdmin() || BOL_AuthorizationService::getInstance()->isModerator()) )
        {
            throw new Redirect403Exception;
        }
        
        $data = $_POST;
        $data["items"] = empty($data["items"]) ? array() : $data["items"];
        list($command, $type) = explode(".", $data["command"]);
        
        $backUrl = OW::getRouter()->urlForRoute("base.moderation_flags", array(
            "group" => $params["group"]
        ));
        
        $itemKeys = $type == "single" ? array($data["item"]) : $data["items"];
        
        if ( empty($itemKeys) )
        {
            OW::getFeedback()->warning(OW::getLanguage()->text("base", "moderation_no_items_warning"));
            $this->redirect($backUrl);
        }
        
        $itemIds = array();
        foreach ( $itemKeys as $itemKey )
        {
            list($entityType, $entityId) = explode(":", $itemKey);
            $itemIds[$entityType] = empty($itemIds[$entityType]) ? array() : $itemIds[$entityType];
            
            $itemIds[$entityType][] = $entityId;
        }
        
        $affected = 0;
        $lastEntityType = null;
        
        foreach ( $itemIds as $entityType => $entityIds )
        {
            if ( $command == "delete" )
            {
                BOL_ContentService::getInstance()->deleteContentList($entityType, $entityIds);
            }

            if ( $command == "unflag" )
            {
                // Pass
            }
            
            BOL_FlagService::getInstance()->deleteFlagList($entityType, $entityIds);
            $affected = count($entityIds);
            $lastEntityType = $entityType;
        }
        
        // Feedback
        $assigns = array();
        
        $multiple = $affected > 1;
        
        if ( $multiple )
        {
            $tmp = BOL_ContentService::getInstance()->getContentGroups();
            $groupInfo = $tmp[$params["group"]];
            
            $assigns["content"] = strtolower($groupInfo["label"]);
            $assigns["count"] = $affected;
        }
        else
        {
            $typeInfo = BOL_ContentService::getInstance()->getContentTypeByEntityType($lastEntityType);
            $assigns["content"] = $typeInfo["entityLabel"];
        }
        
        $feedbackKey = $command == "delete" ? "base+moderation_feedback_delete" : "base+moderation_feedback_unflag";
        
        list($langPrefix, $langKey) = explode("+", $feedbackKey);
        OW::getFeedback()->info(OW::getLanguage()
                ->text($langPrefix, $langKey . ($multiple ? "_multiple" : ""), $assigns));
        
        
        // Redirection
        $this->redirect($backUrl);
    }
}