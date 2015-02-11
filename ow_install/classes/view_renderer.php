<?php

class INSTALL_ViewRenderer
{

    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return INSTALL_ViewRenderer
     */
    public static function getInstance()
    {
        if( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $assigns = array();

    /**
     * Constructor.
     */
    private function __construct()
    {

    }

    /**
     * Assigns list of values to template vars.
     *
     * @param array $vars
     */
    public function assignVars( $vars )
    {
        foreach( $vars as $key => $value )
        {
            $this->assigns[$key] = $value;
        }
    }

    /**
     * Assigns value to template var.
     *
     * @param string $key
     * @param mixed $value
     */
    public function assignVar( $key, $value )
    {
        $this->assigns[$key] = $value;
    }

    /**
     * Renders template using assigned vars and returns generated markup.
     *
     * @param string $template
     * @return string
     */
    public function render( $template )
    {
        
        if( empty($template) )
        {
            throw new LogicException('No template was provided for render!');
        }

        $renderedMarkup = '';

        $_assign_vars = $this->assigns;

        ob_start();
        include $template;
        $renderedMarkup = ob_get_contents();
        ob_end_clean();

        return $renderedMarkup;
    }

    /**
     * Returns list of assigned var values.
     *
     * @return array
     */
    public function getAllAssignedVars()
    {
        return $this->assigns;
    }

    /**
     * Deletes all assigned template vars.
     */
    public function clearAssignedVars()
    {
        $this->assigns = array();
    }
}