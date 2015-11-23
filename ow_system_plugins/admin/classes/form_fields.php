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
 * @author Sardar Madumarov <madumarov@gmail.com>, Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.admin.class
 * @since 1.0
 */
class ColorField extends FormElement
{

    // need to remake with getElementJs method
    public function __construct( $name )
    {
        parent::__construct($name);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('admin')->getStaticJsUrl() . 'color_picker.js');
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

        $output = '<div class="color_input"><input type="text" id="colorh_' . $this->getId() . '" name="' . $this->getName() . '" ' . ( $this->getValue() !== null ? '" value="' . $this->getValue() . '"' : '' ) . ' />' .
            '&nbsp;<input type="button" class="color_button" id="color_' . $this->getId() . '" style="background:' . ( $this->getValue() !== null ? $this->getValue() : '' ) . '" />
        <div style="display:none;"><div id="colorcont_' . $this->getId() . '"></div></div></div>';

        $varName = rand(10, 100000);

        $js = "var callback" . $varName . " = function(color){
            $('#colorh_" . $this->getId() . "').attr('value', color);
            $('#color_" . $this->getId() . "').css({backgroundColor:color});
            window.colorPickers['" . $this->getId() . "'].close();
        };
        new ColorPicker($('#colorcont_" . $this->getId() . "'), callback" . $varName . ", '" . $this->getValue() . "');
        $('#color_" . $this->getId() . "').click(
            function(){
                if( !window.colorPickers )
                {
                    window.colorPickers = {};
                }
                window.colorPickers['" . $this->getId() . "'] = new OW_FloatBox({\$contents:$('#colorcont_" . $this->getId() . "'), \$title:'Color Picker'});
            }
        );";

        OW::getDocument()->addOnloadScript($js);

        return $output;
    }
}

class addValueField extends FormElement
{
    protected $tag;
    protected $disabled;

    // need to remake with getElementJs method
    public function __construct( $name )
    {
        parent::__construct($name);
        
        $tagFieldName = 'input_'  . $this->getName() . '_tag_field';
        $this->tag = new TagsInputField($tagFieldName);
        $this->tag->setMinChars(1);
        $this->value = array();
    }

    public function setValue( $value )
    {
        $values = array();
        
        if ( is_array($value) )
        {
            $this->setArrayValue($value);

            /* if ( isset($value['values']) && is_array($value['values']) )
            {
                $this->setArrayValue($value['values']);
            }
            else
            {
                $this->setArrayValue($value);
            }*/
        }
        else if ( is_string($value) )
        {
            $valueList = json_decode($value, true);

            $result = array();
            
            if ( empty($valueList) )
            {
                return;
            }

            ksort($valueList);
            
            foreach ( $valueList as $order => $val )
            {
                foreach ( $val as $k => $v )
                {
                    $result[$k] = $v;
                }
            }
                
            $this->setArrayValue($result);
        }

        return $this;
    }


    protected function setArrayValue( $value )
    {
        $values = array();

        if ( !empty($value) )
        {
            $count = 0;

            foreach ( $value as $key => $label )
            {
                if ( !empty($key) && isset($label) )
                {
                    $values[$key] = $label;
                    $count++;
                }

                if ( $count >= 32 )
                {
                    break;
                }
            }
        }

        $this->value = $values;
    }


    public function setDisabled( $disabled = true )
    {
        $this->disabled = $disabled;
    }
    /* public function getElementJs()
    {
        $jsString = parent::getElementJs();
        $jsString .= " " . $this->tag->getElementJs();
        return $jsString;
    } */

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        if ( $this->disabled )
        {
            $attributes = $this->attributes;

            unset($attributes['name']);

            $message = OW::getLanguage()->text('admin', 'possible_values_disable_message');

            $event = new OW_Event('admin.get.possible_values_disable_message', array('name' => $this->getName(), 'id' => $this->getId() ), $message);
            OW::getEventManager()->trigger($event);

            $message = $event->getData();

            return UTIL_HtmlTag::generateTag('div', $attributes, true, $message);
        }

