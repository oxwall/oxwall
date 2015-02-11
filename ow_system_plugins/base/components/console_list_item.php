<?php

class BASE_CMP_ConsoleListItem extends OW_Renderable
{
    protected $key, $content, $class = array();

    public function __construct()
    {
        parent::__construct();

        $this->key = uniqid('cli_');

        $template = OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'console_list_item.html';
        $this->setTemplate($template);
    }

    public function setContent( $content )
    {
        $this->content = $content;
    }

    public function setKey( $key )
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function addClass( $class )
    {
        $this->class[$class] = $class;
    }

    public function getClass()
    {
        return implode(' ', $this->class);
    }

    public function render()
    {
        $this->assign('item', array
        (
            'key' => $this->getKey(),
            'class' => $this->getClass(),
            'content' => $this->content
        ));

        return parent::render();
    }
}