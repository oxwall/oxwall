<?php

class ADMIN_CMP_DashboardWidgetPage extends BASE_CMP_DragAndDropFrontendPanel
{
    public function __construct( $placeName, array $componentList, $customizeMode, $componentTemplate = null )
    {
        if ( empty($componentTemplate) )
        {
            $componentTemplate = "drag_and_drop_page";
        }
        
        parent::__construct($placeName, $componentList, $customizeMode, $componentTemplate);
    }
}