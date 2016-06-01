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
 * Question Service
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionService
{
    const EVENT_ON_QUESTION_DELETE = 'base.event.on_question_delete';
    const EVENT_ON_ACCOUNT_TYPE_DELETE = 'base.event.on_account_type_delete';
    const EVENT_ON_ACCOUNT_TYPE_ADD = 'base.event.on_account_type_add';
    const EVENT_ON_ACCOUNT_TYPE_REORDER = 'base.event.on_account_type_reorder';
    const EVENT_AFTER_ADD_QUESTION_VALUE = 'base.event.afret_question_value_add';
    const EVENT_AFTER_UPDATE_QUESTION_VALUE = 'base.event.afret_question_value_update';
    const EVENT_AFTER_DELETE_QUESTION_VALUE = 'base.event.afret_question_value_delete';
    const EVENT_BEFORE_ADD_QUESTIONS_TO_NEW_ACCOUNT_TYPE = 'base.event.on_before_add_questions_to_new_account_type';

    const EVENT_ON_GET_QUESTION_LANG = 'base.event.on_get_question_lang';

    /* account types */
    const ALL_ACCOUNT_TYPES = 'all';

    /* langs */
    const QUESTION_LANG_PREFIX = 'base';

    const LANG_KEY_TYPE_QUESTION_LABEL = 'label';
    const LANG_KEY_TYPE_QUESTION_DESCRIPTION = 'description';
    const LANG_KEY_TYPE_QUESTION_SECTION = 'section';
    const LANG_KEY_TYPE_QUESTION_VALUE = 'value';
    const LANG_KEY_TYPE_ACCOUNT_TYPE = 'account_type';

    /* date field display formats */
    const DATE_FIELD_FORMAT_MONTH_DAY_YEAR = 'mdy';
    const DATE_FIELD_FORMAT_DAY_MONTH_YEAR = 'dmy';

    /* field store types */
    const QUESTION_VALUE_TYPE_TEXT = 'text';
    const QUESTION_VALUE_TYPE_SELECT = 'select';
    const QUESTION_VALUE_TYPE_FSELECT = 'fselect';
    const QUESTION_VALUE_TYPE_MULTISELECT = 'multiselect';
    const QUESTION_VALUE_TYPE_DATETIME = 'datetime';
    const QUESTION_VALUE_TYPE_BOOLEAN = 'boolean';

    /* field presentation types */
    const QUESTION_PRESENTATION_TEXT = 'text';
    const QUESTION_PRESENTATION_TEXTAREA = 'textarea';
    const QUESTION_PRESENTATION_SELECT = 'select';
    const QUESTION_PRESENTATION_FSELECT = 'fselect';
    const QUESTION_PRESENTATION_DATE = 'date';
    const QUESTION_PRESENTATION_BIRTHDATE = 'birthdate';
    const QUESTION_PRESENTATION_AGE = 'age';
    const QUESTION_PRESENTATION_RANGE = 'range';
    const QUESTION_PRESENTATION_LOCATION = 'location';
    const QUESTION_PRESENTATION_CHECKBOX = 'checkbox';
    const QUESTION_PRESENTATION_MULTICHECKBOX = 'multicheckbox';
    const QUESTION_PRESENTATION_RADIO = 'radio';
    const QUESTION_PRESENTATION_URL = 'url';
    const QUESTION_PRESENTATION_PASSWORD = 'password';

    /* field presentation configs */
    const QUESTION_CONFIG_DATE_RANGE = 'dateRange';

    /**
     * @var BOL_QuestionDao
     */
    private $questionDao;
    /**
     * @var BOL_QuestionValueDao
     */
    private $valueDao;
    /**
     * @var BOL_QuestionSectionDao
     */
    private $sectionDao;
    /**
     * @var BOL_QuestionDataDao
     */
    private $dataDao;
    /**
     * @var BOL_QuestionAccountTypeDao
     */
    private $accountDao;

    /**
     * @var BOL_QuestionAccountTypeDao
     */
    private $accountToQuestionDao;

    /**
     * @var BOL_UserService
     */
    private $userService;
    /**
     * @var BOL_QuestionConfigDao
     */
    private $questionConfigDao;

    /**
     * @var int
     */
    private $questionUpdateTime = 0;

    /**
     * @var array
     */
    private $presentations;
    private $questionsBOL = array();
    private $questionsData = array();
    private $presentation2config = array();
    /**
     * Singleton instance.
     *
     * @var BOL_QuestionService
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->questionsBOL['base'] = array();
        $this->questionsBOL['notBase'] = array();

        $this->questionDao = BOL_QuestionDao::getInstance();
        $this->valueDao = BOL_QuestionValueDao::getInstance();
        $this->dataDao = BOL_QuestionDataDao::getInstance();
        $this->sectionDao = BOL_QuestionSectionDao::getInstance();
        $this->accountDao = BOL_QuestionAccountTypeDao::getInstance();
        $this->accountToQuestionDao = BOL_QuestionToAccountTypeDao::getInstance();
        $this->userService = BOL_UserService::getInstance();
        $this->questionConfigDao = BOL_QuestionConfigDao::getInstance();

        // all available presentations are hardcoded here
        $this->presentations = array(
            self::QUESTION_PRESENTATION_TEXT => self::QUESTION_VALUE_TYPE_TEXT,
            self::QUESTION_PRESENTATION_SELECT => self::QUESTION_VALUE_TYPE_SELECT,
            self::QUESTION_PRESENTATION_FSELECT => self::QUESTION_VALUE_TYPE_FSELECT,
            self::QUESTION_PRESENTATION_TEXTAREA => self::QUESTION_VALUE_TYPE_TEXT,
            self::QUESTION_PRESENTATION_CHECKBOX => self::QUESTION_VALUE_TYPE_BOOLEAN,
            self::QUESTION_PRESENTATION_RADIO => self::QUESTION_VALUE_TYPE_SELECT,
            self::QUESTION_PRESENTATION_MULTICHECKBOX => self::QUESTION_VALUE_TYPE_MULTISELECT,
            self::QUESTION_PRESENTATION_DATE => self::QUESTION_VALUE_TYPE_DATETIME,
            self::QUESTION_PRESENTATION_BIRTHDATE => self::QUESTION_VALUE_TYPE_DATETIME,
            self::QUESTION_PRESENTATION_AGE => self::QUESTION_VALUE_TYPE_DATETIME,
            self::QUESTION_PRESENTATION_RANGE => self::QUESTION_VALUE_TYPE_TEXT, // Now we don't use this presentation
            self::QUESTION_PRESENTATION_URL => self::QUESTION_VALUE_TYPE_TEXT,
            self::QUESTION_PRESENTATION_PASSWORD => self::QUESTION_VALUE_TYPE_TEXT
        );
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * Returns all available presentations
     *
     * @return array<string>
     */
    public function getPresentations()
    {
        return $this->presentations;
    }

    /**
     * Returns configs list
     *
     * @return array<string>
     */
    public function getConfigList( $presentation )
    {
        if ( !isset($this->presentation2config[$presentation]) )
        {
            $this->presentation2config[$presentation] = $this->questionConfigDao->getConfigListByPresentation($presentation);
        }

        return $this->presentation2config[$presentation];
    }

    public function getAllConfigs()
    {
        $result = $this->questionConfigDao->getAllConfigs();

        $list = array();

        foreach ( $result as $item )
        {
            $list[$item->questionPresentation] = $item;
        }

        return $list;
    }

     /**
     * @var String
     */
    
    private $birthdayConfig;
    
    /**
     * Returns all available presentations
     *
     * @return array<string>
     */
    public function getPresentationClass( $presentation, $fieldName, $configs = null )
    {
        $event = new OW_Event('base.questions_field_get_label', array(
            'presentation' => $presentation,
            'fieldName' => $fieldName,
            'configs' => $configs,
            'type' => 'edit'
        ));

        OW::getEventManager()->trigger($event);

        $label = $event->getData();

        $class = null;

        $event = new OW_Event('base.questions_field_init', array(
            'type' => 'main',
            'presentation' => $presentation,
            'fieldName' => $fieldName,
            'configs' => $configs
        ));

        OW::getEventManager()->trigger($event);

        $class = $event->getData();

        if ( empty($class) )
        {
            switch ( $presentation )
            {
                case self::QUESTION_PRESENTATION_TEXT :
                    $class = new TextField($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_SELECT :
                case self::QUESTION_PRESENTATION_FSELECT :
                    $class = new Selectbox($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_TEXTAREA :
                    $class = new Textarea($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_CHECKBOX :
                    $class = new CheckboxField($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_RADIO :
                    $class = new RadioField($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_MULTICHECKBOX :
                    $class = new CheckboxGroup($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_BIRTHDATE :
                case self::QUESTION_PRESENTATION_AGE :
                case self::QUESTION_PRESENTATION_DATE :
                    $class = new DateField($fieldName);

                    if ( !empty($configs) && mb_strlen( trim($configs) ) > 0 )
                    {
                        $configsList = json_decode($configs, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinYear($value['from']);
                                $class->setMaxYear($value['to']);
                            }
                        }
                    }

                    $class->addValidator(new DateValidator($class->getMinYear(), $class->getMaxYear()));
                    break;

                case self::QUESTION_PRESENTATION_RANGE :
                    $class = new Range($fieldName);

                    $rangeValidator = new RangeValidator();
                    
                    if ( empty($this->birthdayConfig) )
                    {
                        $birthday = $this->findQuestionByName("birthdate");
                        if ( !empty($birthday) )
                        {
                            $this->birthdayConfig = ($birthday->custom);
                        }
                    }
                    //printVar($this->birthdayConfig);
                    if ( !empty($this->birthdayConfig) && mb_strlen( trim($this->birthdayConfig) ) > 0 )
                    {
                        $configsList = json_decode($this->birthdayConfig, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $rangeValidator->setMinValue(date("Y") - $value['to']);
                                $rangeValidator->setMaxValue(date("Y") - $value['from']);
                                $class->setMinValue(date("Y") - $value['to']);
                                $class->setMaxValue(date("Y") - $value['from']);
                            }
                        }
                    }
                    
                    $class->addValidator($rangeValidator);
                break;

                case self::QUESTION_PRESENTATION_URL :
                    $class = new TextField($fieldName);
                    $class->addValidator(new UrlValidator());
                    break;

                case self::QUESTION_PRESENTATION_PASSWORD :
                    $class = new PasswordField($fieldName);
                    break;
            }
        }

        if ( !empty($label) )
        {
            $class->setLabel($label);
        }

        return $class;
    }

    /**
     * Returns all available presentations
     *
     * @return array<string>
     */
    public function getSearchPresentationClass( $presentation, $fieldName, $configs = array() )
    {
        $event = new OW_Event('base.questions_field_get_label', array(
            'presentation' => $presentation,
            'fieldName' => $fieldName,
            'configs' => $configs,
            'type' => 'edit'
        ));

        OW::getEventManager()->trigger($event);

        $label = $event->getData();

        $class = null;

        $event = new OW_Event('base.questions_field_init', array(
            'type' => 'search',
            'presentation' => $presentation,
            'fieldName' => $fieldName,
            'configs' => $configs
        ));

        OW::getEventManager()->trigger($event);

        $class = $event->getData();

        if ( empty($class) )
        {
            switch ( $presentation )
            {
                case self::QUESTION_PRESENTATION_TEXT :
                case self::QUESTION_PRESENTATION_TEXTAREA :
                    $class = new TextField($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_CHECKBOX :
                    $class = new CheckboxField($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_RADIO :
                case self::QUESTION_PRESENTATION_SELECT :
                case self::QUESTION_PRESENTATION_MULTICHECKBOX :
                    $class = new CheckboxGroup($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_FSELECT :
                    $class = new Selectbox($fieldName);
                    break;

                case self::QUESTION_PRESENTATION_BIRTHDATE :
                case self::QUESTION_PRESENTATION_AGE :
                    $class = new AgeRange($fieldName);
                    
                    if ( !empty($configs) && mb_strlen( trim($configs) ) > 0 )
                    {
                        $configsList = json_decode($configs, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinYear($value['from']);
                                $class->setMaxYear($value['to']);
                            }
                        }
                    }

                    $class->addValidator(new DateValidator($class->getMinYear(), $class->getMaxYear()));
                    
                    break;

                case self::QUESTION_PRESENTATION_RANGE :
                    $class = new Range($fieldName);

                    if ( empty($this->birthdayConfig) )
                    {
                        $birthday = $this->findQuestionByName("birthdate");
                        if ( !empty($birthday) )
                        {
                            $this->birthdayConfig = ($birthday->custom);
                        }
                    }
                    
                    $rangeValidator = new RangeValidator();
                    
                    if ( !empty($this->birthdayConfig) && mb_strlen( trim($this->birthdayConfig) ) > 0 )
                    {
                        $configsList = json_decode($this->birthdayConfig, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinValue(date("Y") - $value['to']);
                                $class->setMaxValue(date("Y") - $value['from']);
                                
                                $rangeValidator->setMinValue(date("Y") - $value['to']);
                                $rangeValidator->setMaxValue(date("Y") - $value['from']);
                            }
                        }
                    }

                    $class->addValidator($rangeValidator);
                    
                    break;

                case self::QUESTION_PRESENTATION_DATE :
                    $class = new DateRange($fieldName);

                    if ( !empty($configs) && mb_strlen( trim($configs) ) > 0 )
                    {
                        $configsList = json_decode($configs, true);
                        foreach ( $configsList as $name => $value )
                        {
                            if ( $name = 'year_range' && isset($value['from']) && isset($value['to']) )
                            {
                                $class->setMinYear($value['from']);
                                $class->setMaxYear($value['to']);
                            }
                        }
                    }

                    $class->addValidator(new DateValidator($class->getMinYear(), $class->getMaxYear()));
                    break;

                case self::QUESTION_PRESENTATION_URL :
                    $class = new TextField($fieldName);
                    $class->addValidator(new UrlValidator());
                    break;
            }

            $value = $this->getQuestionConfig($configs, 'year_range');

            if ( !empty( $value['from'] ) && !empty( $value['to'] ) )
            {
                $class->setMinYear($value['from']);
                $class->setMaxYear($value['to']);
            }
        }

        if ( !empty($label) )
        {
            $class->setLabel($label);
        }

        return $class;
    }

    public function getQuestionConfig( $configString, $configName = null )
    {
        $configsList = array();

        if ( !empty($configString) && mb_strlen( trim($configString) ) > 0 )
        {
            $configsList = json_decode($configString, true);
        }

        if ( !empty($configName) )
        {
            return isset($configsList[$configName]) ? $configsList[$configName] : array();
        }
        else
        {
            return $configsList;
        }
    }

     /**
     * Returns form element for question name
     * Method is used in admin panel.
     *
     * @param string $questionName
     * @param string $presentation
     * @param string $type ( join, edit, search )
     * @return FormElement
     */

    public function getFormElement( $questionName, $presentation = null, $type = 'join' )
    {
        $class = null;

        if( empty($questionName) )
        {
            return $class;
        }

        $question = $this->findQuestionByName($questionName);
        $values = $this->findQuestionsValuesByQuestionNameList(array($questionName));

        return $this->getFormElementByQuestionDto($question, $values, $type);
    }

    public function getFormElementByQuestionDto( BOL_Question $question, array $values, $type = 'join' )
    {
        $class = null;

        if( empty($question) )
        {
            return $class;
        }

        $presentation = $question->presentation;

        if ( empty($presentation) )
        {
            return $class;
        }
        else if ( !empty($this->presentations[$presentation]) && ( $question->type == $this->presentations[$presentation]
            || in_array($question->type, array(self::QUESTION_VALUE_TYPE_SELECT, self::QUESTION_VALUE_TYPE_MULTISELECT) )
            &&  in_array($this->presentations[$presentation], array(self::QUESTION_VALUE_TYPE_SELECT, self::QUESTION_VALUE_TYPE_MULTISELECT) ) ) )
        {
            // ignore
        }
        else
        {
            return $class;
        }

        switch ( $type )
        {
            case 'join':
            case 'edit':
                // @var $class FormElement
                $class = $this->getPresentationClass($presentation, $question->name, $question->custom);
                break;
            case 'search':
                $class = $this->getSearchPresentationClass($presentation, $question->name, $question->custom);
                break;
        }

        if ( !empty($class) )
        {
            $class->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question->name . '_label'));

            if ( in_array($question->type, array(BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT, BOL_QuestionService::QUESTION_VALUE_TYPE_MULTISELECT) ) )
            {
                if ( method_exists($class, 'setColumnCount') )
                {
                    $class->setColumnCount($question->columnCount);
                }

                if ( method_exists($class, 'setOptions') )
                {
                    if ( !empty($values[$question->name]['values']) && is_array($values[$question->name]['values']) )
                    {
                        $valuesArray = array();

                        foreach ( $values[$question->name]['values'] as $value )
                        {
                            $valuesArray[$value->value] = OW::getLanguage()->text( 'base', 'questions_question_' . $value->questionName . '_value_' . ($value->value) );
                        }

                        $class->setOptions($valuesArray);
                    }
                }
            }
        }

        return $class;
    }

    public function findAllQuestions()
    {
        return $this->questionDao->findAll();
    }

    /**
     * Returns fields for provided account type.
     * Method is used in admin panel.
     *
     * @param string $accountType
     * @return array
     */
    public function findAllQuestionsBySectionForAccountType( $accountType )
    {
        $questionList = $this->questionDao->findAllQuestionsWithSectionForAccountType($accountType);

        $list = array();

        foreach ( $questionList as $question )
        {
            $list[$question['sectionName']][] = $question;
        }

        $sections = $this->sectionDao->findSortedSectionList();

        $result = array();

        /* @var $section BOL_QuestionSection */
        foreach ( $sections as $section )
        {
            if ( !$section->isHidden )
            {
                $result[$section->name] = !empty($list[$section->name]) ? $list[$section->name] : array();
            }
        }

        return $result;
    }

    public function createSection($sectionName, $label)
    {
        if ( !$this->findSectionBySectionName($sectionName) )
        {
            // generate a lang value
            $sectionLang = $this->
                    getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_SECTION, $sectionName);

            BOL_LanguageService::getInstance()->replaceLangValue('base', $sectionLang, $label, true);

            // create a section
            $section =  new BOL_QuestionSection();
            $section->name = $sectionName;
            $section->sortOrder = $this->findLastSectionOrder() + 1;
            $section->isHidden = false;
            $section->isDeletable = true;

            $this->saveOrUpdateSection($section);

            return $section;
        }
    }

    /**
     *
     * @param string $accountType
     * @param boolean $baseOnly
     */
    public function findSignUpQuestionsForAccountType( $accountType, $baseOnly = false )
    {
        return $this->questionDao->findSignUpQuestionsForAccountType($accountType, $baseOnly);
    }

    public function findBaseSignUpQuestions()
    {
        return $this->questionDao->findBaseSignUpQuestions();
    }

    public function findEditQuestionsForAccountType( $accountType )
    {
        return $this->questionDao->findEditQuestionsForAccountType($accountType);
    }

    public function findViewQuestionsForAccountType( $accountType )
    {
        return $this->questionDao->findViewQuestionsForAccountType($accountType);
    }

    public function findAllQuestionsForAccountType( $accountType )
    {
        return $this->questionDao->findAllQuestionsForAccountType($accountType);
    }

    public function findRequiredQuestionsForAccountType( $accountType )
    {
        return $this->questionDao->findRequiredQuestionsForAccountType($accountType);
    }

    protected function hideHiddenQuestions( $questionList )
    {
        if ( empty($questionList) )
        {
            return array();
        }
        
        $result = array(); 
        
        $sections = $this->findHiddenSections();
        
        $sectionList = array();
        
        foreach ( $sections as $dto )
        {
            $sectionList[$dto->name] = $dto->name;
        }
        
        foreach ( $questionList as $question )
        {
            if ( !in_array($question->section, $sectionList) )
            {
                $result[$question->name] = $question;
            }
        }
        
        return $result;
    }
    
    /**
     * Returns fields values for provided account type.
     * Method is used in frontend cmps and forms.
     *
     * @param array $questionsNameList
     */
    public function findQuestionsValuesByQuestionNameList( array $questionsNameList )
    {
        return $this->valueDao->findQuestionsValuesByQuestionNameList($questionsNameList);
    }

    /**
     * Returns field by name.
     *
     * @param string $name
     */
    public function findQuestionById( $id )
    {
        return $this->questionDao->findById($id);
    }

    /**
     * Returns field by name.
     *
     * @param string $name
     * @return BOL_Question
     */
    public function findQuestionByName( $questionName )
    {
        return $this->questionDao->findQuestionByName($questionName);
    }

    /**
     * Returns fields list.
     *
     * @param array $questionNameList
     * @return array <BOL_Question>
     */
    public function findQuestionByNameList( $questionNameList )
    {
        $list = $this->questionDao->findQuestionByNameList($questionNameList);

        $result = array();

        if ( !empty($list) )
        {
            foreach ( $list as $question )
            {
                $result[$question->name] = $question;
            }
        }

        return $result;
    }

    public function findQuestionListByPresentationList( $presentation )
    {
        $questions = $this->questionDao->findQuestionsByPresentationList($presentation);

        $result = array();

        foreach ( $questions as $question )
        {
            $result[$question->name] = $question;
        }

        return $result;
    }

    /**
     * Saves/updates <BOL_Question> objects.
     *
     * @param BOL_Question $field
     */
    public function saveOrUpdateQuestion( BOL_Question $question, $label = null, $description = null )
    {
        $this->questionDao->save($question);
        $this->updateQuestionsEditStamp();
    }

    public function setQuestionLabel( $questionName, $label, $generateCahce = true )
    {
        if ( empty($questionName) )
        {
            throw new InvalidArgumentException('invalid questionName');
        }

        $serviceLang = BOL_LanguageService::getInstance();

        $currentLanguageId = OW::getLanguage()->getCurrentId();

        $nameKey = $serviceLang->findKey(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_LABEL, $questionName));

        if ( $nameKey !== null )
        {
            $serviceLang->deleteKey($nameKey->id);
        }

        $serviceLang->addOrUpdateValue($currentLanguageId, self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_LABEL, $questionName), empty($label) ? ' ' : $label, $generateCahce );
    }

    public function setQuestionDescription( $questionName, $description, $generateCahce = true )
    {
        if ( empty($questionName) )
        {
            throw new InvalidArgumentException('invalid questionName');
        }

        $serviceLang = BOL_LanguageService::getInstance();

        $currentLanguageId = OW::getLanguage()->getCurrentId();

        $descriptionKey = $serviceLang->findKey(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_DESCRIPTION, $questionName));

        if ( $descriptionKey !== null )
        {
            $serviceLang->deleteKey($descriptionKey->id);
        }

        $serviceLang->addOrUpdateValue($currentLanguageId, self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_DESCRIPTION, $questionName), empty($description) ? ' ' : $description, $generateCahce);
    }

    /**
     * Saves/updates <BOL_QuestionValue> objects.
     *
     * @param BOL_QuestionValue $value
     */
    public function saveOrUpdateQuestionValue( BOL_QuestionValue $value )
    {
        $this->valueDao->save($value);
        $this->updateQuestionsEditStamp();
    }

    public function findQuestionValues( $questionName )
    {
        return $this->valueDao->findQuestionValues($questionName);
    }
    
    public function findRealQuestionValues( $questionName )
    {
        return $this->valueDao->findRealQuestionValues($questionName);
    }

    public function findQuestionValueById( $id )
    {
        return $this->valueDao->findQuestionValueById($id);
    }

    public function findQuestionValue( $questionId, $value )
    {
        return $this->valueDao->findQuestionValue($questionId, $value);
    }

    public function deleteQuestionValue( $questionName, $value )
    {
        if ( $questionName === null )
        {
            return;
        }

        $name = trim($questionName);
        $valueId = (int) $value;

        $isDelete = $this->valueDao->deleteQuestionValue($name, $valueId);

        if ( $isDelete )
        {
            $serviceLang = BOL_LanguageService::getInstance();
            $key = $serviceLang->findKey('base', 'questions_question_' . $name . '_value_' . $valueId);

            if ( $key !== null )
            {
                $serviceLang->deleteKey($key->id);
            }

            $this->updateQuestionsEditStamp();

            $event = new OW_Event(self::EVENT_AFTER_DELETE_QUESTION_VALUE, array('questionName' => $questionName, 'value' => $value));
            OW::getEventManager()->trigger($event);
        }

        return $isDelete;
    }

    public function addQuestion(BOL_Question $question, $label, array $values = array(), array $accountTypes = array(), $description = null, $generateCache = true)
    {
        $this->saveOrUpdateQuestion($question);

        // translate the question
        $this->setQuestionLabel($question->name, $label);
        $this->setQuestionDescription($question->name, $description);

        // add values
        if ( $values )
        {
            $order = 1;

            foreach($values as $questionValue => $questionLabel)
            {
                $this->addQuestionValue( $question->name, $questionValue, $questionLabel, $order, $generateCache);
                $order++;
            }
        }

        // assign account types
        if ( $accountTypes )
        {
            $this->addQuestionListToAccountTypeList([$question->name], $accountTypes);
        }
    }

    public function saveOrUpdateAccountType( BOL_QuestionAccountType $value )
    {
        $this->accountDao->save($value);
        $this->updateQuestionsEditStamp();
    }

    public function findAccountTypeById( $id )
    {
        return $this->accountDao->findAccountTypeById($id);
    }

    public function deleteAccountType( $accountType )
    {
        if ( !isset($accountType) )
        {
            return false;
        }

        $accountTypeName = trim($accountType);
        $account = null;
        $repleaceToAccount = null;
        $prevKey = null;

        $accounts = $this->accountDao->findAll();

        if ( count($accounts) <= 1 )
        {
            return false;
        }

        foreach ( $accounts as $key => $value )
        {
            if ( $repleaceToAccount === null && $account !== null )
            {
                $repleaceToAccount = $accounts[$key];
            }

            if ( $accountTypeName == $value->name )
            {
                $account = $value;
                if ( $prevKey !== null )
                {
                    $repleaceToAccount = $accounts[$prevKey];
                }
            }

            $prevKey = $key;
        }

        if ( $account === null )
        {
            return false;
        }

        /* $questions = $this->questionDao->findQuestionsForAccountType($account->name);
        $questionIdList = array();

        foreach ( $questions as $key => $value )
        {
            $questionIdList[] = $value['id'];
        }

        $this->deleteQuestion($questionIdList); */

        //$this->userService->replaceAccountTypeForUsers($account->name, $repleaceToAccount->name);
        BOL_QuestionService::getInstance()->deleteUsersRoleByAccountType($account);
        $this->accountToQuestionDao->deleteByAccountType($account->name);

        $key = BOL_LanguageService::getInstance()->findKey(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_ACCOUNT_TYPE, $account->name));

        if ( $key !== null )
        {
            BOL_LanguageService::getInstance()->deleteKey($key->id);
        }

        $this->accountDao->deleteById($account->id);
        $this->updateQuestionsEditStamp();

        $deleted = (boolean) OW::getDbo()->getAffectedRows();

        if ( $deleted )
        {
            $event = new OW_Event(self::EVENT_ON_ACCOUNT_TYPE_DELETE, array('id' => $account->id, 'name' => $account->name));
            OW::getEventManager()->trigger($event);
        }
        
        
        return $deleted;
    }

    public function findVisibleNotDeletableSection()
    {
        return $this->sectionDao->findVisibleNotDeletableSection();
    }
    
    public function findNearestSection( BOL_QuestionSection $section )
    {
        if ( empty($section) )
        {
            return null;
        }
        
        $nearestSection = $this->sectionDao->findPreviousSection($section);

        if ( empty($nearestSection) )
        {
            $nearestSection = $this->sectionDao->findNextSection($section);
        }
        
        if ( empty($nearestSection) )
        {
            $moveQuestionsToSection = $this->findVisibleNotDeletableSection();
        }
        
        return $nearestSection;
    }

    public function findSectionById( $id )
    {
        return $this->sectionDao->findSectionById($id);
    }

    public function deleteSection( $sectionName, BOL_QuestionSection &$moveQuestionsToSection = null )
    {
        if ( $sectionName === null || mb_strlen($sectionName) === 0 )
        {
            return false;
        }

        $section = $this->sectionDao->findBySectionName($sectionName);

        if ( $section !== null )
        {
            if ( empty($moveQuestionsToSection) )
            {
                $moveQuestionsToSection = $this->findNearestSection($section);
            }
            
            $nextSectionName = $moveQuestionsToSection->name;
        }
        else
        {
            return false;
        }

        $questions = $this->questionDao->findQuestionsBySectionNameList(array($sectionName));
        $nextSectionName = isset($moveQuestionsToSection) ? $moveQuestionsToSection->name : null;

        $lastOrder = $this->questionDao->findLastQuestionOrder($nextSectionName);

        if ( $lastOrder === null )
        {
            $lastOrder = 0;
        }

        foreach ( $questions as $key => $question )
        {
            $questions[$key]->sectionName = $nextSectionName;
            $questions[$key]->sortOrder = ++$lastOrder;
        }
        
        if ( count($questions) > 0 )
        {
            $this->questionDao->batchReplace($questions);
        }

        $key = BOL_LanguageService::getInstance()->findKey(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_SECTION, $sectionName));
        if ( $key !== null )
        {
            BOL_LanguageService::getInstance()->deleteKey($key->id);
        }

        $this->sectionDao->deleteById($section->id);
        $this->updateQuestionsEditStamp();

        return true;
    }

    public function deleteQuestion( array $questionIdList )
    {
        if ( $questionIdList === null || count($questionIdList) == 0 )
        {
            return false;
        }

        $questionArray = $this->questionDao->findByIdList($questionIdList);

        $questionsNameList = array();

        foreach ( $questionArray as $question )
        {
            if ( $question->base == 1 || (int) $question->removable == 0 )
            {
                continue;
            }

            $questionsNameList[] = $question->name;

            $valuesObjects = $this->valueDao->findQuestionValues($question->name);

            $values = array();

            foreach($valuesObjects as $v)
            {
                $values[] = $v->value;
            }

            foreach ( $values as $value )
            {
                $key = BOL_LanguageService::getInstance()->findKey(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_VALUE, $question->name, $value));

                if ( $key !== null )
                {
                    BOL_LanguageService::getInstance()->deleteKey($key->id);
                }
            }

            $key = BOL_LanguageService::getInstance()->findKey(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_LABEL, $question->name));

            if ( $key !== null )
            {
                BOL_LanguageService::getInstance()->deleteKey($key->id);
            }

            $key = BOL_LanguageService::getInstance()->findKey(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_DESCRIPTION, $question->name));

            if ( $key !== null )
            {
                BOL_LanguageService::getInstance()->deleteKey($key->id);
            }

            $event = new OW_Event( self::EVENT_ON_QUESTION_DELETE, array( 'questionName' => $question->name, 'dto' => $question ) );

            OW::getEventManager()->trigger($event);

            $this->deleteQuestionValues($question->name, $values);
        }

        $this->dataDao->deleteByQuestionNamesList($questionsNameList);

        $this->questionDao->deleteByIdList($questionIdList);

        $this->updateQuestionsEditStamp();

        return (boolean) OW::getDbo()->getAffectedRows();
    }

    public function saveOrUpdateSection( BOL_QuestionSection $value )
    {
        $this->updateQuestionsEditStamp();
        $this->sectionDao->save($value);
    }

    /**
     * Finds all account types.
     *
     * @return array<BOL_QuestionAccountType>
     */
    public function findAllAccountTypes()
    {
        return $this->accountDao->findAllAccountTypes();
    }

    public function findAllAccountTypesWithLabels()
    {
        $types = $this->accountDao->findAllAccountTypes();

        if ( !$types )
        {
            return null;
        }

        $lang = OW::getLanguage();
        $list = array();

        /* @var $type BOL_QuestionAccountType */
        foreach ( $types as $type )
        {
            $list[$type->name] = $lang->text('base', 'questions_account_type_' . $type->name);
        }

        return $list;
    }

    /**
     * Get default account type
     *
     * @return array<BOL_QuestionAccountType>
     */
    public function getDefaultAccountType()
    {
        return $this->accountDao->getDefaultAccountType();
    }

    public function findAllAccountTypesWithQuestionsCount()
    {
        return $this->accountDao->findAllAccountTypesWithQuestionsCount();
    }

    /*public function findExclusiveQuestionForAccountType()
    {
        return $this->accountDao->findAllAccountTypesWithQuestionsCount();
    }*/

    public function findCountExlusiveQuestionForAccount( $accountType )
    {
        return $this->accountDao->findCountExlusiveQuestionForAccount($accountType);
    }

    public function findLastAccountTypeOrder()
    {
        $value = $this->accountDao->findLastAccountTypeOrder();

        if ( $value === null )
        {
            $value = 0;
        }

        return $value;
    }

    /**
     * Finds all sections.
     *
     * @return array<BOL_QuestionSection>
     */
    public function findAllSections()
    {
        return $this->sectionDao->findAll();
    }

    public function findSortedSectionList()
    {
        return $this->sectionDao->findSortedSectionList();
    }

    public function findHiddenSections()
    {
        return $this->sectionDao->findHiddenSections();
    }
    
    /**
     * Finds all sections.
     *
     * @return array<BOL_QuestionSection>
     */
    public function findSectionBySectionName( $sectionName )
    {
        return $this->sectionDao->findBySectionName($sectionName);
    }

    public function findSectionBySectionNameList( $list )
    {
        $list = $this->sectionDao->findBySectionNameList($list);
        
        $result = array();
        
        if ( !empty($list) )
        {
            foreach ( $list as $item )
            {
                /* @var $item BOL_QuestionSection */
                $result[$item->name] = $item;
            }
        }
        
        return $result;
    }


    public function findLastSectionOrder()
    {
        $value = $this->sectionDao->findLastSectionOrder();

        if ( $value === null )
        {
            $value = 0;
        }

        return $value;
    }

    public function findLastQuestionOrder( $sectionName = null )
    {
        $value = $this->questionDao->findLastQuestionOrder($sectionName);

        if ( $value === null )
        {
            $value = 0;
        }

        return $value;
    }

    /**
     * Save questions data.
     *
     * @param array $data
     * @param int $userId
     */
    public function saveQuestionsData( array $data, $userId )
    {
        if ( $data === null || !is_array($data) )
        {
            return false;
        }

        $user = null;
        if ( (int) $userId > 0 )
        {
            $user = $this->userService->findUserById($userId);

            if ( $user === null )
            {
                return false;
            }
        }
        else
        {
            return false;
        }

        $oldUserEmail = $user->email;
        
        $event = new OW_Event('base.questions_save_data', array('userId' => $userId), $data);

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        $dataFields = array_keys($data);

        $questions = $this->questionDao->findQuestionsByQuestionNameList($dataFields);
        $questionsData = $this->dataDao->findByQuestionsNameList($dataFields, $userId);

        $questionsUserData = array();

        foreach ( $questionsData as $questionData )
        {
            $questionsUserData[$questionData->questionName] = $questionData;
        }

        $questionDataArray = array();

        foreach ( $questions as $key => $question )
        {
            $value = null;
            
            if ( isset($data[$question->name]) )
            {
                switch ( $question->type )
                {
                    case self::QUESTION_VALUE_TYPE_TEXT:

                        $value = $question->presentation !== self::QUESTION_PRESENTATION_PASSWORD ? $this->questionTextFormatter(trim($data[$question->name])) : BOL_UserService::getInstance()->hashPassword($data[$question->name]);

                        if ( (int) $question->base === 1 && in_array($question->name, $dataFields) )
                        {
                            $property = new ReflectionProperty('BOL_User', $question->name);
                            $property->setValue($user, $value);
                        }
                        else
                        {
                            if ( isset($questionsUserData[$question->name]) )
                            {
                                $questionData = $questionsUserData[$question->name];
                            }
                            else
                            {
                                $questionData = new BOL_QuestionData();
                                $questionData->userId = $userId;
                                $questionData->questionName = $question->name;
                            }

                            $questionData->textValue = $value;

                            if ( $question->presentation === self::QUESTION_PRESENTATION_URL && !empty($value) )
                            {
                                $questionData->textValue = $this->urlFilter($value);
                            }

                            $questionDataArray[] = $questionData;
                            //$this->dataDao->save($questionData);
                        }

                        break;

                    case self::QUESTION_VALUE_TYPE_DATETIME:

                        $date = UTIL_DateTime::parseDate($data[$question->name], UTIL_DateTime::DEFAULT_DATE_FORMAT);

                        if (!isset($date))
                        {
                            $date = UTIL_DateTime::parseDate($data[$question->name], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                        }

                        if ( isset($date) )
                        {
                            if ( UTIL_Validator::isDateValid($date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_YEAR]) )
                            {
                                $value = $date[UTIL_DateTime::PARSE_DATE_YEAR] . '-' . $date[UTIL_DateTime::PARSE_DATE_MONTH] . '-' . $date[UTIL_DateTime::PARSE_DATE_DAY];

                                if ( (int) $question->base === 1 && in_array($question->name, $dataFields) )
                                {
                                    $property = new ReflectionProperty('BOL_User', $question->name);
                                    $property->setValue($user, $value);
                                }
                                else
                                {
                                    if ( isset($questionsUserData[$question->name]) )
                                    {
                                        $questionData = $questionsUserData[$question->name];
                                    }
                                    else
                                    {
                                        $questionData = new BOL_QuestionData();
                                        $questionData->userId = $userId;
                                        $questionData->questionName = $question->name;
                                    }

                                    $questionData->dateValue = $value;

                                    $questionDataArray[] = $questionData;
                                }
                            }
                        }

                        break;

                    case self::QUESTION_VALUE_TYPE_MULTISELECT:
                                                
                        if ( !empty($data[$question->name]) && is_array($data[$question->name]) )
                        {
                            $value = array_sum($data[$question->name]);
                        }
                        
                    case self::QUESTION_VALUE_TYPE_SELECT:
                    case self::QUESTION_VALUE_TYPE_FSELECT:

                        if ( !isset($value) )
                        {
                            $value = (int) $data[$question->name];
                        }

                        if ( (int) $question->base === 1 && in_array($question->name, $dataFields) )
                        {
                            $property = new ReflectionProperty('BOL_User', $question->name);
                            $property->setValue($user, $value);
                        }
                        else
                        {
                            if ( isset($questionsUserData[$question->name]) )
                            {
                                $questionData = $questionsUserData[$question->name];
                            }
                            else
                            {
                                $questionData = new BOL_QuestionData();
                                $questionData->userId = $userId;
                                $questionData->questionName = $question->name;
                            }

                            $questionData->intValue = $value;

                            $questionDataArray[] = $questionData;
                            //$this->dataDao->save($questionData);
                        }

                        break;

                    case self::QUESTION_VALUE_TYPE_BOOLEAN:

                        $value = false;

                        $issetValues = array('1', 'true', 'on');

                        if ( in_array(mb_strtolower((string) $data[$question->name]), $issetValues) )
                        {
                            $value = true;
                        }

                        if ( (int) $question->base === 1 && in_array($question->name, $dataFields) )
                        {
                            $property = new ReflectionProperty('BOL_User', $question->name);
                            $property->setValue($user, $value);
                        }
                        else
                        {
                            if ( isset($questionsUserData[$question->name]) )
                            {
                                $questionData = $questionsUserData[$question->name];
                            }
                            else
                            {
                                $questionData = new BOL_QuestionData();
                                $questionData->userId = $userId;
                                $questionData->questionName = $question->name;
                            }

                            $questionData->intValue = $value;

                            $questionDataArray[] = $questionData;
                            //$this->dataDao->save($questionData);
                        }

                        break;
                }
            }
        }

        $sendVerifyMail = false;

        if ( $user->id !== null )
        {
            if ( strtolower($user->email) !== strtolower($oldUserEmail) )
            {
                $user->emailVerify = false;
                $sendVerifyMail = true;
            }

            if ( !empty($data['accountType']) )
            {
                $accountType = $this->findAccountTypeByName($data['accountType']);
                $accountTypeOld = $this->findAccountTypeByName($user->accountType);

                if ( !empty($accountType) )
                {
                    $user->accountType = $accountType->name;
                    $this->updateQuestionsEditStamp();
                }
            }
        }

        $this->userService->saveOrUpdate($user);

        if ( count($questionDataArray) > 0 )
        {
            $this->dataDao->batchReplace($questionDataArray);
        }

        if ( $sendVerifyMail && OW::getConfig()->getValue('base', 'confirm_email') )
        {
            BOL_EmailVerifyService::getInstance()->sendUserVerificationMail($user);
        }

        return true;
    }

    /**
     * Save questions data.
     *
     * @param array $data
     */
    public function questionTextFormatter( $value )
    {
        return strip_tags($value); //TODO: check question value
    }

    public function reOrderAccountType( array $accountTypeList )
    {
        if ( $accountTypeList === null || !is_array($accountTypeList) || count($accountTypeList) === 0 )
        {
            return false;
        }

        $accountTypeNameList = array_keys($accountTypeList);

        $accountTypes = $this->accountDao->findAccountTypeByNameList($accountTypeNameList);

        foreach ( $accountTypes as $key => $accountType )
        {
            if ( isset($accountTypeList[$accountType->name]) )
            {
                $accountTypes[$key]->sortOrder = $accountTypeList[$accountType->name];
            }
        }

        return (boolean) $this->accountDao->batchReplace($accountTypes);
    }

    public function reOrderQuestion( $sectionName, array $questionOrder )
    {
        if ( $questionOrder === null || !is_array($questionOrder) || count($questionOrder) === 0 )
        {
            return false;
        }


        $section = null;

        if ( $sectionName !== null )
        {
            $section = $this->sectionDao->findBySectionName($sectionName);

            if ( $section === null )
            {
                return false;
            }

            $section = $section->name;
        }

        $questionNameList = array_keys($questionOrder);

        $questions = $this->questionDao->findQuestionsByQuestionNameList($questionNameList);

        if ( count($questionOrder) === 0 )
        {
            return false;
        }

        foreach ( $questions as $key => $question )
        {
            if ( isset($questionOrder[$question->name]) )
            {
                $questions[$key]->sortOrder = $questionOrder[$question->name];
                $questions[$key]->sectionName = $section;
            }
        }

        $result = $this->questionDao->batchReplace($questions);
        return $result;
    }

    public function reOrderSection( array $sectionOrder )
    {
        if ( $sectionOrder === null || !is_array($sectionOrder) || count($sectionOrder) === 0 )
        {
            return false;
        }

        $sectionNameList = array_keys($sectionOrder);

        $sections = $this->sectionDao->findBySectionNameList($sectionNameList);

        foreach ( $sections as $key => $section )
        {
            if ( isset($sectionOrder[$section->name]) )
            {
                $sections[$key]->sortOrder = $sectionOrder[$section->name];
            }
        }

        return $this->sectionDao->batchReplace($sections);
    }

    /**
     *
     * @param array $data
     * @param int $userId
     *
     * return array()
     */
    public function getQuestionData( array $userIdList, array $fieldsList )
    {
        if ( $userIdList === null || !is_array($userIdList) || count($userIdList) === 0 )
        {
            return array();
        }

        if ( $fieldsList === null || !is_array($fieldsList) || count($fieldsList) === 0 )
        {
            return array();
        }

        $usersBol = BOL_UserService::getInstance()->findUserListByIdList($userIdList);

        if ( $usersBol === null || count($usersBol) === 0 )
        {
            return array();
        }

        $userData = array();

        // get not cached questions
        $notCachedQuestionsData = array();

        foreach ( $userIdList as $userId )
        {
            if ( array_key_exists($userId, $this->questionsData) )
            {
                foreach ( $fieldsList as $field )
                {
                    if ( array_key_exists($field, $this->questionsData[$userId]) )
                    {
                        $userData[$userId][$field] = $this->questionsData[$userId][$field];
                    }
                    else
                    {
                        if ( !array_key_exists($field, $notCachedQuestionsData) )
                        {
                            $notCachedQuestionsData[$field] = $field;
                        }
                    }
                }
            }
            else
            {
                $userData = array();
                $notCachedQuestionsData = $fieldsList;
                break;
            }
        }

        if ( count($notCachedQuestionsData) > 0 )
        {
           $questionsBolArray['base'] = array();
           $questionsBolArray['notBase'] = array();

            // -- get questions BOL --

            $notCachedQuestions = array();

            foreach ( $notCachedQuestionsData as $field )
            {
                if ( array_key_exists($field, $this->questionsBOL['base']) )
                {
                    $questionsBolArray['base'][$field] = $this->questionsBOL['base'][$field];
                }
                else if ( array_key_exists($field, $this->questionsBOL['notBase']) )
                {
                    $questionsBolArray['notBase'][$field] = $this->questionsBOL['notBase'][$field];
                }
                else
                {
                    $notCachedQuestions[$field] = $field;
                }
            }

            if ( count($notCachedQuestions) > 0 )
            {
                $questions = $this->questionDao->findQuestionsByQuestionNameList($notCachedQuestions);

                foreach ( $questions as $question )
                {
                    if ( $question->base )
                    {
                        $questionsBolArray['base'][$question->name] = $question;
                    }
                    else
                    {
                        $questionsBolArray['notBase'][$question->name] = $question;
                    }
                }

                $this->questionsBOL['base'] = array_merge($questionsBolArray['base'], $this->questionsBOL['base']);
                $this->questionsBOL['notBase'] = array_merge($questionsBolArray['notBase'], $this->questionsBOL['notBase']);
            }

            $baseFields = array_keys($questionsBolArray['base']);
            $notBaseFields = array_keys($questionsBolArray['notBase']);

            unset($questionsBolArray);

            if ( count($notBaseFields) > 0 )
            {
                //get not base question values
                $questionsData = $this->dataDao->findByQuestionsNameListForUserList($notBaseFields, $userIdList);

                if ( count($questionsData) > 0 )
                {
                    foreach ( $userIdList as $userId )
                    {
                        foreach ( $notBaseFields as $field )
                        {
                            if ( isset($questionsData[$userId][$field]) )
                            {
                                $value = null;

                                switch ( $this->questionsBOL['notBase'][$field]->type )
                                {
                                    case self::QUESTION_VALUE_TYPE_BOOLEAN :
                                    case self::QUESTION_VALUE_TYPE_SELECT :
                                    case self::QUESTION_VALUE_TYPE_FSELECT :
                                    case self::QUESTION_VALUE_TYPE_MULTISELECT :
                                        $value = $questionsData[$userId][$field]->intValue;
                                        break;

                                    case self::QUESTION_VALUE_TYPE_TEXT :
                                        $value = $questionsData[$userId][$field]->textValue;
                                        break;

                                    case self::QUESTION_VALUE_TYPE_DATETIME :
                                        $value = $questionsData[$userId][$field]->dateValue;
                                        break;
                                }

                                $userData[$userId][$field] = $value;
                            }
                        }
                    }
                }
            }

            if ( count($baseFields) > 0 )
            {
                //get base question values

                $usersBolArray = array();

                foreach ( $usersBol as $userBol )
                {
                    $usersBolArray[$userBol->id] = $userBol;
                }

                foreach ( $userIdList as $userId )
                {
                    foreach ( $baseFields as $field )
                    {
                        $userData[$userId][$field] = null;

                        if ( isset($usersBolArray[$userId]->$field) )
                        {
                            $userData[$userId][$field] = $usersBolArray[$userId]->$field;
                        }
                    }
                }
            }
        }

        //cached questions data
        if ( count($userData) > 0 )
        {
            foreach ( $userData as $userId => $fields )
            {
                if ( isset($this->questionsData[$userId]) )
                {
                    $this->questionsData[$userId] = array_merge($fields, $this->questionsData[$userId]);
                }
                else
                {
                    $this->questionsData[$userId] = $fields;
                }
            }
        }

        $result = array();

        foreach ( $usersBol as $user )
        {
            $result[$user->id] = isset($userData[$user->id]) ? $userData[$user->id] : array();
        }

        $event = new OW_Event('base.questions_get_data', array('userIdList' => $userIdList, 'fieldsList' => $fieldsList), $result);

        OW::getEventManager()->trigger($event);
        return $event->getData();
    }

    private function urlFilter( $url )
    {
        $value = $url;

        if( !empty($value) )
        {
            $pattern = '/^http(s)?:\/\//';

            if( !preg_match($pattern, $url) )
            {
                $value = 'http://'.$url;
            }
        }

        return $value;
    }

    /**
     *
     * @param string $type
     *
     * $params['name'] - question name
     * $params['value'] - question value
     * @param array $params
     */
    public function getQuestionLangKeyName( $type, $name, $value = null )
    {
        $key = null;

        $event = new OW_Event( self::EVENT_ON_GET_QUESTION_LANG, array('type' => $type, 'name' => $name, 'value' => $value));
        OW::getEventManager()->trigger($event);

        $key = $event->getData();

        if ( !empty($key) )
        {
            return $key;
        }
        
        switch ( $type )
        {
            case self::LANG_KEY_TYPE_QUESTION_LABEL:
                $key = 'questions_question_' . $name . '_label';
                break;

            case self::LANG_KEY_TYPE_QUESTION_DESCRIPTION:
                $key = 'questions_question_' . $name . '_description';
                break;
            
            case self::LANG_KEY_TYPE_QUESTION_SECTION:
                $key = 'questions_section_' . $name . '_label';
                break;

            case self::LANG_KEY_TYPE_QUESTION_VALUE:
                if ( $name == 'f17d6da3dec6687a509721adee152573' )
                {
                    throw new Exception();
                }
                
                $key = 'questions_question_' . $name . '_value_' . $value;
                break;

            case self::LANG_KEY_TYPE_ACCOUNT_TYPE:
                $key = 'questions_account_type_' . $name;
                break;

            default:
                $key = '';
                break;
        }

        return $key;
    }

    public function getQuestionDescriptionLang( $questionName )
    {
        $key = $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_DESCRIPTION, $questionName);
        $text = OW::getLanguage()->text(self::QUESTION_LANG_PREFIX,$key);

        if( preg_match('/^'.preg_quote(self::QUESTION_LANG_PREFIX."+".$key).'$/', $text) )
        {
            $text = '';
        }

        return $text;
    }

    public function getQuestionLang( $questionName )
    {
        return OW::getLanguage()->text(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_LABEL, $questionName));
    }

    public function getQuestionValueLang( $questionName, $value )
    {
        return OW::getLanguage()->text(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_VALUE, $questionName, $value));
    }

    public function getSectionLang( $sectionName )
    {
        return OW::getLanguage()->text(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_QUESTION_SECTION, $sectionName));
    }

    public function getAccountTypeLang( $accountType )
    {
        return OW::getLanguage()->text(self::QUESTION_LANG_PREFIX, $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_ACCOUNT_TYPE, $accountType));
    }

    /**
     * @param $name
     * @return BOL_QuestionAccountType
     */
    public function findAccountTypeByName( $name )
    {
        if ( empty($name) || !mb_strlen($name) )
        {
            return null;
        }

        $types = $this->accountDao->findAccountTypeByNameList(array($name));

        if ( !$types )
        {
            return null;
        }

        $res = array();
        foreach ( $types as $type )
        {
            $res[$type->name] = $type;
        }

        return isset($res[$name]) ? $res[$name] : null;
    }

    public function deleteByQuestionListAndUserId(array $questionNameList, $userId)
    {
        $this->dataDao->deleteByQuestionListAndUserId($questionNameList, $userId);
    }

    public function deleteQuestionDataByUserId( $userId )
    {
        $this->dataDao->deleteByUserId($userId);
    }

    public function findSearchQuestionsForAccountType( $accountType )
    {
        return $this->questionDao->findSearchQuestionsForAccountType($accountType);
    }

    public function createQuestion( BOL_Question $question, $label, $description = '', $values = array(), $saveValuesKeys = false, $generateCache = true )
    {
        if ( empty($question) )
        {
            return;
        }
        
        $this->saveOrUpdateQuestion($question);
        $this->setQuestionDescription($question->name, $description, false);
        $this->setQuestionLabel($question->name, $label, false);
        
        //add question values
        if ( !empty($values) && is_array($values) && count($values) > 0 && in_array( $question->type,  array('fselect','select', 'multiselect') ) )
        {
            $order = 0;
            foreach ( $values as $key => $value )
            {
                if ( $order > 30 && $question->presentation != 'fselect')
                {
                    break;
                }

                $value = trim($value);
                if ( isset($value) && mb_strlen($value) === 0 )
                {
                    continue;
                }

                if ($question->presentation == 'fselect')
                {
                    $valueId = ( $saveValuesKeys ) ? $key : $order;
                }
                else
                {
                    $valueId = ( $saveValuesKeys ) ? $key : pow(2, $order);
                }

                $this->addQuestionValue( $question->name, $valueId, $value, $order, false );
                $order++;
            }
        }
        
        if ( $generateCache )
        {
            BOL_LanguageService::getInstance()->generateCache(OW::getLanguage()->getCurrentId());
        }
        
        $this->updateQuestionsEditStamp();
    }

    public function deleteQuestionValues( $questionName, $values )
    {
        if ( empty($values) || empty($questionName) )
        {
            return;
        }

        foreach( $values as $value )
        {
            $this->deleteQuestionValue($questionName, $value);
        }
    }

    public function updateQuestionValues( BOL_Question $question, $values, $generateCahce = true )
    {
        $questionName = $question->name;
        if ( empty($questionName) || empty($values) )
        {
            return false;
        }

        //add question values
        if ( is_array($values) && count($values) > 0 )
        {
            $existingQuestionValues = $this->findRealQuestionValues($questionName);
            $existingValuesList = array();

            foreach( $existingQuestionValues as $value )
            {
                $existingValuesList[$value->value] = $value;
            }

            $count = 0;

            foreach ( $values as $key => $value )
            {
                if ( $count > 30 && $question->presentation != 'fselect')
                {
                    break;
                }

                if ( !empty($existingValuesList[$key]) )
                {
                    $existingValuesList[$key]->sortOrder = $count;

                    $this->saveOrUpdateQuestionValue($existingValuesList[$key]);
//                    BOL_LanguageService::getInstance()->addOrUpdateValue(OW::getLanguage()->getCurrentId(), 'base', 'questions_question_' . ($questionName) . '_value_' . $value, $label, $generateCache);
                    $event = new OW_Event(self::EVENT_AFTER_UPDATE_QUESTION_VALUE, array('dto' => $existingValuesList[$key]));
                    OW::getEventManager()->trigger($event);
                }
                else
                {
                    $value = trim($value);
                    if ( isset($value) && mb_strlen($value) === 0 )
                    {
                        continue;
                    }

                    $this->addQuestionValue( $questionName, $key, $value, $count, $generateCahce );
                }
                $count++;
            }
        }

        return true;
    }

    public function addQuestionValue( $questionName, $value, $label, $sortOrder, $generateCache = true )
    {
        $questionValue = new BOL_QuestionValue();
        $questionValue->questionName = $questionName;
        $questionValue->sortOrder = $sortOrder;
        $questionValue->value = $value;

        $this->saveOrUpdateQuestionValue($questionValue);

        $event = new OW_Event(self::EVENT_AFTER_ADD_QUESTION_VALUE, array('dto' => $questionValue));
        OW::getEventManager()->trigger($event);

        BOL_LanguageService::getInstance()->addOrUpdateValue(OW::getLanguage()->getCurrentId(), 'base', 'questions_question_' . ($questionName) . '_value_' . $value, $label, $generateCache);

        return $questionValue;
    }

    public function updateQuestionsEditStamp()
    {
        if ( $this->questionUpdateTime < time() )
        {
            OW::getConfig()->saveConfig( 'base', 'profile_question_edit_stamp', time() );
            $this->questionUpdateTime = time();
        }
    }

    public function getQuestionsEditStamp()
    {
        return OW::getConfig()->getValue( 'base', 'profile_question_edit_stamp' );
    }

    public function findQuestionChildren( $parentQuestionName )
    {
        return $this->questionDao->findQuestionChildren($parentQuestionName);
    }

    /*
     * return array();
     */

    public function getAccountTypesToQuestionsList()
    {
        return $this->accountToQuestionDao->findAll();
    }

    public function findAccountTypeListByQuestionName( $questionName )
    {
        return $this->accountToQuestionDao->findByQuestionName($questionName);
    }

    public function addQuestionListToAccountTypeList( $questionNameList, $accountTypeList )
    {
        if ( empty($accountTypeList) || !is_array($accountTypeList) || empty($questionNameList) || !is_array($questionNameList) )
        {
            return;
        }

        $list = array();

        foreach ( $accountTypeList as $key => $value )
        {
            if( !empty($value) )
            {
                foreach( $questionNameList as $questionName )
                {
                    if( !empty($questionName) )
                    {
                        $item = new BOL_QuestionToAccountType();

                        $item->accountType = $value;
                        $item->questionName = $questionName;

                        $list[] = $item;
                    }
                }
            }

            if ( !empty($list) )
            {
                $this->updateQuestionsEditStamp();
                $this->accountToQuestionDao->batchReplace($list);
                $list = array();
            }
        }
    }

    public function addQuestionToAccountType( $questionName, $accountTypeList )
    {
        if ( empty($accountTypeList) || !is_array($accountTypeList) || empty($questionName) )
        {
            return;
        }

        $this->addQuestionListToAccountTypeList(array($questionName), $accountTypeList);
    }

    public function deleteQuestionToAccountTypeByQuestionName( $questionName )
    {
        $this->accountToQuestionDao->deleteByQuestionName($questionName);
        $this->updateQuestionsEditStamp();
    }

    public function deleteQuestionToAccountType( $questionName, $accountTypeList )
    {
        $this->accountToQuestionDao->deleteByQuestionNameAndAccountTypeList($questionName, $accountTypeList);
        $this->updateQuestionsEditStamp();
    }

    public function getQuestionDisableActionList( BOL_question $question )
    {
        $disableActionList = array(
            'disable_account_type' => false,
            'disable_answer_type' => false,
            'disable_presentation' => false,
            'disable_column_count' => false,
            'disable_display_config' => false,
            'disable_possible_values' => false,
            'disable_required' => false,
            'disable_on_join' => false,
            'disable_on_view' => false,
            'disable_on_search' => false,
            'disable_on_edit' => false
        );

        $event = new OW_Event( 'admin.disable_fields_on_edit_profile_question', array( 'questionDto' => $question ), $disableActionList );
        OW::getEventManager()->trigger($event);
        
        return $event->getData();
    }

    public function getRequiredQuestionsForNewAccountType()
    {
        $questions = BOL_QuestionService::getInstance()->findAllQuestions();

        $questionNameList = array();

        foreach ( $questions as $question )
        {
            /* @var $question BOL_Question */
            if ( $question->base == 1 || in_array($question->name, array('birthdate', 'realname', 'match_sex', 'sex', 'joinStamp')) )
            {
                $questionNameList[$question->name] = $question->name;
            }
        }
        
        return $questionNameList;
    }
    
    public function createAccountType( $accountTypeName, $label = '', $roleId = 0 )
    {
        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($accountTypeName);

        if ( !empty($accountType) )
        {
            return;
        }

        $role = BOL_AuthorizationService::getInstance()->getRoleById($roleId);

        if ( empty($role) )
        {
            $role = BOL_AuthorizationService::getInstance()->getDefaultRole();
        }

        $accountType = new BOL_QuestionAccountType();
        $accountType->name = $accountTypeName;
        $accountType->sortOrder = (BOL_QuestionService::getInstance()->findLastAccountTypeOrder()) + 1;
        $accountType->roleId = $role->id;

        BOL_QuestionService::getInstance()->saveOrUpdateAccountType($accountType);

        $event = new OW_Event(self::EVENT_ON_ACCOUNT_TYPE_ADD, array('dto' => $accountType, 'id' => $accountType->id));
        OW::getEventManager()->trigger($event);

        $questionNameList = $this->getRequiredQuestionsForNewAccountType();
        
        $event = new OW_Event(self::EVENT_BEFORE_ADD_QUESTIONS_TO_NEW_ACCOUNT_TYPE, array('dto' => $accountType), $questionNameList);
        OW::getEventManager()->trigger($event);

        $questionNameList = $event->getData();

        $this->addQuestionListToAccountTypeList($questionNameList, array($accountType->name));

        if ( !empty($label) )
        {
            $prefix = 'base';
            $key = $this->getQuestionLangKeyName(self::LANG_KEY_TYPE_ACCOUNT_TYPE, $accountTypeName);

            $languageService = BOL_LanguageService::getInstance();
            $current = $languageService->getCurrent();
            $currentLanguageId = OW::getLanguage()->getCurrentId();
            $currentLangValue = "";

            $keyDto = $languageService->findKey($prefix, $key);

            if ( empty($keyDto) )
            {
                $prefixDto = $languageService->findPrefix($prefix);
                $keyDto = $languageService->addKey($prefixDto->id, $key);
            }

            $value = trim($label);

            if ( mb_strlen(trim($value)) == 0 || $value == json_decode('"\u00a0"') ) // stupid hack
            {
                $value = '&nbsp;';
            }

            $dto = $languageService->findValue($current->getId(), $keyDto->getId());

            if ( $dto !== null )
            {
                if ( $dto->getValue() !== $value )
                {
                    $languageService->saveValue($dto->setValue($value));
                }
            }
            else
            {
                $dto = $languageService->addOrUpdateValue($current->getId(), $prefix, $key, $value);
            }
        }

        $this->updateQuestionsEditStamp();

        return $accountType;
    }

    public function getEmptyRequiredQuestionsList( $userId )
    {
        $user = BOL_UserService::getInstance()->findUserById($userId);
        
        if ( empty($user) )
        {
            return array();
        }

        $accountType = $this->findAccountTypeByName($user->accountType);

        if ( empty($accountType) )
        {
            return array();
        }

        $questionsList = $this->findRequiredQuestionsForAccountType($user->accountType);

        if ( empty($questionsList) )
        {
            return array();
        }

        $questionNameList = array();

        foreach ( $questionsList as $question )
        {
            $questionNameList[$question['name']] = $question['name'];
        }

        $values = $this->findQuestionsValuesByQuestionNameList($questionNameList);
        $questionDtoList = $this->findQuestionByNameList($questionNameList);
        $data = $this->getQuestionData( array($userId), $questionNameList );

        $emptyQuestionsList = array();

        foreach ( $questionsList as $question )
        {
            /*@var $questionDto BOL_Question */
            $questionDto = $questionDtoList[$question['name']];

            if ( empty($questionDto) || !$questionDto->onJoin )
            {
                continue;
            }

            $formElement = $this->getFormElementByQuestionDto( $questionDto , $values, 'edit' );

            if ( !empty($formElement) )
            {
                $value = $this->prepareFieldValue($questionDto->presentation, empty($data[$userId][$question['name']]) ? null : $data[$userId][$question['name']] );

                if ( !empty($value) )
                {
                    $formElement->setValue($value);
                }

                $result = $formElement->getValue();

                if ( empty($result) )
                {
                    $emptyQuestionsList[$question['name']] = $question;
                }
            }
        }

        return $emptyQuestionsList;
    }

    public function prepareFieldValue( $presentation, $value )
    {
        if ( empty($value) )
        {
            return $value;
        }

        $result = $value;

        switch ( $presentation )
        {
            case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX:

                $result = !empty($value);

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_AGE:
            case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE:
            case BOL_QuestionService::QUESTION_PRESENTATION_DATE:

                $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::DEFAULT_DATE_FORMAT);

                if (!isset($date))
                {
                    $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                }

                if ( isset($date) )
                {
                    $result = $date['year'] . '/' . $date['month'] . '/' . $date['day'];
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:

                $data = array();
                $multicheckboxValue = (int) $value;

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = (int) pow(2, $i);

                    if ( $val & $multicheckboxValue )
                    {
                        $data[] = $val;
                    }
                }

                $result = $data;

                break;
        }

        return $result;
    }
    
    public function prepareFieldValueForSearch( $presentation, $value )
    {
        if ( empty($value) )
        {
            return $value;
        }

        $result = $value;

        switch ( $presentation )
        {
            case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX:

                $result = !empty($value);

                break;

//            case BOL_QuestionService::QUESTION_PRESENTATION_AGE:
//            case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE:
//            case BOL_QuestionService::QUESTION_PRESENTATION_DATE:
//
//                $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::DEFAULT_DATE_FORMAT);
//
//                if (!isset($date))
//                {
//                    $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
//                }
//
//                if ( isset($date) )
//                {
//                    $result = $date['year'] . '/' . $date['month'] . '/' . $date['day'];
//                }
//
//                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_SELECT:
            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:

                if ( !is_array($value) )
                {
                    $data = array();
                    $multicheckboxValue = (int) $value;

                    for ( $i = 0; $i < 31; $i++ )
                    {
                        $val = (int) pow(2, $i);

                        if ( $val & $multicheckboxValue )
                        {
                            $data[] = $val;
                        }
                    }

                    $result = $data;
                }

                break;
        }

        return $result;
    }
    
    public function deleteUsersRoleByAccountType( BOL_QuestionAccountType $accountType )
    {
        if ( empty($accountType) )
        {
            return;
        }
        
        /* @var $defaultRole BOL_AuthorizationRole */
        $defaultRole = BOL_AuthorizationService::getInstance()->getDefaultRole();
        
        if( $accountType->roleId == $defaultRole->id )
        {
            return;
        }
        
        $this->accountDao->deleteRoleByAccountType( $accountType );
    }
    
    public function addUsersRoleByAccountType( BOL_QuestionAccountType $accountType )
    {
        if ( empty($accountType) )
        {
            return;
        }
        
        /* @var $defaultRole BOL_AuthorizationRole */
        $defaultRole = BOL_AuthorizationService::getInstance()->getDefaultRole();
        
        if( $accountType->roleId == $defaultRole->id )
        {
            return;
        }
        
        $this->accountDao->addRoleByAccountType( $accountType );
    }
    
    public function getQuestionValueForUserList( BOL_Question $question, $value )
    {
        $stringValue = "";

        $language = OW::getLanguage();

        switch ( $question->presentation )
        {
            case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX:

                if ( (int) $value === 1 )
                {
                    $stringValue = OW::getLanguage()->text('base', 'yes');
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_DATE:

                $format = OW::getConfig()->getValue('base', 'date_field_format');

                $value = 0;

                switch ( $question->type )
                {
                    case BOL_QuestionService::QUESTION_VALUE_TYPE_DATETIME:

                        $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                        if ( isset($date) )
                        {
                            $stringValue = mktime(0, 0, 0, $date['month'], $date['day'], $date['year']);
                        }

                        break;

                    case BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT:

                        $stringValue = (int) $value;

                        break;
                }

                if ( $format === 'dmy' )
                {
                    $stringValue = date("d/m/Y", $value);
                }
                else
                {
                    $stringValue = date("m/d/Y", $value);
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE:

                $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $stringValue = UTIL_DateTime::formatBirthdate($date['year'], $date['month'], $date['day']);

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_AGE:

                $date = UTIL_DateTime::parseDate($value, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $stringValue = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']) . " " . $language->text('base', 'questions_age_year_old');

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_RANGE:

                $range = explode('-', $value);
                $stringValue = $language->text('base', 'form_element_from') . " " . $range[0] . " " . $language->text('base', 'form_element_to') . " " . $range[1];

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_SELECT:
            case BOL_QuestionService::QUESTION_PRESENTATION_FSELECT:
            case BOL_QuestionService::QUESTION_PRESENTATION_RADIO:
            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:
                
                $multicheckboxValue = (int) $value;

                $parentName = $question->name;

                if ( !empty($question->parent) )
                {
                    $parent = BOL_QuestionService::getInstance()->findQuestionByName($question->parent);

                    if ( !empty($parent) )
                    {
                        $parentName = $parent->name;
                    }
                }

                $questionValues = BOL_QuestionService::getInstance()->findQuestionValues($parentName);

                foreach ( $questionValues as $val )
                {
                    /* @var $val BOL_QuestionValue */
                    if ( ( (int) $val->value ) & $multicheckboxValue )
                    {
                        $stringValue .= BOL_QuestionService::getInstance()->getQuestionValueLang($val->questionName, $val->value) .', ';
                    }
                }
                
                if ( !empty($stringValue) )
                {
                    $stringValue = mb_substr($stringValue, 0, -2);
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_URL:
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXT:
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA:
                if ( !is_string($value) )
                {
                    break;
                }

                $value = trim($value);

                if ( strlen($value) > 0 )
                {
                    $stringValue = UTIL_HtmlTag::autoLink(nl2br($value));
                }

                break;
        }
        
        return $stringValue;
    }

    public function getChangedQuestionList($data, $userId)
    {
        // get changes list
        $fields = array_keys($data);
        $questions = $this->findQuestionByNameList($fields);
        $oldData = $this->getQuestionData(array($userId), $fields);
        $changesList = array();

        foreach ( $questions as $question )
        {
            $key = $question->name;

            $value = empty($oldData[$userId][$key]) ? null : $oldData[$userId][$key];

            $value = $this->prepareFieldValue($question->presentation, $value);
            $value1 = $this->prepareFieldValue($question->presentation, $data[$key]);

            if ( $key == 'googlemap_location' && isset($value1['remove'])  )
            {
                unset($value1['remove']);
            }

            if ( $value != $value1 )
            {
                $changesList[$key] = $key;
            }
        }
        return $changesList;
    }

    public function isNeedToModerate($changesList)
    {
        $questions = $this->findQuestionByNameList($changesList);
        $textFields = array(self::QUESTION_PRESENTATION_TEXT, self::QUESTION_PRESENTATION_TEXTAREA );

        foreach ( $questions as $question )
        {
            if ( $question && in_array($question->presentation, $textFields) && $question->name != 'googlemap_location' ) {
                return true;
            }
        }

        return false;
    }
}
