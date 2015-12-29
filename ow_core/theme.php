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
 * The class provides ...
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_Theme
{
    /**
     * List of decorators available in theme.
     *
     * @var array
     */
    protected $decorators = array();
    /**
     * List of master pages available in theme.
     *
     * @var array
     */
    protected $masterPages = array();
    /**
     * List of overriden master pages.
     *
     * @var array
     */
    protected $documentMasterPages = array();
    /**
     * @var BOL_ThemeService
     */
    protected $themeService;
    /**
     * @var BOL_Theme
     */
    protected $dto;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( BOL_Theme $dto )
    {
        $this->dto = $dto;
        $this->themeService = BOL_ThemeService::getInstance();
    }

    /**
     * Checks if theme has decorator.
     *
     * @param string $name
     * @return boolean
     */
    public function hasDecorator( $name )
    {
        return array_key_exists($name, $this->decorators);
    }

    /**
     * Checks if theme has master page.
     *
     * @param string $name
     * @return boolean
     */
    public function hasMasterPage( $name )
    {
        return array_key_exists($name, $this->masterPages);
    }

    /**
     * Returns path to decorator file.
     *
     * @param string $name
     * @return string
     */
    public function getDecorator( $name )
    {
        if ( !$this->hasDecorator($name) )
        {
            throw new InvalidArgumentException('There is no decorator `' . $name . '` in theme `' . $this->name . '` !');
        }

        return $this->decorators[$name];
    }

    /**
     * Returns path to master page file.
     *
     * @param string $name
     * @return string
     */
    public function getMasterPage( $name )
    {
        if ( !$this->hasMasterPage($name) )
        {
            throw new InvalidArgumentException('There is no master page `' . $name . '` in theme `' . $this->name . '` !');
        }

        return $this->masterPages[$name];
    }

    /**
     * Checks if theme overrides master page for document key.
     *
     * @param string $documentKey
     * @return boolean
     */
    public function hasDocumentMasterPage( $documentKey )
    {
        return array_key_exists(trim($documentKey), $this->documentMasterPages);
    }

    /**
     * Returns master page file path for document key.
     *
     * @param string $documentKey
     * @return string
     */
    public function getDocumentMasterPage( $documentKey )
    {
        if ( !$this->hasDocumentMasterPage($documentKey) )
        {
            throw new InvalidArgumentException('Cant find master page for document `' . $documentKey . '` in current theme!');
        }

        return $this->documentMasterPages[trim($documentKey)];
    }

    /**
     * Returns theme static dir path.
     *
     * @return string
     */
    public function getStaticDir( $mobile = false )
    {
        return $this->themeService->getStaticDir($this->dto->getKey(), $mobile);
    }

    /**
     * Returns theme static url.
     *
     * @return string
     */
    public function getStaticUrl( $mobile = false )
    {
        return $this->themeService->getStaticUrl($this->dto->getKey(), $mobile);
    }

    /**
     * Returns theme static images dir path.
     *
     * @return string
     */
    public function getStaticImagesDir( $mobile = false )
    {
        return $this->themeService->getStaticImagesDir($this->dto->getKey(), $mobile);
    }

    /**
     * Returns theme static images url.
     *
     * @return string
     */
    public function getStaticImagesUrl( $mobile = false )
    {
        return $this->themeService->getStaticImagesUrl($this->dto->getKey(), $mobile);
    }

    /**
     * Returns theme root dir path.
     *
     * @return string
     */
    public function getRootDir( $mobile = false )
    {
        return $this->themeService->getRootDir($this->dto->getKey(), $mobile);
    }

    /**
     * Returns theme decorators dir path.
     *
     * @return string
     */
    public function getDecoratorsDir()
    {
        return $this->themeService->getDecoratorsDir($this->dto->getKey());
    }

    /**
     * Returns theme master page dir path.
     *
     * @return string
     */
    public function getMasterPagesDir( $mobile = false )
    {
        return $this->themeService->getMasterPagesDir($this->dto->getKey(), $mobile);
    }

    /**
     * Returns images dir path.
     *
     * @return string
     */
    public function getImagesDir( $mobile = false )
    {
        return $this->themeService->getImagesDir($this->dto->getKey(), $mobile);
    }

    /**
     * @return BOL_Theme
     */
    public function getDto()
    {
        return $this->dto;
    }

    /**
     * @param array $decorators
     * @return OW_Theme
     */
    public function setDecorators( $decorators )
    {
        $this->decorators = $decorators;
        return $this;
    }

    /**
     * @param array $masterPages
     * @return OW_Theme
     */
    public function setMasterPages( $masterPages )
    {
        $this->masterPages = $masterPages;
        return $this;
    }

    /**
     * @param array $documentMasterPages
     * @return OW_Theme
     */
    public function setDocumentMasterPages( $documentMasterPages )
    {
        $this->documentMasterPages = $documentMasterPages;
        return $this;
    }
}