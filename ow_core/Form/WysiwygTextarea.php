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

namespace Oxwall\Core\Form;

use Oxwall\Core\OW;
use Oxwall\Utilities\HtmlTag;

/**
 * Form element: Textarea.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class WysiwygTextarea extends InvitationFormElement
{
    const SIZE_S = 100;
    const SIZE_M = 170;
    const SIZE_L = 300;

    /**
     * @var type
     */
    private $init;

    /**
     * @var array
     */
    private $buttons;

    /**
     * @var BOL_TextFormatService
     */
    private $service;

    /**
     *
     * @var string
     */
    private $size;

    /**
     * @var Textarea
     */
    private $textarea;

    /**
     * @var string
     */
    private $customBodyClass;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name, array $buttons = null, $init = true )
    {
        parent::__construct($name);

        $this->service = \BOL_TextFormatService::getInstance();
        $this->init = (bool) $init;

        if ( !empty($buttons) )
        {
            $buttons = array_unique(array_merge($buttons,
                    array(
                \BOL_TextFormatService::WS_BTN_BOLD,
                \BOL_TextFormatService::WS_BTN_ITALIC,
                \BOL_TextFormatService::WS_BTN_UNDERLINE,
                \BOL_TextFormatService::WS_BTN_LINK,
                \BOL_TextFormatService::WS_BTN_ORDERED_LIST,
                \BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            )));
        }
        else
        {
            $buttons = array(
                \BOL_TextFormatService::WS_BTN_BOLD,
                \BOL_TextFormatService::WS_BTN_ITALIC,
                \BOL_TextFormatService::WS_BTN_UNDERLINE,
                \BOL_TextFormatService::WS_BTN_LINK,
                \BOL_TextFormatService::WS_BTN_ORDERED_LIST,
                \BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            );
        }

        $this->buttons = $this->processButtons($buttons);
        $this->size = self::SIZE_M;

        if ( OW::getRequest()->isMobileUserAgent() )
        {
            $this->textarea = new Textarea($name);
        }

        $stringValidator = new StringValidator(0, 50000);
        $stringValidator->setErrorMessage(OW::getLanguage()->text('base', 'text_is_too_long',
                array('max_symbols_count' => 50000)));

        $this->addValidator($stringValidator);
    }

    /**
     * Returns current buttons set.
     *
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * @param integer $size
     */
    public function setSize( $size )
    {
        $this->size = $size;
    }

    /**
     * Adds custom buttons set.
     *
     * @param array $buttons
     */
    public function setButtons( array $buttons )
    {
        $this->buttons = $this->processButtons($buttons);
    }

    public function getElementJs()
    {
        if ( $this->textarea !== null )
        {
            return $this->textarea->getElementJs();
        }

        $invitation = $this->getHasInvitation() ? $this->getInvitation() : false;

        $jsString = "var formElement = new OwWysiwyg(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ", " . json_encode($invitation) . ");            
        ";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $jsString;
    }

    public function forceAddButtons( array $buttons = array() )
    {
        $this->buttons = array_merge($this->buttons, $buttons);
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->textarea !== null )
        {
            return $this->textarea->renderInput();
        }

        if ( OW::getRegistry()->get('baseWsInit') === null )
        {
            $language = OW::getLanguage();
            $languageDto = \BOL_LanguageService::getInstance()->getCurrent();

            $array = array(
                'editorCss' => OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'htmlarea_editor.css',
                'themeImagesUrl' => OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl(),
                'imagesUrl' => OW::getRouter()->urlFor('BASE_CTRL_MediaPanel', 'index',
                    array('pluginKey' => 'blog', 'id' => '__id__')),
                'labels' => array(
                    'buttons' => array(
                        'bold' => $language->text('base', 'ws_button_label_bold'),
                        'italic' => $language->text('base', 'ws_button_label_italic'),
                        'underline' => $language->text('base', 'ws_button_label_underline'),
                        'orderedlist' => $language->text('base', 'ws_button_label_orderedlist'),
                        'unorderedlist' => $language->text('base', 'ws_button_label_unorderedlist'),
                        'link' => $language->text('base', 'ws_button_label_link'),
                        'image' => $language->text('base', 'ws_button_label_image'),
                        'video' => $language->text('base', 'ws_button_label_video'),
                        'html' => $language->text('base', 'ws_button_label_html'),
                        'more' => $language->text('base', 'ws_button_label_more'),
                        'switchHtml' => $language->text('base', 'ws_button_label_switch_html'),
                    ),
                    'common' => array(
                        'buttonAdd' => $language->text('base', 'ws_add_label'),
                        'buttonInsert' => $language->text('base', 'ws_insert_label'),
                        'videoHeadLabel' => $language->text('base', 'ws_video_head_label'),
                        'htmlHeadLabel' => $language->text('base', 'ws_html_head_label'),
                        'htmlTextareaLabel' => $language->text('base', 'ws_html_textarea_label'),
                        'videoTextareaLabel' => $language->text('base', 'ws_video_textarea_label'),
                        'linkTextLabel' => $language->text('base', 'ws_link_text_label'),
                        'linkUrlLabel' => $language->text('base', 'ws_link_url_label'),
                        'linkNewWindowLabel' => $language->text('base', 'ws_link_new_window_label'),
                    ),
                    'messages' => array(
                        'imagesEmptyFields' => $language->text('base', 'ws_image_empty_fields'),
                        'linkEmptyFields' => $language->text('base', 'ws_link_empty_fields'),
                        'videoEmptyField' => $language->text('base', 'ws_video_empty_field')
                    )
                ),
                'buttonCode' => OW::getThemeManager()->processDecorator('button',
                    array('label' => '#label#', 'class' => 'ow_ic_add mn_submit')),
                'rtl' => ( ( $languageDto !== null && (bool) $languageDto->getRtl() ) ? true : false )
            );



            $script = "window.htmlAreaData = " . json_encode($array);
            OW::getDocument()->addScriptDeclarationBeforeIncludes($script);
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'htmlarea.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'htmlarea.css');
            OW::getRegistry()->set('baseWsInit', true);
        }

        $params = array('toolbar' => $this->buttons, 'size' => $this->size);

        if ( !empty($this->customBodyClass) )
        {
            $params["customClass"] = $this->customBodyClass;
        }

        OW::getDocument()->addOnloadScript("
            $('#{$this->getId()}').get(0).htmlarea = function(){ $(this).htmlarea( " . json_encode($params) . " );};
            $('#{$this->getId()}').get(0).htmlareaFocus = function(){this.jhtmlareaObject.iframe[0].contentWindow.focus();};
            $('#{$this->getId()}').get(0).htmlareaRefresh = function(){if(this.jhtmlareaObject){this.jhtmlareaObject.dispose();$(this).htmlarea( " . json_encode($params) . " );}};
        ");

        if ( $this->value === null && $this->getHasInvitation() )
        {
            $this->addAttribute('value', $this->getInvitation());
            $this->addAttribute('class', 'invitation');
        }

        if ( $this->init )
        {
            OW::getDocument()->addOnloadScript("$('#{$this->getId()}').htmlarea( " . json_encode($params) . " );");
        }

        $this->removeAttribute('value');

        if ( $this->value === null && $this->getHasInvitation() )
        {
            $markup = HtmlTag::generateTag('textarea', $this->attributes, true, $this->getInvitation());
        }
        else
        {
            $markup = HtmlTag::generateTag('textarea', $this->attributes, true,
                    htmlspecialchars(\BOL_TextFormatService::getInstance()->processWsForInput($this->value,
                            array('buttons' => $this->buttons))));
        }


        return $markup;
    }

    public function getValue()
    {
        if ( $this->textarea !== null )
        {
            return nl2br(htmlspecialchars($this->textarea->getValue()));
        }

        return \BOL_TextFormatService::getInstance()->processWsForOutput($this->value, array('buttons' => $this->buttons));
    }

    public function setValue( $value )
    {
        if ( $this->textarea !== null )
        {
            return $this->textarea->setValue($value);
        }

        $this->value = $value;
    }

    private function processButtons( $buttons )
    {
        $keysToUnset = array();

        if ( in_array(\BOL_TextFormatService::WS_BTN_HTML, $buttons) && !$this->service->isCustomHtmlAllowed() )
        {
            $keysToUnset[] = array_search(\BOL_TextFormatService::WS_BTN_HTML, $buttons);
        }

        if ( !$this->service->isRichMediaAllowed() )
        {
            if ( in_array(\BOL_TextFormatService::WS_BTN_VIDEO, $buttons) )
            {
                $keysToUnset[] = array_search(\BOL_TextFormatService::WS_BTN_VIDEO, $buttons);
            }

            if ( in_array(\BOL_TextFormatService::WS_BTN_IMAGE, $buttons) )
            {
                $keysToUnset[] = array_search(\BOL_TextFormatService::WS_BTN_IMAGE, $buttons);
            }
        }

        foreach ( $keysToUnset as $key )
        {
            if ( !empty($buttons[$key]) )
            {
                unset($buttons[$key]);
            }
        }

        return array_values($buttons);
    }

    public function getCustomBodyClass()
    {
        return $this->customBodyClass;
    }

    public function setCustomBodyClass( $customBodyClass )
    {
        $this->customBodyClass = $customBodyClass;
    }

    /**
     * Makes form element required.
     *
     * @param boolean $value
     * @return FormElement
     */
    public function setRequired( $value = true )
    {
        if ( $value )
        {
            $this->addValidator(new WyswygRequiredValidator());
        }
        else
        {
            foreach ( $this->validators as $key => $validator )
            {
                if ( $validator instanceof WyswygRequiredValidator )
                {
                    unset($this->validators[$key]);
                    break;
                }
            }
        }

        return $this;
    }
}