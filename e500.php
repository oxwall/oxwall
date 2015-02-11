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
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', dirname(__FILE__) . DS);

require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');
$session = OW_Session::getInstance();
$session->start();
$errorDetails = '';

if ( $session->isKeySet('errorData') )
{
    $errorData = unserialize($session->get('errorData'));    
    $trace = '';

    if ( !empty($errorData['trace']) )
    {
        $trace = '<tr>
                        <td class="lbl">Trace:</td>
                        <td class="cnt">' . $errorData['trace'] . '</td>
                </tr>';
    }

    $errorDetails = '<div style="margin-top: 30px;">
            <b>Error details</b>:
            <table style="font-size: 13px;">
                <tbody>
                <tr>
                        <td class="lbl">Type:</td>
                        <td class="cnt">' . $errorData['type'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">Message:</td>
                        <td class="cnt">' . $errorData['message'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">File:</td>
                        <td class="cnt">' . $errorData['file'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">Line:</td>
                        <td class="cnt">' . $errorData['line'] . '</td>
                </tr>
                ' . $trace . '
        </tbody></table>
        </div>';
}

$output = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body style="font:18px Tahoma;">
    <div style="display: inline-block; padding-right: 16px; border-bottom: 1px solid #666; padding-bottom: 6px; margin-bottom: 8px;">Error 500<br/>Internal Server Error.</div></br>
    <div style="font-size: 13px; margin-bottom: 4px;">If you are the site admin, <a href="javascript://" onclick="getElementById(\'hiddenNode\').style.display=\'block\'">click here for details (+)</a></div>
    <div style="font-size: 13px; display: none;" id="hiddenNode">
        <div style="margin-top: 30px;">
    	<b style="line-height: 24px;">Something went wrong</b>!</br> 
    	To get the error details follow these steps:</br>
    	- Open <i>ow_includes/config.php</i> file and set <b>DEBUG_MODE</b> to <b>true</b></br>
 		- Reproduce your last action.
       </div>
        ' . $errorDetails . '
    </div>
  </body>
</html>
';

echo $output;

