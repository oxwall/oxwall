# CONTENT OF THIS FILE

* Backup
* Core update
* Plugin update
* Theme update

# BACKUP

Make a full backup of all files, directories, and your database(s) before
starting, and save it outside your Oxwall installation directory.

# CORE UPDATE

**Auto update:**

Auto-update is all about clicking a button in admin area and entering your FTP details. We hope to make this feature run smooth on all possible server setups.

**Manual update:**

If auto update can't be applied you can try manual core update:

 - Download Oxwall Update Pack at http://www.oxwall.org/download/;
 - Unpack it to the root folder of your software install;
 - Finalize update by calling http://www.yoursite.com/ow_updates

# PLUGIN UPDATE

Plugin update works pretty much as core update with possible auto- and manual updates. You should try auto-updates unless manual update recommended. Always read release notes from developers for special instructions before trying to update plugins.

# THEME UPDATE

Currently, theme can be updated manually only. To update theme, follow these steps:

 - Besure that constant DEV_MODE is disabled in ow_includes/config.php file;
 - Download your current theme from our store;
 - Upload this theme to your site to ow_themes folder (overwrite existing theme);
 - After uploading was complete, enable DEV_MODE in ow_includes/config.php file;
 - Press F5 on any page of your site and disable DEV_MODE;



