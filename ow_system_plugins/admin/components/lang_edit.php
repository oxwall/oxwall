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
 * Admin menu class. Works with all admin menu types.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_LangEdit extends OW_Component
{
    /**
     * BOL_LanguageService
     */
    private $service;

    /**
     * Constructor.
     * 
     * @param array $itemsList
     */
    public function __construct( $langId )
    {
        parent::__construct();
        $this->service = BOL_LanguageService::getInstance();

        if ( empty($langId) )
        {
            $this->setVisible(false);
            return;
        }

        $languageDto = $this->service->findById($langId);

        if ( $languageDto === null )
        {
            $this->setVisible(false);
            return;
        }

        $language = OW::getLanguage();

        $form = new Form('lang_edit');
        $form->setAjax();
        $form->setAction(OW::getRouter()->urlFor('ADMIN_CTRL_Languages', 'langEditFormResponder'));
        $form->setAjaxResetOnSuccess(false);

        $labelTextField = new TextField('label');
        $labelTextField->setLabel($language->text('admin', 'clone_form_lbl_label'));
        $labelTextField->setDescription($language->text('admin', 'clone_form_descr_label'));
        $labelTextField->setRequired();
        $labelTextField->setValue($languageDto->getLabel());
        $form->addElement($labelTextField);

        $tagTextField = new TextField('tag');
        $tagTextField->setLabel($language->text('admin', 'clone_form_lbl_tag'));
        $tagTextField->setDescription($language->text('admin', 'clone_form_descr_tag'));
        $tagTextField->setRequired();
        $tagTextField->setValue($languageDto->getTag());

        if ( $languageDto->getTag() == 'en' )
        {
            $tagTextField->addAttribute('disabled', 'disabled');
        }
        
        $form->addElement($tagTextField);

        $rtl = new CheckboxField('rtl');
        $rtl->setLabel($language->text('admin', 'lang_edit_form_rtl_label'));
        $rtl->setDescription($language->text('admin', 'lang_edit_form_rtl_desc'));
        $rtl->setValue((bool) $languageDto->getRtl());
        $form->addElement($rtl);

        $hiddenField = new HiddenField('langId');
        $hiddenField->setValue($languageDto->getId());
        $form->addElement($hiddenField);

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'btn_label_edit'));
        $form->addElement($submit);

        $form->bindJsFunction(Form::BIND_SUCCESS, "function(data){if(data.result){OW.info(data.message);setTimeout(function(){window.location.reload();}, 1000);}else{OW.error(data.message);}}");

        $this->addForm($form);
    }
}