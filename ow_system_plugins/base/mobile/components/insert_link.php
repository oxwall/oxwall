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
 * Singleton. 'InsertLink' Data Access Object
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_InsertLink extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      string linkText
     */
    public function __construct( array $params = array() )
    {
        parent::__construct();

        $title = !empty($params['linkText']) 
            ? trim(strip_tags($params['linkText'])) 
            : null;

        // add a form
        $form = new InsertLinkForm();
        $form->setValues(array(
           'title' => $title  
        ));

        $this->addForm($form);       
    }
}

class InsertLinkForm extends Form
{
    /**
     * Min title length
     */
    const MIN_TITLE_LENGTH = 1;

    /**
     * Max title length
     */
    const MAX_TITLE_LENGTH = 255;
    
    /**
     * Min link length
     */
    const MIN_LINK_LENGTH = 3;

    /**
     * Max link length
     */
    const MAX_LINK_LENGTH = 255;

    public function __construct()
    {
        parent::__construct('insertLink');
 
        // title
        $titleField = new TextField('title');
        $titleField->setRequired(true)->setHasInvitation(true)->setInvitation(OW::getLanguage()->text('base', 'ws_link_text_label'));

        $sValidator = new StringValidator(self::MIN_TITLE_LENGTH, self::MAX_TITLE_LENGTH);
        $sValidator->setErrorMessage(OW::getLanguage()->
                text('base', 'chars_limit_exceeded', array('limit' => self::MAX_TITLE_LENGTH)));

        $titleField->addValidator($sValidator);
        $this->addElement($titleField);

        // link
        $linkField = new TextField('link');
        $linkField->setRequired(true)->setHasInvitation(true)->setInvitation(OW::getLanguage()->text('base', 'ws_link_url_label'));
        $sValidator = new StringValidator(self::MIN_LINK_LENGTH, self::MAX_LINK_LENGTH);
        $sValidator->setErrorMessage(OW::getLanguage()->
                text('base', 'chars_limit_exceeded', array('limit' => self::MAX_LINK_LENGTH)));

        $linkField->addValidator($sValidator);
        $linkField->addValidator(new UrlValidator());
        $this->addElement($linkField);

        // submit
        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('base', 'ws_insert_label'));
        $this->addElement($submit);
    }
}