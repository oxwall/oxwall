<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ImportLanguageTest extends PHPUnit_Framework_TestCase
{
    protected $fixturesDir;

    protected function tearDown()
    {
        //$this->deleteLangs();
    }
    
    protected function deleteLangs()
    {
        $this->fixturesDir = OW_DIR_ROOT.'ow_unittest'.DS.'ow_core'.DS.'fixtures'.DS.'importLanguage'.DS;

        $this->deleteLang( 'test_prefix', 'test_key_1' );
        $this->deleteLang( 'test_prefix', 'test_key_2' );
        $this->deleteLang( 'test_prefix', 'test_key_3' );
        
        $this->deletePrefix( 'test_prefix', true );
    }
    
    protected function deletePrefix($prefix)
    {
        $prefix = BOL_LanguageService::getInstance()->findPrefix($prefix);

        if ( !empty($prefix) )
        {
            BOL_LanguageService::getInstance()->deletePrefix($prefix->id);
        }
    }
    
    protected function deleteLang($prefix, $key)
    {
        $langKey = BOL_LanguageService::getInstance()->findKey($prefix, $key);

        if ( !empty($langKey) )
        {
            BOL_LanguageService::getInstance()->deleteKey($langKey->id, false);
        }
    }
    
    public function testNewImportFormDir()
    {
        $this->deleteLangs();
        
        $langService = BOL_LanguageService::getInstance();
        
        $langService->importPrefixFromDir($this->fixturesDir.'new'.DS.'langs'.DS, 'test_prefix', true);
        $this->isValidLangs();
    }
    
    public function testOldImportFormDir()
    {
        $this->deleteLangs();
        
        $langService = BOL_LanguageService::getInstance();
        
        $langService->importPrefixFromDir($this->fixturesDir.'old'.DS, 'test_prefix', true);
        $this->isValidLangs();
    }
    
    public function testNewImportFormZip()
    {
        $this->deleteLangs();
        
        $langService = BOL_LanguageService::getInstance();
        
        $langService->importPrefixFromZip($this->fixturesDir.'new.zip', 'test_prefix', true);
        $this->isValidLangs();
    }
    
    public function testOldImportFormZip()
    {
        $this->deleteLangs();
        
        $langService = BOL_LanguageService::getInstance();
        
        $langService->importPrefixFromZip($this->fixturesDir.'old.zip', 'test_prefix', true);
        $this->isValidLangs();
    }
    
    public function testExportData()
    {
        $this->deleteLangs();
        
        $langService = BOL_LanguageService::getInstance();
        
        $langService->importPrefixFromZip($this->fixturesDir.'export.zip', 'test_prefix', true);
        $this->isValidLangs();
        
        $this->deleteLangs();
        
        $langService->importPrefixFromDir($this->fixturesDir.'export'.DS.'langs'.DS, 'test_prefix', true);
        $this->isValidLangs();
    }    
    
    protected function isValidLangs()
    {
        $langService = BOL_LanguageService::getInstance();
        $prefix = BOL_LanguageService::getInstance()->findPrefix('test_prefix');
        
        $this->assertTrue( (boolean)(!empty($prefix) && $prefix instanceof BOL_LanguagePrefix) );
        $this->assertEquals('Test prefix', $prefix->label );
        
        $lang = BOL_LanguageService::getInstance()->findByTag('en');
        
        $this->assertEquals( 'test1', $langService->getText($lang->id, 'test_prefix', 'test_key_1'));
        $this->assertEquals( 'test2', $langService->getText($lang->id, 'test_prefix', 'test_key_2'));
        $this->assertEquals( 'test3', $langService->getText($lang->id, 'test_prefix', 'test_key_3'));
    }
}