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
 * @package ow_utilities
 * @since 1.0
 */
final class UTIL_Debug
{
    private static $pvOutput;
    private static $pvObjects;
    private static $pvDepth = 10;

    public static function varDump( $var, $exit = false )
    {
        self::addDebugStyles();

        self::$pvOutput = '';
        self::$pvObjects = array();
        self::dumper($var, 0);

        $debugString = '
    	<div class="ow_debug_cont">
    		<div class="ow_debug_body">
    			<div class="ow_debug_cap vardump">OW Debug - Vardump</div>
    			<div>
    				<pre class="vardumper">' . self::$pvOutput .
            "\n\n" . '<b>Type:</b> <span style="color:red;">' . ucfirst(gettype($var)) . "</span>" . '
    				</pre>
    			</div>
    		</div>
    	</div>
    	';

        echo $debugString;

        if ( $exit )
        {
            exit;
        }
    }

    private static function dumper( $var, $level )
    {
        switch ( gettype($var) )
        {
            case 'boolean':
                self::$pvOutput .= '<span class="bool">' . ( $var ? 'true' : 'false' ) . '</span>';
                break;

            case 'integer':
                self::$pvOutput .= '<span class="number">' . $var . '</span>';
                break;

            case 'double':
                self::$pvOutput .= '<span class="number">' . $var . '</span>';
                break;

            case 'string':
                self::$pvOutput .= '<span class="string">' . htmlspecialchars($var) . '</span>';
                break;

            case 'resource':
                self::$pvOutput .= '{resource}';
                break;

            case 'NULL':
                self::$pvOutput .= '<span class="null">null</span>';
                break;

            case 'unknown type':
                self::$pvOutput .= '{unknown}';
                break;

            case 'array':
                if ( self::$pvDepth <= $level )
                {
                    self::$pvOutput .= '<span class="array">array(...)</span>';
                }
                else if ( empty($var) )
                {
                    self::$pvOutput .= '<span class="array">array()</span>';
                }
                else
                {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', ($level * 4));
                    self::$pvOutput .= '<span class="array">array</span>' . "\n" . $spaces . '(';

                    foreach ( $keys as $key )
                    {
                        self::$pvOutput .= "\n" . $spaces . "    [" . $key . "] => ";
                        self::$pvOutput .= self::dumper($var[$key], ($level + 1));
                    }
                    self::$pvOutput .= "\n" . $spaces . ')';
                }
                break;

            case 'object':
                if ( ( $id = array_search($var, self::$pvObjects, true)) !== false )
                {
                    self::$pvOutput .= get_class($var) . '#' . ($id + 1) . '(...)';
                }
                else if ( self::$pvDepth <= $level )
                {
                    self::$pvOutput .= get_class($var) . '(...)';
                }
                else
                {
                    $id = array_push(self::$pvObjects, $var);
                    $className = get_class($var);
                    $members = (array) $var;
                    $keys = array_keys($members);
                    $spaces = str_repeat(' ', ($level * 4));
                    self::$pvOutput .= '<span class="class">' . "$className</span>#$id\n" . $spaces . '(';

                    foreach ( $keys as $key )
                    {
                        $keyDisplay = strtr(trim($key) . '</span>', array("\0" => ':<span class="class_prop">'));
                        self::$pvOutput .= "\n" . $spaces . "    [$keyDisplay] => ";
                        self::$pvOutput .= self::dumper($members[$key], ($level + 1));
                    }

                    self::$pvOutput .= "\n" . $spaces . ')';
                }
                break;
        }
    }

    public static function printDebugMessage( $data )
    {
        self::addDebugStyles();

        $debugString = '
    		<div class="ow_debug_cont">
    			<div class="ow_debug_body">
    				<div class="ow_debug_cap ' . strtolower($data['type']) . '">OW Debug - ' . $data['type'] . '</div>
    				<table>
    					<tr>
    						<td class="lbl">Message:</td>
    						<td class="cnt">' . $data['message'] . '</td>
    					</tr>
    					<tr>
    						<td class="lbl">File:</td>
    						<td class="cnt">' . $data['file'] . '</td>
    					</tr>
    					<tr>
    						<td class="lbl">Line:</td>
    						<td class="cnt">' . $data['line'] . '</td>
    					</tr>
                        ' . (!empty($data['trace']) ?
                '<tr>
    						<td class="lbl">Trace:</td>
    						<td class="cnt"><pre>' . $data['trace'] . '</pre></td>
    					</tr>
                        ' : '' ) .
            (!empty($data['class']) ?
                '<tr>
    						<td class="lbl">Type:</td>
    						<td class="cnt" style="color:red;">' . $data['class'] . '</td>
    					</tr>
                        ' : '' ) . '
    				</table>
    			</div>
    		</div>
    		';

        echo $debugString;
    }

    private static function addDebugStyles()
    {
        echo '
    	<style>
    		.ow_debug_cont{padding:15px 0;width:80%;margin:0 auto;}
    		.ow_debug_body{background:#fff;border:4px double;padding:5px;}
    		.ow_debug_cap{font:bold 13px Tahoma;color:#fff;padding:5px;border:1px solid #000;width:250px;margin-top:-20px;}
    		.ow_debug_body .notice{background:#fdf403;color:#555;}
    		.ow_debug_body .warning{background:#f8b423;color:#555;}
    		.ow_debug_body .error{background:#c10505;color:#fff;}
    		.ow_debug_body .exception{background:#093dd3;color:#fff;}
    		.ow_debug_body .vardump{background:#333;color:#fff;}
    		.vardumper .string{color:green}
    		.vardumper .null{color:blue}
    		.vardumper .array{color:blue}
            .vardumper .bool{color:blue}
    		.vardumper .property{color:brown}
    		.vardumper .number{color:red}
            .vardumper .class{color:black;}
            .vardumper .class_prop{color:brown;}
    	</style>
    	';
    }
}
