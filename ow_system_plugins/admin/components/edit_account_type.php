<?php

class ADMIN_CMP_EditAccountType extends OW_Component
{
    public function __construct( $accountTypeName = '' )
    {
        $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($accountTypeName);
        
        if ( empty($accountType) )
        {
            $this->setVisible(false);
        }

        $form = new ADMIN_CLASS_AddAccountTypeForm($accountType, 'editAccountType');
        $form->setAjaxResetOnSuccess(false);
        $this->addForm($form);

        $list = BOL_LanguageService::getInstance()->findActiveList();
        
        $key = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_ACCOUNT_TYPE, $accountType->name);
        
        $this->assign('langs', $list);
        $this->assign('prefix', 'base');
        $this->assign('key', $key);
        $this->assign('form', $form);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('admin')->getCmpViewDir() . 'add_account_type.html');
    }
}