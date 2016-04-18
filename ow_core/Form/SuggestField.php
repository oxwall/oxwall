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
 * Form element: CheckboxField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class SuggestField extends FormElement
{
    private $responderUrl;
    private $initialValue;
    private $initialLabel;
    private $minChars = 2;

    public function __construct( $name )
    {
        parent::__construct($name);
    }

    public function setResponderUrl( $responderUrl )
    {
        $this->responderUrl = json_encode($responderUrl);

        return $this;
    }

    public function setInitialLabel( $label )
    {
        $this->initialLabel = $label;

        return $this;
    }

    public function setInitialValue( $value )
    {
        $this->initialValue = $value;

        return $this;
    }

    public function setMinChars( $value )
    {
        $this->minChars = (int) $value;

        return $this;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->value !== null )
        {
            $this->addAttribute('value', $this->value);
            $this->addAttribute('class', 'ow_inputready');
        }

        $this->addAttribute('type', 'text');

        $scriptUrl = OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-suggest.js';
        OW::getDocument()->addScript($scriptUrl);

        $js = '
            var $input = $("#' . $this->getId() . '");
            var initialLabel = $input.val();
            $input.parent().find(".ow_suggest_invitation").click(function(){ $input.focus(); });
            $input.suggest(' . $this->responderUrl . ', {autoSuggest: true, minchars: ' . $this->minChars . ', onFocus: function(first){
                    $(this).removeClass("ow_inputready");
                    if ($(this).val() == initialLabel) {
                        $(this).val("");
                    }
                }, onBlur: function(){
                    var v = $(this).val();
                    
                    if ( !$.trim(v) ) {
                        $(this).val(initialLabel);
                        $(this).addClass("ow_inputready");
                        return 0;
                    }
                }, onAutoSuggest: function(v){
                    if (v) {
                        return false;
                    }
                }});';

        OW::getDocument()->addOnloadScript($js);

        return '<div class="ow_suggest_field">' . HtmlTag::generateTag('input', $this->attributes) . '<div class="ow_suggest_invitation"></div></div>';
    }
}