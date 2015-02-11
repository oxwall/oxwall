<?php

class BASE_MCMP_WidgetPanel extends BASE_CMP_DragAndDropEntityPanel 
{
    public function __construct($placeName, $entityId, array $componentList, $template = "widget_panel" ) 
    {
        parent::__construct($placeName, $entityId, $componentList, false, null);
        
        if ( $template !== null )
        {
            $plugin = OW::getPluginManager()->getPlugin("base");
            $this->setTemplate($plugin->getMobileCmpViewDir() . $template . '.html');
        }
        
        $this->setItemClassName("BASE_MCMP_WidgetPanelItem");
    }
}