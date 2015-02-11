<?php

class BASE_CMP_ConsoleDropdownMenu extends OW_Renderable
{
    protected $items = array();

    /**
     *
     * @var BASE_CMP_ConsoleItem
     */
    protected $consoleItem;

    public function __construct( $label )
    {
        parent::__construct();

        $template = OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'console_dropdown_menu.html';
        $this->setTemplate($template);

        $this->consoleItem = new BASE_CMP_ConsoleDropdownHover($label);
    }

    public function setIsHidden( $hidden = true )
    {
        $this->consoleItem->setIsHidden($hidden);
    }

    public function getIsHidden()
    {
        return $this->consoleItem->getIsHidden();
    }

    public function addClass( $class )
    {
        $this->consoleItem->addClass($class);
    }

    public function addItem( $section, $item )
    {
        $this->items[$section][] = $item;
    }

    public function setUrl( $url )
    {
        $this->consoleItem->setUrl($url);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('items', $this->items);
    }

    public function render()
    {
        $this->consoleItem->setContent(parent::render());

        return $this->consoleItem->render();
    }
}