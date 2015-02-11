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
 * Language value edit component class. 
 *
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_LanguageValueEdit extends OW_Component
{
    public $eventBased;
    
	public function __construct( $prefix, $key, $eventBased = false )
	{
		parent::__construct();

		$this->eventBased = $eventBased;
		
		$this->addForm(new LanguageValueEditForm($prefix, $key, $this));
	}

	public static function process( $prefix, $key )
	{
            $languageService = BOL_LanguageService::getInstance();
            $list = $languageService->findActiveList();
            $currentLanguageId = OW::getLanguage()->getCurrentId();
            $currentLangValue = "";

            foreach ( $list as $item )
            {
                    $keyDto = $languageService->findKey($prefix, $key);

                    if ( empty($keyDto) )
                    {
                        $prefixDto = $languageService->findPrefix($prefix);
                        $keyDto = $languageService->addKey($prefixDto->getId(), $key);
                    }

                    $value = trim($_POST['lang'][$item->getId()][$prefix][$key]);

            
                    if ( mb_strlen(trim($value)) == 0 || $value == json_decode('"\u00a0"') ) // stupid hack
                    {
                        $value = '&nbsp;';
                    }

                    $dto = $languageService->findValue($item->getId(), $keyDto->getId());

                    if ( $dto !== null )
                    {
                        $event = new OW_Event('admin.before_save_lang_value', array('dto'=>$dto));
                        OW::getEventManager()->trigger($event);

                            if ( $dto->getValue() !== $value )
                            {
                                $languageService->saveValue($dto->setValue($value));
                            }
                    }
                    else
                    {
                            $dto = $languageService->addValue($item->getId(), $prefix, $key, $value);
                    }

                if ( (int) $currentLanguageId === (int) $item->getId() )
                {
                    $currentLangValue = $value;
                }
            }

            exit(json_encode(array('result' => 'success', 'prefix' => $prefix, 'key' => $key, 'value' => $currentLangValue)));
	}
}

class LanguageValueEditForm extends Form
{
    /**
     * 
     * Constructor
     * @param $prefix
     * @param $key
     * @param BASE_CMP_LanguageValueEdit $parent
     */
	public function __construct( $prefix, $key, $parent )
	{
		parent::__construct('lang-values-edit');

		$this->setAjax(true);
		$this->setAction(OW::getRouter()->urlFor('ADMIN_CTRL_Languages', 'ajaxEditLangs')."?prefix={$prefix}&key={$key}");

		$languageService = BOL_LanguageService::getInstance();
		$list = $languageService->findActiveList();

		$parent->assign('langs', $list);
		$parent->assign('prefix', $prefix);
		$parent->assign('key', $key);

		foreach ( $list as $item )
		{
			$textArea = new Textarea("lang[{$item->getId()}][{$prefix}][{$key}]");
			$dto = $languageService->getValue($item->getId(), $prefix, $key);

			$value = ($dto !== null)? $dto->getValue(): '';

			$textArea->setValue($value);

			$this->addElement($textArea);
		}

		$submit = new Submit('submit');

		$submit->setValue('Save');

		if ( !$parent->eventBased )
		{
    		$jsString = 'owForms[{$formName}].bind("success", function(json){
                if ( json["result"] == "success") {
                    var fb = document.ajaxLangValueEditForms[ json["prefix"] +"-"+ json["key"] ];
                    var ff = document.ajaxLangValueEditForms[json["prefix"] +"-"+json["key"]+"callback"];
                    ff(json);
                    fb.close();
                }
            })';
		}
		else
		{
            $jsString = 'owForms[{$formName}].bind("success", function(json){
                if ( json["result"] == "success") {
                    OW.trigger("admin.language_key_edit_success", [json], this);
                }
            })';
		}
		
		$script = UTIL_JsGenerator::composeJsString($jsString, array(
		  'formName' => $this->getName()
		));
		
		OW::getDocument()->addOnloadScript($script);

		$this->addElement($submit);
	}
}
