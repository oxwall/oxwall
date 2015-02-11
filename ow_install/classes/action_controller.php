<?php

class INSTALL_ActionController extends INSTALL_Renderable
{
    private $title = 'OW Install';
    private $heading = 'Installation Process';
    
    private $navigation;
    
    public function __construct()
    {
        
    }
    
    public function setPageTitle( $title )
    {
        $this->title = $title;
    }
    
    public function getPageTitle()
    {
        return $this->title;
    }
    
    public function setPageHeading( $heading )
    {
        $this->heading = $heading;
    }
    
    public function getPageHeading()
    {
        return $this->heading;
    }

    /**
     * Makes permanent redirect to provided URL or URI.
     *
     * @param string $redirectTo
     */
    public function redirect( $redirectTo = null )
    {
        // if empty redirect location -> current URI is used
        if ( $redirectTo === null )
        {
            $redirectTo = OW::getRequest()->getRequestUri();
        }

        // if URI is provided need to add site home URL
        if ( !strstr($redirectTo, 'http://') && !strstr($redirectTo, 'https://') )
        {
            $redirectTo = OW::getRouter()->getBaseUrl() . UTIL_String::removeFirstAndLastSlashes($redirectTo);
        }

        UTIL_Url::redirect($redirectTo);
    }

    /**
     * Optional method for override.
     * Called before action is called.
     */
    public function init( $dispatchAttrs = null, $dbReady = null )
    {
    }
}