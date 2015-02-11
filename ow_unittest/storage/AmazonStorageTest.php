<?php

require_once 'PHPUnit/Framework/TestCase.php';


class AmazonStorageTest extends PHPUnit_Framework_TestCase
{
    public $data_patch;
    public $cloudStorage;

    protected function setUp()
    {
        $this->data_patch = dirname(__FILE__) . DS . 'data' . DS;
        $this->cloudStorage = new BASE_CLASS_AmazonCloudStorage();
    }

    // called after the test functions are executed
    // this function is defined in PHPUnit_TestCase and overwritten
    // here
    public function tearDown()
    {
        if( $this->cloudStorage->fileExists($this->data_patch) )
        {
            $this->cloudStorage->removeDir($this->data_patch);
        }
    }

    // test the Mkdir function
    public function testMkdir()
    {
        $this->cloudStorage->mkdir( $this->data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' );
        $this->assertTrue( $this->cloudStorage->isDir( $this->data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' ) );
        $this->assertTrue( !$this->cloudStorage->isFile( $this->data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' ) );

        $this->assertTrue( $this->cloudStorage->isDir( $this->data_patch . 'test'  ) );
        $this->assertTrue( !$this->cloudStorage->isFile( $this->data_patch . 'test'  ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'test'  ) );

        $this->assertTrue( $this->cloudStorage->isDir( $this->data_patch . 'test' . DS . 'test'. DS .'test'  ) );
        $this->assertTrue( !$this->cloudStorage->isFile( $this->data_patch . 'test' . DS . 'test'. DS .'test' ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'test' . DS . 'test'. DS .'test'  ) );
    }

    // test the CopyDir function
    public function testCopyDir()
    {
        $this->tearDown();
        $this->cloudStorage->copyDir( $this->data_patch . DS . 'data', $this->data_patch . 'data'  );

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data' );
        print_r($list);
        sort($list);
        print_r($list);

        $this->assertEquals( $this->data_patch . 'data' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->data_patch . 'data' . DS . '858_646_b.jpg', $list[1]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'folder', $list[2]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'test.tar.gz', $list[3]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'test.txt', $list[4]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'test.xml', $list[5]);

        $this->assertTrue( !$this->cloudStorage->isDir( $list[1]  ) );
        $this->assertTrue( $this->cloudStorage->isFile( $list[1] ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $list[1]  ) );

        $this->assertTrue( $this->cloudStorage->isDir( $list[2]  ) );
        $this->assertTrue( !$this->cloudStorage->isFile( $list[2] ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $list[2]  ) );

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data' . DS . 'folder' );

        sort($list);

        $this->assertEquals( $this->data_patch . 'data' . DS . 'folder' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'folder' . DS . 'test1', $list[1]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt', $list[2]);
    }

    //test the copyFileToLocalFS function
    public function testFileNameList()
    {
        $this->tearDown();
        $this->cloudStorage->copyDir( $this->data_patch . DS . 'data', $this->data_patch . 'data'  );

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data', null, array( 'tar.gz', 'jpg' ) );
        print_r($list);
        $this->assertEquals( 1, count($list) );
        $this->assertEquals(  $this->data_patch . 'data' . DS . '858_646_b.jpg', $list[0]);

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data', 'test' );
        sort($list);

        $this->assertEquals( 3, count($list) );
        $this->assertEquals(  $this->data_patch . 'data' . DS . 'test.tar.gz', $list[0]);
        $this->assertEquals(  $this->data_patch . 'data' . DS . 'test.txt', $list[1]);
        $this->assertEquals(  $this->data_patch . 'data' . DS . 'test.xml', $list[2]);

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data', 'test', array( 'xml' ) );

        $this->assertEquals( 1, count($list) );
        $this->assertEquals(  $this->data_patch . 'data' . DS . 'test.xml', $list[0]);

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data' . DS . 'test.xml' );
        $this->assertEquals( 0, count($list) );
    }

    // test the CopyDir function
    public function testCopyDirWithFileExtention()
    {
        $this->tearDown();

        $this->cloudStorage->copyDir( $this->data_patch . DS . 'data', $this->data_patch . 'data', array( 'txt' )  );

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data' );
        sort($list);
        print_r($list);
        $this->assertEquals( $this->data_patch . 'data' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'folder', $list[1]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'test.txt', $list[2]);

        $list = $this->cloudStorage->getFileNameList( $this->data_patch . 'data' . DS . 'folder' );
        sort($list);
        print_r($list);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'folder' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt', $list[1]);
    }

    // test the copyFileToLocalFS function
    public function testRemove()
    {
        $this->tearDown();

        // remove dir
        $this->cloudStorage->copyDir( $this->data_patch . DS . 'data', $this->data_patch . 'data', array( 'txt' ) );

        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'folder' ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt' ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'folder' . DS . '.svn' ) );

        $this->cloudStorage->removeDir( $this->data_patch . 'data' . DS . 'folder' . DS );

        $this->assertTrue( !$this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'folder' ) );
        $this->assertTrue( !$this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt' ) );
        $this->assertTrue( !$this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'folder' . DS . '.svn' ) );

        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' ) );
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'test.txt' ) );

        // try to remove file use removeDir function
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'test.txt' ) );

        $this->cloudStorage->removeDir( $this->data_patch . 'data' . DS . 'test.txt' );

        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'test.txt' ) );

