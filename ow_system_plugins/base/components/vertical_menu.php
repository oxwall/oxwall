<?php

class BASE_CMP_VerticalMenu extends BASE_CMP_Menu
{
    public function __construct($menuItems = array()) 
    {
        parent::__construct($menuItems);
        
        $this->setTemplate(OW::getPluginManager()
                ->getPlugin('base')->getCmpViewDir() . 'vertical_menu.html');
    }
    
    protected function getItemViewData(BASE_MenuItem $menuItem) 
    {
        $data = parent::getItemViewData($menuItem);

        if ( $menuItem instanceof BASE_VerticalMenuItem )
        {
            $data["number"] = $menuItem->getNumber();
        }
        
        return $data;
    }
}

class BASE_VerticalMenuItem extends BASE_MenuItem
{
    protected $number;
    
    public function setNumber( $number )
    {
        $this->number = $number;
    }
    
    public function getNumber()
    {
        return $this->number;
    }
}
