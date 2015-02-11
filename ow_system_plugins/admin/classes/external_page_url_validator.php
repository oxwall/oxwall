<?php

class ADMIN_CLASS_ExternalPageUrlValidator extends UrlValidator
{

    public function __construct()
    {
        parent::__construct();
    }

    /*public function isValid( $value )
    {
        if ( !empty($_POST['url']) )
        {
            return (parent::isValid($_POST['url']) || substr($_POST['url'], 0, 1) == '/');
        }

        return false;
    }*/

    public function getJsValidator()
    {

        return "{
        	validate : function( value ){
                if( $('#address2').attr('checked') &&  !value ){
                	throw " . json_encode(OW::getLanguage()->text('base', 'form_validator_required_error_message')) . ";
    			}

                return true;
        },
        	getErrorMessage : function(){ return " . json_encode(OW::getLanguage()->text('base', 'form_validator_required_error_message')) . " }
        }";
    }

}