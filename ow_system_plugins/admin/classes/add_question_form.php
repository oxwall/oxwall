<?php

require_once OW_DIR_SYSTEM_PLUGIN . 'admin' . DS . 'classes' . DS . 'form_fields.php';

class ADMIN_CLASS_AddQuestionForm extends Form
{
    public  $presentations2types = array();
    public  $questionConfigs = array();
    protected $qstColumnCountValues;
    protected $presentations2FormElements = array();
    protected $configToPresentation = array();
    protected $questionService;

    public function __construct( $name, $responderUrl )
    {
        parent::__construct( $name );
        $this->questionService = BOL_QuestionService::getInstance();
        $this->setAjax();
        $this->setAction( $responderUrl );

        $this->presentations2types = $this->questionService->getPresentations();
        $presentationConfigList = BOL_QuestionService::getInstance()->getAllConfigs();

        $this->questionConfigs = array();
        $this->configToPresentation = array();
        
        foreach ( $presentationConfigList as $config )
        {
            /* @var $config BOL_QuestionConfig */
            $this->questionConfigs[$config->name] = $config;
            $this->configToPresentation[$config->questionPresentation][$config->name] = $config;
        }

        unset($this->presentations2types[BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD]);
        unset($this->presentations2types[BOL_QuestionService::QUESTION_PRESENTATION_RANGE]);

        $this->qstColumnCountValues = array();
        
        for ( $i = 1; $i <= 5; $i++ )
        {
            $this->qstColumnCountValues[$i] = $i;
        }

        $this->bindJsFunction('success', ' function (result) {
            if ( result.result )
            {
                window.location.reload();
            }
            else
            {
                if ( result.message )
                {
                    OW.error(result.message);
                }
                
                /* if ( result.errors )
                {
                    each( result.errors, function( key, item ) {

                        if ( item )
                        {
                            var element = window.owForms['.  json_encode($name).'].getElement(key);

                            if ( element )
                            {
                                element.showError(item);
                            }
                        }

                    } );
                } */
            }

            
        } ');

        $this->init();
    }

    protected function getPresentations2types()
    {
        return $this->presentations2types;
    }

    public function getPresentations2FormElements()
    {
        if ( !empty($this->presentations2FormElements) )
        {
            return $this->presentations2FormElements;
        }

        $displayFieldList = array(
            'qst_name' => true,
            'qst_description' => true,
            'qst_section' => true,
            'qst_account_type' => true,
            'qst_answer_type' => true,
            'qst_possible_values' => true,
            'qst_infinite_possible_values' => true,
            'qst_column_count' => true,
            'qst_required' => true,
            'qst_on_sign_up' => true,
            'qst_on_edit' => true,
            'qst_on_view' => true,
            'qst_on_search' => true
        );

        //$configToPresentation = array();
        /*@var $config BOL_QuestionConfig */
        foreach ( $this->questionConfigs as $config )
        {
            $displayFieldList[$config->name] = false;
            //$configToPresentation[$config->questionPresentation][$config->name] = $config;
        }

        $columnCountPresentation = array(
            BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX,
            BOL_QuestionService::QUESTION_PRESENTATION_RADIO,
            BOL_QuestionService::QUESTION_PRESENTATION_SELECT
        );

        $possibleValuesTypeList = array(
            BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT,
            BOL_QuestionService::QUESTION_VALUE_TYPE_MULTISELECT
        );

        $infinitePossibleValuesTypeList = array(
            BOL_QuestionService::QUESTION_VALUE_TYPE_FSELECT,
        );

        $result = array();

        foreach ( $this->presentations2types as $presentation => $type )
        {
            $result[$presentation] = $displayFieldList;

            if ( !empty($this->configToPresentation[$presentation]) )
            {
                foreach ( $this->configToPresentation[$presentation] as $config )
                {
                    $result[$presentation][$config->name] = true;
                }
            }

            if ( !in_array( $presentation, $columnCountPresentation ) )
            {
                $result[$presentation]['qst_column_count'] = false;
            }

            if ( !in_array( $type, $possibleValuesTypeList ) )
            {
                $result[$presentation]['qst_possible_values'] = false;
            }

            if ( !in_array( $type, $infinitePossibleValuesTypeList ) )
            {
                $result[$presentation]['qst_infinite_possible_values'] = false;
            }
        }

        $this->presentations2FormElements = $result;

        return $result;
    }

