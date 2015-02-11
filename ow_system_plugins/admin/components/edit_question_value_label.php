<?php

class ADMIN_CMP_EditQuestionValueLabel extends OW_Component
{
    public function  __construct( $value, $languageData )
    {
        parent::__construct();
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'language_value_edit.html');
        
        $list = BOL_LanguageService::getInstance()->findActiveList();
        
		$this->assign('langs', $list);
		$this->assign('prefix', 'value');
		$this->assign('key', $value);

        $form = new QuestionValueEditForm( $value, $languageData );
        $this->addForm($form);

        $formName = $form->getName();

        $jsString = '
            
           owForms[{$formName}].submitForm = function(){

                var self = this;

                this.removeErrors();

                try{
                    this.validate();
                }catch(e){
                    if( this.showErrors ){
                        OW.error(e);
                    }
                    return false;
                }

                var dataToSend = this.getValues();
                self.trigger("submit", dataToSend);
                return false;
            }

            owForms[{$formName}].bind("submit", function($data){
                OW.trigger("admin.questions_edit_question_value", [$data], this);
                return false;
            })';

        $script = UTIL_JsGenerator::composeJsString($jsString, array(
		  'formName' => $form->getName()
		));

		OW::getDocument()->addOnloadScript($script);
    }
}

class QuestionValueEditForm extends Form
{
	public function __construct( $value, $languageData )
	{
		parent::__construct('lang-values-edit');

		$this->setAjax(true);
		//$this->setAction('javascript://');
        
        $hidden = new HiddenField('value');
        $hidden->setValue($value);
        $this->addElement($hidden);

		$languageService = BOL_LanguageService::getInstance();
		$list = $languageService->findActiveList();
        
		foreach ( $list as $item )
		{
			$textArea = new Textarea("lang[{$item->getId()}][value][{$value}]");

            if ( isset($languageData[$item->getId()]) )
            {
                $textArea->setValue($languageData[$item->getId()]);
            }

			$this->addElement($textArea);
		}

		$submit = new Submit('submit');

		$submit->setValue(OW::getLanguage()->text('admin', 'save_btn_label'));

		$this->addElement($submit);
	}
}