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
 * User preference
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Preference extends OW_ActionController
{
    private $preferenceService;
    private $userService;

    public function __construct()
    {
        parent::__construct();

        $this->preferenceService = BOL_PreferenceService::getInstance();
        $this->userService = BOL_UserService::getInstance();

        $contentMenu = new BASE_CMP_PreferenceContentMenu();
        $contentMenu->getElement('preference')->setActive(true);

        $this->addComponent('contentMenu', $contentMenu);
    }

    public function index( $params )
    {
        $userId = OW::getUser()->getId();

        if ( OW::getRequest()->isAjax() )
        {
            exit;
        }
        
        if ( !OW::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'preference_index'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');

        // -- Preference form --
        
        $preferenceForm = new Form('preferenceForm');
        $preferenceForm->setId('preferenceForm');

        $preferenceSubmit = new Submit('preferenceSubmit');
        $preferenceSubmit->addAttribute('class', 'ow_button ow_ic_save');

        $preferenceSubmit->setValue($language->text('base', 'preference_submit_button'));
        
        $preferenceForm->addElement($preferenceSubmit);

        // --

        $sectionList = BOL_PreferenceService::getInstance()->findAllSections();
        $preferenceList = BOL_PreferenceService::getInstance()->findAllPreference();

        $preferenceNameList = array();
        foreach( $preferenceList as $preference )
        {
            $preferenceNameList[$preference->key] = $preference->key;
        }

        $preferenceValuesList = BOL_PreferenceService::getInstance()->getPreferenceValueListByUserIdList($preferenceNameList, array($userId));

        $formElementEvent = new BASE_CLASS_EventCollector( BOL_PreferenceService::PREFERENCE_ADD_FORM_ELEMENT_EVENT, array( 'values' => $preferenceValuesList[$userId] ) );
        OW::getEventManager()->trigger($formElementEvent);
        $data = $formElementEvent->getData();
        
        $formElements = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $formElementList = array();

        foreach( $formElements as $formElement )
        {
            /* @var $formElement FormElement */

            $formElementList[$formElement->getName()] = $formElement;
        }
        
        $resultList = array();

        foreach( $sectionList as $section )
        {
            foreach( $preferenceList as $preference )
            {
                if( $preference->sectionName === $section->name && !empty( $formElementList[$preference->key] ) )
                {
                    $resultList[$section->name][$preference->key] = $preference->key;

                    $element = $formElementList[$preference->key];
                    $preferenceForm->addElement($element);
                }
            }
        }

        if ( OW::getRequest()->isPost() )
        {
            if( $preferenceForm->isValid($_POST) )
            {
                $values = $preferenceForm->getValues();
                $restul = BOL_PreferenceService::getInstance()->savePreferenceValues($values, $userId);

                if ( $restul )
                {
                    OW::getFeedback()->info($language->text('base', 'preference_preference_data_was_saved'));
                }
                else
                {
                    OW::getFeedback()->warning($language->text('base', 'preference_preference_data_not_changed'));
                }
                
                $this->redirect();
            }
        }

        $this->addForm($preferenceForm);

        $data = array();
        $sectionLabelEvent = new BASE_CLASS_EventCollector( BOL_PreferenceService::PREFERENCE_SECTION_LABEL_EVENT );
        OW::getEventManager()->trigger($sectionLabelEvent);
        $data = $sectionLabelEvent->getData();
        
        $sectionLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $this->assign('preferenceList', $resultList);
        $this->assign('sectionLabels', $sectionLabels);
    }


}