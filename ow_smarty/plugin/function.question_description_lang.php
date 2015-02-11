<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license.php. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Skalfa LLC (http://www.skalfa.com/).
 * All portions of the code written by Skalfa LLC are Copyright (c) 2009. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2009 Skalfa LLC. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.mailbox
 * @since 1.0
 */

/**
 *
 * @param array $params
 * @param OW_Smarty $smarty
 *
 * @return string
 *
 * {question_description_lang name="question name"}
 */
function smarty_function_question_description_lang( $params, $smarty )
{
    return BOL_QuestionService::getInstance()->getQuestionDescriptionLang(trim($params['name']));
}
?>
