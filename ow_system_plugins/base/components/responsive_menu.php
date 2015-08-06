<?php

class BASE_CMP_ResponsiveMenu extends BASE_CMP_Menu
{
    protected $uniqId;

    public function __construct( $menuItems = array() ) 
    {
        parent::__construct($menuItems);
        
        $this->uniqId = uniqid("rm-");
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'responsive_menu.html');
    }

    public function initStatic()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->newObject("menu", "OW.ResponsiveMenu", array($this->uniqId));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function onBeforeRender() 
    {
        $this->initStatic();
        
        $this->assign("uniqId", $this->uniqId);
        
        parent::onBeforeRender();
    }
}