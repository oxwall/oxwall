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
 * Ajax widget panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
abstract class BASE_CTRL_AjaxComponentPanel extends OW_ActionController
{
    private $actions = array();
    private $debug = array();

    /**
     * @see OW_ActionController::init()
     *
     */
    public function init()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $this->registerAction('saveComponentPlacePositions', array($this, 'saveComponentPlacePositions'));
        $this->registerAction('cloneComponent', array($this, 'cloneComponent'));
        $this->registerAction('deleteComponent', array($this, 'deleteComponent'));
        $this->registerAction('getSettingsMarkup', array($this, 'getSettingsMarkup'));
        $this->registerAction('saveSettings', array($this, 'saveSettings'));
        $this->registerAction('savePlaceScheme', array($this, 'savePlaceScheme'));
        $this->registerAction('moveComponentToPanel', array($this, 'moveComponentToPanel'));
        $this->registerAction('reload', array($this, 'reloadComponent'));
    }

    public function registerAction( $actionName, $actionCallback )
    {
        $this->actions[$actionName] = $actionCallback;
    }

    public function processQueue()
    {
        $requestQueue = json_decode(urldecode($_POST['request']), true);

        $responseQueue = array();
        $exception = false;

        foreach ( $requestQueue as $request )
        {
            if ( !isset($this->actions[$request['command']]) )
            {
                continue;
            }
            $command = $request['command'];
            $commandId = $request['commandId'];
            $data = empty($request['data']) ? array() : $request['data'];

            BASE_CLASS_Widget::setPlaceData($request['data']);

            $result = call_user_func($this->actions[$request['command']], $request['data']);
            $responseQueue[$commandId] = $result;
        }

        $response = array(
            'responseQueue' => $responseQueue,
            'debug' => $this->debug
        );

        echo json_encode($response);
        exit();
    }

    protected function debug( $var )
    {
        array_push($this->debug, $var);
    }

    private function checkComponentClass( $componentClassName )
    {
        $reflectionClass = new ReflectionClass($componentClassName);

        if ( !$reflectionClass->isSubclassOf('BASE_CLASS_Widget') )
        {
            throw new LogicException('Component is not configurable');
        }
    }

    protected function getComponentSettingList( $componentClassName, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'getSettingList'), $params["componentId"]);
    }

    protected function getComponentAccess( $componentClassName, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'getAccess'), $params["componentId"]);
    }

    protected function getComponentStandardSettingValueList( $componentClassName, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'getStandardSettingValueList'), !empty($params["componentId"]) ? $params["componentId"] : null);
    }

    protected function validateComponentSettingList( $componentClassName, $settingList, $place, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'validateSettingList'), $settingList, $place, $params["componentId"]);
    }

    protected function processSettingList( $componentClassName, $settingList, $place, $isAdmin, $params = array() )
    {
        $this->checkComponentClass($componentClassName);

        return call_user_func(array($componentClassName, 'processSettingList'), $settingList, $place, $isAdmin, $params["componentId"]);
    }

    protected function getComponentMarkup( BASE_CMP_DragAndDropItem $viewInstance, $renderView = false )
    {

        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce = array();

        if ( $renderView )
        {
            $responce['content'] = $viewInstance->renderView();
        }
        else
        {
            $responce['content'] = $viewInstance->renderScheme();
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

    protected function getSettingFormMarkup( OW_Component $viewInstance )
    {
        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce = array();
        $responce['content'] = $viewInstance->render();

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
