<?php

require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'controllers' . DS . 'edit.php';

class BASE_CTRL_CompleteProfile extends OW_ActionController
{
    protected $questionService;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        
        $this->setPageHeading(OW::getLanguage()->text('base', 'complete_your_profile_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('base', 'complete_profile'));
        $item->setUrl(OW::getRouter()->urlForRoute("base.complete_required_questions"));
        $item->setKey('complete_profile');
        $item->setOrder(1);
        
        $masterpage = OW::getDocument()->getMasterPage();

        $masterpage = OW::getDocument()->getMasterPage();
        
        if ( !empty($masterpage) && method_exists($masterpage, 'getMenu') )
        {
            $menu = $masterpage->getMenu('main');

            if ( !empty($menu) )
            {
                $menu->setMenuItems(array($item));
            }
        }
    }

    public function fillAccountType( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        $user = OW::getUser()->getUserObject();
        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

        if ( !empty($accountType) )
        {
            throw new Redirect404Exception();
        }

        $event = new OW_Event( OW_EventManager::ON_BEFORE_USER_COMPLETE_ACCOUNT_TYPE, array( 'user' => $user ) );
        OW::getEventManager()->trigger($event);
        
        $accounts = $this->getAccountTypes();
        
        if ( count($accounts) == 1 )
        {
            $accountTypeList = array_keys($accounts);
            $firstAccountType = reset($accountTypeList);
            $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($firstAccountType);

            if ( $accountType )
            {
                $user->accountType = $firstAccountType;
                BOL_UserService::getInstance()->saveOrUpdate($user);
                //BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', time(), $user->getId());
                $this->redirect(OW::getRouter()->urlForRoute('base_default_index'));
            }
        }

        $form = new Form('accountTypeForm');

        $joinAccountType = new Selectbox('accountType');
        $joinAccountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
        $joinAccountType->setRequired();
        $joinAccountType->setOptions($accounts);
        $joinAccountType->setHasInvitation(false);

        $form->addElement($joinAccountType);

        $submit = new Submit('submit');
        $submit->addAttribute('class', 'ow_button ow_ic_save');
        $submit->setValue(OW::getLanguage()->text('base', 'continue_button'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();            

                $this->saveRequiredQuestionsData($data, $user->id);            
                
            }
        }
        else
        {
            OW::getDocument()->addOnloadScript(" OW.info(".  json_encode(OW::getLanguage()->text('base', 'complete_profile_info')).") ");
        }
        
        $this->addForm($form);
    }

    public function fillRequiredQuestions( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $user = OW::getUser()->getUserObject();

        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

        if ( empty($accountType) )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();
        
        $event = new OW_Event( OW_EventManager::ON_BEFORE_USER_COMPLETE_PROFILE, array( 'user' => $user ) );
        OW::getEventManager()->trigger($event);
        
        // -- Edit form --

        $form = new EditQuestionForm('requiredQuestionsForm', $user->id);
        $form->setId('requiredQuestionsForm');

        $editSubmit = new Submit('submit');
        $editSubmit->addAttribute('class', 'ow_button ow_ic_save');

        $editSubmit->setValue($language->text('base', 'continue_button'));

        $form->addElement($editSubmit);

        $questions = $this->questionService->getEmptyRequiredQuestionsList($user->id);

        if ( empty($questions) )
        {
            $this->redirect(OW::getRouter()->urlForRoute('base_default_index'));
        }

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

        //$questionData = $this->questionService->getQuestionData(array($user->id), $questionNameList);

        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $form->addQuestions($questions, $questionValues, array());

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $this->saveRequiredQuestionsData($form->getValues(), $user->id);
            }
        }
        else
        {
            OW::getDocument()->addOnloadScript(" OW.info(".  json_encode(OW::getLanguage()->text('base', 'complete_profile_info')).") ");
        }

        $this->addForm($form);

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
                'formName' => $form->getName(),
                'responderUrl' => OW::getRouter()->urlFor("BASE_CTRL_Edit", "ajaxResponder"))) . ",
                " . UTIL_Validator::EMAIL_PATTERN . ", " . UTIL_Validator::USER_NAME_PATTERN . ", " . $user->id . " ); ";

        OW::getDocument()->addOnloadScript($onLoadJs);

        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "base_field_validators.js");
    }

    protected function saveRequiredQuestionsData($data, $userId)
    {
        // save user data
        if ( !empty($userId) )
        {
            if ( $this->questionService->saveQuestionsData($data, $userId) )
            {
                OW::getFeedback()->info(OW::getLanguage()->text('base', 'edit_successfull_edit'));
                
                $event = new OW_Event(OW_EventManager::ON_AFTER_USER_COMPLETE_PROFILE, array( 'userId' => $userId ));
                    
                OW::getEventManager()->trigger($event);
                //BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', time(), $userId);
                $this->redirect(OW::getRouter()->urlForRoute('base_default_index'));
            }
            else
            {
                OW::getFeedback()->info(OW::getLanguage()->text('base', 'edit_edit_error'));
            }
        }
        else
        {
            OW::getFeedback()->info(OW::getLanguage()->text('base', 'edit_edit_error'));
        }
    }

    protected function getAccountTypes()
    {
        // get available account types from DB
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

        $accounts = array();

        /* @var $value BOL_QuestionAccount */
        foreach ( $accountTypes as $key => $value )
        {
            $accounts[$value->name] = OW::getLanguage()->text('base', 'questions_account_type_' . $value->name);
        }

        return $accounts;
    }
}
