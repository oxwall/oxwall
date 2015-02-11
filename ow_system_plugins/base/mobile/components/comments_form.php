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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.ow_system_plugins.base.comments
 * @since 1.0
 */
class BASE_MCMP_CommentsForm extends OW_MobileComponent
{

    public function __construct( BASE_CommentsParams $params, $id, $formName )
    {
        parent::__construct();

        $language = OW::getLanguage();
        $form = new Form($formName);
        $textArea = new Textarea('commentText');
        $textArea->setHasInvitation(true);
        $textArea->setInvitation($language->text('base', 'comment_form_element_invitation_text'));
        $form->addElement($textArea);

        $hiddenEls = array(
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'displayType' => $params->getDisplayType(),
            'pluginKey' => $params->getPluginKey(),
            'ownerId' => $params->getOwnerId(),
            'cid' => $id,
            'commentCountOnPage' => $params->getCommentCountOnPage(),
            'isMobile' => 1
        );

        foreach ( $hiddenEls as $name => $value )
        {
            $el = new HiddenField($name);
            $el->setValue($value);
            $form->addElement($el);
        }

        $submit = new Submit('comment-submit');
        $submit->setValue($language->text('base', 'comment_add_submit_label'));
        $form->addElement($submit);

        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_Comments', 'addComment'));
//        $form->bindJsFunction(Form::BIND_SUBMIT, "function(){ $('#comments-" . $id . " .comments-preloader').show();}");
//        $form->bindJsFunction(Form::BIND_SUCCESS, "function(){ $('#comments-" . $id . " .comments-preloader').hide();}");
        $this->addForm($form);
        OW::getDocument()->addOnloadScript("window.owCommentCmps['$id'].initForm('" . $textArea->getId() . "', '".$submit->getId()."');");

        $this->assign('id', $id);
        $this->assign('formName', $formName);
    }
}