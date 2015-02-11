<?php

class BASE_MCMP_ProfileInfo extends OW_MobileComponent
{
    /**
     *
     * @var BOL_User
     */
    protected $user;
    protected $previewMode = false;

    public function __construct( BOL_User $user, $previewMode = false )
    {
        parent::__construct();
        
        $this->user = $user;
        $this->previewMode = $previewMode;
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $questionNames = array();
        
        if ( $this->previewMode )
        {
            $questions = BOL_QuestionService::getInstance()->findViewQuestionsForAccountType($this->user->accountType);
            foreach ( $questions as $question )
            {
                if ( $question["name"] == OW::getConfig()->getValue('base', 'display_name_question') )
                {
                    continue;
                }
                
                $questionNames[$question['sectionName']][] = $question["name"];
            }
        }
        
        $questions = BASE_CMP_UserViewWidget::getUserViewQuestions($this->user->id, OW::getUser()->isAdmin(), reset($questionNames));
        
        $data = array();
        foreach ( $questions['data'][$this->user->id] as $key => $value )
        {
            $data[$key] = $value;

            if ( is_array($value) )
            {
                $data[$key] = implode(', ', $value);
            }
        }

        $this->assign("displaySections", !$this->previewMode);
        $this->assign('questionArray', $questions['questions']);
        $this->assign('questionData', $data);
        $this->assign('questionLabelList', $questions['labels']);
    }
}