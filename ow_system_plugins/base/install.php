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

// Structure

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `addStamp` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `fileName` varchar(100) DEFAULT NULL,
  `origFileName` varchar(100) DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `bundle` varchar(128) DEFAULT NULL,
  `pluginKey` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `bundle` (`bundle`),
  KEY `pluginKey` (`pluginKey`),
  KEY `userId_2` (`userId`),
  KEY `bundle_2` (`bundle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_authorization_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `availableForGuest` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupId` (`groupId`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=172 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_authorization_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `moderated` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_authorization_moderator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_authorization_moderator_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moderatorId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `moderatorId` (`moderatorId`),
  KEY `groupId` (`groupId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_authorization_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actionId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actionId` (`actionId`,`roleId`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_authorization_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  `displayLabel` tinyint(1) DEFAULT '0',
  `custom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_authorization_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user2role` (`userId`,`roleId`),
  KEY `userId` (`userId`),
  KEY `roleId` (`roleId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `hash` int(11) NOT NULL DEFAULT '0',
  `status` varchar(32) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_billing_gateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gatewayKey` varchar(50) NOT NULL,
  `adapterClassName` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `dynamic` tinyint(1) DEFAULT '1',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `currencies` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gatewayKey` (`gatewayKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_billing_gateway_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gatewayId` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_billing_gateway_product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `gatewayId` int(10) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `entityType` varchar(50) NOT NULL,
  `entityId` int(10) NOT NULL,
  `productId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_billing_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productKey` varchar(255) NOT NULL,
  `adapterClassName` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `productKey` (`productKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_billing_sale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL,
  `pluginKey` varchar(255) DEFAULT NULL,
  `entityKey` varchar(50) NOT NULL,
  `entityId` int(10) DEFAULT NULL,
  `entityDescription` varchar(255) DEFAULT NULL,
  `gatewayId` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `transactionUid` varchar(32) DEFAULT NULL,
  `price` float(9,3) NOT NULL,
  `period` int(10) DEFAULT NULL,
  `quantity` int(10) NOT NULL,
  `totalAmount` float(9,3) NOT NULL DEFAULT '0.000',
  `currency` varchar(3) NOT NULL,
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('init','prepared','verified','delivered','processing','error') NOT NULL DEFAULT 'init',
  `timeStamp` int(10) NOT NULL DEFAULT '0',
  `extraData` text,
  PRIMARY KEY (`id`),
  KEY `entityKey` (`entityKey`),
  KEY `entityId` (`entityId`),
  KEY `userId` (`userId`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `expireTimestamp` int(11) NOT NULL,
  `instantLoad` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `key_index` (`key`),
  KEY `expire_index` (`expireTimestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_cache_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `cacheId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tag_index` (`tag`),
  KEY `cacheId_index` (`cacheId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `commentEntityId` int(11) NOT NULL,
  `message` text NOT NULL,
  `createStamp` int(11) NOT NULL,
  `attachment` text,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `commentEntityId` (`commentEntityId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_comment_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` int(11) NOT NULL,
  `pluginKey` varchar(100) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`),
  KEY `pluginKey` (`pluginKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `className` varchar(50) NOT NULL,
  `clonable` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=767 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component_entity_place` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentId` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  `clone` tinyint(4) NOT NULL DEFAULT '0',
  `entityId` int(11) NOT NULL,
  `uniqName` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`entityId`,`uniqName`),
  KEY `componentId` (`componentId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component_entity_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentPlaceUniqName` varchar(50) NOT NULL,
  `section` enum('top','left','bottom','right') NOT NULL,
  `order` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`entityId`,`componentPlaceUniqName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component_entity_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  `componentPlaceUniqName` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `componentUniqName` (`entityId`,`componentPlaceUniqName`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component_place` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentId` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  `clone` tinyint(1) unsigned DEFAULT '0',
  `uniqName` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqName` (`uniqName`),
  KEY `componentId` (`componentId`)
) ENGINE=MyISAM AUTO_INCREMENT=100791 DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component_place_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `placeId` int(11) NOT NULL,
  `state` longtext NOT NULL,
  `entityId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userId` (`entityId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentPlaceUniqName` varchar(50) NOT NULL DEFAULT '',
  `section` varchar(100) DEFAULT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `componentPlaceUniqName` (`componentPlaceUniqName`)
) ENGINE=MyISAM AUTO_INCREMENT=11266 DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_component_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentPlaceUniqName` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` longtext NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `componentPlaceUniqName` (`componentPlaceUniqName`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1447 DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`name`)
) ENGINE=MyISAM AUTO_INCREMENT=730 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_cron_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `methodName` varchar(200) NOT NULL DEFAULT '',
  `runStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `className` (`methodName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_db_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expireStamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `class` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `isStatic` tinyint(1) NOT NULL DEFAULT '0',
  `isMobile` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  UNIQUE KEY `uriIndex` (`uri`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_email_verify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL DEFAULT '0',
  `type` enum('user','site') NOT NULL,
  `email` varchar(128) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `createStamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_entity_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityId` int(10) unsigned NOT NULL,
  `entityType` varchar(255) NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `entityId` (`entityId`),
  KEY `entityType` (`entityType`),
  KEY `tagId` (`tagId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_flag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(100) NOT NULL,
  `entityId` int(11) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_invitation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `viewed` int(11) NOT NULL,
  `sent` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_invite_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `expiration_stamp` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `label` varchar(32) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '1',
  `status` enum('active','inactive') DEFAULT 'inactive',
  `rtl` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_language_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefixId` int(11) NOT NULL DEFAULT '0',
  `key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix_key` (`prefixId`,`key`)
) ENGINE=MyISAM AUTO_INCREMENT=18662 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_language_prefix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix` (`prefix`)
) ENGINE=MyISAM AUTO_INCREMENT=254 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_language_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `languageId` int(11) NOT NULL DEFAULT '0',
  `keyId` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyId` (`keyId`,`languageId`)
) ENGINE=MyISAM AUTO_INCREMENT=62092 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `type` varchar(100) NOT NULL,
  `key` varchar(100) DEFAULT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_login_cookie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `cookie` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `cookie` (`cookie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipientEmail` varchar(100) NOT NULL,
  `senderEmail` varchar(100) NOT NULL,
  `senderName` varchar(100) NOT NULL,
  `subject` text NOT NULL,
  `textContent` text NOT NULL,
  `htmlContent` text,
  `sentTime` int(11) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '1',
  `senderSuffix` int(11) NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_mass_mailing_ignore_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_media_panel_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `userId` int(11) NOT NULL,
  `data` text NOT NULL,
  `stamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_menu_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(110) NOT NULL DEFAULT '',
  `key` varchar(150) NOT NULL DEFAULT '',
  `documentKey` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(70) NOT NULL DEFAULT '',
  `order` int(11) DEFAULT NULL,
  `routePath` varchar(255) DEFAULT NULL,
  `externalUrl` varchar(255) DEFAULT NULL,
  `newWindow` tinyint(1) DEFAULT '0',
  `visibleFor` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`prefix`),
  KEY `documentKey` (`documentKey`)
) ENGINE=MyISAM AUTO_INCREMENT=482 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_place` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `editableByUser` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_place_entity_scheme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `placeId` int(11) DEFAULT NULL,
  `schemeId` int(11) DEFAULT NULL,
  `entityId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`entityId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_place_scheme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `placeId` int(11) DEFAULT NULL,
  `schemeId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `module` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `developerKey` varchar(255) DEFAULT NULL,
  `isSystem` tinyint(1) NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `adminSettingsRoute` varchar(255) DEFAULT NULL,
  `uninstallRoute` varchar(255) DEFAULT NULL,
  `build` int(11) NOT NULL DEFAULT '0',
  `update` tinyint(1) NOT NULL DEFAULT '0',
  `licenseKey` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  UNIQUE KEY `module` (`module`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_preference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `defaultValue` text NOT NULL,
  `sectionName` varchar(100) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `sortOrder` (`sortOrder`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_preference_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`key`),
  KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_preference_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sectionName` varchar(32) DEFAULT NULL,
  `accountTypeName` varchar(32) DEFAULT NULL,
  `type` enum('text','select','datetime','boolean','multiselect') NOT NULL DEFAULT 'text',
  `presentation` enum('text','textarea','select','date','location','checkbox','multicheckbox','radio','url','password','age','birthdate') NOT NULL DEFAULT 'text',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `onJoin` tinyint(1) NOT NULL DEFAULT '0',
  `onEdit` tinyint(1) NOT NULL DEFAULT '0',
  `onSearch` tinyint(1) NOT NULL DEFAULT '0',
  `onView` tinyint(1) NOT NULL DEFAULT '0',
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `removable` tinyint(1) NOT NULL DEFAULT '1',
  `columnCount` int(11) NOT NULL DEFAULT '1',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `custom` varchar(2048) DEFAULT '',
  `parent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `sectionId` (`sectionName`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_question_account_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `roleId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_question_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionPresentation` enum('text','textarea','select','date','location','checkbox','multicheckbox','radio','url','password','age','birthdate') NOT NULL DEFAULT 'text',
  `name` varchar(255) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `presentationClass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_question_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionName` varchar(255) NOT NULL DEFAULT '',
  `userId` int(11) NOT NULL DEFAULT '0',
  `textValue` text NOT NULL,
  `intValue` int(11) NOT NULL DEFAULT '0',
  `dateValue` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`questionName`),
  KEY `fieldName` (`questionName`),
  KEY `intValue` (`intValue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_question_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT '1',
  `isHidden` int(11) NOT NULL DEFAULT '0',
  `isDeletable` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sectionName` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_question_to_account_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountType` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `questionName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_question_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionName` varchar(255) DEFAULT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `questionName` (`questionName`,`value`)
) ENGINE=MyISAM AUTO_INCREMENT=427 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `score` int(10) unsigned NOT NULL,
  `timeStamp` int(10) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `entityType` (`entityType`),
  KEY `entityId` (`entityId`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_remote_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `userId` int(11) NOT NULL,
  `remoteId` varchar(50) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `custom` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_restricted_usernames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_scheme` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `rightCssClass` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `leftCssClass` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `cssClass` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_search_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `searchId` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) NOT NULL,
  `sortOrder` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `searchResult` (`searchId`,`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `developerKey` varchar(255) DEFAULT NULL,
  `build` int(11) NOT NULL DEFAULT '0',
  `update` tinyint(4) NOT NULL DEFAULT '0',
  `licenseKey` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT '0',
  `customCss` text,
  `mobileCustomCss` text,
  `customCssFileName` varchar(255) DEFAULT NULL,
  `sidebarPosition` enum('left','right','none') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=956 DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_theme_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `themeId` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `themeId` (`themeId`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_theme_control` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) NOT NULL,
  `selector` text NOT NULL,
  `defaultValue` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'text',
  `themeId` int(10) unsigned NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT '',
  `section` text NOT NULL,
  `label` text NOT NULL,
  `description` text,
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`key`),
  KEY `themeId` (`themeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_theme_control_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `themeControlKey` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `themeId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `themeControlKey` (`themeControlKey`,`themeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_theme_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_theme_master_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `themeId` int(11) NOT NULL,
  `documentKey` varchar(255) NOT NULL,
  `masterPage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `themeId` (`themeId`),
  KEY `documentKey` (`documentKey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL DEFAULT '',
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `joinStamp` int(11) NOT NULL DEFAULT '0',
  `activityStamp` int(11) NOT NULL DEFAULT '0',
  `accountType` varchar(32) NOT NULL DEFAULT '',
  `emailVerify` tinyint(2) NOT NULL DEFAULT '0',
  `joinIp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `accountType` (`accountType`),
  KEY `joinStamp` (`joinStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_auth_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `token` varchar(50) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `blockedUserId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_blockedUserId` (`userId`,`blockedUserId`),
  KEY `userId` (`userId`),
  KEY `blockedUserId` (`blockedUserId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_disapprove` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_featured` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 MIN_ROWS=20;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `activityStamp` int(11) NOT NULL,
  `context` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_reset_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `code` varchar(150) NOT NULL,
  `expirationTimeStamp` int(11) NOT NULL,
  `updateTimeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_user_suspend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_vote` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `entityId` int(11) unsigned NOT NULL,
  `entityType` varchar(255) NOT NULL,
  `vote` tinyint(4) NOT NULL,
  `timeStamp` int(11) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `entityId` (`entityId`),
  KEY `entityType` (`entityType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_search_entity` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `entityType` varchar(50) NOT NULL,
    `entityId` int(10) unsigned NOT NULL,
    `text` text NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'active',
    `timeStamp` int(10) unsigned NOT NULL,
    `activated` tinyint(1) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `entity` (`entityType`,`entityId`),
    KEY `status` (`status`, `activated`, `timeStamp`),
    FULLTEXT KEY `entityText` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

OW::getDbo()->query("CREATE TABLE `" . OW_DB_PREFIX . "base_search_entity_tag` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `entityTag` varchar(50) NOT NULL,
    `searchEntityId` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `searchEntityId` (`searchEntityId`),
    KEY `entityTag` (`entityTag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

// Configs

OW::getConfig()->addConfig("base", "avatar_big_size", "190", "User avatar width");
OW::getConfig()->addConfig("base", "avatar_size", "90", "User avatar height");
OW::getConfig()->addConfig("base", "selectedTheme", "origin", "Selected theme.");
OW::getConfig()->addConfig("base", "military_time", "1", "Desc");
OW::getConfig()->addConfig("base", "site_name", "ow-1.7.0", "Site name");
OW::getConfig()->addConfig("base", "confirm_email", "1", "Confirm email");
OW::getConfig()->addConfig("base", "user_view_presentation", "table", "User view presentation");
OW::getConfig()->addConfig("base", "site_tagline", "ow-1.7.0", "Site tagline");
OW::getConfig()->addConfig("base", "site_description", "Just another Oxwall site", "Site Description");
OW::getConfig()->addConfig("base", "site_timezone", "US/Pacific", "Site Timezone");
OW::getConfig()->addConfig("base", "site_use_relative_time", "1", "Use relative date/time");
OW::getConfig()->addConfig("base", "display_name_question", "username", "Question used for display name");
OW::getConfig()->addConfig("base", "site_email", "qwe@mail.com", "Email address from which your users will receive notifications and mass mailing.");
OW::getConfig()->addConfig("base", "google_analytics", "NULL,NULL", null);
OW::getConfig()->addConfig("base", "mail_smtp_enabled", null, "Smtp enabled");
OW::getConfig()->addConfig("base", "date_field_format", "dmy", "Date format");
OW::getConfig()->addConfig("base", "mail_smtp_host", "Host", "Smtp Host");
OW::getConfig()->addConfig("base", "mail_smtp_user", "Username", "Smtp User");
OW::getConfig()->addConfig("base", "mail_smtp_password", "Password", "Smtp passwprd");
OW::getConfig()->addConfig("base", "mail_smtp_port", "Port", "Smtp Port");
OW::getConfig()->addConfig("base", "mail_smtp_connection_prefix", null, "Smpt connection prefix (tsl, ssl)");
OW::getConfig()->addConfig("base", "splash_screen", null, null);
OW::getConfig()->addConfig("base", "who_can_join", "1", null);
OW::getConfig()->addConfig("base", "who_can_invite", "1", null);
OW::getConfig()->addConfig("base", "guests_can_view", "1", null);
OW::getConfig()->addConfig("base", "guests_can_view_password", null, null);
OW::getConfig()->addConfig("base", "splash_leave_url", "http://google.com", null);
OW::getConfig()->addConfig("base", "maintenance", null, null);
OW::getConfig()->addConfig("base", "mandatory_user_approve", null, "mandatory_user_approve");
OW::getConfig()->addConfig("base", "billing_currency", "USD", "Site currency 3-char code");
OW::getConfig()->addConfig("base", "tf_max_pic_size", "2.500000", null);
OW::getConfig()->addConfig("base", "soft_build", "8710", "Current soft version");
OW::getConfig()->addConfig("base", "update_soft", null, "Soft core update flag");
OW::getConfig()->addConfig("base", "unverify_site_email", null, "Email address from which your users will receive notifications and mass mailing.");
OW::getConfig()->addConfig("base", "soft_version", "1.7.3", null);
OW::getConfig()->addConfig("base", "site_installed", null, null);
OW::getConfig()->addConfig("base", "check_mupdates_ts", null, "Last manual updates check timestamp.");
OW::getConfig()->addConfig("base", "dev_mode", "1", null);
OW::getConfig()->addConfig("base", "log_file_max_size_mb", "20", null);
OW::getConfig()->addConfig("base", "attch_file_max_size_mb", "2", null);
OW::getConfig()->addConfig("base", "attch_ext_list", "[\"txt\",\"doc\",\"docx\",\"sql\",\"csv\",\"xls\",\"ppt\",\"pdf\",\"jpg\",\"jpeg\",\"png\",\"gif\",\"bmp\",\"psd\",\"ai\",\"avi\",\"wmv\",\"mp3\",\"3gp\",\"flv\",\"mkv\",\"mpeg\",\"mpg\",\"swf\",\"zip\",\"gz\",\"tgz\",\"gzip\",\"7z\",\"bzip2\",\"rar\"]", null);
OW::getConfig()->addConfig("base", "admin_cookie", "turUXYruzErYXEJymA8eBeZy7aWyqYju", null);
OW::getConfig()->addConfig("base", "disable_mobile_context", null, null);
OW::getConfig()->addConfig("base", "default_avatar", "[]", "Default avatar");
OW::getConfig()->addConfig("base", "language_switch_allowed", "1", "Allow users switch languages on site");
OW::getConfig()->addConfig("base", "rss_loading", null, null);
OW::getConfig()->addConfig("base", "cron_is_active", "1", "Flag showing if cron script is activated after soft install");
OW::getConfig()->addConfig("base", "users_count_on_page", "30", "Users count on page");
OW::getConfig()->addConfig("base", "join_display_photo_upload", "display", "Display \'Photo Upload\' field on Join page.");
OW::getConfig()->addConfig("base", "join_photo_upload_set_required", "1", "Make \'Photo Upload\' a required field on Join Page.");
OW::getConfig()->addConfig("base", "join_display_terms_of_use", null, "Display \'Terms of use\' field on Join page.");
OW::getConfig()->addConfig("base", "favicon", "1", null);
OW::getConfig()->addConfig("base", "html_head_code", null, "Code (meta, css, js) added from admin panel into head section of HTML document.");
OW::getConfig()->addConfig("base", "html_prebody_code", null, "Code (js) added before \'body\' closing tag.");
OW::getConfig()->addConfig("base", "tf_user_custom_html_disable", "1", null);
OW::getConfig()->addConfig("base", "tf_user_rich_media_disable", null, null);
OW::getConfig()->addConfig("base", "tf_comments_rich_media_disable", null, null);
OW::getConfig()->addConfig("base", "tf_resource_list", "[\"clipfish.de\",\"youtube.com\",\"google.com\",\"metacafe.com\",\"myspace.com\",\"novamov.com\",\"myvideo.de\"]", null);
OW::getConfig()->addConfig("base", "cachedEntitiesPostfix", "53b266e920eba", null);
OW::getConfig()->addConfig("base", "master_page_theme_info", "[]", null);
OW::getConfig()->addConfig("base", "user_invites_limit", "50", null);
OW::getConfig()->addConfig("base", "profile_question_edit_stamp", "1402999957", null);
OW::getConfig()->addConfig("base", "install_complete", null, null);
OW::getConfig()->addConfig("base", "users_on_page", "12", null);
OW::getConfig()->addConfig("base", "avatar_max_upload_size", "1", "Enable file attachments");

// Menus
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "base_index", "base", "main_menu_index", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("hidden", "base_member_profile", "base", "main_menu_my_profile", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "users", "base", "users_main_menu_item", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "base_join", "base", "base_join_menu_item", OW_Navigation::VISIBLE_FOR_GUEST);
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "base_member_dashboard", "base", "dashboard", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_HIDDEN, "base_member_dashboard", "mobile", "mobile_pages_dashboard", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, "base.desktop_version", "base", "desktop_version_menu_item", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, "base_index", "base", "index_menu_item", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::BOTTOM, "base.mobile_version", "base", "mobile_version_menu_item", OW_Navigation::VISIBLE_FOR_ALL);

// Custom menu items

$menuItem = new BOL_MenuItem(); // Terms of use
$menuItem->prefix = "base";
$menuItem->key = "page-119658";
$menuItem->documentKey = "page-119658";
$menuItem->type = OW_Navigation::BOTTOM;
$menuItem->order = 1;
$menuItem->visibleFor = OW_Navigation::VISIBLE_FOR_ALL;

BOL_NavigationService::getInstance()->saveMenuItem($menuItem);

$menuItem = new BOL_MenuItem(); // Terms of use
$menuItem->prefix = "base";
$menuItem->key = "page_81959573";
$menuItem->documentKey = "page_81959573";
$menuItem->type = OW_Navigation::BOTTOM;
$menuItem->order = 2;
$menuItem->visibleFor = OW_Navigation::VISIBLE_FOR_ALL;

BOL_NavigationService::getInstance()->saveMenuItem($menuItem);

$menuItem = new BOL_MenuItem(); // Mobile terms of use
$menuItem->prefix = "ow_custom";
$menuItem->key = "mobile_page_14788567";
$menuItem->documentKey = "mobile_page_14788567";
$menuItem->type = OW_Navigation::MOBILE_BOTTOM;
$menuItem->order = 0;
$menuItem->visibleFor = OW_Navigation::VISIBLE_FOR_ALL;

BOL_NavigationService::getInstance()->saveMenuItem($menuItem);

// Documents
$document = new BOL_Document(); // Terms of use
$document->key = "page-119658";
$document->uri = "terms-of-use";
$document->isStatic = 1;
$document->isMobile = 0;

BOL_NavigationService::getInstance()->saveDocument($document);

$document = new BOL_Document(); // Privacy policy
$document->key = "page_81959573";
$document->uri = "privacy-policy";
$document->isStatic = 1;
$document->isMobile = 0;

BOL_NavigationService::getInstance()->saveDocument($document);

$document = new BOL_Document(); // Mobile terms of use
$document->key = "mobile_page_14788567";
$document->uri = "cp-55";
$document->isStatic = 1;
$document->isMobile = 1;

BOL_NavigationService::getInstance()->saveDocument($document);

// Roles
$guestRole = new BOL_AuthorizationRole();
$guestRole->name = "guest";
$guestRole->sortOrder = 0;

BOL_AuthorizationService::getInstance()->saveRole($guestRole);

$freeRole = new BOL_AuthorizationRole();
$freeRole->name = "free";
$freeRole->sortOrder = 1;

BOL_AuthorizationService::getInstance()->saveRole($guestRole);

OW::getAuthorization()->addGroup('base');
OW::getAuthorization()->addAction('base', 'add_comment');
OW::getAuthorization()->addAction('base', 'search_users', true);
OW::getAuthorization()->addAction('base', 'view_profile', true);

OW::getAuthorization()->addGroup('rate'); // TODO check if the group is used somewhere

// Widgets

BOL_ComponentAdminService::getInstance()->addPlace(BOL_ComponentService::PLACE_DASHBOARD, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_ComponentService::PLACE_INDEX, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_ComponentService::PLACE_PROFILE, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_MobileWidgetService::PLACE_MOBILE_INDEX, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD, false);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_AboutMeWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_RssWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_UserViewWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_JoinNowWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_ProfileWallWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_UserAvatarWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_IndexWallWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_AddNewContent", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_SIDEBAR, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_CustomHtmlWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX, "admin-4b543d8cdc488", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);
BOL_ComponentAdminService::getInstance()->saveComponentSettingList($placeWidget->uniqName, array("content" => "Welcome to our new site! Feel free to participate in our community!"));

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_UserListWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_MyAvatarWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_SIDEBAR, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_QuickLinksWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 2);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_MCMP_CustomHtmlWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX, "admin-5295f2e03ec8a", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 0);
BOL_ComponentAdminService::getInstance()->saveComponentSettingList($placeWidget->uniqName, array("content" => "Welcome to our community! Here you\'ll find like-minded individuals who are passionate about the same things as you!"));
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX, "admin-5295f2e40db5c", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 1);
BOL_ComponentAdminService::getInstance()->saveComponentSettingList($placeWidget->uniqName, array("content" => "Feel free to participate! Take a look around and help yourself."));

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_MCMP_RssWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_MCMP_UserListWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 2);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_ModerationToolsWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_WelcomeWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 1);


// Langs
OW::getLanguage()->importPluginLangs(dirname(__FILE__) . DS . "langs.zip", "base", false, true);


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
$questionSection->questionName = "d68489df439fe45427e305a0e2dbe349";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d68489df439fe45427e305a0e2dbe349";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "32";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "490d035a492be91d7bf9589f881e2d22";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "490d035a492be91d7bf9589f881e2d22";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "490d035a492be91d7bf9589f881e2d22";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "16";
$questionSection->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "8";
$questionSection->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "4";
$questionSection->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "1";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionSection->value = "32";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "92947e48441284286fe8a7b175f34a6e";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "128";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d68489df439fe45427e305a0e2dbe349";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "512";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "sex";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "sex";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "32";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "64";
$questionSection->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "128";
$questionSection->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "256";
$questionSection->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "512";
$questionSection->sortOrder = "9";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "28f881c609c933f6b1719cdf6dcf4cab";
$questionSection->value = "1024";
$questionSection->sortOrder = "10";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "32";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "64";
$questionSection->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "5d32f746a541b97f18a957ad5856318e";
$questionSection->value = "128";
$questionSection->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "ab9fc810a1938e599b7d084efea97d91";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "4971fc7002dca728f9a7f2a417c5284e";
$questionSection->value = "256";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "1e615090f832c4fbee805ded8e9ced08";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "1e615090f832c4fbee805ded8e9ced08";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "1e615090f832c4fbee805ded8e9ced08";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "f8f4c260c54166c8fcf79057fd85aec0";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "f8f4c260c54166c8fcf79057fd85aec0";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "f8f4c260c54166c8fcf79057fd85aec0";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "match_sex";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "match_sex";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "relationship";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "relationship";
$questionSection->value = "2";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "relationship";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "relationship";
$questionSection->value = "8";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "32";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "64";
$questionSection->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "128";
$questionSection->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "256";
$questionSection->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "512";
$questionSection->sortOrder = "9";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "1024";
$questionSection->sortOrder = "10";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "2048";
$questionSection->sortOrder = "11";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "4096";
$questionSection->sortOrder = "12";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "8192";
$questionSection->sortOrder = "13";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "16384";
$questionSection->sortOrder = "14";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "32768";
$questionSection->sortOrder = "15";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "65536";
$questionSection->sortOrder = "16";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "131072";
$questionSection->sortOrder = "17";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "262144";
$questionSection->sortOrder = "18";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "524288";
$questionSection->sortOrder = "19";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "1048576";
$questionSection->sortOrder = "20";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "2097152";
$questionSection->sortOrder = "21";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "4194304";
$questionSection->sortOrder = "22";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "8388608";
$questionSection->sortOrder = "23";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "16777216";
$questionSection->sortOrder = "24";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "33554432";
$questionSection->sortOrder = "25";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "67108864";
$questionSection->sortOrder = "26";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "134217728";
$questionSection->sortOrder = "27";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "268435456";
$questionSection->sortOrder = "28";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "536870912";
$questionSection->sortOrder = "29";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "9ce3cf807fd94892c8c7bb75dc2af60d";
$questionSection->value = "1073741824";
$questionSection->sortOrder = "30";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "8100f639e8becdefa741e05f0de73a15";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "8100f639e8becdefa741e05f0de73a15";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d37d41b71a78dfb62b379d0aa7bd3ba5";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "c5dc53f371fe6ba3001a7c7e31bd95fc";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7f2450f06779439551c75a8566c4070e";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7f2450f06779439551c75a8566c4070e";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7f2450f06779439551c75a8566c4070e";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7f2450f06779439551c75a8566c4070e";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7f2450f06779439551c75a8566c4070e";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7f2450f06779439551c75a8566c4070e";
$questionSection->value = "32";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionSection->value = "32";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "7fbd88047415229961f4d2aac620fe25";
$questionSection->value = "64";
$questionSection->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "a5115de7f38988e748370a59ba0b311d";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "1";
$questionSection->sortOrder = "0";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "2";
$questionSection->sortOrder = "1";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "4";
$questionSection->sortOrder = "2";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "8";
$questionSection->sortOrder = "3";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "16";
$questionSection->sortOrder = "4";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "32";
$questionSection->sortOrder = "5";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "64";
$questionSection->sortOrder = "6";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "128";
$questionSection->sortOrder = "7";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "256";
$questionSection->sortOrder = "8";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

$questionValue = new BOL_QuestionValue();
$questionSection->questionName = "d8aa20d67fbb6c6864e46c474d0bde10";
$questionSection->value = "512";
$questionSection->sortOrder = "9";
BOL_QuestionService::getInstance()->saveOrUpdateQuestionValue($questionValue);

// Question Configs

$questionConfig = new BOL_QuestionConfig();
$question->questionPresentation = "date";
$question->name = "year_range";
$question->description = "";
$question->presentationClass = "YearRange";
BOL_QuestionConfigDao::getInstance()->save($questionConfig);

$questionConfig = new BOL_QuestionConfig();
$question->questionPresentation = "age";
$question->name = "year_range";
$question->description = "";
$question->presentationClass = "YearRange";
BOL_QuestionConfigDao::getInstance()->save($questionConfig);

$questionConfig = new BOL_QuestionConfig();
$question->questionPresentation = "birthdate";
$question->name = "year_range";
$question->description = "";
$question->presentationClass = "YearRange";
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