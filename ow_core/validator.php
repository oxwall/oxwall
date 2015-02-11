<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * Base validator class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
abstract class OW_Validator
{
    /**
     * Error message.
     *
     * @var string
     */
    protected $errorMessage;

    /**
     * Checks if provided value is valid.
     *
     * @param mixed $value
     * @return boolean
     */
    abstract function isValid( $value );

    /**
     * Returns validator error message.
     *
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     * Sets validator error message.
     *
     * @param string $errorMessage
     * @return OW_Validator
     * @throws InvalidArgumentException
     */
    public function setErrorMessage( $errorMessage )
    {
        if ( $errorMessage === null || mb_strlen(trim($errorMessage)) === 0 )
        {
            //throw new InvalidArgumentException('Invalid error message!');
            return;
        }

        $this->errorMessage = trim($errorMessage);
    }

    /**
     * Returns validator js object code.
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
            validate : function( value ){}
        }";
    }
}

/**
 * Required validator.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class RequiredValidator extends OW_Validator
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_required_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Required Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        if ( is_array($value) )
        {
            if ( sizeof($value) === 0 )
            {
                return false;
            }
        }
        else if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return false;
        }

        return true;
    }

    /**
     * @see OW_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
                if(  $.isArray(value) ){ if(value.length == 0  ) throw " . json_encode($this->getError()) . "; return;}
                else if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}


/**
 * StringValidator validates String.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class StringValidator extends OW_Validator
{
    /**
     * String min length
     *
     * @var int
     */
    private $min;
    /**
     * String max length
     *
     * @var int
     */
    private $max;

    /**
     * Class constructor.
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $min = null, $max = null )
    {
        if ( isset($min) )
        {
            $this->setMinLength($min);
        }

        if ( isset($max) )
        {
            $this->setMaxLength($max);
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_string_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'String Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }

    /**
     * Sets string max length
     *
     * @param int $max
     */
    public function setMaxLength( $max )
    {
        if ( !isset($max) )
        {
            throw new InvalidArgumentException('Empty max length!');
        }

        $this->max = (int) $max;
    }

