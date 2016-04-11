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
 * Base class for renderable elements. Allows to assign vars and compile HTML using template engine.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
abstract class OW_Renderable
{
    /**
     * List of added components.
     *
     * @var array
     */
    protected $components = array();

    /**
     * List of registered forms.
     *
     * @var array
     */
    protected $forms = array();

    /**
     * List of assigned vars.
     *
     * @var array
     */
    protected $assignedVars = array();

    /**
     * Template path.
     *
     * @var string
     */
    protected $template;

    /**
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var array
     */
    private static $renderedClasses = array();

    /**
     * @var boolean
     */
    private static $devMode = false;

    /**
     * Getter for renderedClasses static property
     * 
     * @return array
     */
    public static function getRenderedClasses()
    {
        return self::$renderedClasses;
    }

    /**
     * Sets developer mode.
     * 
     * @param boolean $mode 
     */
    public static function setDevMode( $mode )
    {
        self::$devMode = (bool) $mode;
    }

    /**
     * Sets vomponent visibility.
     *
     * @param boolean $visible
     * @return OW_Renderable
     */
    public function setVisible( $visible )
    {
        $this->visible = (bool) $visible;
        return $this;
    }

    /**
     * Checks if component is visible.
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate( $template )
    {
        $this->template = $template;
    }

    /**
     * Adds component to renderable object.
     *
     * @param string $key
     * @param OW_Renderable $component
     */
    public function addComponent( $key, OW_Renderable $component )
    {
        $this->components[$key] = $component;
    }

    /**
     * Returns added component by key.
     *
     * @param string $key
     * @return OW_Component
     */
    public function getComponent( $key )
    {
        return ( isset($this->components[$key]) ? $this->components[$key] : null );
    }

    /**
     * Deletes added component.
     *
     * @param string $key
     */
    public function removeComponent( $key )
    {
        if ( isset($this->components[$key]) )
        {
            unset($this->components[$key]);
        }
    }

    /**
     * Adds form to renderable object.
     *
     * @param Form $form
     */
    public function addForm( Form $form )
    {
        $this->forms[$form->getName()] = $form;
    }

    /**
     * Returns added form by key.
     *
     * @param string $key
     * @return OW_Form
     */
    public function getForm( $name )
    {
        return ( isset($this->forms[$name]) ? $this->forms[$name] : null );
    }

    /**
     * Assigns variable.
     *
     * @param string $name
     * @param mixed $value
     */
    public function assign( $name, $value )
    {
        $this->assignedVars[$name] = $value;
    }

    /**
     * @param string $varName
     */
    public function clearAssign( $varName )
    {
        if ( isset($this->assignedVars[$varName]) )
        {
            unset($this->assignedVars[$varName]);
        }
    }

    public function onBeforeRender()
    {
        
    }

    /**
     * Returns rendered markup.
     *
     * @return string
     */
    public function render()
    {
        $this->onBeforeRender();
        if ( !$this->visible )
        {
            return '';
        }

        // TODO additional check
        if ( $this->template === null )
        {
            throw new LogicException('No template was provided for render! Class `' . get_class($this) . '`.');
        }

        $className = get_class($this);
        OW::getEventManager()->trigger(new OW_Event("core.performance_test", array("key" => "renderable_render.start", "params" => array("class" => $className))));

        $viewRenderer = OW_ViewRenderer::getInstance();

        $prevVars = $viewRenderer->getAllAssignedVars();

        if ( !empty($this->components) )
        {
            $renderedCmps = array();

            foreach ( $this->components as $key => $value )
            {
                $renderedCmps[$key] = $value->isVisible() ? $value->render() : '';
            }

            $viewRenderer->assignVars($renderedCmps);
        }

        if ( !empty($this->forms) )
        {
            $viewRenderer->assignVar('_owForms_', $this->forms);
        }

        $viewRenderer->assignVars($this->assignedVars);

        $renderedMarkup = $viewRenderer->renderTemplate($this->template);

        $viewRenderer->clearAssignedVars();

        $viewRenderer->assignVars($prevVars);

        // temp dirty data collect for dev tool
        if ( self::$devMode )
        {
            self::$renderedClasses[$className] = $this->template;
        }

        OW::getEventManager()->trigger(new OW_Event("core.performance_test", array("key" => "renderable_render.end", "params" => array("class" => $className))));

        return $renderedMarkup;
    }
}
