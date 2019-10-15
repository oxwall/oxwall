<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_plugins.birthdays
 * @since 1.0
 */
//add 'birthdays' widget to index page
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_BirthdaysWidget', false),
        BOL_ComponentAdminService::PLACE_INDEX
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

//--
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_FriendBirthdaysWidget', false),
        BOL_ComponentAdminService::PLACE_DASHBOARD
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);

//--
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_MyBirthdayWidget', false),
        BOL_ComponentAdminService::PLACE_PROFILE
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new BIRTHDAYS_CLASS_Credits();
$credits->triggerCreditActionsAdd();
