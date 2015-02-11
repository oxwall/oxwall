<?php

class ADMIN_CMP_MobileWidgetPanel extends ADMIN_CMP_DragAndDropAdminPanel 
{
    public function __construct( $placeName, array $componentList, $template = 'drag_and_drop_panel'  ) 
    {
        parent::__construct($placeName, $componentList, $template);
        
        $this->setSettingsClassName("ADMIN_CMP_MobileWidgetSettings");
    }
}