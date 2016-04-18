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

namespace Oxwall\Core\Form;

use Oxwall\Core\OW;

/**
 * UrlValidator validates Url.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.8.3
 */
class FloatValidator extends Validator
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
        $this->pattern = \Oxwall\Utilities\Validator::FLOAT_PATTERN;

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
            throw new \InvalidArgumentException('Empty max value!');
        }

        $this->max = (float) $value;
    }

    public function setMinValue( $min )
    {
        $value = (float) $min;

        if ( empty($value) )
        {
            throw new \InvalidArgumentException('Empty min value!');
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

        if ( !\Oxwall\Utilities\Validator::isFloatValid($value) )
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
