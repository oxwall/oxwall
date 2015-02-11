<?php

class ADMIN_CLASS_EditQuestionForm extends ADMIN_CLASS_AddQuestionForm
{
    protected $question = null;
    //protected $disabledElements = array();

    public function __construct( $name, $responderUrl )
    {
        parent::__construct( $name, $responderUrl );
    }
    
    public function loadQuestionData( BOL_Question $question )
    {
        /*@var $question BOL_Question */
        $this->question = $question;
        
        if ( empty($question) || !($question instanceof BOL_Question) )
        {
            return;
        }

        $accountTypeDtoList = BOL_QuestionService::getInstance()->findAccountTypeListByQuestionName($question->name);
        
        $accountTypeValues = array();
        if ( !empty($accountTypeDtoList) )
        {
            foreach ( $accountTypeDtoList as $dto )
            {
                /* @var $dto BOL_QuestionToAccountType */
                $accountTypeValues[$dto->accountType] = $dto->accountType;
            }
        }
        
        $valuesDto = BOL_QuestionService::getInstance()->findQuestionValues($question->name);
        $values = array();
        /* @var $valueDto BOL_QuestionValue */
        foreach ( $valuesDto as $valueDto )
        {
            $values[$valueDto->value] = BOL_QuestionService::getInstance()->getQuestionValueLang($question->name, $valueDto->value);
        }

        if( $question->presentation == BOL_QuestionService::QUESTION_PRESENTATION_RANGE )
        {
            $this->disableFormElement('qst_answer_type');
            $this->presentations2types[BOL_QuestionService::QUESTION_PRESENTATION_RANGE] = BOL_QuestionService::QUESTION_VALUE_TYPE_TEXT;
        }

        $presentationsLabel = array();

        foreach ( $this->presentations2types as $key => $item )
        {
            if ( $question->type == $item )
            {
                $presentationsLabel[$key] = OW::getLanguage()->text('base', 'questions_question_presentation_' . $key . '_label');
            }
        }

        $this->deleteElement('qst_name');
        $this->deleteElement('qst_description');

        /* @var $question BOL_Question*/
        $this->getElement('qst_section')->setValue($question->sectionName);

        if ( $this->getElement('qst_account_type') )
        {
            $this->getElement('qst_account_type')->setValue($accountTypeValues);
            $this->getElement('qst_account_type')->setRequired(false);
        }

        $this->getElement('qst_answer_type')->setOptions($presentationsLabel);
        $this->getElement('qst_answer_type')->setValue($question->presentation);

        $this->getElement('qst_possible_values')->setValue($values);
        $this->getElement('qst_column_count')->setValue($question->columnCount);
        $this->getElement('qst_required')->setValue($question->required);
        $this->getElement('qst_on_sign_up')->setValue($question->onJoin);
        $this->getElement('qst_on_edit')->setValue($question->onEdit);
        $this->getElement('qst_on_view')->setValue($question->onView);
        $this->getElement('qst_on_search')->setValue($question->onSearch);

        $element = new HiddenField('questionId');
        $element->setValue($question->id);
        
        $this->addElement($element);

        $presentationConfigList = !empty($this->configToPresentation[$question->presentation]) ? $this->configToPresentation[$question->presentation] : array();
        $presentationConfigValues = json_decode($question->custom, true);

        foreach ( $presentationConfigList as $config )
        {
            $element = $this->getElement($config->name);

            if ( !empty($element) && !empty($presentationConfigValues[$config->name]) )
            {
                $element->setValue($presentationConfigValues[$config->name]);
            }
        }

        $disableActionList = BOL_QuestionService::getInstance()->getQuestionDisableActionList($question);

        $this->disableFormElements($disableActionList);
    }

    public function disableFormElements( $disableActionList )
    {
        if ( empty($disableActionList) )
        {
            return;
        }

        foreach( $disableActionList as $key => $value )
        {
            if ( $value )
            {
                switch($key)
                {
                    case 'disable_account_type' :
                        $this->disableFormElement('qst_account_type');
                        break;
                    case 'disable_answer_type' :
                        $this->disableFormElement('qst_answer_type');
                        break;
                    case 'disable_presentation' :
                        $this->disableFormElement('presentation');
                        break;
                    case 'disable_column_count' :
                        $this->disableFormElement('qst_column_count');
                        break;
                    case 'disable_display_config' :
                        
                        foreach ( $this->configToPresentation as $configs )
                        {
                            foreach ( $configs as $config )
                            {
                                $this->deleteElement($config->name);
                            }
                        }

                        break;
                    case 'disable_required' :
                        $this->disableFormElement('qst_required');
                        break;
                    case 'disable_on_join' :
                        $this->disableFormElement('qst_on_sign_up');
                        break;
                    case 'disable_on_view' :
                        $this->disableFormElement('qst_on_view');
                        break;
                    case 'disable_on_search' :
                        $this->disableFormElement('qst_on_search');
                        break;
                    case 'disable_on_edit' :
                        $this->disableFormElement('qst_on_edit');
                        break;
                    case 'disable_possible_values' :
                        $this->disableFormElement('qst_possible_values');
                        break;

                }
            }
        }
    }

