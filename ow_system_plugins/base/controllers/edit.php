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
 * Edit user details
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Edit extends OW_ActionController
{
    const EDIT_SYNCHRONIZE_HOOK = 'edit_synchronize_hook';

    private $questionService;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        $this->userService = BOL_UserService::getInstance();
    }

    public function index( $params )
    {
        $adminMode = false;
        $oneAccountType = false;
        $viewerId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthenticated() || $viewerId === null )
        {
            throw new AuthenticateException();
        }

        if ( !empty($params['userId']) && $params['userId'] != $viewerId )
        {
            
            if ( OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base') )
            {
                $adminMode = true;
                $userId = (int) $params['userId'];
                $user = BOL_UserService::getInstance()->findUserById($userId);

                if ( empty($user) || BOL_AuthorizationService::getInstance()->isSuperModerator($userId) )
                {
                    throw new Redirect404Exception();
                }

                $editUserId = $userId;
            }
            else
            {
                throw new Redirect403Exception();
            }
        }
        else
        {
            $editUserId = $viewerId;

            $changePassword = new BASE_CMP_ChangePassword();
            $this->addComponent("changePassword", $changePassword);

            $contentMenu = new BASE_CMP_DashboardContentMenu();
            $contentMenu->getElement('profile_edit')->setActive(true);

            $this->addComponent('contentMenu', $contentMenu);

            $user = OW::getUser()->getUserObject(); //BOL_UserService::getInstance()->findUserById($editUserId);
        }
        
        $accountType = $user->accountType;
        
        // dispaly account type
        if ( OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base') )
        {
            $accountType = !empty( $_GET['accountType'] ) ? $_GET['accountType'] : $user->accountType;
            
            // get available account types from DB
            $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

            $accounts = array();

            if ( count($accountTypes) > 1 )
            {                
                /* @var $value BOL_QuestionAccount */
                foreach ( $accountTypes as $key => $value )
                {
                    $accounts[$value->name] = OW::getLanguage()->text('base', 'questions_account_type_' . $value->name);
                }

                if ( !in_array($accountType, array_keys($accounts) ) )
                {
                    if ( in_array($user->accountType, array_keys($accounts) ) )
                    {
                        $accountType = $user->accountType;
                    }
                    else 
                    {
                        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
                    }
                }
                
                $editAccountType = new Selectbox('accountType');
                $editAccountType->setId('accountType');
                $editAccountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
                $editAccountType->setRequired();
                $editAccountType->setOptions($accounts);
                $editAccountType->setHasInvitation(false);
            }
            else 
            {
                $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
            }
        }
        
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'edit_index'));
        $this->setPageHeadingIconClass('ow_ic_user');
        // -- Edit form --

        $editForm = new EditQuestionForm('editForm', $editUserId);
        $editForm->setId('editForm');
        
        $this->assign('displayAccountType', false);
        
        // dispaly account type
        if ( !empty($editAccountType) )
        {
            $editAccountType->setValue($accountType);
            $editForm->addElement($editAccountType);
            
            OW::getDocument()->addOnloadScript( " $('#accountType').change(function() { 
                
                var form = $(\"<form method='get'><input type='text' name='accountType' value='\" + $(this).val() + \"' /></form>\");
                $('body').append(form);
                $(form).submit();

            }  ); " );
            
            $this->assign('displayAccountType', true);
        }

        // add avatar field
        $editAvatar = OW::getClassInstance("BASE_CLASS_AvatarField", 'avatar', false);
        $editAvatar->setLabel(OW::getLanguage()->text('base', 'questions_question_user_photo_label'));
        $editAvatar->setValue(BOL_AvatarService::getInstance()->getAvatarUrl($user->id, 1, null, true, false));
        $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');

        // add the required avatar validator
        if ( $displayPhotoUpload == BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD ) 
        {
            $avatarValidator = OW::getClassInstance("BASE_CLASS_AvatarFieldValidator", true);
            $editAvatar->addValidator($avatarValidator);
        }

        $editForm->addElement($editAvatar);

        $editSubmit = new Submit('editSubmit');
        $editSubmit->addAttribute('class', 'ow_button ow_ic_save');

        $editSubmit->setValue($language->text('base', 'edit_button'));

        $editForm->addElement($editSubmit);

        $questions = $this->questionService->findEditQuestionsForAccountType($accountType);

        $section = null;
        $questionArray = array();
        $questionNameList = array();

        foreach ( $questions as $sort => $question )
        {
            if ( $section !== $question['sectionName'] )
            {
                $section = $question['sectionName'];
            }

            $questionArray[$section][$sort] = $questions[$sort];
            $questionNameList[] = $questions[$sort]['name'];
        }

        $this->assign('questionArray', $questionArray);

        $questionData = $this->questionService->getQuestionData(array($editUserId), $questionNameList);
        
        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $editForm->addQuestions($questions, $questionValues, !empty($questionData[$editUserId]) ? $questionData[$editUserId]: array() );

        if ( OW::getRequest()->isPost() && isset($_POST['editSubmit']) )
        {
            if ( $editForm->isValid($_POST) )
            {
                $data = $editForm->getValues();

                foreach ( $questionArray as $section )
                {
                    foreach ( $section as $key => $question )
                    {
                        switch ( $question['presentation'] )
                        {
                            case 'multicheckbox':

                                if ( is_array($data[$question['name']]) )
                                {
                                    $data[$question['name']] = array_sum($data[$question['name']]);
                                }
                                else
                                {
                                    $data[$question['name']] = 0;
                                }

                                break;
                        }
                    }
                }

                // save user data
                if ( !empty($user->id) )
                {
                    if ( $this->questionService->saveQuestionsData($data, $user->id) )
                    {
                        // delete avatar
                        if ( empty($data['avatar']) ) 
                        {
                            if ( empty($_POST['avatarPreloaded']) )
                            {
                                BOL_AvatarService::getInstance()->deleteUserAvatar($user->id);
                            }
                        }
                        else 
                        {
                            // update user avatar
                            BOL_AvatarService::getInstance()->createAvatar($user->id);
                        }

                        if ( !$adminMode )
                        {
                            $event = new OW_Event(OW_EventManager::ON_USER_EDIT, array('userId' => $user->id, 'method' => 'native',));
                            OW::getEventManager()->trigger($event);

                            OW::getFeedback()->info($language->text('base', 'edit_successfull_edit'));
                            $this->redirect();
                        }
                        else
                        {
                            $event = new OW_Event(OW_EventManager::ON_USER_EDIT_BY_ADMIN, array('userId' => $user->id));
                            OW::getEventManager()->trigger($event);

                            OW::getFeedback()->info($language->text('base', 'edit_successfull_edit'));
                            $this->redirect(OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName($editUserId))));
                        }
                    }
                    else
                    {
                        OW::getFeedback()->info($language->text('base', 'edit_edit_error'));
                    }
                }
                else
                {
                    OW::getFeedback()->info($language->text('base', 'edit_edit_error'));
                }
            }
        }

        $this->addForm($editForm);

        $this->assign('unregisterProfileUrl', OW::getRouter()->urlForRoute('base_delete_user'));

        $language->addKeyForJs('base', 'join_error_username_not_valid');
        $language->addKeyForJs('base', 'join_error_username_already_exist');
        $language->addKeyForJs('base', 'join_error_email_not_valid');
        $language->addKeyForJs('base', 'join_error_email_already_exist');
        $language->addKeyForJs('base', 'join_error_password_not_valid');
        $language->addKeyForJs('base', 'join_error_password_too_short');
        $language->addKeyForJs('base', 'join_error_password_too_long');

        //include js
        $onLoadJs = " window.edit = new OW_BaseFieldValidators( " .
            json_encode(array(
                'formName' => $editForm->getName(),
                'responderUrl' => OW::getRouter()->urlFor("BASE_CTRL_Edit", "ajaxResponder"))) . ",
                                                        " . UTIL_Validator::EMAIL_PATTERN . ", " . UTIL_Validator::USER_NAME_PATTERN . ", " . $editUserId . " ); ";

        $this->assign('isAdmin', OW::getUser()->isAdmin());

        OW::getDocument()->addOnloadScript($onLoadJs);

        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "base_field_validators.js");

        if ( !$adminMode )
        {
            $editSynchronizeHook = OW::getRegistry()->getArray(self::EDIT_SYNCHRONIZE_HOOK);

            if ( !empty($editSynchronizeHook) )
            {
                $content = array();

                foreach ( $editSynchronizeHook as $function )
                {
                    $result = call_user_func($function);

                    if ( trim($result) )
                    {
                        $content[] = $result;
                    }
                }

                $content = array_filter($content, 'trim');

                if ( !empty($content) )
                {
                    $this->assign('editSynchronizeHook', $content);
                }
            }
        }
    }

    public function ajaxResponder()
    {
        $adminMode = false;

        if ( empty($_POST["command"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $editorId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthenticated() || $editorId === null )
        {
            throw new AuthenticateException(); // TODO: Redirect to login page
        }

        $editedUserId = $editorId;

        if ( !empty($_POST["userId"]) )
        {
            $adminMode = true;

            $userId = (int) $_POST["userId"];
            $user = $this->userService->findUserById($userId);

            if ( empty($user) )
            {
                echo json_encode(array('result' => false));
                exit;
            }

            if ( !OW::getUser()->isAdmin() )
            {
                echo json_encode(array('result' => false));
                exit;
            }

            $editedUserId = $userId;
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'isExistEmail':

                $result = false;

                $email = $_POST["value"];

                $result = $this->userService->isExistEmail($email);

                if ( $result )
                {
                    $user = $this->userService->findUserById($editedUserId);

                    if ( isset($user) && $user->email === $email )
                    {
                        $result = false;
                    }
                }

                echo json_encode(array('result' => !$result));

                break;

            case 'validatePassword':

                $result = false;

                if ( !$adminMode )
                {
                    $password = $_POST["value"];

                    $result = $this->userService->isValidPassword(OW::getUser()->getId(), $password);
                }

                echo json_encode(array('result' => $result));

                break;

            case 'isExistUserName':
                $username = $_POST["value"];

                $validator = new editUserNameValidator();
                $result = $validator->isValid($username);

                echo json_encode(array('result' => $result));

                break;

            default:
        }
        exit();
    }
}

class editUserNameValidator extends OW_Validator
{
    private $userId = null;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( $userId = null )
    {
        $this->userId = $userId;
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

        if ( BOL_UserService::getInstance()->isExistUserName($value) )
        {
            $userId = OW::getUser()->getId();

            if ( !empty($this->userId) )
            {
                $userId = $this->userId;
            }

            $user = BOL_UserService::getInstance()->findUserById($userId);

            if ( $value !== $user->username )
            {
                $this->setErrorMessage($language->text('base', 'join_error_username_already_exist'));
                return false;
            }
        }

        if ( BOL_UserService::getInstance()->isRestrictedUsername($value) )
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
                    // window.edit.validateUsername(false);
                    if( window.edit.errors['username']['error'] !== undefined )
                    {
                        throw window.edit.errors['username']['error'];
                    }
                },
                getErrorMessage : function(){
                    if( window.edit.errors['username']['error'] !== undefined ){ return window.edit.errors['username']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}

class editEmailValidator extends OW_Validator
{
    private $userId = null;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( $userId = null )
    {
        $this->userId = $userId;
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

        if ( BOL_UserService::getInstance()->isExistEmail($value) )
        {
            $userId = $this->userId;

            if ( empty($this->userId) )
            {
                $userId = OW::getUser()->getId();
            }

            $user = BOL_UserService::getInstance()->findUserById($userId);

            if ( $value !== $user->email )
            {
                $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));
                return false;
            }
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
                    // window.edit.validateEmail(false);
                    if( window.edit.errors['email']['error'] !== undefined )
                    {
                        throw window.edit.errors['email']['error'];
                    }
                },
        	getErrorMessage : function(){
                    if( window.edit.errors['email']['error'] !== undefined ){ return window.edit.errors['email']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}

class EditQuestionForm extends BASE_CLASS_UserQuestionForm
{
    private $userId = null;

    public function __construct( $name, $userId = null )
    {
        parent::__construct($name);

        if ( $userId != null )
        {
            $this->userId = $userId;
        }
    }

    /**
     * Set field validator
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function addFieldValidator( $formField, $question )
    {
        if ( (string) $question['base'] === '1' )
        {
            if ( $question['name'] === 'email' )
            {
                $formField->addValidator(new editEmailValidator($this->userId));
            }
            else if ( $question['name'] === 'username' )
            {
                $formField->addValidator(new editUserNameValidator($this->userId));
            }
        }

        return $formField;
    }
}