        // remove file
        $this->assertTrue( $this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'test.txt' ) );

        $this->cloudStorage->removeFile( $this->data_patch . 'data' . DS . 'test.txt' );

        $this->assertTrue( !$this->cloudStorage->fileExists( $this->data_patch . 'data' . DS . 'test.txt' ) );

        //try to remove dir use removeFile function
        $this->assertTrue( $this->cloudStorage->fileExists(  $this->data_patch . 'data' ) );

        $this->cloudStorage->removeFile( $this->data_patch . 'data' . DS . 'test.txt' );

        $this->assertTrue( $this->cloudStorage->fileExists(  $this->data_patch . 'data' ) );
    }

    // test the copyFileToLocalFS function
    public function testCopyFileToLocalFS()
    {
        $this->tearDown();

        $path = $this->data_patch . 'test777.txt';

        $this->cloudStorage->Mkdir( $this->data_patch . 'data' . DS . 'folder' );

        $this->cloudStorage->copyFile( $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt',  $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt' );

        $this->cloudStorage->copyFileToLocalFS(  $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt',  $path);

        $this->assertTrue( file_exists( $path ) );

        unlink($this->data_patch . 'test777.txt');
    }

    // test the copyFileToLocalFS function
    public function testFileSetContent()
    {
        $this->tearDown();

        $this->cloudStorage->Mkdir($this->data_patch . 'data' . DS . 'folder');

        $string = 'Hello WORLD! 1233123123 *%*#@*%&#*@&*#(!)%#!*_#%*_*%_"';
        $path = $this->data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt';

        $this->cloudStorage->fileSetContent( $path, $string );
        $this->assertEquals( $string, $this->cloudStorage->fileGetContent( $path ) );
    }

    // test the copyFileToLocalFS function
    public function testFileUrl()
    {
        $this->tearDown();

        $this->cloudStorage->Mkdir($this->data_patch . 'data' . DS . 'folder');

        $string = 'Hello WORLD! 1233123123 *%*#@*%&#*@&*#(!)%#!*_#%*_*%_"';
        $path = $this->data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt';
        $this->cloudStorage->fileSetContent( $path, $string );

        $url = $this->cloudStorage->getFileUrl( $this->data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt' );
        $this->assertEquals( $this->cloudStorage->getBucketUrl() . '/ow_unittest/storage/data/data/folder/testContent.txt', $url );
    }

    // test the copyFileToLocalFS function
    public function testRenameFile()
    {
        $this->tearDown();

        $this->cloudStorage->mkdir($this->data_patch . 'data' . DS . 'folder');
        $string = 'Hello WORLD! 1233123123 *%*#@*%&#*@&*#(!)%#!*_#%*_*%_"';
        $path = $this->data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt';
        $this->cloudStorage->fileSetContent( $path, $string );
        $this->cloudStorage->mkdir( $this->data_patch . 'data' . DS . 'folder' );
        $this->cloudStorage->renameFile( $this->data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt', $this->data_patch . 'data' . DS . 'folder' . DS . 'testContent777.txt' );

        //$this->assertTrue($this->cloudStorage->FileExists( $this->data_patch . 'data' . DS . 'folder' . DS .'testContent777.txt' ));
        //$this->assertTrue($this->cloudStorage->isFile( $this->data_patch . 'data' . DS . 'folder' . DS . 'testContent777.txt' ));
    }
}