    protected function init( $params = array() )
    {
        $accountTypes = $this->questionService->findAllAccountTypes();

        $serviceLang = BOL_LanguageService::getInstance();
        $language = OW::getLanguage();
        $currentLanguageId = OW::getLanguage()->getCurrentId();

        $accounts = array();

        /* @var $value BOL_QuestionAccount */
        foreach ( $accountTypes as $value )
        {
            $accounts[$value->name] = $this->questionService->getAccountTypeLang($value->name);
        }

        $sections = $this->questionService->findSortedSectionList();

        // need to hide sections select box
        if ( empty($sections) )
        {
            $this->assign('no_sections', true);
        }

        $sectionsArray = array();

        /* @var $section BOL_QuestionSection */
        foreach ( $sections as $section )
        {
            $sectionsArray[$section->name] = $language->text('base', 'questions_section_' . $section->name . '_label');
        }

        $event = new OW_Event('base.question.add_question_form.on_get_available_sections', $sectionsArray, $sectionsArray);
        OW::getEventManager()->trigger($event);
        
        $sectionsArray = $event->getData();
        
        $presentationList = array_keys($this->presentations2types);

        $presentations = array();
        $presentationsLabel = array();

        foreach ( $presentationList as $item )
        {
            $presentations[$item] = $item;
            $presentationsLabel[$item] = $language->text('base', 'questions_question_presentation_' . $item . '_label');
        }
        
        $presentation = $presentationList[0];

        if ( isset($_POST['qst_answer_type']) && isset($presentations[$_POST['qst_answer_type']]) )
        {
            $presentation = $presentations[$_POST['qst_answer_type']];
        }
        
        $qstName = new TextField('qst_name');
        $qstName->setLabel($language->text('admin', 'questions_question_name_label'));
        //$qstName->addValidator(new StringValidator(0, 24000));
        $qstName->setRequired();

        $this->addElement($qstName);

        $qstName = new TextField('qst_description');
        $qstName->setLabel($language->text('admin', 'questions_question_description_label'));
        //$qstName->addValidator(new StringValidator(0, 24000));

        $this->addElement($qstName);

        if ( count($accountTypes) > 1 )
        {
            $qstAccountType = new CheckboxGroup('qst_account_type');
            $qstAccountType->setLabel($language->text('admin', 'questions_for_account_type_label'));
            $qstAccountType->setRequired();
            $qstAccountType->setDescription($language->text('admin', 'questions_for_account_type_description'));
            $qstAccountType->setOptions($accounts);

            $this->addElement($qstAccountType);
        }

        if ( !empty($sectionsArray) )
        {
            $qstSection = new Selectbox('qst_section');
            $qstSection->setLabel($language->text('admin', 'questions_question_section_label'));
            $qstSection->setOptions($sectionsArray);
            $qstSection->setHasInvitation(false);

            $this->addElement($qstSection);
        }

        $qstAnswerType = new Selectbox('qst_answer_type');
        $qstAnswerType->setLabel($language->text('admin', 'questions_answer_type_label'));
        $qstAnswerType->addAttribute('class', $qstAnswerType->getName());
        $qstAnswerType->setOptions($presentationsLabel);
        $qstAnswerType->setRequired();
        $qstAnswerType->setHasInvitation(false);
        $qstAnswerType->setValue($presentation);

        $this->addElement($qstAnswerType);

        $qstPossibleValues = new addValueField('qst_possible_values');
        $qstPossibleValues->setLabel($language->text('admin', 'questions_possible_values_label'));
        $qstPossibleValues->setDescription($language->text('admin', 'questions_possible_values_description'));
        $this->addElement($qstPossibleValues);

        $qstInfinitePossibleValues = new infiniteValueField('qst_infinite_possible_values');
        $qstInfinitePossibleValues->setLabel($language->text('admin', 'questions_infinite_possible_values_label'));
        $qstInfinitePossibleValues->setDescription($language->text('admin', 'questions_infinite_possible_values_description'));
        $this->addElement($qstInfinitePossibleValues);

        $configList = $this->questionConfigs;

        foreach ( $configList as $config )
        {
            $className = $config->presentationClass;

            /* @var $qstConfig OW_FormElement */
            $qstConfig = OW::getClassInstance($className, $config->name);
            $qstConfig->setLabel($language->text('admin', 'questions_config_' . ($config->name) . '_label'));

            if ( !empty($config->description) )
            {
                $qstConfig->setDescription($config->description);
            }

            $this->addElement($qstConfig);
        }

        $qstColumnCount = new Selectbox('qst_column_count');
        $qstColumnCount->addAttribute('class', $qstColumnCount->getName());
        $qstColumnCount->setLabel($language->text('admin', 'questions_columns_count_label'));
        $qstColumnCount->setOptions($this->qstColumnCountValues);
        $qstColumnCount->setValue(1);

        $this->addElement($qstColumnCount);
        
        $qstRequired = new CheckboxField('qst_required');
        $qstRequired->setLabel($language->text('admin', 'questions_required_label'));
        $qstRequired->setDescription($language->text('admin', 'questions_required_description'));

        $this->addElement($qstRequired);

        $qstOnSignUp = new CheckboxField('qst_on_sign_up');
        $qstOnSignUp->setLabel($language->text('admin', 'questions_on_sing_up_label'));
        $qstOnSignUp->setDescription($language->text('admin', 'questions_on_sing_up_description'));

        $this->addElement($qstOnSignUp);

        $qstOnEdit = new CheckboxField('qst_on_edit');
        $qstOnEdit->setLabel($language->text('admin', 'questions_on_edit_label'));
        $qstOnEdit->setDescription($language->text('admin', 'questions_on_edit_description'));

        $this->addElement($qstOnEdit);

        $qstOnView = new CheckboxField('qst_on_view');
        $qstOnView->setLabel($language->text('admin', 'questions_on_view_label'));
        $qstOnView->setDescription($language->text('admin', 'questions_on_view_description'));

        $this->addElement($qstOnView);

        $qstOnSearch = new CheckboxField('qst_on_search');
        $qstOnSearch->setLabel($language->text('admin', 'questions_on_search_label'));
        $qstOnSearch->setDescription($language->text('admin', 'questions_on_search_description'));

        $this->addElement($qstOnSearch);

        $qstSubmit = new Submit('qst_submit');
        $qstSubmit->setValue($language->text('admin', 'save_btn_label'));
        $qstSubmit->addAttribute('class', 'ow_button ow_ic_save');
        $this->addElement($qstSubmit);

        $this->addElement($qstSubmit);
    }
        
