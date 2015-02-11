<?php

abstract class BASE_CMP_ConsoleDropdown extends OW_Renderable
{
    /**
     *
     * @var BASE_CMP_ConsoleItem
     */
    protected $consoleItem;

    protected $key;

    public function __construct( $label, $key = null )
    {
        parent::__construct();

        $this->consoleItem = new BASE_CMP_ConsoleItem();
        $this->assign('label', $label);

        $this->key = empty($key) ? $this->consoleItem->getUniqId() : $key;

        $this->addClass('ow_console_dropdown');
    }

    abstract protected function initJs();

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

    public function getKey()
    {
        return $this->key;
    }

    public function setContent( $content )
    {
        $this->consoleItem->setContent($content);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->initJs();
    }

    public function render()
    {
        $this->consoleItem->setControl(parent::render());

        return $this->consoleItem->render();
    }
}