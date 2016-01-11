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
 * Serialize utility.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_utilities
 * @since 1.8.1
 */

class UTIL_Serialize
{
    const SERIALIZED_OBJECT_MARK = '#!serialized!#';

    /**
     * Checks if a string is serialized object
     *
     * @param string $serialized
     * @return boolean
     */

    public static function isSerializedObject($serialized) {
        return self::getClassName($serialized) != null;
    }

    /**
     * Returns class name of serialized object
     *
     * @param string $serialized
     * @return string
     */
    public static function getClassName($serialized) {
        if ( preg_match('/^'.self::SERIALIZED_OBJECT_MARK.'(.+?)'.self::SERIALIZED_OBJECT_MARK.'.*$/', $serialized, $matches) )
        {
            return $matches[1];
        }

        return null;
    }

    /**
     * Returns serialized data
     *
     * @param string $serialized
     * @return string
     */
    public static function getSerializedData($serialized) {
        if ( preg_match('/^'.self::SERIALIZED_OBJECT_MARK.'.+?'.self::SERIALIZED_OBJECT_MARK.'(.*)$/', $serialized, $matches) )
        {
            return $matches[1];
        }

        return null;
    }

    /**
     * Serialize object
     *
     * @param string $serialized
     * @return string
     */
    public static function serialize(Serializable $object) {
        return self::SERIALIZED_OBJECT_MARK. get_class($object) . self::SERIALIZED_OBJECT_MARK . $object->serialize();
    }

    /**
     * @param string $serialized
     * @return Serializable
     */
    public static function unserialize($serialized) {
        $className = self::getClassName($serialized);
        $serializedData = self::getSerializedData($serialized);

        if ( $className == null || $serializedData == null )
        {
            return null;
        }

        if( !class_exists($className) )
        {
            return null;
        }

        /* @var $object Serializable */
        $object = new $className;
        $object->unserialize($serializedData);

        return $object;
    }
}