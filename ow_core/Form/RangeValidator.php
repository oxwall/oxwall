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
class RangeValidator extends Validator
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

        if ( !isset($value) )
        {
            throw new \InvalidArgumentException('Empty max value!');
        }

        $this->max = (int) $value;
    }

    public function setMinValue( $min )
    {
        $value = (int) $min;

        if ( !isset($value) )
        {
            throw new \InvalidArgumentException('Empty min value!');
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
        if ( $value === null )
        {
            return true;
        }

        if ( is_string($value) && mb_strlen(trim($value)) === 0 )
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

        if ( empty($valArray) || !isset($valArray[0]) || !isset($valArray[1]) )
        {
            return false;
        }

        if ( $valArray[0] > $valArray[1] )
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

        if ( isset($this->min) || isset($this->max) )
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
