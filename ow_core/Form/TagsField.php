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
class TagsField extends FormElement
{
    private $tags;

    public function __construct( $name, $tags = array() )
    {
        parent::__construct($name);
        $this->tags = $tags;
        $this->setValue(implode(',', $this->tags) . "|sep|");
    }

    public static function getTags( $raw )
    {
        $arr = explode(",", str_replace("|sep|", "", $raw));
        $arr = is_array($arr) ? $arr : array();

        foreach ( $arr as $key => $value )
        {
            if ( strlen(trim($value)) > 0 )
                continue;
            unset($arr[$key]);
        }

        return $arr;
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
            $this->addAttribute('value', $this->value);
        }

        $markup = '';

        $tagTpl = '<div class="tags-field-tag-cont" style="margin: 2px 2px 0pt 0pt; padding: 2px 5px; background: rgb(204, 204, 204) none repeat scroll 0% 0%; float: left;"><span class="tags-field-tag">${tag}</span> <a class="ow_lbutton ow_red tags-field-del-tag">x</a></div>';

        $tagsMarkup = '';

        foreach ( $this->tags as $tag )
        {
            $tagsMarkup .= str_replace('${tag}', $tag, $tagTpl);
        }

        $id = $this->getId() . '-cont';

        $tpl = '<span id=${id}>' . '${hidden}' . '<input class="ow_text" type="text">' . '${tags}' . '</span>';

        $js = "if(window.tagsFields === undefined) window.tagsFields = {}; window.tagsFields['{$id}'] = new TagsField('{$id}');";

        $plugin = OW::getPluginManager()->getPlugin('base');

        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'tags_field.js');

        OW::getDocument()->addOnloadScript($js);

        $markup .= str_replace('${tags}', $tagsMarkup, $tpl);

        $markup = str_replace('${id}', $id, $markup);

        $this->addAttribute('type', 'hidden');

        $markup = str_replace('${hidden}', HtmlTag::generateTag('input', $this->attributes), $markup);

        return $markup;
    }

    /**
     * @see FormElement::getElementJs()
     *
     * @return string
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $js .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        $js .= "
			formElement.getValue = function(){

				var value = [];
				var i = 0;
			
			    $.each(  $('input:hidden[name='+ this.name +']', $(this.input.form) ).attr('value').split('|sep|'), function(){
			    	var val = $.trim(this);
			    	if(val.lentgth == 0) return;
			    	
			    	$.each( val.split(','), function(){
			    		var val_d2 = $.trim( this );
			    		if( val_d2.length == 0 ) return;
			
			    		value[i++] = val_d2;
			    	});
				});
				

			    return ( value == undefined ? '' : value );
			};

			formElement.resetValue = function(){
		        window.tagsFields['{$this->getId()}-cont'].reset();
		    };		
		";

        return $js;
    }
}
