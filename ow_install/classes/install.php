<?php

class INSTALL
{
    /**
     * 
     * @return INSTALL_Storage
     */
    public static function getStorage()
    {
        return INSTALL_Storage::getInstance();    
    }
    
    /**
     * 
     * @return INSTALL_FeedBack
     */
    public static function getFeedback()
    {
        return INSTALL_FeedBack::getInstance();    
    }
    
    /**
     * 
     * @return INSTALL_CMP_Steps
     */
    public static function getStepIndicator()
    {
        static $stepIndicator;
        
        if ( empty($stepIndicator) )
        {
            $stepIndicator = new INSTALL_CMP_Steps();
        }
        
        return $stepIndicator;    
    }
    
    /**
     * 
     * @return INSTALL_ViewRenderer
     */
    public static function getViewRenderer()
    {
        return INSTALL_ViewRenderer::getInstance();
    }
}