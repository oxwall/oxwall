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
 * Join user
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Join extends OW_ActionController
{
    const JOIN_CONNECT_HOOK = 'join_connect_hook';
    protected $responderUrl;
    protected $joinForm;

    public function __construct()
    {
        parent::__construct();

        $this->responderUrl = OW::getRouter()->urlFor("BASE_CTRL_Join", "ajaxResponder");

        $this->userService = BOL_UserService::getInstance();
    }

    public function index( $params )
    {
        $session = OW::getSession();

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('base_member_dashboard'));
        }

        $language = OW::getLanguage();
        $this->setPageHeading($language->text('base', 'join_index'));
        
        //TODO DELETE config who_can_join from join
        if ( (int) OW::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
        {
            $code = null;
            if ( isset($_GET['code']) )
            {
                $code = $_GET['code'];
            }
            else if ( isset($params['code']) )
            {
                $code = $params['code'];
            }

            //close join form
            try
            {
                $event = new OW_Event(OW_EventManager::ON_JOIN_FORM_RENDER, array('code' => $code));
                OW::getEventManager()->trigger($event);
                $this->assign('notValidInviteCode', true);
                return;
            }
            catch ( JoinRenderException $ex )
            {
                //ignore;
            }
        }

        $urlParams = $_GET;
        if ( is_array($params) && !empty($params) )
        {
            $urlParams = array_merge($_GET, $params);
        }
        
        $this->joinForm = OW::getClassInstance('JoinForm', $this);
        $this->joinForm->setAction(OW::getRouter()->urlFor('BASE_CTRL_Join', 'joinFormSubmit', $urlParams));
        $step = $this->joinForm->getStep();

        $this->addForm($this->joinForm);

        $language->addKeyForJs('base', 'join_error_username_not_valid');
        $language->addKeyForJs('base', 'join_error_username_already_exist');
        $language->addKeyForJs('base', 'join_error_email_not_valid');
        $language->addKeyForJs('base', 'join_error_email_already_exist');
        $language->addKeyForJs('base', 'join_error_password_not_valid');
        $language->addKeyForJs('base', 'join_error_password_too_short');
        $language->addKeyForJs('base', 'join_error_password_too_long');

        //include js
        $onLoadJs = " window.join = new OW_BaseFieldValidators( " .
            json_encode(array(
                'formName' => $this->joinForm->getName(),
                'responderUrl' => $this->responderUrl,
                'passwordMaxLength' => UTIL_Validator::PASSWORD_MAX_LENGTH,
                'passwordMinLength' => UTIL_Validator::PASSWORD_MIN_LENGTH)) . ",
                " . UTIL_Validator::EMAIL_PATTERN . ", " . UTIL_Validator::USER_NAME_PATTERN . " ); ";

        OW::getDocument()->addOnloadScript($onLoadJs);

        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "base_field_validators.js");

        $joinConnectHook = OW::getRegistry()->getArray(self::JOIN_CONNECT_HOOK);

        if ( !empty($joinConnectHook) )
        {
            $content = array();

            foreach ( $joinConnectHook as $function )
            {
                $result = call_user_func($function);

                if ( trim($result) )
                {
                    $content[] = $result;
                }
            }

            $this->assign('joinConnectHook', $content);
        }

        $this->setDocumentKey('base_user_join');

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "join",
            "title" => "base+meta_title_join",
            "description" => "base+meta_desc_join",
            "keywords" => "base+meta_keywords_join"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function joinFormSubmit( $params )
    {
	$this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCtrlViewDir().'join_index.html');

	//TODO DELETE config who_can_join from join
        if ( (int) OW::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
        {
            $code = null;
            if ( isset($params['code']) )
            {
                $code = $params['code'];
            }

            //close join form
            try
            {
                $event = new OW_Event(OW_EventManager::ON_JOIN_FORM_RENDER, array('code' => $code));
                OW::getEventManager()->trigger($event);
                $this->assign('notValidInviteCode', true);
                return;
            }
            catch ( JoinRenderException $ex )
            {
                //ignore;
            }
        }
        
        $this->index($params);
        $this->postProcess( $params );
    }

    protected function postProcess( $params )
    {
        if ( OW::getRequest()->isPost() )
        {
            if ( !$this->joinForm->isBot() )
            {
                if ( $this->joinForm->isValid($this->joinForm->getPost()) )
                {
                    $session = OW::getSession();
                    $joinData = $session->get(JoinForm::SESSION_JOIN_DATA);

                    if ( !isset($joinData) || !is_array($joinData) )
                    {
                        $joinData = array();
                    }

                    $data = $this->joinForm->getRealValues();

                    unset($data['repeatPassword']);
                    $this->joinForm->clearSession();

                    foreach ( $this->joinForm->questions as $question )
                    {
                        switch ( $question['presentation'] )
                        {
                            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:

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

                    $joinData = array_merge($joinData, $data);

                    if ( $this->joinForm->isLastStep() )
                    {
                        $session->set(JoinForm::SESSION_JOIN_DATA, $joinData);
                        $this->joinUser($joinData, $this->joinForm->getAccountType(), $params);

                        $this->redirect(OW::getRouter()->getBaseUrl());
                    }
                    else
                    {
                        $step = $this->joinForm->getStep();

                        $step++;

                        $session->set(JoinForm::SESSION_JOIN_DATA, $joinData);
                        $session->set(JoinForm::SESSION_JOIN_STEP, $step);

                        $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), $params));

                    }
                }
            }
            else
            {
                $this->joinForm->clearSession();
                $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), $params));
            }
        }
        else
        {
            $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), $params));
        }
    }

    public function ajaxResponder()
    {
        if ( empty($_POST["command"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];
        
        switch ( $command )
        {
            case 'isExistUserName':

                $username = $_POST["value"];
                $result = $this->userService->isExistUserName($username);

                echo json_encode(array('result' => !$result));

                break;

            case 'isExistEmail':

                $email = $_POST["value"];

                $result = $this->userService->isExistEmail($email);

                echo json_encode(array('result' => !$result));

                break;

            default:
        }
        exit;
    }

    protected function joinUser( $joinData, $accountType, $params )
    {
        $event = new OW_Event(OW_EventManager::ON_BEFORE_USER_REGISTER, $joinData);
        OW::getEventManager()->trigger($event);

        $language = OW::getLanguage();
        // create new user
        $user = $this->userService->createUser($joinData['username'], $joinData['password'], $joinData['email'], $accountType);

        unset($joinData['username']);
        unset($joinData['password']);
        unset($joinData['email']);
        unset($joinData['accountType']);

        // save user data
        if ( !empty($user->id) )
        {
            if ( BOL_QuestionService::getInstance()->saveQuestionsData($joinData, $user->id) )
            {
                OW::getSession()->delete(JoinForm::SESSION_JOIN_DATA);
                OW::getSession()->delete(JoinForm::SESSION_JOIN_STEP);

                // authenticate user
                OW::getUser()->login($user->id);
                
                // create Avatar
                $this->createAvatar($user->id);
                
                $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array('userId' => $user->id, 'method' => 'native', 'params' => $params));
                OW::getEventManager()->trigger($event);

                OW::getFeedback()->info(OW::getLanguage()->text('base', 'join_successful_join'));

                if ( OW::getConfig()->getValue('base', 'confirm_email') )
                {
                    BOL_EmailVerifyService::getInstance()->sendUserVerificationMail($user);
                }
            }
            else
            {
                OW::getFeedback()->error($language->text('base', 'join_join_error'));
            }
        }
        else
        {
            OW::getFeedback()->error($language->text('base', 'join_join_error'));
        }
    }
    
    protected function createAvatar($userId)
    {
         BOL_AvatarService::getInstance()->createAvatar($userId, false, false);
    }
}

