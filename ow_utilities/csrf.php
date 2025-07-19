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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.8.3
 */
class UTIL_Csrf
{
    const SESSION_VAR_NAME = "csrf_tokens";

    /**
     * Generates and returns CSRF token
     * 
     * @return string
     */
    public static function generateToken()
    {
        $tokenList = self::getTokenList();
        $token = base64_encode(time() . UTIL_String::getRandomString(32));
        $tokenList[$token] = ['createTime'=>time(),'lastValidateTime'=>0,'isValidCount'=>0];
        self::saveTokenList($tokenList);

        return $token;
    }

    /**
     * Checks if provided token is valid and not expired
     * 
     * @param string $token
     * @return bool
     */
    public static function isTokenValid($token)
{
    $tokenList = self::getTokenList();

    if (!isset($tokenList[$token])) {
        return false;
    }

    $tokenData = $tokenList[$token];

    // delete expired (10 minuted old)
    if (isset($tokenData['createTime']) && $tokenData['createTime'] < strtotime('-10 minutes')) {
        unset($tokenList[$token]);
        self::saveTokenList($tokenList);
        return false;
    }

// do not set validation limit. Exp: 3 seconds as in many cases token gets validated by multiple classes it take milliseconds which will make it fail

    // delete by validation count. Keep count_limit relativelly high but not too high. =>50 and =<100
    if (isset($tokenData['isValidCount']) && $tokenData['isValidCount'] >= 50) {
        unset($tokenList[$token]);
        self::saveTokenList($tokenList);
        return false;
    }

    // update values
    $tokenData['lastValidateTime'] = time();
    $tokenData['isValidCount'] = isset($tokenData['isValidCount']) ? $tokenData['isValidCount'] + 1 : 1;
    $tokenList[$token] = $tokenData;
    self::saveTokenList($tokenList);

    return true;
}
    /* -------------------------------------------------------------------------------------------------------------- */

    private static function getTokenList()
    {
        return OW::getSession()->isKeySet(self::SESSION_VAR_NAME) ? OW::getSession()->get(self::SESSION_VAR_NAME) : array();
    }

    private static function saveTokenList( array $list )
    {
        OW::getSession()->set(self::SESSION_VAR_NAME, $list);
    }
}
