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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_utilities
 * @since 1.8.1
 */

class BASE_CLASS_LanguageParamsUrl extends BASE_CLASS_LanguageParams {

    protected $route;
    protected $controller;
    protected $action;
    protected $params = array();

    /**
     * @param string $route
     * @param array $params
     */

    public function setRoute( $route, $params = array() ) {
        $this->route = $route;
        $this->params = $params;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param array $params
     */

    public function setActionController( $controller, $action, $params = array() ) {
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(array('route' => $this->route, 'controller' =>$this->controller, 'action' => $this->action, 'params' => $this->params ));
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        if ( !empty($data) ) {
            $this->route = !empty($data['route']) ? $data['route'] : null;
            $this->controller = !empty($data['controller']) ? $data['controller'] : null;
            $this->action = !empty($data['action']) ? $data['action'] : null;
            $this->params = !empty($data['params']) ? $data['params'] : array();
        }
    }

    public function fetch()
    {
        if ( !empty($this->route) ) {
            return OW::getRouter()->urlForRoute($this->route, $this->params);
        }

        if ( !empty($this->controller) && !empty($this->action)  ) {
            return OW::getRouter()->urlForRoute($this->controller, $this->action, $this->params);
        }

        return null;
    }
}