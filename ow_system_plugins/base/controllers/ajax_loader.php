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
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_AjaxLoader extends OW_ActionController
{
    public function init()
    {
        if( !OW::getRequest()->isAjax() )
        {
           throw new Redirect404Exception();
        }
    }

    public function component()
    {
        if ( empty($_GET['cmpClass']) )
        {
            exit;
        }

        $cmpClass = trim($_GET['cmpClass']);
        $params = empty($_POST['params']) ? array() : json_decode($_POST['params'], true);

        if ( strpos($cmpClass, "_CMP_") === false && strpos($cmpClass, "_MCMP_") === false )
        {
            throw new LogicException('Only OxWall components are available for rendering!');
        }

        $cmp = OW::getClassInstanceArray($cmpClass, $params);
        $responce = $this->getComponentMarkup($cmp);

        exit(json_encode($responce));
    }

    protected function getComponentMarkup( OW_Component $cmp )
    {

        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce = array();

        $responce['content'] = trim($cmp->render());

        $beforeIncludes = $document->getScriptBeforeIncludes();
        if ( !empty($beforeIncludes) )
        {
            $responce['beforeIncludes'] = $beforeIncludes;
        }

        foreach ( $document->getScripts() as $script )
        {
            $responce['scriptFiles'][] = $script;
        }

        $onloadScript = $document->getOnloadScript();
        if ( !empty($onloadScript) )
        {
            $responce['onloadScript'] = $onloadScript;
        }

        $styleDeclarations = $document->getStyleDeclarations();
        if ( !empty($styleDeclarations) )
        {
            $responce['styleDeclarations'] = $styleDeclarations;
        }

        $styleSheets = $document->getStyleSheets();
        if ( !empty($styleSheets) )
        {
            $responce['styleSheets'] = $styleSheets;
        }

        return $responce;
    }
}