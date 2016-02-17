<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 2/10/16
 * Time: 9:15 AM
 */

class BASE_CLASS_JoinEmailValidator extends OW_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();
        if ( !UTIL_Validator::isEmailValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_not_valid'));
            
            return false;
        }
        else if ( BOL_UserService::getInstance()->isExistEmail($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));
            
            return false;
        }

        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value )
                {
                    // window.join.validateEmail(false);
                    if( window.join.errors['email']['error'] !== undefined )
                    {
                        throw window.join.errors['email']['error'];
                    }
                },
        	getErrorMessage : function(){
                    if( window.join.errors['email']['error'] !== undefined ){ return window.join.errors['email']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                 }
        }";
    }
}
