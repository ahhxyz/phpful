<?php
namespace phpful\Core;
/*
 *配置
 *路由设置
 */
class Config {
	public  $config=array();
 	private static $instance=false;
 	private function __construct(){
	 	//载入配置文件；
		if(file_exists(APP_PATH.'/Common/Conf/config.php')){
			$this->config['Common']=require_once(APP_PATH.'/Common/Conf/config.php');
			$modules=array_values($this->config['Common']['MODULE_MAP']);
			foreach ($modules as  $mod) {
				if(strtolower($mod)!='common'){
					$this->config[$mod]=require_once(APP_PATH.'/'.$mod.'/Conf/config.php');
				}
			}
		}
 	}
    /*
     *；
     */
	public static function getIns(){
	 	if(self::$instance===false){
	 		self::$instance=new Config();
	 	}
	 	return self::$instance;
	}
	 
	public function getAll(){
	 	return $this->config;
	}


}


?>