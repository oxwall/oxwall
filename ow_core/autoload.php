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
 * OW_Autoload is class keeping developer from manual class includes.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_Autoload
{
    /**
     * Registered package pointers.
     *
     * @var array
     */
    private $packagePointers = array();
    /**
     * Registered classes.
     *
     * @var array
     */
    private $classPathArray = array();

    /**
     * Constructor.
     *
     */
    private function __construct()
    {

    }
    /**
     * Singleton instance.
     *
     * @var OW_Autoload
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_Autoload
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
     * @return array
     */
    public function getClassPathArray()
    {
        return $this->classPathArray;
    }

    /**
     * @param array $classPathArray
     */
    public function setClassPathArray( array $classPathArray )
    {
        $this->classPathArray = $classPathArray;
    }

    /**
     * Main static method registered as autoloader.
     * Don't call it manually.
     */
    public static function autoload( $className )
    {
        $thisObj = self::getInstance();

        try
        {
            $path = $thisObj->getClassPath($className);
        }
        catch ( Exception $e )
        {
            return;
        }

        include $path;
    }

    /**
     * Returns class definition file path for provided classname.
     *
     * @throws InvalidArgumentException
     * @param string $class
     * @return string
     */
    public function getClassPath( $className )
    {
        // if class isn't found in class path array
        if ( !isset($this->classPathArray[$className]) )
        {
            $packagePointer = $this->getPackagePointer($className);

            // throw exception if package pointer is not registered
            if ( !isset($this->packagePointers[$packagePointer]) )
            {
                throw new InvalidArgumentException("Package pointer `" . $packagePointer . "` is not registered!");
            }

            $this->classPathArray[$className] = $this->packagePointers[$packagePointer] . $this->classToFilename($className);
        }

        return $this->classPathArray[$className];
    }

    /**
     * Registers class in autoloader.
     *
     * @throws LogicException
     * @param string $className
     * @param string $filePath
     */
    public function addClass( $className, $filePath )
    {
        $className = trim($className);

        if ( isset($this->classPathArray[$className]) )
        {
            throw new LogicException("Can't register `" . $className . "` in autoloader. Duplicated class name!");
        }

        $this->classPathArray[$className] = $filePath;
    }

    /**
     * Registers class list in autoloader.
     *
     * @throws LogicException
     * @param array $classArray
     */
    public function addClassArray( array $classArray )
    {
        foreach ( $classArray as $className => $filePath )
        {
            $this->addClass($className, $filePath);
        }
    }

    /**
     * Returns file name for provided class name.
     *
     * Examples:
     * 		`MyNewClass` => `my_new_class.php`
     * 		`OW_MyClass` => `my_class.php`
     * 		`OW_BOL_MyClass` => `my_class.php`
     *
     * @param string $className
     * @param boolean $extension
     * @return string
     */
    public function classToFilename( $className, $extension = true )
    {
        // need to remove package pointer
        if ( strstr($className, '_') )
        {
            $className = substr($className, (strrpos($className, '_') + 1));
        }

        return substr(UTIL_String::capsToDelimiter($className), 1) . ($extension ? '.php' : '');
    }

    /**
     * Returns class name for provided file name and package pointer.
     *
     * @param string $fileName
     * @param string $packagePointer
     * @return string
     */
    public function filenameToClass( $fileName, $packagePointer = null )
    {
        $packagePointer = ( ( $packagePointer === null ) ? '' : strtoupper($packagePointer) . '_' );

        return $packagePointer . UTIL_String::delimiterToCaps('_' . substr($fileName, 0, -4));
    }

    /**
     * Returns package pointer for provided class name.
     *
     * @throws InvalidArgumentException
     * @param string $className
     * @return string
     */
    public function getPackagePointer( $className )
    {
        // throw exception if class doesn't have package pointer
        if ( !strstr($className, '_') )
        {
            throw new InvalidArgumentException("Can't find package pointer in class `" . $className . "` !");
        }

        return substr($className, 0, strrpos($className, '_'));
    }

    /**
     * Returns plugin key for provided class name.
     *
     * @throws InvalidArgumentException
     * @param string $className
     * @return string
     */
    public function getPluginKey( $className )
    {
        // throw exception if class doesn't contain underscore symbols
        if ( !strstr($className, '_') )
        {
            throw new InvalidArgumentException("Can't find plugin key in class `" . $className . "` !");
        }

        return substr($className, 0, strpos($className, '_'));
    }

    /**
     * Registers package pointer in autoloader.
     *
     * @throws InvalidArgumentException
     * @param string $packagePointer
     * @param string $dir
     */
    public function addPackagePointer( $packagePointer, $dir )
    {
        $packagePointer = trim($packagePointer);
        $dir = trim($dir);

        // throw exception if package pointer already registered
        if ( isset($this->packagePointers[$packagePointer]) )
        {
            throw new InvalidArgumentException("Can't add package pointer `" . $packagePointer . "`! Duplicated package pointer!");
        }

        // add directory separator if needed
        if ( substr($dir, -1) !== DS )
        {
            $dir = trim($dir) . DS;
        }

        $this->packagePointers[trim(strtoupper($packagePointer))] = $dir;
    }
}