class JoinForm extends BASE_CLASS_UserQuestionForm
{
    const SESSION_JOIN_DATA = 'joinData';

    const SESSION_JOIN_STEP = 'joinStep';

    const SESSION_REAL_QUESTION_LIST = 'join.real_question_list';

    const SESSION_ALL_QUESTION_LIST = 'join.all_question_list';

    const SESSION_START_STAMP = 'join.session_start_stamp';

    protected $post = array();
    protected $stepCount = 1;
    protected $isLastStep = false;
    protected $displayAccountType = false;
    public  $questions = array();
    protected $sortedQuestionsList = array();
    protected $questionListBySection = array();
    protected $questionValuesList = array();
    protected $accountType = null;
    protected $isBot = false;
    protected $data = array();
    protected $toggleClass = '';

    public function __construct( $controller )
    {
        parent::__construct('joinForm');

        $this->setId('joinForm');

        $stamp = OW::getSession()->get(self::SESSION_START_STAMP);

        if ( empty($stamp) )
        {
            OW::getSession()->set(self::SESSION_START_STAMP, time());
        }

        unset($stamp);

        $this->checkSession();

        $joinSubmitLabel = "";

        // get available account types from DB
        $accounts = $this->getAccountTypes();

        $joinData = OW::getSession()->get(self::SESSION_JOIN_DATA);

        if ( !isset($joinData) || !is_array($joinData) )
        {
            $joinData = array();
        }

        $accountsKeys = array_keys($accounts);
        $this->accountType = $accountsKeys[0];

        if ( isset($joinData['accountType']) )
        {
            $this->accountType = trim($joinData['accountType']);
        }

        $step = $this->getStep();

        if ( count($accounts) > 1 )
        {
            $this->stepCount = 2;
            switch ( $step )
            {
                case 1:
                    $this->displayAccountType = true;
                    $joinSubmitLabel = OW::getLanguage()->text('base', 'join_submit_button_continue');
                    break;

                case 2:
                    $this->isLastStep = true;
                    $joinSubmitLabel = OW::getLanguage()->text('base', 'join_submit_button_join');
                    break;
            }
        }
        else
        {
            $this->isLastStep = true;
            $joinSubmitLabel = OW::getLanguage()->text('base', 'join_submit_button_join');
        }

        $joinSubmit = new Submit('joinSubmit');
        $joinSubmit->addAttribute('class', 'ow_button ow_ic_submit');
        $joinSubmit->setValue($joinSubmitLabel);
        $this->addElement($joinSubmit);

        $this->init($accounts);

        $this->getQuestions();

        $section = null;

        $questionNameList = array();
        $this->sortedQuestionsList = array();

        foreach ( $this->questions as $sort => $question )
        {
            if ( (string) $question['base'] === '0' && $step === 2 || $step === 1 )
            {
                if ( $section !== $question['sectionName'] )
                {
                    $section = $question['sectionName'];
                }

                //$this->questionListBySection[$section][] = $this->questions[$sort];
                $questionNameList[] = $this->questions[$sort]['name'];
                $this->sortedQuestionsList[] = $this->questions[$sort];
            }
        }

        $this->questionValuesList = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addFakeQuestions();

        $this->addQuestions($this->sortedQuestionsList, $this->questionValuesList, $this->updateJoinData());

        $this->setQuestionsLabel();

        $this->addClassToBaseQuestions();

        if ( $this->isLastStep )
        {
            $this->addLastStepQuestions($controller);
        }

        $controller->assign('step', $step);
        $controller->assign('questionArray', $this->questionListBySection);
        $controller->assign('displayAccountType', $this->displayAccountType);
        $controller->assign('isLastStep', $this->isLastStep);
    }

