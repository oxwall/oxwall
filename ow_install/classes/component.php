<?php

class INSTALL_Component extends INSTALL_Renderable
{
    public function __construct( $template = null )
    {
        parent::__construct();

        if( $template === null )
        {
            $template = OW::getAutoloader()->classToFilename(get_class($this), false);
        }

        $this->setTemplate(INSTALL_DIR_VIEW_CMP . $template . '.php');
    }
}