    protected function prepareData( $data )
    {
        $presentation = htmlspecialchars($data['qst_answer_type']);

        $list = $this->getPresentations2FormElements();

        if ( empty($list[$presentation]) )
        {
            throw new InvalidArgumentException('Undefined presentation');
        }

        foreach ( $data as $key => $value )
        {
            if ( isset($list[$presentation][$key]) && !$list[$presentation][$key] )
            {
                unset($data[$key]);
            }
        }

        return $data;
    } 

    public function process()
    {
        if ( OW_Request::getInstance()->isPost() )
        {
            $data = $this->prepareData($_POST);
            
            if ( $this->isValid($data) )
            {
                $data = $this->getValues();

                if ( !isset($data['qst_section']) )
                {
                    $data['qst_section'] = null;
                }
                else
                {
                    $data['qst_section'] = htmlspecialchars(trim($data['qst_section']));
                }
                
                $presentations = BOL_QuestionService::getInstance()->getPresentations();

                // insert question
                $question = new BOL_Question();

                $question->name = 'field_'.md5(uniqid());

                $question->required = !empty($data['qst_required']) ? (int) $data['qst_required'] : 0;
                $question->onJoin = !empty($data['qst_on_sign_up']) ? (int) $data['qst_on_sign_up'] : 0;
                $question->onEdit = !empty($data['qst_on_edit']) ? (int) $data['qst_on_edit'] : 0;
                $question->onSearch = !empty($data['qst_on_search']) ? (int) $data['qst_on_search'] : 0;
                $question->onView = !empty($data['qst_on_view']) ? (int) $data['qst_on_view'] : 0;
                $question->presentation = !empty($data['qst_answer_type']) ? htmlspecialchars($data['qst_answer_type']) : '';
                $question->type = !empty($data['qst_answer_type']) ? htmlspecialchars($presentations[trim($data['qst_answer_type'])]) : '';

                $presentations2FormElements = $this->getPresentations2FormElements();
                
                
                if ( $presentations2FormElements[$question->presentation]['qst_column_count'] && !empty($data['qst_column_count']) )
                {
                    $question->columnCount = (int) $data['qst_column_count'];
                }

                if ( !empty($data['qst_section']) )
                {
                    $section = $this->questionService->findSectionBySectionName(htmlspecialchars(trim($data['qst_section'])));
                    if ( isset($section) )
                    {
                        $question->sectionName = $section->name;
                    }
                    else
                    {
                        $question->sectionName = null;
                    }
                }

                $question->sortOrder = ( (int) BOL_QuestionService::getInstance()->findLastQuestionOrder($question->sectionName) ) + 1;

                // save question configs
                $configs = array();

                if ( !empty($configToPresentation[$question->presentation]) )
                {
                    foreach ( $configToPresentation[$question->presentation] as $config )
                    {
                        if ( isset($data[$config->name]) )
                        {
                            $configs[$config->name] = $data[$config->name];
                        }
                    }
                }

                $question->custom = json_encode($configs);

                if (!empty($data['qst_infinite_possible_values']))
                {
                    $questionValues = $data['qst_infinite_possible_values'];
                }
                else
                {
                    $questionValues = !empty($data['qst_possible_values']) ? $data['qst_possible_values'] : array();
                }

                $name = !empty($data['qst_name']) ? trim($data['qst_name']) : '';
                $description = !empty($data['qst_description']) ? htmlspecialchars(trim($data['qst_description'])) : '';
                
                $this->questionService->createQuestion($question, $name, $description, $questionValues, true);
                
                if ( !empty($data['qst_account_type']) && is_array($data['qst_account_type']) )
                {
                    $this->questionService->addQuestionToAccountType($question->name, $data['qst_account_type']);
                }

                if ( !empty($_POST['valuesStorage']) )
                {
                    $langValues = json_decode($_POST['valuesStorage'], true);

                    if ( !empty($langValues) && is_array($langValues) )
                    {
                        $languages = BOL_LanguageService::getInstance()->getLanguages();
                        
                        foreach ( $langValues as $value => $languageData )
                        {
                            foreach ( $languages as $lang )
                            {
                                if ( isset($languageData[$lang->id]) )
                                {
                                    BOL_LanguageService::getInstance()->addOrUpdateValue($lang->id, 'base', 'questions_question_' . ($question->name) . '_value_' . $value, $languageData[$lang->id], false);
                                }
                            }
                        }
                    }
                    
                }

                BOL_LanguageService::getInstance()->generateCache( OW::getLanguage()->getCurrentId() );

                OW::getFeedback()->info(OW::getLanguage()->text( 'admin', 'questions_add_question_message' ));

                echo json_encode( array( 'result' => true, 'errors' => array()) );

            }
            else
            {
                echo json_encode( array( 'result' => false, 'errors' => $this->getErrors(), 'message' => OW::getLanguage()->text( 'admin', 'questions_add_question_error' ) ) );
            }
            exit;

            //OW::getSession()->set(self::ADD_QUESTION_SESSION_VAR, $_POST);
        }
    }
}
