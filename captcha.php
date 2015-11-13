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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.8.1
 */

define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', dirname(__FILE__));

require_once 'ow_includes/config.php';
require_once 'ow_libraries/securimage/securimage.php';

session_name(md5(OW_URL_HOME));

$cookie = session_get_cookie_params();
$cookie['httponly'] = true;

session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

$img = new securimage();

//Change some settings 
$img->image_width = !empty($_GET['width']) ? (int) $_GET['width'] : 200;
$img->image_height = !empty($_GET['height']) ? (int) $_GET['height'] : 68;
$img->ttf_file = 'ow_libraries/securimage/AHGBold.ttf';
$img->perturbation = 0.45;
$img->image_bg_color = new Securimage_Color(0xf6, 0xf6, 0xf6);
$img->text_angle_minimum = -5;
$img->text_angle_maximum = 5;
$img->use_transparent_text = true;
$img->text_transparency_percentage = 30; // 100 = completely transparent
$img->num_lines = 7;
$img->line_color = new Securimage_Color("#7B92AA");
$img->signature_color = new Securimage_Color("#7B92AA");
$img->text_color = new Securimage_Color("#7B92AA");
$img->use_wordlist = true;

$img->show();