    protected function disableFormElement( $name )
    {
        $element = $this->getElement($name);

        if ( !empty($element) )
        {
            if ( method_exists($element, 'setDisabled') )
            {
                $element->setDisabled();
            }
            else
            {
                $element->addAttribute('disabled', 'disabled');
            }
        }
    }


    public function process()
    {
        if ( OW_Request::getInstance()->isPost() )
        {
            $data = $this->prepareData($_POST);

            if ( $this->isValid($data) )
            {
                $data = $this->getValues();
                
                // --------------------------------------------

                if( !$this->getElement('qst_answer_type')->getAttribute('disabled') )
                {
                    $this->question->presentation = htmlspecialchars($data['qst_answer_type']);
                }
                

                foreach ( $this->getElements() as $element )
                {
                    if ( !$element->getAttribute('disabled') )
                    {
                        switch ( $element->getName() )
                        {
                            case 'qst_required':
                                $this->question->required = isset($data['qst_required']) ? 1 : 0;
                                break;

                            case 'qst_on_sign_up':
                                $this->question->onJoin = isset($data['qst_on_sign_up']) ? 1 : 0;
                                break;

                            case 'qst_on_edit':
                                $this->question->onEdit = isset($data['qst_on_edit']) ? 1 : 0;
                                break;

                            case 'qst_on_search':
                                $this->question->onSearch = isset($data['qst_on_search']) ? 1 : 0;
                                break;

                            case 'qst_on_view':
                                $this->question->onView = isset($data['qst_on_view']) ? 1 : 0;
                                break;

                            case 'qst_column_count':
                                $presentations2FormElements = $this->getPresentations2FormElements();
                                
                                $this->question->columnCount = 1;
                                
                                if ( $presentations2FormElements[$this->question->presentation]['qst_column_count'] && !empty($data['qst_column_count']) && (int)$data['qst_column_count'] > 0  )
                                {
                                    $this->question->columnCount = (int) $data['qst_column_count'];
                                }
                                break;

                            case 'qst_section':
                                if ( !empty($data['qst_section']) )
                                {
                                    $section = $this->questionService->findSectionBySectionName(htmlspecialchars(trim($data['qst_section'])));

                                    $sectionName = null;
                                    if ( isset($section) )
                                    {
                                        $sectionName = $section->name;
                                    }

                                    if ( $this->question->sectionName !== $sectionName )
                                    {
                                        $this->question->sectionName = $sectionName;
                                        $this->question->sortOrder = ( (int) BOL_QuestionService::getInstance()->findLastQuestionOrder($this->question->sectionName) ) + 1;
                                    }
                                }
                                break;

                            case 'qst_account_type':
                                if ( $data['qst_account_type'] !== null )
                                {
                                    if ( !empty($data['qst_account_type']) && is_array($data['qst_account_type']) )
                                    {
                                        $this->questionService->deleteQuestionToAccountTypeByQuestionName($this->question->name);
                                        $this->questionService->addQuestionToAccountType($this->question->name, $data['qst_account_type']);
                                    }
                                }
                                break;
                        }
                    }
                }

                // -----------------------

//                if ( !$disableActionList['disable_display_config'] )
//                {
                    // save question configs
                    $configs = array();

                    $presentationConfigList = !empty($this->configToPresentation[$this->question->presentation]) ? $this->configToPresentation[$this->question->presentation] : array();

                    foreach ( $presentationConfigList as $config )
                    {
                        if ( isset($data[$config->name]) )
                        {
                            $configs[$config->name] = $data[$config->name];
                        }
                    }

                   $this->question->custom = json_encode($configs);
//                }

                $this->questionService->saveOrUpdateQuestion($this->question);
                
                $updated = false;
                if ( OW::getDbo()->getAffectedRows() > 0 )
                {
                    $updated = true;
                    $list = $this->questionService->findQuestionChildren($this->question->name);

                    /* @var BOL_Question $child */
                    foreach ( $list as $child )
                    {
                        $child->columnCount =  $this->question->columnCount;
                        $this->questionService->saveOrUpdateQuestion($child);
                    }
                }

                $this->questionService = BOL_QuestionService::getInstance();

                //update question values sort
                
                /* if ( !empty($_POST['qst_possible_values']) )
                {
                    $values = json_decode($_POST['qst_possible_values'], true);
                    
                    if ( !empty($values['deletedValues']) && is_array($values['deletedValues']) )
                    {
                        $this->questionService->deleteQuestionValues($this->question->name, $values['deletedValues']);
                    }
                } */
                
                if ( !empty($data['qst_possible_values']) )
                {
                    if ( $this->questionService->updateQuestionValues($this->question->name, $data['qst_possible_values']) )
                    {
                        $updated = true;
                    }
                }

                $message = OW::getLanguage()->text('admin', 'questions_question_was_not_updated_message');

                if ( $updated )
                {
                    $message = OW::getLanguage()->text('admin', 'questions_update_question_message');
                }

                OW::getFeedback()->info($message);

                echo json_encode( array( 'result' => true, 'errors' => array(), 'message' => $message ) );
            }
            else
            {
                echo json_encode( array( 'result' => false, 'errors' => $this->getErrors(), 'message' => OW::getLanguage()->text( 'admin', 'questions_update_error' ) ) );
            }
            exit;

        }
    }
}
?>
