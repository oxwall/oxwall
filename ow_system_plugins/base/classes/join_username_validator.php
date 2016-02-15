<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 2/10/16
 * Time: 9:14 AM
 */

class BASE_CLASS_JoinUsernameValidator extends OW_Validator
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
        if ( !UTIL_Validator::isUserNameValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_not_valid'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isExistUserName($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_already_exist'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isRestrictedUsername($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_restricted'));
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
                    // window.join.validateUsername(false);
                    if( window.join.errors['username']['error'] !== undefined )
                    {
                        throw window.join.errors['username']['error'];
                    }
                },
                getErrorMessage : function(){
                    if( window.join.errors['username']['error'] !== undefined ){ return window.join.errors['username']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}

