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
 * About Me widget
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_AboutMeWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $param )
    {
        parent::__construct();

        $userId = $param->additionalParamList['entityId'];

        if ( isset($param->customParamList['content']) )
        {
            $content = $param->customParamList['content'];
        }
        else
        {
            $settings = BOL_ComponentEntityService::getInstance()->findSettingList($param->widgetDetails->uniqName, $userId, array(
                'content'
            ));

            $content = empty($settings['content']) ? null : $settings['content'];
        }

        if ( $param->additionalParamList['entityId'] == OW::getUser()->getId() )
        {
            $this->assign('ownerMode', true);
            $this->assign('noContent', $content === null);

            $this->addForm(new AboutMeForm($param->widgetDetails->uniqName, $content));
        }
        else
        {
            if ( empty($content) )
            {
                $this->setVisible(false);

                return;
            }

            $this->assign('ownerMode', false);

            $content = UTIL_HtmlTag::autoLink($content);
            $this->assign('contentText', nl2br($content));
        }
    }

    public function render()
    {
        return parent::render();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['content'] = array(
            'presentation' => self::PRESENTATION_HIDDEN,
            'label' => '',
            'value' => null
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'about_me_widget_default_title'),
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_INFO,
            self::SETTING_FREEZE => true
        );
    }

    public static function processForm( $data )
    {
        $form = new AboutMeForm();
        return $form->process($data);
    }
}

class AboutMeForm extends Form
{
    private $widgetUniqName;

    public function __construct( $widgetUniqName = null, $content = null )
    {
        parent::__construct('about_me_form');

        $this->widgetUniqName = $widgetUniqName;

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlFor('BASE_CTRL_ComponentPanel', 'ajaxSaveAboutMe'));

        $input = new Textarea('about_me');
        $input->addAttribute('style', 'width: 93%');
        $input->setId('about_me_widget_input');
        $input->setHasInvitation(true);
        $input->setInvitation(OW::getLanguage()->text('base', 'about_me_widget_inv_text'));
        //$input->setRequired(true);
        $input->setValue($content);
        $this->addElement($input);

        $hidden = new HiddenField('widget_uniq_name');
        $hidden->setValue($widgetUniqName);

        $this->addElement($hidden);

        $submit = new Submit('save');

        //$submit->setLabel(OW::getLanguage()->text('base', 'widget_about_me_save_btn'));

        $this->addElement($submit);

        OW::getDocument()->addOnloadScript('
           window.owForms["about_me_form"].bind("success", function(data){
                OW.info(data.message);
           });
           window.owForms["about_me_form"].reset = false;
        ');
    }

    public function process( $data )
    {
        if ( !$this->isValid($data) )
        {
            return false;
        }

        $userId = OW::getUser()->getId();

        if ( !$userId )
        {
            return false;
        }

        $content = htmlspecialchars($data['about_me']);

        BOL_ComponentEntityService::getInstance()->saveComponentSettingList($data['widget_uniq_name'], $userId, array('content' => $content));
        BOL_ComponentEntityService::getInstance()->clearEntityCache(BOL_ComponentEntityService::PLACE_PROFILE, $userId);

        return array('message' => OW::getLanguage()->text('base', 'about_me_widget_content_saved'));
    }
}
