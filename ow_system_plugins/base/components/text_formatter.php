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
 * Text Formatter Component
 *
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_TextFormatter extends OW_Component
{
    public static $tagList = array(
        array(
            'tag' => 'a',
            'pair' => true,
            'attributes' => array('href')
        ),
        array(
            'tag' => 'img',
            'pair' => false,
            'attributes' => array('src', 'class', 'style')
        ),
        array(
            'tag' => 'strong',
            'pair' => true,
            'attributes' => array()
        ),
        array(
            'tag' => 'u',
            'pair' => true,
            'attributes' => array()
        ),
        array(
            'tag' => 'i',
            'pair' => true,
            'attributes' => array()
        )
    );

    function __construct( $plugin, $elId, $dl, $dr, $controls, $template = null )
    {
        parent::__construct();

        if ( $dl == '[' && $dr == ']' )
        {
            $this->assign('bb', 'true');
        }
        else
        {
            $this->assign('bb', 'false');
        }

        $this->assign('elId', $elId);

        if ( $template == null )
        {
            $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'text_formatter.html');
        }

        $this->assign('plugin', $plugin);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-fieldselection.js');

        $tid = $elId;

        $js = '$(function(){';

        $mlist = array('image');

        $this->assign('mlist', $mlist);

        foreach ( $controls as $ctrl )
        {
            $isPaired = !(isset($ctrl['isPaired']) && $ctrl['isPaired'] == false) ? 1 : 0;

            if ( $ctrl['id'] == 'lid' )
            {
                $js .= "
	$('#{$ctrl['id']}').click(function(){

		var open = '{$ctrl['open']}', close = '{$dl}/a$dr';

		var s = $('#{$tid}').getSelection();

		var s2 = $('#{$tid}').val();

		if(s.text.length == 0){
			if(typeof(this.closed) == 'undefined' || this.closed == true){
				this.url = prompt('{$ctrl['extra']['#url#']['inv']}', '{$ctrl['extra']['#url#']['def']}');
				
				if(!this.url) return;
				
				$(this).html('/'+$(this).html());

				this.closed = false;
				$('#{$tid}').val( s2.substr(0, s.start) + open.replace('#url#', this.url) +  s2.substr(s.end, s2.length-1 ));
				setCaretPosition('{$tid}', s.start+open.replace('#url#', this.url).length + s.text.length);
			}
			else{

				$('#{$tid}').val( s2.substr(0, s.start) + close +  s2.substr(s.end, s2.length-1 ));
				setCaretPosition('{$tid}', s.start+close.length);
				var v = $(this).html().toString();
				$(this).html( v.substr( 1, v.length) );

				this.closed = !this.closed;
			}

			return;
		}

		this.url = prompt('{$ctrl['extra']['#url#']['inv']}', '{$ctrl['extra']['#url#']['def']}');

		if(!this.url) return;
		
		var r =  s2.substr(0, s.start) + open.replace('#url#', this.url) + s.text + close +  s2.substr(s.end, s2.length-1 );

		$('#{$tid}').val(r);

		setCaretPosition('{$tid}', s.start+open.replace('#url#', this.url).length + s.text.length + close.length);

		$('#{$tid}').trigger('keydown');

	});					
					";
                continue;
            };

            $ctrl['close'] = empty($ctrl['close']) ? '' : $ctrl['close'];

            $js.="
					$('#{$ctrl['id']}').click( function(){

					    window.tf_st = $('#{$tid}').attr('scrollTop');

						var open = '{$ctrl['open']}', close  = '{$ctrl['close']}';

						var s = $('#{$tid}').getSelection();

						var s2 = $('#{$tid}').val();
						
						if(s.text.length == 0 || !{$isPaired}){
							if(typeof(this.closed) == 'undefined' || this.closed == true){
								$('#{$tid}').val( s2.substr(0, s.start) + open + s2.substr(s.end, s2.length-1) );

								setCaretPosition('{$tid}', s.start+open.length);

								if({$isPaired}){
									this.closed = false;
									$(this).html('/'+$(this).html());
								}
							}
							else{

								$('#{$tid}').val( s2.substr(0, s.start) + close + s2.substr(s.end, s2.length-1) );

								var v = $(this).html().toString();
								$(this).html( v.substr( 1, v.length) );
								setCaretPosition('{$tid}', s.start+close.length)
								this.closed = !this.closed;
							}

							return;
						}

						var r =  s2.substr(0, s.start) + open + s.text + close +  s2.substr(s.end, s2.length-1 );
				
						$('#{$tid}').val(r);
						setCaretPosition('{$tid}', s.start+open.length + s.text.length + close.length);
						$('#{$tid}').trigger('keydown');
					});
				";
        }

        $js .= '});';

        OW::getDocument()->addOnloadScript($js, 100);

        $this->assign('controls', $controls);
        $this->assign('tid', $tid);


        OW::getDocument()->addScriptDeclaration("
			function getRangeObject(selectionObject) {
				if (selectionObject.getRangeAt)
					return selectionObject.getRangeAt(0);
				else { // Safari!
					var range = document.createRange();
					range.setStart(selectionObject.anchorNode,selectionObject.anchorOffset);
					range.setEnd(selectionObject.focusNode,selectionObject.focusOffset);
					return range;
				}
			}

			function setCaretPosition(elemId, caretPos) {
			    var elem = document.getElementById(elemId);

			    var \$elId = $(elem);

			    var scrollTop = \$elId.attr( 'scrollTop' );
			    elem.focus();
			    \$elId.attr( 'scrollTop', window.tf_st );
			    elem.setSelectionRange(caretPos, caretPos);
			}		
		");
    }

    /**
     * Convert BB tegs to html
     * @param  string $txt
     * @param  array $tagList
     *
     * example :
     * $tagList = array(
     *      array(
     *          'tag' => 'a',
     *          'pair' = true,
     *          'attributes' => array( 'href' )
     *      )
     * };
     *
     * @return string
     *
     */
    public static function fromBBtoHtml( $txt, array $tagList = null )
    {
        if ( empty($tagList) )
        {
            $tagList = self::$tagList;
        }

        $result = $txt;

        foreach ( $tagList as $tag )
        {
            if ( empty($tag['tag']) || !isset($tag['pair']) )
            {
                continue;
            }

            $tagName = $tag['tag'];
            $pair = $tag['pair'];
            $attributes = (!empty($tag['attributes']) && is_array($tag['attributes']) ) ? $tag['attributes'] : array();

            $pairRegexp = $pair ? '(.*?)?\[[\s]*\/[\s]*' . $tagName . '\s*\]' : '';

            $regexp = '/\[\s*' . $tagName . '\s*.*?\]' . $pairRegexp . '/s';

            preg_match_all($regexp, $result, $matches);
            if ( preg_match_all($regexp, $result, $matches) )
            {
                foreach ( $matches[0] as $key => $match )
                {
                    $attr = '';
                    $tagString = $match;

                    foreach ( $attributes as $attribute )
                    {
                        if ( preg_match('/' . $attribute . '=\'.*?\'/', $tagString, $attrMatches) )
                        {
                            $attr .= $attrMatches[0] . ' ';
                        }
                        else if ( preg_match('/' . $attribute . '=".*?"/', $tagString, $attrMatches) )
                        {
                            $attr .= $attrMatches[0] . ' ';
                        }
                    }

                    $string = '<' . $tagName . ' ' . $attr;
                    if ( $pair )
                    {
                        $string .= '>';
                    }
                    else
                    {
                        $string .= '/>';
                    }

                    if ( $pair && !empty($matches[1][$key]) )
                    {
                        $innerHtml = $matches[1][$key];
                        $string .= $innerHtml . '</' . $tagName . '>';
                    }

                    $result = BASE_CMP_TextFormatter::mbStrReplace($result, $tagString, $string);
                }
            }
        }

        return $result;
    }

    public static function mbStrReplace( $haystack, $search, $replace, $offset=0, $encoding='auto' )
    {
        $lenSch = mb_strlen($search, $encoding);
        $lenRep = mb_strlen($replace, $encoding);

        while ( ($offset = mb_strpos($haystack, $search, $offset, $encoding)) !== false )
        {
            $haystack = mb_substr($haystack, 0, $offset, $encoding) . $replace . mb_substr($haystack, $offset + $lenSch, mb_strlen($haystack), $encoding);
            $offset = $offset + $lenRep;

            if ( $offset > mb_strlen($haystack, $encoding) )
            {
                break;
            }
        }
        return $haystack;
    }
}