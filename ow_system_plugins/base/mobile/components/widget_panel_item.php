<?php

class BASE_MCMP_WidgetPanelItem extends BASE_CMP_DragAndDropItem 
{
    public function __construct($componentUniqName, $isClone = false, $template = null, $sharedData = array())
    {
        parent::__construct($componentUniqName, $isClone, null, $sharedData);
        
        $template = empty($template) ? "widget_panel_item" : $template;
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . $template . '.html');
    }
    
    protected function getBoxSettingList(array $settingList, array $runTimeSettingList) 
    {
        $box = parent::getBoxSettingList($settingList, $runTimeSettingList);
        
        $box["title"] = !$box["show_title"] ? "" : $box["title"];
        $box["capEnabled"] = !empty($box["title"]) || !empty($box["capContent"]);
        
        return $box;
    }
}