<?php

OW::getRouter()->addRoute(new OW_Route('requirements', 'install', 'INSTALL_CTRL_Install', 'requirements'));
OW::getRouter()->addRoute(new OW_Route('site', 'install/site', 'INSTALL_CTRL_Install', 'site'));
OW::getRouter()->addRoute(new OW_Route('db', 'install/data-base', 'INSTALL_CTRL_Install', 'db'));

OW::getRouter()->addRoute(new OW_Route('install', 'install/installation', 'INSTALL_CTRL_Install', 'install'));
OW::getRouter()->addRoute(new OW_Route('install-action', 'install/installation/:action', 'INSTALL_CTRL_Install', 'install'));

OW::getRouter()->addRoute(new OW_Route('plugins', 'install/plugins', 'INSTALL_CTRL_Install', 'plugins'));
OW::getRouter()->addRoute(new OW_Route('finish', 'install/security', 'INSTALL_CTRL_Install', 'finish'));

function install_tpl_feedback_flag($flag, $class = 'error')
{
    if ( INSTALL::getFeedback()->getFlag($flag) )
    {
        return $class;
    }
    
    return '';
}

function install_tpl_feedback()
{
    $feedBack = new INSTALL_CMP_FeedBack();
    
    return $feedBack->render();
}