    protected function init( array $accounts )
    {
        if ( $this->displayAccountType )
        {
            $joinAccountType = new Selectbox('accountType');
            $joinAccountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
            $joinAccountType->setRequired();
            $joinAccountType->setOptions($accounts);
            $joinAccountType->setValue($this->accountType);
            $joinAccountType->setHasInvitation(false);

            $this->addElement($joinAccountType);
        }
    }

        public function checkSession()
    {
        $stamp = BOL_QuestionService::getInstance()->getQuestionsEditStamp();
        $sessionStamp = OW::getSession()->get(self::SESSION_START_STAMP);
        
        if ( !empty($sessionStamp) && $stamp > $sessionStamp )
        {
            OW::getSession()->delete(self::SESSION_ALL_QUESTION_LIST);
            OW::getSession()->delete(self::SESSION_JOIN_DATA);
            OW::getSession()->delete(self::SESSION_JOIN_STEP);
            OW::getSession()->delete(self::SESSION_REAL_QUESTION_LIST);
            OW::getSession()->delete(self::SESSION_START_STAMP);

            if ( OW::getRequest()->isPost() )
            {
                UTIL_Url::redirect(OW::getRouter()->urlForRoute('base_join'));
            }
        }
    }

    public function setQuestionsLabel()
    {
        foreach ( $this->sortedQuestionsList as $question )
        {
            if ( !empty($question['realName']) )
            {
                $this->getElement($question['name'])->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question['realName'] . '_label'));
            }
        }
    }

    public function addClassToBaseQuestions()
    {
        foreach ( $this->sortedQuestionsList as $question )
        {
            if ( !empty($question['realName']) )
            {
                if ( $question['realName'] == 'username' )
                {
                    $this->getElement($question['name'])->addAttribute("class", "ow_username_validator");
                }

                if ( $question['realName'] == 'email' )
                {
                    $this->getElement($question['name'])->addAttribute("class", "ow_email_validator");
                }
            }
        }
    }

    protected function toggleQuestionClass()
    {
        $class = 'ow_alt1';
        switch ( $this->toggleClass )
        {
            case null:
            case 'ow_alt2':
                break;
            case 'ow_alt1':
                $class = 'ow_alt2';
        }

        $this->toggleClass = $class;

        return $class;
    }

    protected function randQuestionClass()
    {
        $rand = rand(0, 1);

        if ( !$rand )
        {
            $class = 'ow_alt1';
        }
        else
        {
            $class = 'ow_alt2';
        }

        return $class;
    }

    protected function addFakeQuestions()
    {
        $step = $this->getStep();
        $realQuestionList = array();
        $valueList = $this->questionValuesList;
        $this->questionValuesList = array();
        $this->sortedQuestionsList = array();
        $this->questionListBySection = array();
        $section = '';

        $oldQuestionList = OW::getSession()->get(self::SESSION_REAL_QUESTION_LIST);
        $allQuestionList = OW::getSession()->get(self::SESSION_ALL_QUESTION_LIST);

        if ( !empty($oldQuestionList) && !empty($oldQuestionList) )
        {
            $realQuestionList = $oldQuestionList;
            $this->sortedQuestionsList = $allQuestionList;

            foreach ( $this->sortedQuestionsList as $key => $question )
            {
                $this->questionListBySection[$question['sectionName']][] = $question;

                if ( $question['fake'] == true )
                {
                    $this->addDisplayNoneClass(preg_replace('/\s+(ow_alt1|ow_alt2)/', '', $question['trClass']));
                }
                else
                {
                    $this->addEmptyClass(preg_replace('/\s+(ow_alt1|ow_alt2)/', '', $question['trClass']));
                }
                
                if ( !empty($valueList[$question['realName']]) )
                {
                    $this->questionValuesList[$question['name']] = $valueList[$question['realName']];
                }
            }
        }
        else
        {
            foreach ( $this->questions as $sort => $question )
            {
                if ( (string) $question['base'] === '0' && $step === 2 || $step === 1 )
                {
                    if ( $section !== $question['sectionName'] )
                    {
                        $section = $question['sectionName'];
                    }

                    $event = new OW_Event('base.questions_field_add_fake_questions', $question, true);

                    OW::getEventManager()->trigger($event);

                    $addFakes = $event->getData();

                    if ( !$addFakes || in_array( $this->questions[$sort]['presentation'], array('password', 'range') ) )
                    {
                        $this->questions[$sort]['fake'] = false;
                        $this->questions[$sort]['realName'] = $question['name'];

                        $this->questions[$sort]['trClass'] = $this->toggleQuestionClass();

                        if ( $this->questions[$sort]['presentation'] == 'password' )
                        {
                            $this->toggleQuestionClass();
                        }

                        $this->sortedQuestionsList[$question['name']] = $this->questions[$sort];
                        $this->questionListBySection[$section][] = $this->questions[$sort];
                        
                        if ( !empty($valueList[$question['name']]) )
                        {
                            $this->questionValuesList[$question['name']] = $valueList[$question['name']];
                        }
                        
                        continue;
                    }

                    $fakesCount = rand(2, 5);
                    $fakesCount = $fakesCount + 1;
                    $randId = rand(0, $fakesCount);

                    for ( $i = 0; $i <= $fakesCount; $i++ )
                    {
                        $randName = uniqid(UTIL_String::getRandomString(rand(5, 13), 2)); 
                        $question['trClass'] = uniqid('ow_'. UTIL_String::getRandomString(rand(5, 10), 2));

                        if ( $i == $randId )
                        {
                            $realQuestionList[$randName] = $this->questions[$sort]['name'];
                            $question['fake'] = false;
                            $question['required'] = $this->questions[$sort]['required'];

                            $this->addEmptyClass($question['trClass']);

                            $question['trClass'] = $question['trClass'] . " " . $this->toggleQuestionClass();

                        }
                        else
                        {
                            $question['required'] = 0;
                            $question['fake'] = true;

                            $this->addDisplayNoneClass($question['trClass']);

                            $question['trClass'] = $question['trClass'] . " " . $this->randQuestionClass();
                        }
                        
                        $question['realName'] = $this->questions[$sort]['name'];

                        $question['name'] = $randName;

                        $this->sortedQuestionsList[$randName] = $question;

                        if ( !empty($valueList[$this->questions[$sort]['name']]) )
                        {
                            $this->questionValuesList[$randName] = $valueList[$this->questions[$sort]['name']];
                        }

                        $this->questionListBySection[$section][] = $question;
                    }
                }
            }
        }

        if ( OW::getRequest()->isPost() )
        {
            $this->post = $_POST;

            if ( empty($oldQuestionList) )
            {
                $oldQuestionList = array();
            }

            if ( empty($allQuestionList) )
            {
                $allQuestionList = array();
            }

            if ( $oldQuestionList && $allQuestionList )
            {
                foreach ( $oldQuestionList as $key => $value )
                {
                    $newKey = array_search($value, $realQuestionList);

                    if ( $newKey !== false && isset($_POST[$key]) && isset($realQuestionList[$newKey]) )
                    {
                        $this->post[$newKey] = $_POST[$key];
                    }
                }

                foreach ( $allQuestionList as $question )
                {
                    if ( !empty($question['fake']) && !empty($_POST[$question['name']]) )
                    {
                        $this->isBot = true;
                    }
                }
            }
        }

        if ( $this->isBot )
        {
            $event = new OW_Event('base.bot_detected', array('isBot' => true));
            OW::getEventManager()->trigger($event);
        }

        OW::getSession()->set(self::SESSION_REAL_QUESTION_LIST, $realQuestionList);
        OW::getSession()->set(self::SESSION_ALL_QUESTION_LIST, $this->sortedQuestionsList);
    }

    protected function updateJoinData()
    {
        $joinData = OW::getSession()->get(self::SESSION_JOIN_DATA);

        if ( empty($joinData) )
        {
            return;
        }

        $this->data = $joinData;

        $list = OW::getSession()->get(self::SESSION_REAL_QUESTION_LIST);

        if ( !empty($list) )
        {
            foreach ( $list as $fakeName => $realName )
            {
                if ( !empty($joinData[$realName]) )
                {
                    unset($this->data[$realName]);
                    $this->data[$fakeName] = $joinData[$realName];
                }
            }
        }

        return $this->data;
    }

    public function getRealValues()
    {
        $list = $this->sortedQuestionsList;

        $values = $this->getValues();
        $result = array();

        if ( !empty($list) )
        {
            foreach ( $values as $fakeName => $value )
            {
                if ( !empty($list[$fakeName]) && isset($list[$fakeName]['fake']) && $list[$fakeName]['fake'] == false )
                {
                    $result[$list[$fakeName]['realName']] = $value;
                }

                if ( $fakeName == 'accountType' )
                {
                    $result[$fakeName] = $value;
                }
            }
        }
        
        return $result;
    }

    public function getStep()
    {
        $session = OW::getSession();

        $step = $session->get(self::SESSION_JOIN_STEP);

        if ( isset($step) )
        {
            $step = (int) $step;

            if ( $step === 0 )
            {
                $step = 1;
                $session->set(self::SESSION_JOIN_STEP, $step);
            }
        }
        else
        {
            $step = 1;
            $session->set(self::SESSION_JOIN_STEP, $step);
        }

        return $step;
    }

    public function getQuestions()
    {
        $this->questions = array();

        if ( $this->isLastStep )
        {
            $this->questions = BOL_QuestionService::getInstance()->findSignUpQuestionsForAccountType($this->accountType);
        }
        else
        {
            $this->questions = BOL_QuestionService::getInstance()->findBaseSignUpQuestions();
        }
    }
    
    protected function addLastStepQuestions( $controller )
    {
        $displayPhoto = false;

        $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');
        $avatarValidator = OW::getClassInstance("BASE_CLASS_AvatarFieldValidator", false);

        switch ( $displayPhotoUpload )
        {
            case BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD :
                $avatarValidator = OW::getClassInstance("BASE_CLASS_AvatarFieldValidator", true);

            case BOL_UserService::CONFIG_JOIN_DISPLAY_PHOTO_UPLOAD :
                $userPhoto = OW::getClassInstance("BASE_CLASS_JoinUploadPhotoField", 'userPhoto');
                $userPhoto->setLabel(OW::getLanguage()->text('base', 'questions_question_user_photo_label'));
                $userPhoto->addValidator($avatarValidator);
                $this->addElement($userPhoto);

                $displayPhoto = true;
        }

        $displayTermsOfUse = false;

        if ( OW::getConfig()->getValue('base', 'join_display_terms_of_use') )
        {
            $termOfUse = new CheckboxField('termOfUse');
            $termOfUse->setLabel(OW::getLanguage()->text('base', 'questions_question_user_terms_of_use_label'));
            $termOfUse->setRequired();

            $this->addElement($termOfUse);

            $displayTermsOfUse = true;
        }

        $this->setEnctype('multipart/form-data');

        $event = new OW_Event('join.get_captcha_field');
        OW::getEventManager()->trigger($event);
        $captchaField = $event->getData();

        $displayCaptcha = false;

        $enableCaptcha = OW::getConfig()->getValue('base', 'enable_captcha');
        
        if ( $enableCaptcha && !empty($captchaField) && $captchaField instanceof FormElement )
        {
            $captchaField->setName('captchaField');
            $this->addElement($captchaField);
            $displayCaptcha = true;
        }

        $controller->assign('display_captcha', $displayCaptcha);
        $controller->assign('display_photo', $displayPhoto);
        $controller->assign('display_terms_of_use', $displayTermsOfUse);

        if ( OW::getRequest()->isPost() )
        {
            if ( !empty($captchaField) && $captchaField instanceof FormElement )
            {
                $captchaField->setValue(null);
            }

            if ( isset($userPhoto) && isset($_FILES[$userPhoto->getName()]['name']) )
            {
                $_POST[$userPhoto->getName()] = $_FILES[$userPhoto->getName()]['name'];
            }
        }
    }

    protected function addFieldValidator( $formField, $question )
    {
        $list = OW::getSession()->get(self::SESSION_ALL_QUESTION_LIST);

        $questionInfo = empty($list[$question['name']]) ? null : $list[$question['name']];

        if ( (string) $question['base'] === '1' )
        {
            if ( !empty($questionInfo['realName']) && $questionInfo['realName'] === 'email' && $questionInfo['fake'] == false )
            {
                $formField->addValidator(new BASE_CLASS_JoinEmailValidator());
            }

            if ( !empty($questionInfo['realName']) && $questionInfo['realName'] === 'username' && $questionInfo['fake'] == false )
            {
                $formField->addValidator(new BASE_CLASS_JoinUsernameValidator());
            }

            if ( $question['name'] === 'password' )
            {
                $passwordRepeat = BOL_QuestionService::getInstance()->getPresentationClass($question['presentation'], 'repeatPassword');
                $passwordRepeat->setLabel(OW::getLanguage()->text('base', 'questions_question_repeat_password_label'));
                $passwordRepeat->setRequired((string) $question['required'] === '1');
                $this->addElement($passwordRepeat);

                $formField->addValidator(new PasswordValidator());
            }
        }
    }

    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        $realQuestionList = OW::getSession()->get(self::SESSION_REAL_QUESTION_LIST);

        $name = $questionName;
        if ( !empty($realQuestionList[$questionName]) )
        {
            $name = $realQuestionList[$questionName];
        }

        parent::setFieldOptions($formField, $name, $questionValues);
    }

    public function isBot()
    {
        return $this->isBot;
    }

    public function isLastStep()
    {
        return $this->isLastStep;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getAccountType()
    {
        return $this->accountType;
    }

    public function addEmptyClass( $className )
    {
        OW::getDocument()->addStyleDeclaration("
            .{$className}
            {
                
            } ");
    }

    public function addDisplayNoneClass( $className )
    {
        OW::getDocument()->addStyleDeclaration("
            .{$className}
            {
                display:none;
            } ");
    }

    public function clearSession()
    {
        OW::getSession()->delete(self::SESSION_REAL_QUESTION_LIST);
        OW::getSession()->delete(self::SESSION_ALL_QUESTION_LIST);
    }

    public function getSortedQuestionsList()
    {
        return $this->sortedQuestionsList;
    }
}

class PasswordValidator extends BASE_CLASS_PasswordValidator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        parent::__construct();
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
                    if( !window.join.validatePassword() )
                    {
                        throw window.join.errors['password']['error'];
                    }
                },
                getErrorMessage : function()
                {
                       if( window.join.errors['password']['error'] !== undefined ){ return window.join.errors['password']['error'] }
                       else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}

class JoinRenderException extends Exception
{
    
}
