<?php
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);
define('OW_DIR_ROOT', substr(dirname(__FILE__), 0, - strlen('ow_unittest')));

$_SERVER['REQUEST_URI'] = '';// just a hack that kills the annoying notice

// Cloud Files
define('OW_USE_CLOUDFILES', true);
define('OW_CLOUDFILES_USER', 'skalfa');
define('OW_CLOUDFILES_API_KEY', '5b8492da49cec3487df21007efdadc41');
define('OW_CLOUDFILES_CONTAINER', 'unittest');

require_once(OW_DIR_ROOT.'ow_includes'.DS.'init.php');

$application = OW_Application::getInstance();

$application->init();

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class AllTests
{
  public static function main()
  {
    $reportDir = dirname(__FILE__) . '/test_report/';
    PHPUnit_TextUI_TestRunner::run(self::suite(), array(
      'backupGlobals' => false,
      'backupStaticAttributes' => false,
    ), $reportDir);
  }

  public static function suite()
  {
    $testDir = dirname(__FILE__) . DS . 'AllTests' . DS ;

    $suite = new PHPUnit_Framework_TestSuite('OW tests');

    require_once($testDir . 'storage.php');
    $suite->addTest(StorageTests::suite());

    return $suite;
  }
}

AllTests::main();

