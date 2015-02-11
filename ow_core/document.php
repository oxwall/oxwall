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
 * Base document class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
abstract class OW_Document
{
    const HTML = 1;
    const AJAX = 2;
    const XML = 3;
    const JSON = 4;
//	const FEED = 3;
//	const PDF = 4;

    const APPEND_PLACEHOLDER = '###ow_postappend_placeholder###';

    /**
     * Document title.
     *
     * @var string
     */
    protected $title;

    /**
     * Document description.
     *
     * @var string
     */
    protected $description;

    /**
     * Document language.
     *
     * @var string
     */
    protected $language;

    /**
     * Document direction.
     *
     * @var string
     */
    protected $direction;

    /**
     * Document type.
     *
     * @var string
     */
    protected $type;

    /**
     * Document charset.
     *
     * @var string
     */
    protected $charset;

    /**
     * Document mime type.
     *
     * @var string
     */
    protected $mime;

    /**
     * Document assigned template
     *
     * @var string
     */
    protected $template;

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset( $charset )
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription( $description )
    {
        $description = str_replace(PHP_EOL, "", $description);
        $this->throwEvent("core.set_document_description", array("str" => $description));
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     */
    public function setDirection( $direction )
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage( $language )
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param string $mime
     */
    public function setMime( $mime )
    {
        $this->mime = $mime;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle( $title )
    {
        $title = str_replace(PHP_EOL, "", $title);
        $this->throwEvent("core.set_document_title", array("str" => $title));
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType( $type )
    {
        $this->type = $type;
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

    protected function throwEvent( $name, $params = array() )
    {
        
    }

    abstract function render();
}
