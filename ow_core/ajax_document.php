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
 * Description...
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_AjaxDocument extends OW_HtmlDocument
{

    public function __construct()
    {
        $this->setType(OW_Document::AJAX);
    }

    public function getOnloadScript()
    {
        $onloadJS = '';

        ksort($this->onloadJavaScript['items']);

        foreach ( $this->onloadJavaScript['items'] as $priority => $scripts )
        {
            foreach ( $scripts as $script )
            {
                $onloadJS .= $script;
            }
        }

        return $onloadJS;
    }
    
    public function getScriptBeforeIncludes()
    {
        $onloadJS = '';
        
        ksort($this->preIncludeJavaScriptDeclarations);

        foreach ( $this->preIncludeJavaScriptDeclarations as $priority => $types )
        {
            foreach ( $types as $type => $declarations )
            {
                foreach ( $declarations as $declaration )
                {
                    $onloadJS .= $declaration . PHP_EOL;
                }
            }
        }
        
        return $onloadJS;
    }

    public function getScripts()
    {
        $jsUrlList = array();

        ksort($this->javaScripts['items']);

        foreach ( $this->javaScripts['items'] as $priority => $types )
        {
            foreach ( $types as $type => $urls )
            {
                foreach ( $urls as $url )
                {
                    $jsUrlList[] = $url;
                }
            }
        }

        return $jsUrlList;
    }

    public function getStyleSheets()
    {
        $cssFiles = array();

        ksort($this->styleSheets['items']);

        foreach ( $this->styleSheets['items'] as $priority => $scipts )
        {
            foreach ( $scipts as $media => $urls )
            {
                foreach ( $urls as $url )
                {
                    $cssFiles[] = $url;
                }
            }
        }

        return $cssFiles;
    }

    public function getStyleDeclarations()
    {
        $cssCode = '';

        ksort($this->styleDeclarations['items']);

        foreach ( $this->styleDeclarations['items'] as $priority => $mediaTypes )
        {
            foreach ( $mediaTypes as $media => $declarations )
            {
                foreach ( $declarations as $declaration )
                {
                    $cssCode .= $declaration;
                }
            }
        }

        return $cssCode;
    }

    public function render()
    {
        //TODO compile all scripts, styles, assigned vars and send as JSON array
        return '';
    }
}