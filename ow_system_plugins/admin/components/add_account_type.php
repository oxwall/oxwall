<?php


class ADMIN_CMP_AddAccountType extends OW_Component
{
    public function __construct()
    {
        $accountType = new BOL_QuestionAccountType();
        $accountType->name = md5(uniqid());
        $accountType->roleId = 0;

        $form = new ADMIN_CLASS_AddAccountTypeForm($accountType);
        $this->addForm($form);

        $list = BOL_LanguageService::getInstance()->findActiveList();
        
        $this->assign('langs', $list);
        $this->assign('prefix', 'base');
        $this->assign('key', BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_ACCOUNT_TYPE, $accountType->name));
        $this->assign('form', $form);
    }
}