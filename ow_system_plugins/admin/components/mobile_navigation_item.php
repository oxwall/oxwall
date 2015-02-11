<?php

class ADMIN_CMP_MobileNavigationItem extends OW_Component
{
    public function __construct( $options ) 
    {
        parent::__construct();
        
        $this->assign("item", $options);
    }
}
