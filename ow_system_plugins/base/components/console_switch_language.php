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
class BASE_CMP_ConsoleSwitchLanguage extends BASE_CMP_ConsoleDropdownHover
{
    /**
     * Constructor.
     *
     */
public function __construct()
    {
        parent::__construct('');

        $template = OW::getPluginManager()->getPlugin('BASE')->getCmpViewDir() . 'console_switch_language.html';
        $this->setTemplate($template);

        $languages = BOL_LanguageService::getInstance()->getLanguages();
        $session_language_id = BOL_LanguageService::getInstance()->getCurrent()->getId();

        $active_languages = array();

        foreach($languages as $id=>$language)
        {
            if ( $language->status == 'active' )
            {
                $tag = $this->parseCountryFromTag($language->tag);

                $active_lang = array(
                    'id'=>$language->id,
                    'label'=>$tag['label'],
                    'order'=>$language->order,
                    'tag'=>$language->tag,
                    'class'=>"ow_console_lang{$tag['country']}",
                    'url'=> OW::getRequest()->buildUrlQueryString(null, array( "language_id"=>$language->id ) ),
                    'is_current'=>false
                    );

                if ( $session_language_id == $language->id )
                {
                        $active_lang['is_current'] = true;
                        $this->assign('label', $tag['label']);
                        $this->assign('class', "ow_console_lang{$tag['country']}");
                }

                $active_languages[] = $active_lang;
            }
        }

        if ( count($active_languages) <= 1)
        {
            $this->setVisible(false);
            return;
        }

        function sortActiveLanguages($lang1, $lang2 )
        {
            return ( $lang1['order'] < $lang2['order'] ) ? -1 : 1;
        }
        usort($active_languages, 'sortActiveLanguages');

        $switchLanguage = new BASE_CMP_SwitchLanguage($active_languages);
        $this->setContent($switchLanguage->render());
    }

    protected function parseCountryFromTag($tag)
    {
        $tags = preg_match("/^([a-zA-Z]{2})$|^([a-zA-Z]{2})-([a-zA-Z]{2})(-\w*)?$/", $tag, $matches);

        if (empty($matches))
        {
            return array("label"=>$tag, "country"=>"");
        }
        if (!empty($matches[1]))
        {
            $country = strtolower($matches[1]);
            return array("label"=>$matches[1], "country"=>"_".$country);
        }
        else if (!empty($matches[2]))
        {
            $country = strtolower($matches[3]);
            return array("label"=>$matches[2], "country"=>"_".$country);
        }

        return "";
    }

    protected function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('OW.Console.addItem(new OW_ConsoleDropdownHover({$uniqId}, {$contentIniqId}), {$key});', array(
            'key' => $this->getKey(),
            'uniqId' => $this->consoleItem->getUniqId(),
            'contentIniqId' => $this->consoleItem->getContentUniqId()
        ));

        OW::getDocument()->addOnloadScript($js);
    }

}
