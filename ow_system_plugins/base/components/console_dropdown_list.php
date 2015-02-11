<?php

class BASE_CMP_ConsoleDropdownList extends BASE_CMP_ConsoleDropdownClick
{
    protected $counter = array(
        'number' => 0,
        'active' => false
    );

    /**
     *
     * @var BASE_CMP_ConsoleList
     */
    protected $list;

    public function __construct($label, $key)
    {
        parent::__construct($label, $key);

        $template = OW::getPluginManager()->getPlugin('BASE')->getCmpViewDir() . 'console_dropdown_list.html';
        $this->setTemplate($template);

        $this->list = new BASE_CMP_ConsoleList($this->getKey());
    }

    protected function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('OW.Console.addItem(new OW_ConsoleDropdownList({$uniqId}, {$contentIniqId}), {$key});', array(
            'uniqId' => $this->consoleItem->getUniqId(),
            'key' => $this->getKey(),
            'contentIniqId' => $this->consoleItem->getContentUniqId()
        ));
        OW::getDocument()->addOnloadScript($js);

        $this->list->initJs();

        return $this->consoleItem->getUniqId();
    }

    public function setViewAll( $label, $url )
    {
        $this->list->setViewAll($label, $url);
    }

    public function setCounter( $number, $active = true )
    {
        $this->counter['number'] = $number;
        $this->counter['active'] = $active;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('counter', $this->counter);
        $this->setContent($this->list->render());
    }
}