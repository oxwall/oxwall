<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
    "ALTER TABLE  `{$tblPrefix}base_log` CHANGE  `key`  `key` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL",
    "ALTER TABLE  `{$tblPrefix}base_log` DROP INDEX  `type`",
    "CREATE TABLE `{$tblPrefix}base_user_reset_password` (
      id INT(11) NOT NULL AUTO_INCREMENT,
      userId INT(11) NOT NULL,
      code VARCHAR(150) NOT NULL,
      expirationTimeStamp INT(11) NOT NULL,
      PRIMARY KEY (id),
      INDEX userId (userId)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;
    "
);

$sqlErrors = array();

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        $sqlErrors[] = $e;
    }
}

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( !empty($sqlErrors) )
{
    printVar($sqlErrors);
}
