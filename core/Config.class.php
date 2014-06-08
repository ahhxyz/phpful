<?php
namespace phpful\core;
/*
 *
 *
 */
class Config {
    public  $config=array();
    private $appPath;
    private static $instance=false;
    private function __construct(){
        $this->appPath=APP_PATH;
        //载入配置文件；
        if(file_exists($this->appPath.'/config/main.php')){
            $this->config=require_once($this->appPath.'/config/main.php');
            
        }
    }
    
    
    
    private function __clone(){}
    
    /*
     *获取config类的单例
     */
    public static function getIns(){
            if(self::$instance===false){
                    self::$instance=new Config();
            }
            return self::$instance;
    }
/*
 * 获取配置信息，参数区分大小写
 */
    public function getConfig($name=""){
        if(empty($name)){
            return $this->config;
        }
        //$first=substr($name, 0,1);
        if(strtoupper($name)===$name){//获取默认配置中指定索引的配置信息
            
                return $this->config[$name];
            
        }else{//获取模块配置中指定索引的配置信息
            
            if(($pos=strpos($name,"/"))==-1){
                $mod=$name;
                
            }else{
                $mod=  substr($name, 0,$pos);
                $key= substr($name, $pos+1); 
                
            }
            
            $config=require_once($this->appPath.'/modules/'.$mod.'/config/main.php');
            if(!in_array($mod,$this->config['MODULES'])){
                return null;
            }
            
            //如果指定了key，则返回该模块配置中key对应的值，否则返回该模块所有的配置信息
            if($key){                
                return isset($config[$key])?$config[$key]:null;
            }else{
                return $config;
            }
            
            
        }
    }

    
}


?>