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
 * Mail
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
class BASE_CLASS_Mail
{
    private $state = array(
        'recipientEmailList' => array(),
        'sender' => null,
        'subject' => null,
        'textContent' => null,
        'htmlContent' => null,
        'sentTime' => null,
        'priority' => self::PRIORITY_NORMAL,
        'replyTo' => null,
        'senderSuffix' => null
    );

    const PRIORITY_HIDE = 1;
    const PRIORITY_NORMAL = 3;
    const PRIORITY_LOW = 5;

    public function __construct( array $state = null )
    {
        if ( !empty($state) && is_array($state) )
        {
            $this->state = array_merge($this->state, $state);
        }
    }

    /**
     *
     * @param $email
     * @return BASE_CLASS_Mail
     */
    public function addRecipientEmail( $email )
    {
        if ( !UTIL_Validator::isEmailValid($email) )
        {
            throw new InvalidArgumentException('Invalid argument `$email`');
        }

        $this->state['recipientEmailList'][] = $email;

        return $this;
    }

    /**
     *
     * @param $email
     * @param $name
     * @return BASE_CLASS_Mail
     */
    public function setReplyTo ( $email, $name = '' )
    {
        if ( !UTIL_Validator::isEmailValid($email) )
        {
            throw new InvalidArgumentException('Invalid argument `$email`');
        }

        $this->state['replyTo'] = array($email, $name);

        return $this;
    }

    /**
     *
     * @param $email
     * @param $name
     * @return BASE_CLASS_Mail
     */
    public function setSender ( $email, $name = '' )
    {
        if ( !UTIL_Validator::isEmailValid($email) )
        {
            throw new InvalidArgumentException('Invalid argument `$email`');
        }

        $this->state['sender'] = array( $email, $name );

        return $this;
    }

    /**
     *
     * @param $subject
     * @return BASE_CLASS_Mail
     */
    public function setSubject( $subject )
    {
        if ( !trim($subject) )
        {
            throw new InvalidArgumentException('Invalid argument `$subject`');
        }

        $this->state['subject'] = $subject;

        return $this;
    }

    /**
     *
     * @param $content
     * @return BASE_CLASS_Mail
     */
    public function setTextContent( $content )
    {
        if ( !trim($content) )
        {
            throw new InvalidArgumentException('Invalid argument `$content`');
        }

        $this->state['textContent'] = $content;

        return $this;
    }

    /**
     *
     * @param $content
     * @return BASE_CLASS_Mail
     */
    public function setHtmlContent( $content )
    {
        $this->state['htmlContent'] = $content;

        return $this;
    }

    /**
     *
     * @param $time
     * @return BASE_CLASS_Mail
     */
    public function setSentTime( $time )
    {
        if ( !( $time = intval($time) ) )
        {
            throw new InvalidArgumentException('Invalid argument `$time`');
        }
        $this->state['sentTime'] = $time;

        return $this;
    }

    /**
     *
     * @param $priority
     * @return BASE_CLASS_Mail
     */
    public function setPriority( $priority )
    {
        if ( !( $priority = intval($priority) ) )
        {
            throw new InvalidArgumentException('Invalid argument `$priority`');
        }
        $this->state['priority'] = $priority;

        return $this;
    }

    public function setSenderSuffix( $suffix )
    {
        $this->state['senderSuffix'] = $suffix;

        return $this;
    }

    public function saveToArray()
    {
        return $this->state;
    }
}
