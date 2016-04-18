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
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @since 1.8.3
 */
class MobileWysiwygTextarea extends Textarea
{
    /**
     * Plugin key
     * @var string
     */
    protected $pluginKey;

    /**
     * Text format service
     * @var BOL_TextFormatService
     */
    protected $textFormatService;

    /**
     * Buttons list
     *
     * @var array
     */
    private $buttons = array(
        \BOL_TextFormatService::WS_BTN_BOLD,
        \BOL_TextFormatService::WS_BTN_ITALIC,
        \BOL_TextFormatService::WS_BTN_UNDERLINE,
        \BOL_TextFormatService::WS_BTN_LINK,
        \BOL_TextFormatService::WS_BTN_IMAGE,
        \BOL_TextFormatService::WS_BTN_VIDEO
    );

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $pluginKey
     * @param array $buttons
     */
    public function __construct( $name, $pluginKey = 'blog', array $buttons = array() )
    {
        parent::__construct($name);

        $this->pluginKey = $pluginKey;
        $this->textFormatService = \BOL_TextFormatService::getInstance();

        // init list of buttons
        if ( !empty($buttons) )
        {
            $this->buttons = $buttons;
        }

        // remove image and video buttons
        if ( !$this->textFormatService->isRichMediaAllowed() )
        {
            $imageIndex = array_search(\BOL_TextFormatService::WS_BTN_IMAGE, $this->buttons);

            if ( $imageIndex !== false )
            {
                unset($this->buttons[$imageIndex]);
            }

            $videoIndex = array_search(\BOL_TextFormatService::WS_BTN_VIDEO, $this->buttons);

            if ( $videoIndex !== false )
            {
                unset($this->buttons[$videoIndex]);
            }
        }

        $stringValidator = new StringValidator(0, 50000);
        $stringValidator->setErrorMessage(OW::getLanguage()->text('base', 'text_is_too_long',
                array('max_symbols_count' => 50000)));

        $this->addValidator($stringValidator);
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {

        if ( OW::getRegistry()->get('baseWsInit') === null )
        {
            if ( in_array(\BOL_TextFormatService::WS_BTN_IMAGE, $this->buttons) )
            {
                OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.html5_upload.js');
            }

            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'suitup.jquery.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'suitup.css');

            // register js langs
            OW::getLanguage()->addKeyForJs('base', 'ws_button_label_link');
            OW::getLanguage()->addKeyForJs('base', 'ws_button_label_video');
            OW::getLanguage()->addKeyForJs('base', 'ws_error_video');

            OW::getRegistry()->set('baseWsInit', true);
        }

        $this->addAttribute('class', 'owm_suitup_wyswyg');
        $js = \Oxwall\Utilities\JsGenerator::newInstance();

        $js->addScript('$("#" + {$uniqId}).suitUp({$buttons}, {$imageUploadUrl}, {$embedUrl}).show();',
            array(
            'buttons' => $this->buttons,
            'uniqId' => $this->getId(),
            'imageUploadUrl' => OW::getRouter()->urlFor('BASE_CTRL_MediaPanel', 'ajaxUpload',
                array(
                'pluginKey' => $this->pluginKey
            )),
            'embedUrl' => OW::getRouter()->urlFor('BASE_MCTRL_Oembed', 'getAjaxEmbedCode')
        ));

        OW::getDocument()->addOnloadScript($js);

        return parent::renderInput($params);
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