    /**
     * Sets string min length
     *
     * @param int $min
     */
    public function setMinLength( $min )
    {
        if ( !isset($min) )
        {
            throw new InvalidArgumentException('Empty min length!');
        }

        $this->min = (int) $min;
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $trimValue = trim($value);

        if ( isset($this->min) && mb_strlen($trimValue) < (int) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && mb_strlen($trimValue) > (int) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( $.trim(value).length < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( $.trim(value).length > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

/**
 * RegExpValidator validates value by RegExp.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class RegExpValidator extends OW_Validator
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * Class constructor.
     *
     * @param string pattern
     */
    public function __construct( $pattern = null )
    {
        if ( isset($pattern) )
        {
            $this->setPattern($pattern);
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_regexp_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Regexp Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }

    /**
     * Sets pattern
     *
     * @param string $pattern
     */
    public function setPattern( $pattern )
    {
        if ( !isset($pattern) || mb_strlen(trim($pattern)) === 0 )
        {
            throw new InvalidArgumentException('Empty pattern!');
        }

        $this->pattern = trim($pattern);
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $trimValue = trim($value);

        if ( !preg_match($this->pattern, $trimValue) )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
                var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        	}}
        ";

        return $js;
    }
}

/**
 * EmailValidator validates Email.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class EmailValidator extends RegExpValidator
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::EMAIL_PATTERN);

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_email_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Email Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}


/**
 * UrlValidator validates Url.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class UrlValidator extends RegExpValidator
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::URL_PATTERN);

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_url_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Url Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}


/**
 * AlphaNumericValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class AlphaNumericValidator extends RegExpValidator
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::ALPHA_NUMERIC_PATTERN);

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_url_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Alphanumeric Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}


/**
 * IntValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class IntValidator extends OW_Validator
{
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;
    /**
     * @var string
     */
    private $pattern;

    /**
     * Class constructor
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $min = null, $max = null )
    {
        $this->pattern = UTIL_Validator::INT_PATTERN;

        if ( isset($min) )
        {
            $this->min = (int) $min;
        }

        if ( isset($max) )
        {
            $this->max = (int) $max;
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_int_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Int Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxValue( $max )
    {
        $value = (int) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (int) $value;
    }

    public function setMinValue( $min )
    {
        $value = (int) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (int) $value;
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $intValue = (int) $value;

        if ( !UTIL_Validator::isIntValid($value) )
        {
            return false;
        }

        if ( isset($this->min) && $intValue < (int) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && $intValue > (int) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        		
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
            	var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( parseInt(value) < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( parseInt(value) > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

/**
 * Oxwall: Open Source Community Software
 * @copyright Skalfa LLC Copyright (C) 2009. All rights reserved.
 * @license CPAL 1.0 License - http://www.oxwall.org/license
 */

/**
 * FloatValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class FloatValidator extends OW_Validator
{
    /**
     * @var float
     */
    private $min;
    /**
     * @var float
     */
    private $max;
    /**
     * @var string
     */
    private $pattern;

    /**

      /**
     * Class constructor
     *
     * @param float $min
     * @param float $max
     */
    public function __construct( $min = null, $max = null )
    {
        $this->pattern = UTIL_Validator::FLOAT_PATTERN;

        if ( isset($min) )
        {
            $this->min = (float) $min;
        }

        if ( isset($max) )
        {
            $this->max = (float) $max;
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_float_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Float Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxValue( $max )
    {
        $value = (float) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (float) $value;
    }

    public function setMinValue( $min )
    {
        $value = (float) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (float) $value;
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $floatValue = (float) $value;

        if ( !UTIL_Validator::isFloatValid($value) )
        {
            return false;
        }

        if ( isset($this->min) && $floatValue < (float) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && $floatValue > (float) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        		
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
                var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( parseFloat(value) < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( parseFloat(value) > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

/**
 * DateValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class DateValidator extends OW_Validator
{
    /**
     * @var int
     */
    private $minYear;
    /**
     * @var int
     */
    private $maxYear;
    /**
     * @var string
     */
    private $dateFormat = UTIL_DateTime::DEFAULT_DATE_FORMAT;

    /**
     * Class constructor
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $minYear = null, $maxYear = null )
    {
        if ( isset($minYear) )
        {
            $this->setMinYear($minYear);
        }

        if ( isset($maxYear) )
        {
            $this->setMaxYear($maxYear);
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_date_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Date Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxYear( $maxYear )
    {
        $value = (int) $maxYear;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Incorrect max year value!');
        }

        $this->maxYear = (int) $value;
    }

    public function setDateFormat( $dateFormat )
    {
        $format = trim($dateFormat);

        if ( empty($format) )
        {
            throw new InvalidArgumentException('Incorrect argument `$format`!');
        }

        $this->dateFormat = trim($format);
    }

    public function setMinYear( $minYear )
    {
        $value = (int) $minYear;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Incorrect min year value!');
        }

        $this->minYear = (int) $value;
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        $date = UTIL_DateTime::parseDate($value, $this->dateFormat);

        if ( $date === null )
        {
            return false;
        }

        if ( !UTIL_Validator::isDateValid($date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_YEAR]) )
        {
            return false;
        }

        if ( !empty($this->maxYear) && $date[UTIL_DateTime::PARSE_DATE_YEAR] > $this->maxYear )
        {
            return false;
        }

        if ( !empty($this->minYear) && $date[UTIL_DateTime::PARSE_DATE_YEAR] < $this->minYear )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}

/**
 * DateValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class CaptchaValidator extends OW_Validator
{
    protected $jsObjectName = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_captcha_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Captcha Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        return UTIL_Validator::isCaptchaValid($value);
    }

    public function getJsValidator()
    {
        if ( empty($this->jsObjectName) )
        {
            return "{
                    validate : function( value ){
            },
                    getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
            }";
        }
        else
        {
            return "{
                 
                    validate : function( value )
                    {
                        if( !window." . $this->jsObjectName . ".validateCaptcha() )
                        {
                            throw " . json_encode($this->getError()) . ";
                        }
                    },
                    
                    getErrorMessage : function()
                    {
                        return " . json_encode($this->getError()) . ";
                    }
            }";
        }
    }
}

class RangeValidator extends OW_Validator
{
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;
    /**
     * Class constructor.
     *
     */
    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('base', 'form_validator_range_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Range Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }
    
    public function setMaxValue( $max )
    {
        $value = (int) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (int) $value;
    }

    public function setMinValue( $min )
    {
        $value = (int) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (int) $value;
    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // doesn't check empty values
        if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }
        
        if ( is_array($value) )
        {
            $value = implode('-', $value);
        }
        
        return $this->checkValue($value);
    }

    public function checkValue( $value )
    {
        $value = trim($value);
        
        if ( empty($value) )
        {
            return false;
        }
        
        $valArray = explode('-', $value);

        if ( empty($valArray) || empty($valArray[0]) || empty($valArray[1]) )
        {
            return false;
        }

        if ($valArray[0] > $valArray[1])
        {
            return false;
        }
        
        if ( isset($this->min) && ($valArray[0] < (int) $this->min || $valArray[1] < (int) $this->min) )
        {
            return false;
        }

        if ( isset($this->max) && ($valArray[0] > (int) $this->max || $valArray[1] > (int) $this->max) )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
        ";

        if (isset($this->min) || isset($this->max))
        {
            if ( isset($this->min) )
            {
                $js .= "
                if( $.trim(value) < " . $this->min . " )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
            }

            if ( isset($this->max) )
            {
                $js .= "
                if( $.trim(value) > " . $this->max . " )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
            }
        }
        else
        {
            $js .= "if( $.trim(value).length == 0 )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
        }

        $js .= "}
    		}";

        return $js;
    }
}