        parent::renderInput($params);

        $template = '
                        <div class="clearfix question_value_block" style="cursor:move;">
                                <span class="tag">
                                    <input type="hidden" value="{$value}">
                                    <span class="label" style="max-width:250px;overflow:hidden;">{$label}</span>
                                    <a title='.json_encode(OW::getLanguage()->text('admin', 'remove_value')).' class="remove" href="javascript://"></a>
                                </span>
                        </div>';

        $template = UTIL_String::replaceVars($template, array('label' => '', 'value' => 0));
        
        $addButtonName = $this->getName() . '_add_button';

        $jsDir = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "questions.js");
        
        $json = json_encode(array( 'tagFieldId' => $this->tag->getId(), 'dataFieldId' => $this->getId(), 'value' =>  $this->value, 'order' =>  array_keys($this->value), 'template' => $template ));
        
        OW::getDocument()->addOnloadScript("
            if ( !window.addQuestionValues )
            {
                window.addQuestionValues = {};
            }

            window.addQuestionValues[".json_encode($this->getId())."] = new questionValuesField(" . $json . "); ");

        OW::getLanguage()->addKeyForJs('admin', 'questions_edit_delete_value_confirm_message');
 
        $inputValues = array();
                
        foreach ( $this->value as $key => $val )
        {
            $inputValues[] = array($key => $val);
        }
        
        $html = '<div class="values_list">
                </div>
                <input type="hidden" id='.json_encode($this->getId()).' name='.json_encode($this->getName()).' value=' . json_encode($inputValues) . ' />
                <input type="hidden" id='.json_encode($this->getId()."_deleted_values").' name='.json_encode($this->getName() . "_deleted_values").' value="" />
                <div style="padding-left: 4px;" class="ow_smallmargin">'.OW::getLanguage()->text('admin', 'add_question_value_description').'</div>
                <div class="clearfix">
                    <div class="ow_left" style="width: 260px;">'.($this->tag->renderInput()).'</div>
                    <div class="ow_right">
                        <span class="ow_button">
                            <span class="ow_ic_add">
                                <input type="button" value='.json_encode(OW::getLanguage()->text('admin', 'add_button')).' class="ow_ic_add" name="'.$addButtonName.'">
                            </span>
                        </span>
                    </div>
                </div>';
                
        return $html;
    }
}

class infiniteValueField extends addValueField
{
    protected function setArrayValue( $value )
    {
        $values = array();

        if ( !empty($value) )
        {
            $count = 0;

            foreach ( $value as $key => $label )
            {
                if ( isset($label) )
                {
                    $values[$key] = $label;
                    $count++;
                }
            }
        }
        $this->value = $values;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $html = parent::renderInput($params);

        $template = '
                        <div class="clearfix question_value_block" style="cursor:move;">
                                <span class="tag">
                                    <input type="hidden" value="{$value}">
                                    <span class="label" style="max-width:250px;overflow:hidden;">{$label}</span>
                                    <a title='.json_encode(OW::getLanguage()->text('admin', 'remove_value')).' class="remove" href="javascript://"></a>
                                </span>
                        </div>';

        $template = UTIL_String::replaceVars($template, array('label' => '', 'value' => 0));

        $json = json_encode(
            array(
                'tagFieldId' => $this->tag->getId(),
                'dataFieldId' => $this->getId(),
                'value' =>  $this->value,
                'order' =>  array_keys($this->value),
                'template' => $template
            )
        );

        OW::getDocument()->addOnloadScript("
            if ( !window.addInfiniteQuestionValues )
            {
                window.addInfiniteQuestionValues = {};
            }

            window.addInfiniteQuestionValues[".json_encode($this->getId())."] = new infiniteQuestionValuesField(" . $json . "); ");

        return $html;
    }

}
