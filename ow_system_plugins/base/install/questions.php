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


// Account types
$accountType = new BOL_QuestionAccountType();
$accountType->name = "standard"; // Standard account type
$accountType->sortOrder = 0;
$accountType->roleId = 0;

BOL_QuestionService::getInstance()->saveOrUpdateAccountType($accountType);

// Question Sections 

$questionSection = new BOL_QuestionSection();
$questionSection->name = "basic"; // Basic section
$questionSection->sortOrder = "0";
$questionSection->isHidden = "0";
$questionSection->isDeletable = "0";
BOL_QuestionService::getInstance()->saveOrUpdateSection($questionSection);

$questionSection = new BOL_QuestionSection();
$questionSection->name = "interests"; // Interests section
$questionSection->sortOrder = "1";
$questionSection->isHidden = "0";
$questionSection->isDeletable = "1";
BOL_QuestionService::getInstance()->saveOrUpdateSection($questionSection);

// Questions 

$question = new BOL_Question();
$question->name = "relationship";
$question->sectionName = "basic";
$question->type = "multiselect";
$question->presentation = "multicheckbox";
$question->required = "0";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "1";
$question->onView = "1";
$question->base = "0";
$question->removable = "1";
$question->columnCount = "1";
$question->sortOrder = "7";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "music"; // Music
$question->sectionName = "interests";
$question->type = "text";
$question->presentation = "textarea";
$question->required = "0";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "1";
$question->onView = "1";
$question->base = "0";
$question->removable = "1";
$question->columnCount = "0";
$question->sortOrder = "0";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "favorite_books"; // Favorite books
$question->sectionName = "interests";
$question->type = "text";
$question->presentation = "textarea";
$question->required = "0";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "1";
$question->onView = "1";
$question->base = "0";
$question->removable = "1";
$question->columnCount = "0";
$question->sortOrder = "1";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "password";
$question->sectionName = "basic";
$question->type = "text";
$question->presentation = "password";
$question->required = "1";
$question->onJoin = "1";
$question->onEdit = "0";
$question->onSearch = "0";
$question->onView = "0";
$question->base = "1";
$question->removable = "0";
$question->columnCount = "1";
$question->sortOrder = "2";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "realname";
$question->sectionName = "basic";
$question->type = "text";
$question->presentation = "text";
$question->required = "1";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "0";
$question->onView = "1";
$question->base = "0";
$question->removable = "0";
$question->columnCount = "0";
$question->sortOrder = "3";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "sex";
$question->sectionName = "basic";
$question->type = "select";
$question->presentation = "radio";
$question->required = "1";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "1";
$question->onView = "1";
$question->base = "0";
$question->removable = "0";
$question->columnCount = "1";
$question->sortOrder = "4";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "email";
$question->sectionName = "basic";
$question->type = "text";
$question->presentation = "text";
$question->required = "1";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "0";
$question->onView = "0";
$question->base = "1";
$question->removable = "0";
$question->columnCount = "1";
$question->sortOrder = "1";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "match_sex";
$question->sectionName = "basic";
$question->type = "multiselect";
$question->presentation = "multicheckbox";
$question->required = "0";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "0";
$question->onView = "1";
$question->base = "0";
$question->removable = "1";
$question->columnCount = "1";
$question->sortOrder = "6";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "birthdate";
$question->sectionName = "basic";
$question->type = "datetime";
$question->presentation = "birthdate";
$question->required = "1";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "0";
$question->onView = "0";
$question->base = "0";
$question->removable = "0";
$question->columnCount = "0";
$question->sortOrder = "5";
$question->custom = json_encode(array(
    "year_range" => array("from" => 1930, "to" => 1992)
));
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "username";
$question->sectionName = "basic";
$question->type = "text";
$question->presentation = "text";
$question->required = "1";
$question->onJoin = "1";
$question->onEdit = "1";
$question->onSearch = "0";
$question->onView = "0";
$question->base = "1";
$question->removable = "0";
$question->columnCount = "1";
$question->sortOrder = "0";
$question->custom = "[]";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "joinStamp";
$question->sectionName = "basic";
$question->type = "select";
$question->presentation = "date";
$question->required = "0";
$question->onJoin = "0";
$question->onEdit = "0";
$question->onSearch = "0";
$question->onView = "1";
$question->base = "1";
$question->removable = "0";
$question->columnCount = "0";
$question->sortOrder = "8";
$question->custom = json_encode(array(
    "year_range" => array("from" => 1930, "to" => 1975)
));
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

// Question Values 

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "sex";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "sex";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "match_sex";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "match_sex";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "relationship";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "relationship";
$questionValue->value = "2";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "relationship";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "relationship";
$questionValue->value = "8";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

// Question Configs

$questionConfig = new BOL_QuestionConfig();
$questionConfig->questionPresentation = "date";
$questionConfig->name = "year_range";
$questionConfig->description = "";
$questionConfig->presentationClass = "YearRange";
BOL_QuestionConfigDao::getInstance()->save($questionConfig);

$questionConfig = new BOL_QuestionConfig();
$questionConfig->questionPresentation = "age";
$questionConfig->name = "year_range";
$questionConfig->description = "";
$questionConfig->presentationClass = "YearRange";
BOL_QuestionConfigDao::getInstance()->save($questionConfig);

$questionConfig = new BOL_QuestionConfig();
$questionConfig->questionPresentation = "birthdate";
$questionConfig->name = "year_range";
$questionConfig->description = "";
$questionConfig->presentationClass = "YearRange";
BOL_QuestionConfigDao::getInstance()->save($questionConfig);

// Questions Account types 

BOL_QuestionService::getInstance()->addQuestionListToAccountTypeList(array(
    "relationship", 
    "music",
    "favorite_books",
    "password", 
    "realname", 
    "sex", 
    "email", 
    "match_sex", 
    "birthdate", 
    "username", 
    "joinStamp"
), array("standard"));