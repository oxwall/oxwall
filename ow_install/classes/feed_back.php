<?php

class INSTALL_FeedBack
{
    private static $classInstance;
    
    /**
     *
     * @return INSTALL_FeedBack
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $session;
    
    protected function __construct()
    {
        $this->session = OW::getSession()->get('OW-INSTALL-FEEDBACK');
        $this->session = empty($this->session) ? array(
            'message' => array(),
            'flag' => array()
        ) : $this->session;
    }
    
    public function __destruct()
    {
        OW::getSession()->set('OW-INSTALL-FEEDBACK', $this->session);
    }
    
    public function errorMessage( $msg )
    {
        $this->session['message'][] = array(
            'type' => 'error',
            'message' => $msg
        ); 
    }
    
    public function errorFlag( $flag )
    {
        $this->session['flag'][$flag] = true;
    }
    
    public function getFlag( $flag )
    {
        $out = !empty($this->session['flag'][$flag]);
        unset($this->session['flag'][$flag]);
        
        return $out;
    }
    
    public function getMessages()
    {
        $msgs = $this->session['message'];
        $this->session['message'] = array();
        
        return $msgs;
    }
}