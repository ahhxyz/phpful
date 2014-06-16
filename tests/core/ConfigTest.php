<?php
require_once '../core/Config.class.php';
class ConfigTest extends PHPUnit_Framework_TestCase{
    protected $object;
    
    protected function setUp(){
        $this->object=Config::getInstance();
    }
    
    protected function tearDown(){
        
    }
    
    
    public function testGetConfig(){
        
    }
    
    public function testGetConfig2(){
        
    }
    
    
}

