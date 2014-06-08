<?php
namespace phpful;
use phpful\core\Config;

/*
 *@框架入口文件
 *1.开启session
 *2.定义路径常量
 *3.引入基础文件
 *4.注册自动加载文件的方法
 *5.启动框架
*/
session_start();
header("Content-type:text/html;charset=utf-8");
version_compare(PHP_VERSION,'5.3.0','>') or die('PHP版本必须不低于5.3.0，请升级您的PHP版本');

defined('APP_PATH') or die('You must define the application path !');

define('APP_NAME',basename(APP_PATH));

@ini_set('magic_quotes_runtime', 0);


//var_dump($_SERVER);

//定义常量

//defined('PHPFUL_PATH') or define('PHPFUL_PATH', __DIR__);
//defined('CORE_PATH')   or define('CORE_PATH',  PHPFUL_PATH.'/core');//框架核心库路径
//defined('LIB_PATH')    or define('LIB_PATH',PHPFUL_PATH.'/Lib');//框架扩展库路径
define('DS', DIRECTORY_SEPARATOR);
define('ROOT',$_SERVER['DOCUMENT_ROOT']);//网站根目录
define('URI',$_SERVER['REQUEST_URI']);
//自动加载类
spl_autoload_register('phpful\autoLoad');//这个注册是全局的；

//获取配置信息
$config= Config::getIns()->getConfig();


//创建应用的文件夹
$folders=array("controllers","models","views","config","modules","runtimes");
foreach($folders as $folder){
    if(!file_exists(APP_PATH.'/'.$folder)){
	mkdir(APP_PATH.'/'.$folder);
    }
}


/*
 *启动框架
 *
 */



//检查是否存在已经定义的路由，
if(($router=checkRouters($config))!=false){
  list($module,$controller,$query)=$router;
    
}else{
  list($module,$controller,$query)=parseURI($config,URI);
    
}


if(isset($module)){
    $classname=APP_NAME."\modules\\".$module."\controllers\\".$controller."Controller";
}else{
    $classname=APP_NAME."\controllers\\".$controller."Controller";
}


$method=ucfirst($_SERVER['REQUEST_METHOD']);//访问页面使用的请求方法
    


//不管上面如何处理，只要最终返回真实的模块名、控制器名、方法名即可，分别是$module、$controller、$method

define('MODULE',  $module);//真实的模块名(如果存在)
define('CONTROLLER',$controller);//真实的控制器名称和模型名称
define('METHOD',$method);





//调用对应控制器的方法

if(class_exists($classname)){

        $ref=new \ReflectionClass($classname);
        $controller=$ref->newInstance();
    //前置的拦截器方法    
    if(method_exists($classname,"before".$method)) {
        call_user_func(array($classname,"before".$method)); 
    }
    
    //$controller->query=$query;
    $controller->$method(); //执行对应的控制器方法
    
    //后置的拦截器方法
    if(method_exists($classname,"after".$method)) {
        call_user_func(array($classname,"after".$method)); 	    	
    }

}else{
    exit('<div><h1  style="color:#f00" >404</h1></div>');
    //Class '.$classname.' is Not found !
}

/*
 *自动加载类的函数
 */
function autoLoad($classname) {  //参数$classname就是要调用但可能尚未加载的类名
    //echo $classname.PHP_EOL;
    
    $classpath=dirname(__DIR__).'/'.$classname.'.class.php';
    $filepath=dirname(__DIR__).'/'.$classname.'.php';
    //var_dump(file_exists($classpath));
    if(file_exists($classpath)){
		require_once($classpath);
    }elseif(file_exists($filepath)){
		require_once($filepath);
    }

  
}

function checkRouters($conf){
    
    if(isset($conf['ROUTERS'])){
        foreach ($conf['ROUTERS'] as $mode => $action) {
            $pattern="/".str_replace("/", "\/", $mode)."/";
            if(preg_match($pattern, URI)){
                return parseURI($conf,"/".$action);
                
            }
        }
    }
    return false;

}


/*
 * 返回一个完整的控制器的类名
 */
function parseURI($config,$URI){
   
    if($URI=='/'){//如果是根目录，则调用 App\controllers\IndexController这个控制器
        $module=null;
        $controller='Index';
        return array($module,$controller,null);
    }    
    
    
    $URIs=parse_url($URI);
    // var_dump($URI,$$URIs);
    $path=substr($URIs['path'],1);
    @$query=$URIs['query'];
    
    if(empty($query)){//RESTFUL的URL:admin/app/3&user/cat/2
        
        //关联数据表
        if(($pos=strpos($path, "&"))!==false&&$config['URL_REF']){ //预留扩展
            //$master=  substr($path, 0,$pos);           
            
            $modelClassName=  str_replace("/models/","/controllers/",$controllerClassName);
            //$model=new $modelClassName();
            //$model->sql="";
            
            return $controllerClassName;
        }else{//不关联数据表
            $uris=  explode("/", $URI);
            
            if(in_array($uris[1],$config["MODULES"])){
                $module=$uris[1];
                $controller=$uris[2];
                $query=  array_slice($uris, 3);
                
            
            }else{
                $module=null;
                $controller=$uris[1];
                $query=  array_slice($uris, 2);
                
                
            }
        }
    }else{//常规的URL形式：/admin/app?id=3&r=user&cat=2
        
    }
    return array($module,$controller,$query);
}






//根据模块映射中定义的控制器名来获取真实的控制器名
function getRealController($config,$controller){
    if (isset($config[MODULE]['CONTROLLER_MAP'])) {
            if (array_key_exists($controller,$config[$module]['CONTROLLER_MAP'])){

                    $controller=$config[$module]['CONTROLLER_MAP'][$controller];//真实的模型名，对应着控制器

            }
    }	
    return $controller;
}




?>
