<?php

class BOL_MobileWidgetService extends BOL_ComponentAdminService
{
    const PLACE_MOBILE_INDEX = "mobile.index";
    const PLACE_MOBILE_DASHBOARD = "mobile.dashboard";
    const PLACE_MOBILE_PROFILE = "mobile.profile";
    
    const SECTION_MOBILE_MAIN = "mobile.main";
    
    /**
     * @var BOL_MobileWidgetService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MobileWidgetService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
}
