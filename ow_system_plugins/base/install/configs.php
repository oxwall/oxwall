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

// Configs

// Selected theme
OW::getConfig()->addConfig("base", "selectedTheme", "simplicity", "Selected theme.");

// TODO Read from ow_version.xml
OW::getConfig()->addConfig("base", "soft_build", "9900", "Current soft version");
OW::getConfig()->addConfig("base", "soft_version", "1.8.0", null);


OW::getConfig()->addConfig("base", "avatar_big_size", "190", "User avatar width");
OW::getConfig()->addConfig("base", "avatar_size", "90", "User avatar height");
OW::getConfig()->addConfig("base", "military_time", "1", "Desc");
OW::getConfig()->addConfig("base", "site_name", "", "Site name");
OW::getConfig()->addConfig("base", "confirm_email", "1", "Confirm email");
OW::getConfig()->addConfig("base", "user_view_presentation", "table", "User view presentation");
OW::getConfig()->addConfig("base", "site_tagline", "", "Site tagline");
OW::getConfig()->addConfig("base", "site_description", "Just another Oxwall site", "Site Description");
OW::getConfig()->addConfig("base", "site_timezone", "US/Pacific", "Site Timezone");
OW::getConfig()->addConfig("base", "site_use_relative_time", "1", "Use relative date/time");
OW::getConfig()->addConfig("base", "display_name_question", "realname", "Question used for display name");
OW::getConfig()->addConfig("base", "site_email", "", "Email address from which your users will receive notifications and mass mailing.");
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
OW::getConfig()->addConfig("base", "site_statistics_disallowed_entity_types", "user-status,avatar-change", null);
OW::getConfig()->addConfig("base", "tf_max_pic_size", "2.500000", null);
OW::getConfig()->addConfig("base", "update_soft", null, "Soft core update flag");
OW::getConfig()->addConfig("base", "unverify_site_email", null, "Email address from which your users will receive notifications and mass mailing.");
OW::getConfig()->addConfig("base", "site_installed", null, null);
OW::getConfig()->addConfig("base", "check_mupdates_ts", null, "Last manual updates check timestamp.");
OW::getConfig()->addConfig("base", "dev_mode", "1", null);
OW::getConfig()->addConfig("base", "log_file_max_size_mb", "20", null);
OW::getConfig()->addConfig("base", "attch_file_max_size_mb", "2", null);
OW::getConfig()->addConfig("base", "attch_ext_list", json_encode(array(
    "txt","doc","docx","sql","csv","xls","ppt","pdf","jpg","jpeg","png","gif","bmp","psd","ai","avi","wmv","mp3","3gp","flv","mkv","mpeg","mpg","swf","zip","gz","tgz","gzip","7z","bzip2","rar"
)), null);
OW::getConfig()->addConfig("base", "admin_cookie", "", null);
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
OW::getConfig()->addConfig("base", "tf_resource_list", json_encode(array(
    "clipfish.de", "youtube.com", "google.com", "metacafe.com", "myspace.com", "novamov.com", "myvideo.de"
)), null);
OW::getConfig()->addConfig("base", "cachedEntitiesPostfix", "53b266e920eba", null);
OW::getConfig()->addConfig("base", "master_page_theme_info", "[]", null);
OW::getConfig()->addConfig("base", "user_invites_limit", "50", null);
OW::getConfig()->addConfig("base", "profile_question_edit_stamp", "", null);
OW::getConfig()->addConfig("base", "install_complete", null, null);
OW::getConfig()->addConfig("base", "users_on_page", "12", null);
OW::getConfig()->addConfig("base", "avatar_max_upload_size", "1", "Enable file attachments");
OW::getConfig()->addConfig('base', 'enable_captcha', 'true', 'is captcha enabled on join form?');