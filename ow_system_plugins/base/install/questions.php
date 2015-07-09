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
$accountType->name = "290365aadde35a97f11207ca7e4279cc"; // TODO rename
$accountType->sortOrder = 0;
$accountType->roleId = 0;

BOL_QuestionService::getInstance()->saveOrUpdateAccountType($accountType);

// Question Sections 

$questionSection = new BOL_QuestionSection();
$questionSection->name = "47f3a94e6cfe733857b31116ce21c337";
$questionSection->sortOrder = "1";
$questionSection->isHidden = "0";
$questionSection->isDeletable = "1";
BOL_QuestionService::getInstance()->saveOrUpdateSection($questionSection);

$questionSection = new BOL_QuestionSection();
$questionSection->name = "f90cde5913235d172603cc4e7b9726e3";
$questionSection->sortOrder = "0";
$questionSection->isHidden = "0";
$questionSection->isDeletable = "0";
BOL_QuestionService::getInstance()->saveOrUpdateSection($questionSection);

// Questions 

$question = new BOL_Question();
$question->name = "relationship";
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->name = "9221d78a4201eac23c972e1d4aa2cee6";
$question->sectionName = "47f3a94e6cfe733857b31116ce21c337";
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
$question->name = "c441a8a9b955647cdf4c81562d39068a";
$question->sectionName = "47f3a94e6cfe733857b31116ce21c337";
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
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->custom = "{\"year_range\":{\"from\":1930,\"to\":1992}}";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

$question = new BOL_Question();
$question->name = "username";
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->sectionName = "f90cde5913235d172603cc4e7b9726e3";
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
$question->custom = "{\"year_range\":{\"from\":1930,\"to\":1975}}";
BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

// Question Values 

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d68489df439fe45427e305a0e2dbe349";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d68489df439fe45427e305a0e2dbe349";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "32";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "490d035a492be91d7bf9589f881e2d22";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "490d035a492be91d7bf9589f881e2d22";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "490d035a492be91d7bf9589f881e2d22";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "16";
$questionValue->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "8";
$questionValue->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "4";
$questionValue->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "1";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionValue->value = "32";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "128";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d68489df439fe45427e305a0e2dbe349";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "512";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

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
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "32";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "64";
$questionValue->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "128";
$questionValue->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "256";
$questionValue->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "512";
$questionValue->sortOrder = "9";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionValue->value = "1024";
$questionValue->sortOrder = "10";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "32";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "64";
$questionValue->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionValue->value = "128";
$questionValue->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionValue->value = "256";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "1e615090f832c4fbee805ded8e9ced08";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "1e615090f832c4fbee805ded8e9ced08";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "1e615090f832c4fbee805ded8e9ced08";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "f8f4c260c54166c8fcf79057fd85aec0";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "f8f4c260c54166c8fcf79057fd85aec0";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "f8f4c260c54166c8fcf79057fd85aec0";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
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

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "32";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "64";
$questionValue->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "128";
$questionValue->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "256";
$questionValue->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "512";
$questionValue->sortOrder = "9";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "1024";
$questionValue->sortOrder = "10";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "2048";
$questionValue->sortOrder = "11";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "4096";
$questionValue->sortOrder = "12";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "8192";
$questionValue->sortOrder = "13";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "16384";
$questionValue->sortOrder = "14";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "32768";
$questionValue->sortOrder = "15";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "65536";
$questionValue->sortOrder = "16";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "131072";
$questionValue->sortOrder = "17";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "262144";
$questionValue->sortOrder = "18";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "524288";
$questionValue->sortOrder = "19";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "1048576";
$questionValue->sortOrder = "20";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "2097152";
$questionValue->sortOrder = "21";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "4194304";
$questionValue->sortOrder = "22";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "8388608";
$questionValue->sortOrder = "23";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "16777216";
$questionValue->sortOrder = "24";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "33554432";
$questionValue->sortOrder = "25";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "67108864";
$questionValue->sortOrder = "26";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "134217728";
$questionValue->sortOrder = "27";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "268435456";
$questionValue->sortOrder = "28";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "536870912";
$questionValue->sortOrder = "29";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionValue->value = "1073741824";
$questionValue->sortOrder = "30";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "8100f639e8becdefa741e05f0de73a15";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "8100f639e8becdefa741e05f0de73a15";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d37d41b71a78dfb62b379d0aa7bd3ba5";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7f2450f06779439551c75a8566c4070e";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7f2450f06779439551c75a8566c4070e";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7f2450f06779439551c75a8566c4070e";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7f2450f06779439551c75a8566c4070e";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7f2450f06779439551c75a8566c4070e";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7f2450f06779439551c75a8566c4070e";
$questionValue->value = "32";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionValue->value = "32";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionValue->value = "64";
$questionValue->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "1";
$questionValue->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "2";
$questionValue->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "4";
$questionValue->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "8";
$questionValue->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "16";
$questionValue->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "32";
$questionValue->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "64";
$questionValue->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "128";
$questionValue->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "256";
$questionValue->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionValue->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionValue->value = "512";
$questionValue->sortOrder = "9";
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
    "9221d78a4201eac23c972e1d4aa2cee6", 
    "c441a8a9b955647cdf4c81562d39068a", 
    "password", 
    "realname", 
    "sex", 
    "email", 
    "match_sex", 
    "birthdate", 
    "username", 
    "joinStamp", 
    "relationship", 
    "9221d78a4201eac23c972e1d4aa2cee6", 
    "c441a8a9b955647cdf4c81562d39068a", 
    "password", 
    "realname", 
    "sex", 
    "email", 
    "match_sex", 
    "birthdate", 
    "username", 
    "joinStamp"
), array("290365aadde35a97f11207ca7e4279cc"));