<?php

class BASE_MCMP_WidgetMenu extends OW_MobileComponent
{

    public function __construct( $items )
    {
        parent::__construct();

        $this->assign('items', $items);
        OW::getDocument()->addOnloadScript('OWM.initWidgetMenu(' . json_encode($items) . ')');
    }
}