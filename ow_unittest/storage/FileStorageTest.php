<?php

define('_OW_', true);

require_once 'PHPUnit/Framework/TestCase.php';


class FileStorageTest extends PHPUnit_Framework_TestCase
{
    public $data_patch;
    public $fileStorage;

    protected function setUp()
    {
        $this->data_patch = dirname(__FILE__) . DS . 'data' . DS;
        $this->file_storage_data_patch = dirname(__FILE__) . DS . 'file_storage_data' . DS ;
        $this->fileStorage = new BASE_CLASS_FileStorage();
    }

    // called after the test functions are executed
    // this function is defined in PHPUnit_TestCase and overwritten
    // here
    public function tearDown()
    {
        if( $this->fileStorage->fileExists($this->file_storage_data_patch) )
        {
            $this->fileStorage->removeDir($this->file_storage_data_patch);
        }
    }

    // test the Mkdir function
    public function testMkdir()
    {
        $this->fileStorage->mkdir( $this->file_storage_data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' );
        $this->assertTrue( $this->fileStorage->isDir( $this->file_storage_data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' ) );
        $this->assertTrue( !$this->fileStorage->isFile( $this->file_storage_data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' ) );
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'test' . DS . 'test'. DS .'test' . DS . 'test' . DS . 'test' ) );

        $this->assertTrue( $this->fileStorage->isDir( $this->file_storage_data_patch . 'test' ) );
        $this->assertTrue( !$this->fileStorage->isFile( $this->file_storage_data_patch . 'test'  ) );
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'test'  ) );

        $this->assertTrue( $this->fileStorage->isDir( $this->file_storage_data_patch . 'test' . DS . 'test'. DS .'test'  ) );
        $this->assertTrue( !$this->fileStorage->isFile( $this->file_storage_data_patch . 'test' . DS . 'test'. DS .'test' ) );
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'test' . DS . 'test'. DS .'test'  ) );
    }

    // test the CopyDir function
    public function testCopyDir()
    {
        $this->tearDown();
        $this->fileStorage->copyDir( $this->data_patch . DS . 'data', $this->file_storage_data_patch . 'data'  );

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data' );
        
        sort($list);

        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . '858_646_b.jpg', $list[1]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'folder', $list[2]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'test.tar.gz', $list[3]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'test.txt', $list[4]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'test.xml', $list[5]);

        $this->assertTrue( !$this->fileStorage->isDir( $list[1]  ) );
        $this->assertTrue( $this->fileStorage->isFile( $list[1] ) );
        $this->assertTrue( $this->fileStorage->fileExists( $list[1]  ) );

        $this->assertTrue( $this->fileStorage->isDir( $list[2] ) );
        $this->assertTrue( !$this->fileStorage->isFile( $list[2] ) );
        $this->assertTrue( $this->fileStorage->fileExists( $list[2] ) );

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data' . DS . 'folder' );

        sort($list);

        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test1', $list[1]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test777.txt', $list[2]);
    }

    // test the copyFileToLocalFS function
    public function testFileNameList()
    {
        $this->tearDown();
        $this->fileStorage->copyDir( $this->data_patch . DS . 'data', $this->file_storage_data_patch . 'data'  );

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data', null, array( 'tar.gz', 'jpg' ) );

        $this->assertEquals( 1, count($list) );
        $this->assertEquals(  $this->file_storage_data_patch . 'data' . DS . '858_646_b.jpg', $list[0]);

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data', 'test' );

        $this->assertEquals( 3, count($list) );
        $this->assertEquals(  $this->file_storage_data_patch . 'data' . DS . 'test.xml', $list[0]);
        $this->assertEquals(  $this->file_storage_data_patch . 'data' . DS . 'test.txt', $list[1]);
        $this->assertEquals(  $this->file_storage_data_patch . 'data' . DS . 'test.tar.gz', $list[2]);

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data', 'test', array( 'xml' ) );

        $this->assertEquals( 1, count($list) );
        $this->assertEquals(  $this->file_storage_data_patch . 'data' . DS . 'test.xml', $list[0]);

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data' . DS . 'test.xml' );
        $this->assertEquals( 0, count($list) );
    }

    // test the CopyDir function
    public function testCopyDirWithFileExtention()
    {
        $this->tearDown();

        $this->fileStorage->copyDir( $this->data_patch . DS . 'data', $this->file_storage_data_patch . 'data', array( 'txt' )  );

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data' );
        sort($list);

        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'folder', $list[1]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'test.txt', $list[2]);

        $list = $this->fileStorage->getFileNameList( $this->file_storage_data_patch . 'data' . DS . 'folder' );
        sort($list);
        
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . '.svn', $list[0]);
        $this->assertEquals( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test777.txt', $list[1]);
    }
    
    // test the copyFileToLocalFS function
    public function testRemove()
    {
        $this->tearDown();

        // remove dir
        $this->fileStorage->copyDir( $this->data_patch . DS . 'data', $this->file_storage_data_patch . 'data', array( 'txt' ) );

        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' ) );
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test777.txt' ) );
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . '.svn' ) );

        $this->fileStorage->removeDir( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS );

        $this->assertTrue( !$this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' ) );
        $this->assertTrue( !$this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test777.txt' ) );
        $this->assertTrue( !$this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . '.svn' ) );

        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' ) );
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'test.txt' ) );

        // try to remove file use removeDir function
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'test.txt' ) );

        $this->fileStorage->removeDir( $this->file_storage_data_patch . 'data' . DS . 'test.txt' );

        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'test.txt' ) );

        // remove file
        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'test.txt' ) );

        $this->fileStorage->removeFile( $this->file_storage_data_patch . 'data' . DS . 'test.txt' );

        $this->assertTrue( !$this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'test.txt' ) );

        //try to remove dir use removeFile function
        $this->assertTrue( $this->fileStorage->fileExists(  $this->file_storage_data_patch . 'data' ) );

        $this->fileStorage->removeFile( $this->file_storage_data_patch . 'data' . DS . 'test.txt' );

        $this->assertTrue( $this->fileStorage->fileExists(  $this->file_storage_data_patch . 'data' ) );
    }

    // test the copyFileToLocalFS function
    public function testCopyFileToLocalFS()
    {
        $this->tearDown();

        $this->fileStorage->Mkdir( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS );

        $this->fileStorage->copyFile( $this->data_patch . 'data' . DS . 'folder' . DS . 'test777.txt',  $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test777.txt' );

        $this->assertTrue( $this->fileStorage->fileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test777.txt' ) );

        $this->fileStorage->copyFileToLocalFS( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'test777.txt',  $this->data_patch . 'test777.txt' );

        $this->assertTrue( file_exists( $this->data_patch . 'test777.txt' ) );

        unlink($this->data_patch . 'test777.txt');
    }

    // test the copyFileToLocalFS function
    public function testFileSetContent()
    {
        $this->tearDown();

        $this->fileStorage->Mkdir($this->file_storage_data_patch . 'data' . DS . 'folder');

        $string = 'Hello WORLD! 1233123123 *%*#@*%&#*@&*#(!)%#!*_#%*_*%_"';
        $path = $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt';

        $this->fileStorage->fileSetContent( $path, $string );
        $this->assertEquals( $string, $this->fileStorage->fileGetContent( $path ) );
    }

    // test the copyFileToLocalFS function
    public function testFileUrl()
    {
        $this->tearDown();

        $this->fileStorage->Mkdir($this->file_storage_data_patch . 'data' . DS . 'folder');

        $string = 'Hello WORLD! 1233123123 *%*#@*%&#*@&*#(!)%#!*_#%*_*%_"';
        $path = $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt';

        $this->fileStorage->fileSetContent( $path, $string );

        $url = $this->fileStorage->getFileUrl( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt' );
        $this->assertEquals( OW_URL_HOME . 'ow_unittest/storage/file_storage_data/data/folder/testContent.txt', $url );
    }

    // test the copyFileToLocalFS function
    public function testRenameFile()
    {
        $this->tearDown();

        $this->fileStorage->Mkdir($this->file_storage_data_patch . 'data' . DS . 'folder');

        $string = 'Hello WORLD! 1233123123 *%*#@*%&#*@&*#(!)%#!*_#%*_*%_"';
        $path = $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt';

        $this->fileStorage->fileSetContent( $path, $string );

        $this->fileStorage->renameFile( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent.txt', $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent777.txt' );

        $this->assertTrue($this->fileStorage->FileExists( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent777.txt' ));
        $this->assertTrue($this->fileStorage->isFile( $this->file_storage_data_patch . 'data' . DS . 'folder' . DS . 'testContent777.txt' ));
    }
}