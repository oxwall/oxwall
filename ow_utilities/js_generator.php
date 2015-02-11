<?php

class UTIL_JsGenerator
{
    /**
     * Javascript operations queue.
     *
     * @var array
     */
    private $operations = array ();

    /**
     * Constructor.
     */
    public function __construct()
    {

    }

    /**
     *
     * @return UTIL_JsGenerator
     */
    public static function newInstance()
    {
        return new self;
    }

    protected function getJsVarName( $var )
    {
        if ( is_string($var) )
        {
            return $var;
        }

        if ( $this->isProperty($var) )
        {
            return implode('.', $var);
        }

   	if ( count($var) == 1 )
        {
            return $var[0];
        }

    }

    private function isProperty( $variable )
    {
    	return ( is_array($variable) && ( count($variable) > 1 ) );
    }

    /**
     * Add Javascript variable Declaration
     *
     * @param string|array $variable
     *
     * @param string $value
     *
     * @return UTIL_JsGenerator
     */
    public function newVariable ( $variable, $value = null )
    {
        $end = empty($value) ? '' : ' = ' . json_encode($value) . '';

        $var = $this->isProperty($variable) ? '' : 'var ';

        $this->operations[] = $var . $this->getJsVarName($variable) . $end;

        return $this;
    }

    /**
     * Add a variable definition.
     *
     * @param string|array $variable
     * @param mixed $value
     *
     * @return UTIL_JsGenerator
     */
    public function setVariable ( $variable, $value )
    {
        $this->operations[] = $this->getJsVarName($variable) . ' = ' . json_encode($value);

        return $this;
    }

    /**
     * Equate two variables
     *
     * @param string|array $variableTo
     * @param string|array $variableFrom
     *
     * @return UTIL_JsGenerator
     */
    public function equateVarables($variableTo, $variableFrom)
    {
    	$this->operations[] = $this->getJsVarName($variableTo) . ' = ' . $this->getJsVarName($variableFrom);

    	return $this;
    }

    /**
     * Add a function or method call.
     *
     * @param string|array $fnc
     * @param array $args
     * @param string|array $resultTo
     *
     * @return UTIL_JsGenerator
     */
    public function callFunction( $fnc, array $args = array(), $resultTo = null )
    {
        $jsonArgs = array_map('json_encode', $args);
        $jsFnc = $this->getJsVarName($fnc);

    	$jsResultTo = empty($resultTo) ? '' : $this->getJsVarName($resultTo) . " = ";

        $this->operations[] = $jsResultTo . "$jsFnc(" . implode(', ', $jsonArgs) . ')';

        return $this;
    }


    /**
     * Add Javascript function declaration
     *
     * @param string $fncContent - function content without {}
     * @param array $fncArgs - indexed array of parameter names
     * @param string|array $resultTo - name of variable
     *
     * @return UTIL_JsGenerator
     */
    public function newFunction ( $fncContent, array $fncArgs = array(), $resultTo = null )
    {

        $argsDef = implode(',', $fncArgs);

        $resultVariable = false;
        if ( !empty($resultTo) )
        {
            $resultVariable = $this->getJsVarName($resultTo);
        }

        $var = '';

        if ( is_string($resultTo) || count($resultTo) == 1 )
        {
            $var = 'var ';
        }

        $this->operations[] = ( $resultVariable ?  $var . "$resultVariable =" : '' ) . "function($argsDef) { $fncContent }";

        return $this;
    }

    /**
     * Add Javascript object constraction
     *
     * @param string|array $objectName
     * @param string $className
     * @param array $args
     *
     * @return UTIL_JsGenerator
     */
    public function newObject ( $objectName, $constructorName, array $args = array() )
    {
        $jsonArgs = array_map('json_encode', $args);
        $jsArgs = implode(',', $jsonArgs);

        $var = '';
        
        if ( is_string($objectName) || count($objectName) == 1 )
        {
            $var = 'var ';
        }
        $jsObjectName = $this->getJsVarName($objectName);
        $this->operations[] = $var . "$jsObjectName = new $constructorName($jsArgs)";

        return $this;
    }


    /**
     * Add Javascript code
     *
     * @param string $code
     *
     * @return UTIL_JsGenerator
     */
    public function addScript($code, $assignVars = array())
    {
        $code = self::composeJsString($code, $assignVars);

    	$code = rtrim($code);
    	if (substr($code, -1) == ";")
    	{
    		$code = substr($code, 0, -1);
    	}
    	$this->operations[] = $code;

    	return $this;
    }

    public static function composeJsString($code, $assignVars = array())
    {
        $jsonAssignVars = array_map('json_encode', $assignVars);
        $vars = array();
        foreach ($jsonAssignVars as $key => $value)
        {
            $vars['{$' . $key .'}'] = $value;
        }

        return str_replace(array_keys($vars), array_values($vars), $code);
    }

    /**
     * Returns the Javascript code of called operations.
     *
     * @return string
     */
    public function generateJs()
    {
        $jsCode = '';

        foreach ( $this->operations as $operation )
        {
            $jsCode .= $operation . ";\n";
        }

        return $jsCode;
    }

    /**
     *
     * @param string $method
     * @param string $selector
     * @param array $args
     * @param string $resultTo
     *
     * @return UTIL_JsGenerator
     */
    public function jQueryCall( $method, $selector = null, array $args = array(), $resultTo = null )
    {
        $jsonArgs = array_map('json_encode', $args);
        $operation = ( empty($selector) ? '$' : '$("' . $selector . '")' ) . '.' . $method;

        $jsResultTo = empty($resultTo) ? '' : "$resultTo = ";

        $this->operations[] = $jsResultTo . "$operation(" . implode(', ', $jsonArgs) . ')';

        return $this;
    }

    /**
     *
     * @param string $selector
     * @param string $event
     * @param string $callbackContent
     * @param array $args
     * @return UTIL_JsGenerator
     */
    public function jQueryEvent( $selector, $event, $callbackContent, array $args = array(), $data = array() )
    {
        $eventParams = implode(', ', $args);
        $jsonData = empty($data) ? '' : ' ' . json_encode($data) . ',';

        $operation = <<<EOT
$('$selector').on('$event',$jsonData function($eventParams) {
     $callbackContent
})
EOT;
        $this->operations[] = $operation;

        return $this;
    }

    public function __toString()
    {
    	return $this->generateJs();
    }

    /**
     *
     * @return UTIL_JsGenerator
     */
    public function clear()
    {
        $this->operations = array();

        return $this;
    }

}
