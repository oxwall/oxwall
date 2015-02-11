<?php

class BASE_CMP_ConsoleDropdownClick extends BASE_CMP_ConsoleDropdown
{
    public function __construct($label, $key = null)
    {
        parent::__construct($label, $key);

        $template = OW::getPluginManager()->getPlugin('BASE')->getCmpViewDir() . 'console_dropdown_click.html';
        $this->setTemplate($template);

        $this->addClass('ow_console_dropdown_click');
    }

    protected function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('OW.Console.addItem(new OW_ConsoleDropdownClick({$uniqId}, {$contentIniqId}), {$key});', array(
            'uniqId' => $this->consoleItem->getUniqId(),
            'key' => $this->getKey(),
            'contentIniqId' => $this->consoleItem->getContentUniqId()
        ));

        OW::getDocument()->addOnloadScript($js);

        return $this->consoleItem->getUniqId();
    }
}