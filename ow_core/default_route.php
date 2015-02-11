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
 * The class is responsible for default stratagy of url generation.
 * All URIs (except URIs working with custom routes) are generated and decomposed by default route.
 * DefaultRoute class can be extended and modified to change whole url generation strategy.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_DefaultRoute
{
    private $controllerNamePrefix = 'CTRL';

    /**
     * @return string
     */
//    public function getControllerNamePrefix()
//    {
//        return $this->controllerNamePrefix;
//    }
//
//    /**
//     * @param string $controllerNamePrefix
//     */
//    public function setControllerNamePrefix( $controllerNamePrefix )
//    {
//        $this->controllerNamePrefix = $controllerNamePrefix;
//    }

    /**
     * Generates URI using provided params.
     *
     * @throws InvalidArgumentException
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return string
     */
    public function generateUri( $controller, $action = null, array $params = array() )
    {
        if ( empty($controller) || ( empty($action) && !empty($params) ) )
        {
            throw new InvalidArgumentException("Can't generate uri for empty controller/action !");
        }

        $ctrlParts = explode('_', $controller);
        $moduleNamePrefix = str_replace('ctrl', '', strtolower($ctrlParts[1]));

        if ( strlen($moduleNamePrefix) > 0 )
        {
            $moduleNamePrefix .= '-';
        }

        $controller = trim($controller);
        $action = ( $action === null ) ? null : trim($action);

        $paramString = '';

        foreach ( $params as $key => $value )
        {
            $paramString .= trim($key) . '/' . urlencode(trim($value)) . '/';
        }

        $className = str_replace(OW::getAutoloader()->getPackagePointer($controller) . '_', '', $controller);

        $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey($controller));

        if ( $action === null )
        {
            return strtolower($plugin->getModuleName()) . '/' . substr(UTIL_String::capsToDelimiter($className, '-'), 1);
        }

        return $moduleNamePrefix . strtolower($plugin->getModuleName()) . '/' . substr(UTIL_String::capsToDelimiter($className, '-'), 1) . '/' . UTIL_String::capsToDelimiter($action, '-') . '/' . $paramString;
    }

    /**
     * Returns dispatch params (controller, action, vars) for provided URI.
     * 
     * @throws Redirect404Exception
     * @param string $uri
     * @return array
     */
    public function getDispatchAttrs( $uri )
    {//TODO check if method is in try/catch
        $uriString = UTIL_String::removeFirstAndLastSlashes($uri);

        $uriArray = explode('/', $uriString);

        if ( sizeof($uriArray) < 2 )
        {
            throw new Redirect404Exception('Invalid uri was provided for routing!');
        }

        $controllerNamePrefixAdd = '';

        if ( strstr($uriArray[0], '-') )
        {
            $uriPartArray = explode('-', $uriArray[0]);
            $uriArray[0] = $uriPartArray[1];
            $controllerNamePrefixAdd = strtoupper($uriPartArray[0]);
        }

        $dispatchAttrs = array();

        $classPrefix = null;

        $arraySize = sizeof($uriArray);

        for ( $i = 0; $i < $arraySize; $i++ )
        {
            if ( $i === 0 )
            {
                try
                {
                    $classPrefix = strtoupper(OW::getPluginManager()->getPluginKey($uriArray[$i])) . '_' . $controllerNamePrefixAdd . $this->controllerNamePrefix;
                }
                catch ( InvalidArgumentException $e )
                {
                    throw new Redirect404Exception('Invalid uri was provided for routing!');
                }

                continue;
            }

            if ( $i === 1 )
            {
                if ( $classPrefix === null )
                {
                    throw new Redirect404Exception('Invalid uri was provided for routing!');
                }

                $ctrClass = $classPrefix . '_' . UTIL_String::delimiterToCaps('-' . $uriArray[$i], '-');

                if ( !file_exists(OW::getAutoloader()->getClassPath($ctrClass)) )
                {
                    throw new Redirect404Exception('Invalid uri was provided for routing!');
                }

                $dispatchAttrs['controller'] = $ctrClass;
                continue;
            }

            if ( $i === 2 )
            {
                $dispatchAttrs['action'] = UTIL_String::delimiterToCaps($uriArray[$i], '-');
                continue;
            }

            if ( $i % 2 !== 0 )
            {
                $dispatchAttrs['vars'][$uriArray[$i]] = null;
            }
            else
            {
                $dispatchAttrs['vars'][$uriArray[$i - 1]] = $uriArray[$i];
            }
        }

        return $dispatchAttrs;
    }
}
