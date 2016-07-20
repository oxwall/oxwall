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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.classes
 * @since 1.8.4
 */
class ADMIN_CLASS_SeoMetaForm extends Form
{
    /**
     * @var array
     */
    private $entities = array();

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $seoData;

    /**
     * @var BOL_SeoService
     */
    private $seoService;

    /**
     * ADMIN_CLASS_SeoMetaForm constructor.
     * @param array $data
     */
    public function __construct( array $data )
    {
        parent::__construct("meta_form");
        $this->seoService = BOL_SeoService::getInstance();
        $this->data = $data;
        $this->seoData = $this->seoService->getMetaData();
        $language = OW::getLanguage();
        $langService = BOL_LanguageService::getInstance();
        $langId = $langService->getCurrent()->getId();

        $disabledItems = isset($this->seoData["disabledEntities"][current($this->data)["sectionKey"]]) ? $this->seoData["disabledEntities"][current($this->data)["sectionKey"]] : array();

        foreach( $this->data as $item ){

            $title = new TextField("seo_title_{$item["entityKey"]}");
            list($prefix, $key) = explode("+", $item["langs"]["title"]);
            $valDto = $langService->getValue($langId, $prefix, $key);
            $title->setValue($valDto ? $valDto->getValue() : $prefix ."+". $key);
            $title->setLabel($language->text("base", "seo_meta_form_element_title_label"));
            $title->setDescription($language->text("base", "seo_meta_form_element_title_desc"));
            $title->addValidator(new MetaInfoValidator());
            $this->addElement($title);

            $desc = new Textarea("seo_description_{$item["entityKey"]}");
            list($prefix, $key) = explode("+",$item["langs"]["description"]);
            $valDto = $langService->getValue($langId, $prefix, $key);
            $desc->setValue($valDto ? $valDto->getValue() : $prefix ."+". $key);
            $desc->setLabel($language->text("base", "seo_meta_form_element_desc_label"));
            $desc->setDescription($language->text("base", "seo_meta_form_element_desc_desc"));
            $desc->addValidator(new MetaInfoValidator());
            $this->addElement($desc);

            $keywords = new Textarea("seo_keywords_{$item["entityKey"]}");
            list($prefix, $key) = explode("+",$item["langs"]["keywords"]);
            $valDto = $langService->getValue($langId, $prefix, $key);
            $keywords->setValue($valDto ? $valDto->getValue() : $prefix ."+". $key);
            $keywords->setLabel($language->text("base", "seo_meta_form_element_keywords_label"));
            $keywords->addValidator(new MetaInfoValidator());
            $this->addElement($keywords);

            $indexCheckbox = new CheckboxField("seo_index_{$item["entityKey"]}");
            $indexCheckbox->setValue(!in_array($item["entityKey"], $disabledItems));
            $indexCheckbox->setLabel($language->text("base", "seo_meta_form_element_index_label"));
            $this->addElement($indexCheckbox);

            $this->entities[$item["entityKey"]] = array(
                "label" => $item["entityLabel"],
                "iconClass" => empty($item["iconClass"]) ? "" : $item["iconClass"],
                "title" => array(
                    "length" => mb_strlen($title->getValue()),
                    "max" => BOL_SeoService::META_TITLE_MAX_LENGTH,
                    "isRed" => mb_strlen($title->getValue()) > BOL_SeoService::META_TITLE_MAX_LENGTH
                ),
                "desc" => array(
                    "length" => mb_strlen($desc->getValue()),
                    "max" => BOL_SeoService::META_DESC_MAX_LENGTH,
                    "isRed" => mb_strlen($desc->getValue()) > BOL_SeoService::META_DESC_MAX_LENGTH
                )
            );
        }

        $submit = new Submit("save");
        $submit->setValue(OW::getLanguage()->text("base", "edit_button"));
        $this->addElement($submit);
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    public function processData( $post )
    {
        $langService = BOL_LanguageService::getInstance();

        if( $this->isValid($post) ){
            $values = $this->getValues();
            $dataToUpdate = array();
            reset($this->data);

            $this->seoData["disabledEntities"][current($this->data)["sectionKey"]] = array();

            foreach( $values as $key => $val )
            {
                if( strstr($key, "seo") )
                {
                    $arr = explode("_", $key);
                    array_shift($arr);
                    $attribute = array_shift($arr);
                    $entity = implode("_", $arr);

                    if( !isset($dataToUpdate[$entity]) )
                    {
                        $dataToUpdate[$entity] = array();
                    }

                    $dataToUpdate[$entity][$attribute] = $val;
                }
            }

            foreach ( $dataToUpdate as $entity => $items )
            {
                if(empty($items["index"]))
                {
                    $this->seoData["disabledEntities"][current($this->data)["sectionKey"]][] = $entity;
                }
            }

            $this->seoService->setMetaData($this->seoData);

            foreach ($this->data as $item)
            {
                if( empty($dataToUpdate[$item["entityKey"]]) )
                {
                    continue;
                }

                foreach ( $item["langs"] as $type => $langKey )
                {
                    if( empty($dataToUpdate[$item["entityKey"]][$type]) )
                    {
                        $dataToUpdate[$item["entityKey"]][$type] = "";
                    }

                    list($prefix, $key) = explode("+", $langKey);

                    $keyDto = $langService->findKey($prefix, $key);

                    if( $keyDto === null )
                    {
                        $prefixDto = $langService->findPrefix($prefix);

                        if( $prefixDto == null )
                        {
                            continue;
                        }

                        $keyDto = new BOL_LanguageKey();
                        $keyDto->setKey($key);
                        $keyDto->setPrefixId($prefixDto->getId());
                        $langService->saveKey($keyDto);
                    }

                    $valueDto = $langService->findValue($langService->getCurrent()->getId(), $keyDto->getId());

                    if ( $valueDto === null )
                    {
                        $valueDto = new BOL_LanguageValue();
                        $valueDto->setKeyId($keyDto->getId());
                        $valueDto->setLanguageId($langService->getCurrent()->getId());
                    }

                    $valueDto->setValue($dataToUpdate[$item["entityKey"]][$type]);
                    $langService->saveValue($valueDto);

                }
            }

            OW_DeveloperTools::getInstance()->clearLanguagesCache();

            return true;
        }

        return false;
    }
}

class MetaInfoValidator extends OW_Validator
{
    /**
     * Class constructor
     *
     * @param array $predefinedValues
     */
    public function __construct()
    {
        $this->setErrorMessage(OW::getLanguage()->text("base", "invalid_meta_error_message"));
    }

    /**
     * Is data valid
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid( $value )
    {
        return strip_tags(trim($value)) == trim($value);
    }

    /**
     * Get js validator
     *
     * @return string
     */
    public function getJsValidator()
    {
        $js = "{
            validate : function( value )
        	{       	
        	    var a = document.createElement('div');
                a.innerHTML = value;
                for (var c = a.childNodes, i = c.length; i--; ) {
                    if (c[i].nodeType == 1){
                        throw " . json_encode($this->getError()) . ";    
                    }
                }
        	
        	    return true;
        	},

        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		}
        }";

        return $js;
    }
}
