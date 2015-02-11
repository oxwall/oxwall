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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_UserSearch extends OW_ActionController
{

    public function __construct()
    {
        parent::__construct();

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('base', 'user_search_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'user_search_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
    }

    public function index()
    {
        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'users_list_user_search_meta_description'));

        $this->addComponent('menu', BASE_CTRL_UserList::getMenu('search'));

        if ( !OW::getUser()->isAuthorized('base', 'search_users') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
            $this->assign('authMessage', $status['msg']);
            return;
        }

        $mainSearchForm = OW::getClassInstance('MainSearchForm', $this);
        $mainSearchForm->process($_POST);
        $this->addForm($mainSearchForm);

        $displayNameSearchForm = new DisplayNameSearchForm($this);
        $displayNameSearchForm->process($_POST);
        $this->addForm($displayNameSearchForm);
    }

    public function result()
    {
        if ( !OW::getUser()->isAuthorized('base', 'search_users') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
            throw new AuthorizationException($status['msg']);
        }

        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'users_list_user_search_meta_description'));

        $this->addComponent('menu', BASE_CTRL_UserList::getMenu('search'));

        $language = OW::getLanguage();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $rpp = OW::getConfig()->getValue('base', 'users_count_on_page');

        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $listId = OW::getSession()->get(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE);
        $list = BOL_UserService::getInstance()->findSearchResultList($listId, $first, $count);
        $itemCount = BOL_SearchService::getInstance()->countSearchResultItem($listId);

        $cmp = new BASE_CLASS_SearchResultList($list, $itemCount, $rpp, true);

        $this->addComponent('cmp', $cmp);
        $this->assign('listType', 'search');

        $searchUrl = OW::getRouter()->urlForRoute('users-search');
        $this->assign('searchUrl', $searchUrl);
    }
}

class MainSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'MainSearchFormSubmit';

    const FORM_SESSEION_VAR = 'MAIN_SEARCH_FORM_DATA';

    public $controller;
    public $accountType;
    public $displayAccountType = false;
    public $displayMainSearch = true;

    /*
     * @var OW_ActionController $controller
     * 
     */

    public function __construct( $controller )
    {
        parent::__construct('MainSearchForm');

        $this->controller = $controller;

        $questionService = BOL_QuestionService::getInstance();
        $language = OW::getLanguage();

        $this->setId('MainSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        $questionData = OW::getSession()->get(self::FORM_SESSEION_VAR);

        if ( $questionData === null )
        {
            $questionData = array();
        }

        $accounts = $this->getAccountTypes();

        $accountList = array();
        $accountList[BOL_QuestionService::ALL_ACCOUNT_TYPES] = OW::getLanguage()->text('base', 'questions_account_type_' . BOL_QuestionService::ALL_ACCOUNT_TYPES);

        foreach ( $accounts as $key => $account )
        {
            $accountList[$key] = $account;
        }

        $keys = array_keys($accountList);

        $this->accountType = $keys[0];

        if ( isset($questionData['accountType']) && in_array($questionData['accountType'], $keys) )
        {
            $this->accountType = $questionData['accountType'];
        }

        if ( count($accounts) > 1 )
        {
            $this->displayAccountType = true;

            $accountType = new Selectbox('accountType');
            $accountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
            $accountType->setRequired();
            $accountType->setOptions($accountList);
            $accountType->setValue($this->accountType);
            $accountType->setHasInvitation(false);

            $this->addElement($accountType);
        }

        $questions = $questionService->findSearchQuestionsForAccountType($this->accountType);

        $mainSearchQuestion = array();
        $questionNameList = array();

        foreach ( $questions as $key => $question )
        {
            $sectionName = $question['sectionName'];
            $mainSearchQuestion[$sectionName][] = $question;
            $questionNameList[] = $question['name'];
            $questions[$key]['required'] = '0';
        }

        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addQuestions($questions, $questionValueList, $questionData);

        $controller->assign('questionList', $mainSearchQuestion);
        $controller->assign('displayAccountType', $this->displayAccountType);
    }

    public function process( $data )
    {
        if ( OW::getRequest()->isPost() && !$this->isAjax() && isset($data['form_name']) && $data['form_name'] === $this->getName() )
        {
            OW::getSession()->set(self::FORM_SESSEION_VAR, $data);

            if ( isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !$this->isAjax() )
            {
                if ( !OW::getUser()->isAuthorized('base', 'search_users') )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');;
                    OW::getFeedback()->warning($status['msg']);
                    $this->controller->redirect();
                }
                
                if ( isset($data['accountType']) && $data['accountType'] === BOL_QuestionService::ALL_ACCOUNT_TYPES )
                {
                    unset($data['accountType']);
                }
                
                $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE);
                $listId = 0;

                if ( count($userIdList) > 0 )
                {
                    $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
                }

                OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

                BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');

                $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
            }
            $this->controller->redirect(OW::getRouter()->urlForRoute("users-search"));
        }
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
    }

    protected function setFieldValue( $formField, $presentation, $value )
    {

    }
}

class DisplayNameSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'DisplayNameSearchFormSubmit';

    public $controller;
    public $accountType;
    public $displayAccountType = false;
    public $displayMainSearch = true;

    /*
     * @var OW_ActionController $controller
     *
     */

    public function __construct( $controller )
    {
        parent::__construct('DisplayNameSearchForm');

        $this->controller = $controller;

        $questionService = BOL_QuestionService::getInstance();
        $language = OW::getLanguage();

        $this->setId('DisplayNameSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(OW::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        $questionName = OW::getConfig()->getValue('base', 'display_name_question');

        $question = $questionService->findQuestionByName($questionName);

        $questionPropertyList = array();
        foreach ( $question as $property => $value )
        {
            $questionPropertyList[$property] = $value;
        }

        $this->addQuestions(array($questionName => $questionPropertyList), array(), array());

        $controller->assign('displayNameQuestion', $questionPropertyList);
    }

    public function process( $data )
    {
        if ( OW::getRequest()->isPost() && isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !$this->isAjax() )
        {
            if ( !OW::getUser()->isAuthorized('base', 'search_users') )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                OW::getFeedback()->warning($status['msg']);
                $this->controller->redirect();
            }
            
            $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE);
            $listId = 0;

            if ( count($userIdList) > 0 )
            {
                $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
            }

            OW::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

            BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');

            $this->controller->redirect(OW::getRouter()->urlForRoute("users-search-result", array()));
        }
    }
}