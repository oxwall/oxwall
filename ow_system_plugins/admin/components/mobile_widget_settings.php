<?php

class ADMIN_CMP_MobileWidgetSettings extends BASE_CMP_ComponentSettings
{
    public function __construct($uniqName, array $componentSettings = array(), array $defaultSettings = array(), $access = null) 
    {
        parent::__construct($uniqName, $componentSettings, $defaultSettings, $access);
                
        $this->markAsHidden("freeze");
        $this->markAsHidden("icon");
    }
}