<?php
require_once(dirname(__FILE__) . '/../storage/CloudStorageTest.php');
require_once(dirname(__FILE__) . '/../storage/FileStorageTest.php');
require_once(dirname(__FILE__) . '/../storage/AmazonStorageTest.php');


class StorageTests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite('storage');

    $suite->addTestSuite("AmazonStorageTest");
    $suite->addTestSuite("CloudStorageTest");
    $suite->addTestSuite("FileStorageTest");

    return $suite;
  }
}
