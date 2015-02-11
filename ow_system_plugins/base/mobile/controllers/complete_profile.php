<?php

class BASE_MCTRL_CompleteProfile extends BASE_CTRL_CompleteProfile
{
    public function __construct()
    {
        //parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();

        $this->setPageHeading(OW::getLanguage()->text('base', 'complete_your_profile_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
    }

    public function fillAccountType( $params )
    {
        parent::fillAccountType($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'complete_profile_fill_account_type.html');

        $form = $this->getForm('accountTypeForm');

        //BASE_MCLASS_JoinFormUtlis::setInvitations($form);
        BASE_MCLASS_JoinFormUtlis::setColumnCount($form);
        
        $this->assign('presentationToClass', BASE_MCLASS_JoinFormUtlis::presentationToCssClass());

        BASE_MCLASS_JoinFormUtlis::addOnloadJs($form->getName());
    }

    public function fillRequiredQuestions( $params )
    {
        parent::fillRequiredQuestions($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'complete_profile_fill_required_questions.html');

        $form = $this->getForm('requiredQuestionsForm');

        $questions = $this->questionService->getEmptyRequiredQuestionsList(OW::getUser()->getId());

        BASE_MCLASS_JoinFormUtlis::setLabels($form, $questions);
        BASE_MCLASS_JoinFormUtlis::setInvitations($form, $questions);
        BASE_MCLASS_JoinFormUtlis::setColumnCount($form);

        $this->assign('presentationToClass', BASE_MCLASS_JoinFormUtlis::presentationToCssClass());

        BASE_MCLASS_JoinFormUtlis::addOnloadJs($form->getName());
    }
}