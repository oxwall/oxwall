<?php

class BASE_CMP_ConsoleButton extends OW_Component
{
    /**
     *
     * @var BASE_CMP_ConsoleItem
     */
    protected $consoleItem;
    protected $key;

    public function __construct( $label, $url = 'javascript://', $onClick = '', $key = null )
    {
        parent::__construct();

        $this->assign('label', $label);
        $this->assign('href', $url);
        $this->assign('onClick', $onClick);

        $this->consoleItem = new BASE_CMP_ConsoleItem();
        $this->key = empty($key) ? $this->consoleItem->getUniqId() : $key;

        $this->addClass('ow_console_button');
    }

    public function getKey()
    {
        return $this->key;
    }

    public function addClass( $class )
    {
        $this->consoleItem->addClass($class);
    }

    public function render()
    {
        $this->consoleItem->setControl(parent::render());

        return $this->consoleItem->render();
    }
}