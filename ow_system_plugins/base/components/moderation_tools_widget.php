<?php

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ModerationToolsWidget extends BASE_CLASS_Widget
{
    const EVENT_COLLECT_CONTENTS = "base.moderation_panel_widget_collect_contents";
    
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        
        if ( !BOL_AuthorizationService::getInstance()->isModerator() && !OW::getUser()->isAdmin() )
        {
            $this->setVisible(false);
            
            return;
        }
        
        $uniqId = uniqid("mp-");
        $this->assign("uniqId", $uniqId);
        
        $event = new BASE_CLASS_EventCollector(self::EVENT_COLLECT_CONTENTS);
        OW::getEventManager()->trigger($event);

        $tplContents = array();
        $activeTab = null;
        foreach ( $event->getData() as $content )
        {
            $tplContent = array_merge(array(
                "name" => null,
                "content" => null,
                "active" => false
            ), $content);
            
            $activeTab = $tplContent["active"] ? $tplContent["name"] : $activeTab;
            $tplContents[$tplContent["name"]] = $tplContent;
        }
        
        if ( empty($tplContents) )
        {
            $this->setVisible(false);
            
            return;
        }
        
        if ( $activeTab === null )
        {
            $firstTab = reset($tplContents);
            $activeTab = $firstTab["name"];
            $tplContents[$activeTab]["active"] = true;
        }
        
        $this->assign("items", $tplContents);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'moderation_panel'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_EDIT
        );
    }
}