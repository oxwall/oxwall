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
 * The class provides access to event system of framework.
 * Works as simple as it can be - plugins add PHP listeners (callbacks) to manager stack.
 * When event triggered the whole stack is called.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_EventManager
{
    /* list of predefined system events: application lifecycle */
    const ON_APPLICATION_INIT = 'core.app_init';
    const ON_PLUGINS_INIT = 'core.plugins_init';
    const ON_AFTER_ROUTE = 'core.after_route';
    const ON_AFTER_REQUEST_HANDLE = 'core.after_dispatch';
    const ON_BEFORE_DOCUMENT_RENDER = 'core.before_document_render';
    const ON_AFTER_DOCUMENT_RENDER = 'core.document_render';
    const ON_FINALIZE = 'core.finalize';
    const ON_AFTER_PLUGIN_INSTALL = 'core.plugin_install';
    const ON_BEFORE_PLUGIN_UNINSTALL = 'core.plugin_uninstall';
    const ON_AFTER_PLUGIN_UNINSTALL = 'core.after_plugin_uninstall';
    const ON_AFTER_PLUGIN_ACTIVATE = 'core.plugin_activate';
    const ON_BEFORE_PLUGIN_DEACTIVATE = 'core.plugin_deactivate';
    const ON_AFTER_PLUGIN_DEACTIVATE = 'core.after_plugin_deactivate';
    const ON_AFTER_PLUGIN_UPDATE = "core.plugin_update";

    const ON_CLI_RUN = 'cli.run';

    /* list of predefined system events: general events  */
    const ON_BEFORE_USER_REGISTER = 'base.before_user_register';
    const ON_BEFORE_USER_LOGIN = 'base.before_user_login';
    const ON_USER_REGISTER = 'base.user_register';
    const ON_USER_UNREGISTER = 'base.user_unregister';
    const ON_USER_LOGIN = 'base.user_login';
    const ON_USER_LOGOUT = 'base.user_logout';
    const ON_USER_SUSPEND = 'base.user_suspend';
    const ON_USER_UNSUSPEND = 'base.user_unsuspend';
    const ON_USER_EDIT = 'base.user_edit';
    const ON_USER_EDIT_BY_ADMIN = 'base.user_edit_by_admin';
    const ON_JOIN_FORM_RENDER = 'base.join_form_render';
    const ON_USER_BLOCK = 'base.on_user_block';
    const ON_USER_UNBLOCK = 'base.on_user_unblock';
    const ON_USER_APPROVE = 'base.on_user_approve';
    const ON_USER_DISAPPROVE = 'base.on_user_disapprove';
    const ON_USER_MARK_FEATURED = 'base.on_user_mark_featured';
    const ON_USER_UNMARK_FEATURED = 'base.on_user_unmark_featured';
    const ON_BEFORE_USER_COMPLETE_PROFILE = 'base.on_before_user_complete_profile';
    const ON_AFTER_USER_COMPLETE_PROFILE = 'base.on_after_user_complete_profile';
    const ON_BEFORE_USER_COMPLETE_ACCOUNT_TYPE = 'base.on_before_user_complete_account_type';

    /**
     * @var array
     */
    private $eventsToSkip = array(
        "core.get_text",
        "core.get_storage",
        "class.get_instance",
        "base.before_decorator",
        "core.sql.get_query_result",
        "core.sql.set_query_result",
        "core.sql.exec_query",
        "core.performance_test"
    );

    /**
     * @var int
     */
    private $maxItemsInLog = 200;

    /**
     * @var boolean
     */
    private $devMode = false;

    /**
     * @var array
     */
    private $eventsLog = array();

    /**
     * @var UTIL_Profiler
     */
    private $profiler;

    /**
     * List of binded listeners.
     *
     * @var array
     */
    private $listeners = array();

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->profiler = UTIL_Profiler::getInstance('event_manager');
    }
    /**
     * Singleton instance.
     *
     * @var OW_EventManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_EventManager
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Binds listener to event.
     * Callback should be valid `PHP callback`.
     *
     * @param string $name
     * @param callback $listener
     * @param null $priority
     */
    public function bind( $name, $listener, $priority = null )
    {
        $priority = ($priority === null) ? 1000 : (int) $priority;

        if ( !isset($this->listeners[$name][$priority]) )
        {
            $this->listeners[$name][$priority] = array();
        }

        $this->listeners[$name][$priority][] = $listener;
    }

    /**
     * Unbinds listener from event.
     * Callback should be valid `PHP callback`.
     *
     * @param string $name
     * @param callback $listener
     */
    public function unbind( $name, $listener )
    {
        foreach ( $this->listeners[$name] as $priority => $data )
        {
            foreach ( $data as $key => $handler )
            {
                if ( $handler == $listener )
                {
                    unset($this->listeners[$name][$priority][$key]);
                    return;
                }
            }
        }
    }

    /**
     * Triggers event listeners.
     *
     * @param OW_Event $event
     * @return OW_Event
     */
    public function trigger( OW_Event $event )
    {
        if ( isset($this->listeners[$event->getName()]) && !empty($this->listeners[$event->getName()]) )
        {
            ksort($this->listeners[$event->getName()]);

            // log triggered events for developer mode
            if ( $this->devMode )
            {
                $startTime = UTIL_Profiler::getInstance()->getTotalTime();
                $this->profiler->reset();
                foreach ( $this->listeners[$event->getName()] as $priority => $data )
                {
                    foreach ( $data as $listener )
                    {
                        if ( call_user_func($listener, $event) === false || $event->isStopped() )
                        {
                            break 2;
                        }
                    }
                }

                if ( !in_array($event->getName(), $this->eventsToSkip) && count($this->eventsLog) < $this->maxItemsInLog )
                {
                    $this->eventsLog[] = array('type' => 'trigger', 'start' => $startTime, 'exec' => $this->profiler->getTotalTime(),
                        'event' => $event, 'listeners' => $this->listeners[$event->getName()]);
                }
            }
            else
            {
                foreach ( $this->listeners[$event->getName()] as $priority => $data )
                {
                    foreach ( $data as $listener )
                    {
                        if ( call_user_func($listener, $event) === false || $event->isStopped() )
                        {
                            break 2;
                        }
                    }
                }
            }
        }
        else
        {
            // log events with no listeners
            $startTime = UTIL_Profiler::getInstance()->getTotalTime();

            if ( $this->devMode && !in_array($event->getName(), $this->eventsToSkip) && count($this->eventsLog) < $this->maxItemsInLog )
            {
                $this->eventsLog[] = array('type' => 'trigger', 'start' => $startTime, 'event' => $event, 'listeners' => array(),
                    'exec' => 0);
            }
        }

        return $event;
    }

    /**
     * Calls last event listener and returns it's result value.
     *
     * @param string $eventName
     * @param array $eventParams
     * @return mixed
     */
    public function call( $eventName, $eventParams = array() )
    {
        $event = new OW_Event($eventName, $eventParams);

        if ( !empty($this->listeners[$eventName]) )
        {
            ksort($this->listeners[$event->getName()]);

            // log triggered events for developer mode
            if ( $this->devMode )
            {
                $startTime = UTIL_Profiler::getInstance()->getTotalTime();
                $this->profiler->reset();
                $handlers = reset($this->listeners[$eventName]);
                $result = call_user_func(end($handlers), $event);

                if ( !in_array($event->getName(), $this->eventsToSkip) && count($this->eventsLog) < $this->maxItemsInLog )
                {
                    $this->eventsLog[] = array('type' => 'call', 'start' => $startTime, 'exec' => $this->profiler->getTotalTime(),
                        'event' => $event, 'listeners' => $this->listeners[$event->getName()]);
                }
            }
            else
            {
                $handlers = reset($this->listeners[$eventName]);
                $result = call_user_func(end($handlers), $event);
            }

            return $result;
        }
        else
        {
            // log events with no listeners
            $startTime = UTIL_Profiler::getInstance()->getTotalTime();

            if ( $this->devMode && !in_array($event->getName(), $this->eventsToSkip) && count($this->eventsLog) < $this->maxItemsInLog )
            {
                $this->eventsLog[] = array('type' => 'call', 'start' => $startTime, 'event' => $event, 'listeners' => array(),
                    'exec' => 0);
            }
        }
    }

    /**
     * @param boolean $devMode
     */
    public function setDevMode( $devMode )
    {
        $this->devMode = (bool) $devMode;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return array('bind' => $this->listeners, 'call' => $this->eventsLog);
    }
}
