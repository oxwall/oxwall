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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.8.3
 */
class CaptchaField extends FormElement
{
    const CAPTCHA_PREFIX = 'ow_captcha_';

    public $jsObjectName = null;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'text');
        $this->jsObjectName = self::CAPTCHA_PREFIX . preg_replace('/[^\d^\w]/', '_', $this->getId());
        $this->setRequired();
        $this->addAttribute('style', 'width:100px;');
        $this->addValidator(new CaptchaValidator());
    }

    public function addValidator( $validator )
    {
        if ( $validator instanceof CaptchaValidator )
        {
            $validator->setJsObjectName($this->jsObjectName);
        }

        return parent::addValidator($validator);
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

        if ( $this->value !== null )
        {
            $this->addAttribute('value', str_replace('"', '&quot;', $this->value));
        }

        $captchaUrl = OW_URL_HOME . 'captcha.php';
        $captchaResponderUrl = OW::getRouter()->urlFor('BASE_CTRL_Captcha', 'ajaxResponder');
        $captchaClass = $this->getName() . '_' . $this->getId();
        $uniqueId = md5(time());

        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "captcha.js");

        $string = ' window.' . $this->jsObjectName . ' = new OW_Captcha( ' . json_encode(array('captchaUrl' => $captchaUrl,
                'captchaClass' => $captchaClass,
                'captchaId' => $this->getId(),
                'responderUrl' => $captchaResponderUrl
            )) . ');';

        OW::getDocument()->addOnloadScript($string);

        return '<div class="' . $captchaClass . '">
                    <div class="ow_automargin clearfix" style="width: 230px;">
                            <div class="ow_left"><img src="' . $captchaUrl . '" id="siimage"></div>
                            <div class="ow_right" style="padding-top: 21px;"><span class="ic_refresh ow_automargin" id="siimage_refresh" style="cursor:pointer;"></span></div>
                    </div>
                    <div style="padding-top: 10px;">' . HtmlTag::generateTag('input', $this->attributes) . '</div>
               </div>';
    }
}
