<?php

class UPDATE_WidgetService
{
    
    /**
     * Class instance
     *
     * @var UPDATE_WidgetService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return UPDATE_WidgetService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    const PLACE_DASHBOARD = 'dashboard';
    const PLACE_INDEX = 'index';
    const PLACE_PROFILE = 'profile';
    
    const SECTION_TOP = 'top';
    const SECTION_BOTTOM = 'bottom';
    const SECTION_LEFT = 'left';
    const SECTION_RIGHT = 'right';
    const SECTION_SIDEBAR = 'sidebar';

    /**
     * 
     * @var BOL_ComponentAdminService
     */
    private $service;
    
    protected function  __construct()
    {
       $this->service = BOL_ComponentAdminService::getInstance();
    }
    
    /**
     * 
     * @param string $componentClass
     * @param bool $isClonable
     * @return BOL_Component
     */
    public function addWidget( $widgetClass, $isClonable = false )
    {
        return $this->service->addWidget($widgetClass, $isClonable);
    }
    
    /**
     * 
     * @param BOL_Component $widget
     * @param string $place
     * @param string $uniqName
     * @return BOL_ComponentPlace
     */
    public function addWidgetToPlace( BOL_Component $widget, $place, $uniqName = null )
    {
        return $this->service->addWidgetToPlace($widget, $place, $uniqName);
    }
    
    /**
     * 
     * @param BOL_ComponentPlace $placeWidget
     * @param string $section
     * @param int $order
     */
    public function addWidgetToPosition(BOL_ComponentPlace $placeWidget, $section, $order = -1)
    {
        $this->service->addWidgetToPosition($placeWidget, $section, $order);
    }
    
    /**
     * 
     * @param $widgetClass
     */
    public function deleteWidget( $widgetClass )
    {
        $this->service->deleteWidget($widgetClass);
    }
    
    /**
     * 
     * @param $uniqName
     */
    public function deleteWidgetPlace( $uniqName )
    {
        $this->service->deleteWidgetPlace($uniqName);
    }
}