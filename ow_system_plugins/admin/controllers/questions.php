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
 * Questions controller
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Questions extends ADMIN_CTRL_Abstract
{
    const ADD_QUESTION_SESSION_VAR = "ADMIN_ADD_QUESTION";
    const EDIT_QUESTION_SESSION_VAR = "ADMIN_EDIT_QUESTION";
    const SESSION_VAR_ACCIUNT_TYPE = "BASE_QUESTION_ACCOUNT_TYPE";

    /**
     * @var BOL_QuestionService
     *
     */
    private $questionService;
    private $ajaxResponderUrl;
    private $columnCountValues = array();
    /**
     * @var BASE_CMP_ContentMenu
     */
    private $contentMenu;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        $this->ajaxResponderUrl = OW::getRouter()->urlFor("ADMIN_CTRL_Questions", "ajaxResponder");

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('admin', 'heading_questions'));
        $this->setPageHeadingIconClass('ow_ic_files');

        OW::getNavigation()->activateMenuItem('admin_users', 'admin', 'sidebar_menu_item_questions');
    }

    public function pages( $params = array() )
    {
        $serviceLang = BOL_LanguageService::getInstance();
        $language = OW::getLanguage();
        $currentLanguageId = OW::getLanguage()->getCurrentId();

        // -- Get all section, questions and question values --

        $questions = $this->questionService->findAllQuestionsBySectionForAccountType('all');

        $section = null;
        $questionBySectionList = array();
        $sectionDeleteUrlList = array();
        $parentList = array();
        $questionNameList = array();
        $questionList = array();

        $deleteEditButtonsContent = array();
        $previewQuestionValuesContent = array();
        $pagesCheckboxContent = array();
        
        $sectionsNameList = array_keys($questions);
        $sectionDtoList = BOL_QuestionService::getInstance()->findSectionBySectionNameList($sectionsNameList);
        
        foreach ( $questions as $section => $list )
        {
            $sectionDeleteUrlList[$section] = OW::getRouter()->urlFor('ADMIN_CTRL_Questions', 'deleteSection', array("sectionName" => $section));
            $questionBySectionList[$section] = array();

            foreach ( $list as $question )
            {
                $questionList[$question['name']] = $question;
                
                if ( !empty($question['parent']) )
                {
                    $parent = $this->questionService->findQuestionByName($question['parent']);

                    if ( !empty($parent) )
                    {
                        $question['parentUrl'] = 'javascript://';
                        $question['parentLabel'] = $this->questionService->getQuestionLang($parent->name);
                        $question['parentId'] = $parent->id;
                        
                        $parentList[$question['parent']][] = array(
                            'name' => $question['name'],
                            'editUrl' => 'javascript://');
                    }
                    else
                    {
                        $question['parent'] = '';
                    }
                }

                $questionBySectionList[$section][] = $question;
                $questionNameList[] = $question['name'];

                $event = new OW_Event('admin.questions.get_edit_delete_question_buttons_content', array( 'question' => $question ), null);
                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                $deleteEditButtonsContent[$question['name']] = $data;

                $event = new OW_Event('admin.questions.get_preview_question_values_content', array( 'question' => $question ), null);
                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                $previewQuestionValuesContent[$question['name']] = $data;
                
                $pageCheckboxData = array(
                    'required' => null, 
                    'join' => null, 
                    'edit' => null, 
                    'view' => null, 
                    'search' => null);
                
                $event = new OW_Event('admin.questions.get_question_page_checkbox_content', array( 'actionList' => $pageCheckboxData, 'question' => $question ), $pageCheckboxData);
                OW::getEventManager()->trigger($event);

                $data = $event->getData();
                    
                $pagesCheckboxContent[$question['name']] = $data;
            }
        }

        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);
        
        foreach ( $questionList as $sort => $question )
        {
            if ( empty($question['name']) )
            {
                continue;
            }

            $text = $language->text('admin', 'questions_delete_question_confirmation');

            if ( array_key_exists($question['name'], $parentList) )
            {
                $questionStringList = array();
                foreach ( $parentList[$question['name']] as $child )
                {
                    $questionStringList[] = BOL_QuestionService::getInstance()->getQuestionLang($child['name']);
                }

                $text = $language->text('admin', 'questions_delete_question_parent_confirmation', array('questions' => implode(', ', $questionStringList)));
            }

            $text = json_encode($text);
            OW::getDocument()->addOnloadScript("OW.registerLanguageKey('admin', 'questions_delete_question_confirmation_" . (int) $question['id'] . "', {$text});");

            // ------------------------------------------------------------

            $disableActionList = $this->questionService->getQuestionDisableActionList($questionDtoList[$question['name']]);

            $questionList[$sort] = array_merge($questionList[$sort], $disableActionList);

            // ------------------------------------------------------------
        }

        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $valueLabels = array();

        foreach ( $questionValues as $name => $value )
        {
            if ( empty($valueLabels[$name]) )
            {
                $valueLabels[$name] = array();
            }

            /* @var $value BOL_QuestionValue */
            foreach ( $value['values'] as $item )
            {
                $valueLabels[$item->questionName][$item->value] = BOL_QuestionService::getInstance()->getQuestionValueLang($item->questionName, $item->value);
            }
        }
        
        $this->assign('questionList', $questionList);
        $this->assign('questionsBySections', $questionBySectionList);
        $this->assign('questionValues', $questionValues);
        $this->assign('valueLabels', $valueLabels);
        $this->assign('accountTypesUrl', OW::getRouter()->urlForRoute('questions_account_types'));
        $this->assign('deleteEditButtons', $deleteEditButtonsContent);
        $this->assign('previewValues', $previewQuestionValuesContent);
        $this->assign('pagesCheckboxContent', $pagesCheckboxContent);
        $this->assign('sectionList', $sectionDtoList);
        

        $language->addKeyForJs('admin', 'questions_delete_section_confirmation');

        $script = ' window.indexQuest = new indexQuestions( ' . json_encode(array('questions' => $questionList, 'questionAddUrl' => OW::getRouter()->urlFor("ADMIN_CTRL_Questions", "add"), 'ajaxResponderUrl' => $this->ajaxResponderUrl)) . ' )'; //' . json_encode( array( 'questionEditUrl' => $questionEditUrl ) ) . ' ); ';

        OW::getDocument()->addOnloadScript($script);

        $jsDir = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();

        OW::getDocument()->addScript($jsDir . "questions.js");

        $baseJsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($baseJsDir . "jquery-ui.min.js");

        OW::getLanguage()->addKeyForJs('admin', 'questions_profile_question_sections_title');

        OW::getLanguage()->addKeyForJs('admin', 'questions_edit_profile_question_title');
        OW::getLanguage()->addKeyForJs('admin', 'questions_add_profile_question_title');
        OW::getLanguage()->addKeyForJs('admin', 'questions_values_should_not_be_empty');
    }

    public function accountTypes( $params = array() )
    {
        $serviceLang = BOL_LanguageService::getInstance();
        $language = OW::getLanguage();
        $currentLanguageId = OW::getLanguage()->getCurrentId();

        // -- Get all section, questions and question values --
        
        $questions = $this->questionService->findAllQuestionsBySectionForAccountType('all');

        $section = null;
        $questionBySectionList = array();
        $sectionDeleteUrlList = array();
        $parentList = array();
        $questionNameList = array();
        $questionList = array();

        $deleteEditButtonsContent = array();
        $previewQuestionValuesContent = array();
        $accountTypesCheckboxContent = array();

        $sectionsNameList = array_keys($questions);
        $sectionDtoList = BOL_QuestionService::getInstance()->findSectionBySectionNameList($sectionsNameList);
        
        foreach ( $questions as $section => $list )
        {
            $sectionDeleteUrlList[$section] = OW::getRouter()->urlFor('ADMIN_CTRL_Questions', 'deleteSection', array("sectionName" => $section));
            $questionBySectionList[$section] = array();

            foreach ( $list as $question )
            {
                $questionList[$question['name']] = $question;

                //$question['questionEditUrl'] = OW::getRouter()->urlFor('ADMIN_CTRL_Questions', 'edit', array("questionId" => $question['id']));
                //$question['questionDeleteUrl'] = OW::getRouter()->urlFor('ADMIN_CTRL_Questions', 'deleteQuestion', array("questionId" => $question['id']));

                if ( !empty($question['parent']) )
                {
                    $parent = $this->questionService->findQuestionByName($question['parent']);

                    if ( !empty($parent) )
                    {
                        $question['parentUrl'] = 'javascript://';
                        $question['parentLabel'] = $this->questionService->getQuestionLang($parent->name);
                        $question['parentId'] = $parent->id;
                        
                        $parentList[$question['parent']][] = array(
                            'name' => $question['name'],
                            'editUrl' => 'javascript://');
                    }
                    else
                    {
                        $question['parent'] = '';
                    }
                }

                $questionBySectionList[$section][] = $question;
                $questionNameList[] = $question['name'];

                $event = new OW_Event('admin.questions.get_edit_delete_question_buttons_content', array( 'question' => $question ), null);
                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                $deleteEditButtonsContent[$question['name']] = $data;

                $event = new OW_Event('admin.questions.get_preview_question_values_content', array( 'question' => $question ), null);
                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                $previewQuestionValuesContent[$question['name']] = $data;

                $event = new OW_Event('admin.questions.get_account_types_checkbox_content', array( 'question' => $question ), null);
                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                $accountTypesCheckboxContent[$question['name']] = $data;
            }
        }

        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);

        foreach ( $questionList as $sort => $question )
        {
            if ( empty($question['name']) )
            {
                continue;
            }

            $text = $language->text('admin', 'questions_delete_question_confirmation');

            if ( array_key_exists($question['name'], $parentList) )
            {
                $questionStringList = array();
                foreach ( $parentList[$question['name']] as $child )
                {
                    $questionStringList[] = BOL_QuestionService::getInstance()->getQuestionLang($child['name']);
                }

                $text = $language->text('admin', 'questions_delete_question_parent_confirmation', array('questions' => implode(', ', $questionStringList)));
            }

            $text = json_encode($text);
            OW::getDocument()->addOnloadScript("OW.registerLanguageKey('admin', 'questions_delete_question_confirmation_" . (int) $question['id'] . "', {$text});");

            // ------------------------------------------------------------

            $disableActionList = $this->questionService->getQuestionDisableActionList($questionDtoList[$question['name']]);

            $questionList[$sort] = array_merge($questionList[$sort], $disableActionList);

            // ------------------------------------------------------------
        }

        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);
        $accountTypeDtoList = $this->questionService->findAllAccountTypes();
        $accountTypeList = array();
        $valueLabels = array();

        foreach ( $questionValues as $name => $value )
        {
            if ( empty($valueLabels[$name]) )
            {
                $valueLabels[$name] = array();
            }

            /* @var $value BOL_QuestionValue */
            foreach ( $value['values'] as $item )
            {
                $valueLabels[$item->questionName][$item->value] = BOL_QuestionService::getInstance()->getQuestionValueLang($item->questionName, $item->value);
            }
        }
        
        foreach ( $accountTypeDtoList as $dto )
        {
            $accountTypeList[$dto->name] = $dto->name;            
        }

        $accountTypesToQuestionsDtoList = $this->getAccountTypesToQuestionsList();

        $this->assign('questionList', $questionList);
        $this->assign('td_width', (int) ( 375 / (count($accountTypeDtoList) + 1) ));
        $this->assign('div_width', (int) ( 375 / (count($accountTypeDtoList) + 1)) - 18);
        $this->assign('accountTypeDtoList', $accountTypeDtoList);
        $this->assign('accountTypesCount', count($accountTypeDtoList) + 1);
        $this->assign('tableColumnCount', count($accountTypeDtoList) + 5);
        $this->assign('accountTypesToQuestionsDtoList', $accountTypesToQuestionsDtoList);
        $this->assign('questionsBySections', $questionBySectionList);
        $this->assign('questionValues', $questionValues);
        $this->assign('valueLabels', $valueLabels);
        $this->assign('sectionDeleteUrlList', $sectionDeleteUrlList);
        $this->assign('propertiesUrl', OW::getRouter()->urlForRoute('questions_properties'));
        $this->assign('deleteEditButtons', $deleteEditButtonsContent);
        $this->assign('previewValues', $previewQuestionValuesContent);
        $this->assign('accountTypesCheckboxContent', $accountTypesCheckboxContent);
        $this->assign('sectionList', $sectionDtoList);

        $language->addKeyForJs('admin', 'questions_delete_section_confirmation');

        $script = ' window.indexQuest = new indexQuestions( ' . json_encode(array('questions' => $questionList, 'ajaxResponderUrl' => $this->ajaxResponderUrl, 'accountTypes' => array_keys($accountTypeList)) ) . ' )';

        OW::getDocument()->addOnloadScript($script);

        $jsDir = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();

        OW::getDocument()->addScript($jsDir . "questions.js");

        $baseJsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($baseJsDir . "jquery-ui.min.js");

        OW::getLanguage()->addKeyForJs('admin', 'questions_profile_question_sections_title');

        OW::getLanguage()->addKeyForJs('admin', 'questions_edit_profile_question_title');
        OW::getLanguage()->addKeyForJs('admin', 'questions_add_profile_question_title');

        OW::getLanguage()->addKeyForJs('admin', 'questions_add_account_type_title');
        OW::getLanguage()->addKeyForJs('admin', 'questions_edit_account_type_title');

        OW::getLanguage()->addKeyForJs('admin', 'questions_account_type_was_added');
        OW::getLanguage()->addKeyForJs('admin', 'questions_account_type_was_updated');
        OW::getLanguage()->addKeyForJs('admin', 'questions_account_type_added_error');

        OW::getLanguage()->addKeyForJs('admin', 'questions_delete_account_type_confirmation');

        OW::getLanguage()->addKeyForJs('admin', 'questions_values_should_not_be_empty');

        $contextAction = new BASE_CMP_ContextAction();

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('account_type_menu');
        $contextParentAction->setClass('ow_newsfeed_context');
        $contextAction->addAction($contextParentAction);

        $editAction = new BASE_ContextAction();
        $editAction->setKey('edit');
        $editAction->setLabel(OW::getLanguage()->text('admin', 'btn_label_edit'));
        $editAction->setParentKey($contextParentAction->getKey());
        $editAction->setClass('question_edit_account_type_button');
        $editAction->setUrl('javascript://');
        $editAction->setOrder(1);

        $contextAction->addAction($editAction);

        $deleteAction = new BASE_ContextAction();
        $deleteAction->setKey('delete');
        $deleteAction->setLabel(OW::getLanguage()->text('admin', 'btn_label_delete'));
        $deleteAction->setParentKey($contextParentAction->getKey());
        $deleteAction->setClass('question_delete_account_type_button');
        $deleteAction->setUrl('javascript://');
        $deleteAction->setOrder(2);

        $contextAction->addAction($deleteAction);

        $this->addComponent('accountTypeMenu', $contextAction);
    }

    protected function getAccountTypesToQuestionsList()
    {
        $list = $this->questionService->getAccountTypesToQuestionsList();

        $result = array();

        /* @var $dto BOL_QuestionToAccountType */
        foreach ( $list as $dto )
        {
            $result[$dto->questionName][$dto->accountType] = $dto;
        }

        return $result;
    }

    private function addContentMenu()
    {
        $language = OW::getLanguage();

        $router = OW_Router::getInstance();

        $menuItems = array();

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('qst_index')->setLabel($language->text('base', 'questions_menu_index'))->setUrl($router->urlForRoute('questions_account_types'))->setOrder('1');
        $menuItem->setIconClass('ow_ic_files');

        $menuItems[] = $menuItem;

        $this->contentMenu = new BASE_CMP_ContentMenu($menuItems);

        $this->addComponent('contentMenu', $this->contentMenu);
    }

    public function ajaxResponder()
    {
        if ( !OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), 'admin') || empty($_POST["command"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'deleteQuestion':

                $questionId = (int)$_POST['questionId'];

                $question = $this->questionService->findQuestionById($questionId);

                if ( empty($question) )
                {
                    echo json_encode(array('result' => false));
                    exit;
                }

                $parent = null;

                if ( !empty($question->parent) )
                {
                    $parent = $this->questionService->findQuestionByName($question->parent);
                }

                if ( $question->base == 1 || !$question->removable || !empty($parent) )
                {
                    echo json_encode(array('result' => false));
                    exit;
                }

                $childList = $this->questionService->findQuestionChildren($question->name);

                $deleteList = array();
                $deleteQuestionNameList = array();

                foreach ( $childList as $child )
                {
                    $deleteList[] = $child->id;
                    $deleteQuestionNameList[$child->name] = $child->name;
                }

                if ( !empty($deleteList) )
                {
                    $this->questionService->deleteQuestion($deleteList);
                }

                if ( $this->questionService->deleteQuestion(array((int) $_POST['questionId'])) )
                {
                    echo json_encode(array('result' => "success", 'message' => OW::getLanguage()->text('admin', 'questions_question_was_deleted'), 'deleteList' => $deleteQuestionNameList));
                    exit;
                }

                echo json_encode(array('result' => false));
                exit;

                break;
            
            
            case 'findNearestSection':
                
                $sectionName = $_POST['sectionName'];
                
                if ( !empty($sectionName) )
                {
                    $section = $this->questionService->findSectionBySectionName($sectionName);
                    
                    if ( empty($section) )
                    {
                        echo json_encode(array('result' => false));
                        exit;
                    }
                    
                    $nearSection = $this->questionService->findNearestSection( $section );
                            
                    if ( empty($nearSection) )
                    {
                        echo json_encode(array('result' => false));
                        exit;
                    }
                    
                    echo json_encode( array(
                        'result' => "success", 
                        'message' => OW::getLanguage()->text('admin', 'questions_delete_section_confirmation_with_move_questions' , array('sectionName' => BOL_QuestionService::getInstance()->getSectionLang($nearSection->name) ))
                    ) );
                    exit;
                }
                
                echo json_encode(array('result' => false));
                exit;

                break;
                
            case 'deleteSection':

                if ( !empty($_POST['sectionName']) && mb_strlen($_POST['sectionName']) > 0 )
                {
                    /*@var $nearSection BOL_QuestionSection*/
                    $nearSection = $this->questionService->findSectionBySectionName($_POST['sectionName']);
                    
                    $moveQuestionsToSection = null;
                    
                    if ( !empty($nearSection) && $nearSection->isDeletable && $this->questionService->deleteSection(htmlspecialchars($_POST['sectionName']), $moveQuestionsToSection) )
                    {
                        $result = array('result' => "success", 'message' => OW::getLanguage()->text('admin', 'questions_section_was_deleted'));
                        
                        if ( !empty($moveQuestionsToSection) )
                        {
                            $result['moveTo'] = $moveQuestionsToSection->name;
                        }
                        
                        echo json_encode($result);
                        exit;
                    }
                }
                echo json_encode(array('result' => "false"));
                exit;
                break;

            case 'DeleteQuestionValue':

                $result = false;

                $questionId = htmlspecialchars($_POST["questionId"]);

                $question = $this->questionService->findQuestionById($questionId);

                $value = (int) $_POST["value"];

                if ( empty($question) || (empty($value) && $value !== 0) )
                {
                    echo json_encode(array('result' => $result));
                    return;
                }

                if ( $this->questionService->deleteQuestionValue($question->name, $value) )
                {
                    $result = true;
                }

                echo json_encode(array('result' => $result));

                break;

            case 'deleteAccountType':

                if ( !empty($_POST['accountType']) && mb_strlen($_POST['accountType']) > 0 )
                {
                    $accountTypes = $this->questionService->findAllAccountTypes();
                    $accountTypeList = array();
                    
                    foreach ( $accountTypes as $key => $account )
                    {
                        if ( $account->name != $_POST['accountType'] )
                        {
                            $accountTypeList[$account->name] = $account->name;
                        }
                    }

                    if ( empty($accountTypeList) )
                    {
                        echo json_encode(array('result' => "false", 'message' => OW::getLanguage()->text('admin', 'questions_cant_delete_last_account_type')));
                        exit;
                    }
                    else if ( $this->questionService->deleteAccountType($_POST['accountType']) )
                    {
                        echo json_encode(array('result' => "success", 'message' => OW::getLanguage()->text('admin', 'questions_account_type_was_deleted')));
                        exit;
                    }
                }

                echo json_encode(array('result' => "false"));
                exit;

                break;

            case 'AddQuestionValues':

                $result = false;

                $questionId = (int) $_POST["questionId"];

                $question = $this->questionService->findQuestionById($questionId);

                $values = !empty($_POST["values"]) && is_array($_POST["values"]) ? $_POST["values"] : array();

                if ( empty($question) || empty($values) )
                {
                    echo json_encode(array('result' => $result));
                    return;
                }

                if ( $this->questionService->updateQuestionValues($question, $values) )
                {
                    $result = true;
                }

                echo json_encode(array('result' => $result));

                break;

           case 'AddAccountType':

                $result = false;

                $name = htmlspecialchars($_POST["accountTypeName"]);
                $roleId = (int) $_POST["role"];

                $accountType = new BOL_QuestionAccountType();
                $accountType->name = $name;
                $accountType->roleId = $roleId;

                $form = new ADMIN_CLASS_AddAccountTypeForm($accountType);

                $result = false;

                if ( $form->isValid($_POST) )
                {
                    $result = $form->process($_POST);
                }

                echo json_encode(array('result' => $result, 'accountTypeName' => $name, 'roleId' => $roleId ));

                break;

            case 'sortAccountType':

                $sortAccountType = json_decode($_POST['accountTypeList'], true);

                $result = false;

                if ( isset($sortAccountType) && is_array($sortAccountType) && count($sortAccountType) > 0 )
                {
                    $result = $this->questionService->reOrderAccountType($sortAccountType);
                }

                echo json_encode(array('result' => $result));

                break;

            case 'sortQuestions':

                $sectionName = htmlspecialchars($_POST['sectionName']);
                $sectionQuestionOrder = json_decode($_POST['questionOrder'], true);

                $check = true;

                if ( !isset($sectionName) )
                {
                    $check = false;
                }

                if ( !isset($sectionQuestionOrder) || !is_array($sectionQuestionOrder) || !count($sectionQuestionOrder) > 0 )
                {
                    $check = false;
                }

                if ( $sectionName === 'no_section' )
                {
                    $sectionName = null;
                }

                $result = false;
                if ( $check )
                {
                    $result = $this->questionService->reOrderQuestion($sectionName, $sectionQuestionOrder);
                }

                echo json_encode(array('result' => $result));

                break;

            case 'sortSection':

                $sectionOrder = json_decode($_POST['sectionOrder'], true);

                if ( !isset($sectionOrder) || !is_array($sectionOrder) || !count($sectionOrder) > 0 )
                {
                    return false;
                }

                $result = $this->questionService->reOrderSection($sectionOrder);

                echo json_encode(array('result' => $result));

                break;

            case 'questionPages':

                $question = $_POST['question'];
                
                $required = $_POST['required'] == 'true';
                $onJoin = $_POST['onJoin'] == 'true';
                $onEdit = $_POST['onEdit'] == 'true';
                $onView = $_POST['onView'] == 'true';
                $onSearch = $_POST['onSearch'] == 'true';
                
                $changed = !empty($_POST['changed']) ? $_POST['changed'] : null;
                
                if ( empty($question) ) 
                {
                    echo json_encode(array('result' => false));
                    exit;
                }

                $questionDto = $this->questionService->findQuestionByName($question);

                if ( !empty($questionDto) )
                {
                    $disableActionList = BOL_QuestionService::getInstance()->getQuestionDisableActionList($questionDto);
                    
                    switch ( $changed )
                    {
                        case 'required':
                            
                            if ( !$disableActionList['disable_required'] )
                            {
                                $questionDto->required = $required;
                            }
                            
                            break;
                        
                        case 'onJoin':
                            
                            if ( !$disableActionList['disable_on_join'] )
                            {
                                $questionDto->onJoin = $onJoin;
                            }
                            
                            break;
                        
                        case 'onEdit':
                            
                            if ( !$disableActionList['disable_on_edit'] )
                            {
                                $questionDto->onEdit = $onEdit;
                            }
                            
                            break;
                        
                        case 'onSearch':
                            
                            if ( !$disableActionList['disable_on_search'] )
                            {
                                $questionDto->onSearch = $onSearch;
                            }
                            
                            break;
                        
                        case 'onView':
                            
                            if ( !$disableActionList['disable_on_view'] )
                            {
                                $questionDto->onView = $onView;
                            }
                            
                            break;
                        
                        default:
                            
                            if ( !$disableActionList['disable_required'] )
                            {
                                $questionDto->required = $required;
                            }

                            if ( !$disableActionList['disable_on_join'] )
                            {
                                $questionDto->onJoin = $onJoin;
                            }

                            if ( !$disableActionList['disable_on_edit'] )
                            {
                                $questionDto->onEdit = $onEdit;
                            }

                            if ( !$disableActionList['disable_on_view'] )
                            {
                                $questionDto->onView = $onView;
                            }

                            if ( !$disableActionList['disable_on_search'] )
                            {
                                $questionDto->onSearch = $onSearch;
                            }
                            
                            break;
                    }
                }

                $this->questionService->saveOrUpdateQuestion($questionDto);

                echo json_encode(json_encode(array('result' => true)));

                break;

            case 'questionAccountTypes':

                $question = $_POST['question'];
                $data = $_POST['data'];

                if ( empty($question) || empty($data) )
                {
                    echo json_encode(array('result' => false));
                    exit;
                }
                
                $questionDto = $this->questionService->findQuestionByName($question);
                
                if ( !empty($questionDto) )
                {

                    $disableActionList = BOL_QuestionService::getInstance()->getQuestionDisableActionList($questionDto);

                    if ( !$disableActionList['disable_account_type'] )
                    {
                        $add = array();
                        $delete = array();

                        foreach ( $data as $accountType => $value )
                        {
                            if ( $value === "true" )
                            {
                                $add[] = $accountType;
                            }
                            else
                            {
                                $delete[] = $accountType;
                            }
                        }

                        if ( !empty($delete) )
                        {
                            BOL_QuestionService::getInstance()->deleteQuestionToAccountType($questionDto->name, $delete);
                        }

                        if ( !empty($add) )
                        {
                            BOL_QuestionService::getInstance()->addQuestionToAccountType($questionDto->name, $add);
                        }
                    }
                }

                echo json_encode(json_encode(array('result' => true)));

                break;

            case 'addSection' :

                if ( empty($_POST['section_name']) )
                {
                    echo json_encode(array('result' => false, 'message' => ''));
                    exit;
                }

                $sectionName = $_POST['section_name'];

                $questionSection = new BOL_QuestionSection();
                $questionSection->name = md5(uniqid());
                $questionSection->sortOrder = ($this->questionService->findLastSectionOrder()) + 1;

                $this->questionService->saveOrUpdateSection($questionSection);

                BOL_LanguageService::getInstance()->addOrUpdateValue(OW::getLanguage()->getCurrentId(), 'base', 'questions_section_' . ( $questionSection->name ) . '_label', htmlspecialchars($sectionName));

                if ( OW::getDbo()->getAffectedRows() > 0 )
                {
                    echo json_encode(array('result' => true, 'message' => OW::getLanguage()->text('admin', 'questions_section_was_added')));
                }

                break;

            case 'addQuestion' :
                /* @var $form ADMIN_CLASS_AddQuestionForm */
                $form = OW::getClassInstance('ADMIN_CLASS_AddQuestionForm', 'qst_add_form', '');
                $form->process();

                break;

            case 'editQuestion' :

                if ( empty($_POST['questionId']) )
                {
                    echo json_encode(array('result' => false, 'errors' => array(), 'message' => OW::getLanguage()->text('admin', 'questions_not_found')));
                    exit;
                }

                $question = BOL_QuestionService::getInstance()->findQuestionById($_POST['questionId']);

                if ( empty($question) || !($question instanceof BOL_Question) )
                {
                    echo json_encode(array('result' => false, 'errors' => array(), 'message' => OW::getLanguage()->text('admin', 'questions_not_found')));
                    exit;
                }

                $form = OW::getClassInstance('ADMIN_CLASS_EditQuestionForm', 'qst_edit_form', '');
                $form->loadQuestionData($question);
                $form->process();

                break;

            default:
        }
        exit;
    }

}
