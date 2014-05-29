<?php
namespace phpful;
use phpful\Core\Config;

/*
 *@框架入口文件
 *1.开启session
 *2.定义路径常量
 *3.引入基础文件
 *4.注册自动加载文件的方法
 *5.启动框架
*/
session_start();

version_compare(PHP_VERSION,'5.3.0','>') or die('PHP版本必须不低于5.3.0，请升级您的PHP版本');
defined('APP_PATH') or die('You must define the application path !');
@ini_set('magic_quotes_runtime', 0);

//定义常量

defined('PHPFUL_PATH') or define('PHPFUL_PATH', __DIR__);
defined('CORE_PATH')   or define('CORE_PATH',  PHPFUL_PATH.'/Core');//框架核心库路径
defined('LIB_PATH')    or define('LIB_PATH',PHPFUL_PATH.'/Lib');//框架扩展库路径
define('DS', DIRECTORY_SEPARATOR);
define('ROOT',dirname(realpath($_SERVER['SCRIPT_FILENAME'])));//网站根目录

//自动加载类
spl_autoload_register('phpful\autoLoad');//这个注册是全局的；

//获取配置信息
$config= Core\Config::getIns()->getAll();
$modules=array_values($config["default"]["MODULE_MAP"]);

$config['default']=array_merge(
        array(
	'MODULE'=>$modules,
	'DEFAULT_INDEX'=>'index.php',
	'DEFAULT_MOUDLE'=>'Common',
	'RESOURCE_SEPERATOR'=>'/'

	),$config['default']);



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

$resource=array();
$query=array();
$uri=$_SERVER['REQUEST_URI'];
//检查是否存在已经定义的路由，
if(($router=checkRouters($uri,$config['default']))!=false){
    list($module,$controller,$method,$query)=$router;
    $controller=str_ireplace("Controller", "", $controller);
    $query=explode($config['default']['RESOURCE_SEPERATOR'], substr($query,1));
}else{
    if($uri=='/'){//如果是根目录，则调用 App\controllers\IndexController这个控制器
        $module=$config['default']['DEFAULT_MOUDLE'];
        $controller='Index';
    }else{
        
        $query=$url=false===strpos($uri,'/'.$config['default']['DEFAULT_INDEX'])?substr($uri,1):substr($uri,11); //规则是：/模块名/模型名/字段1的索引/字段1的值/字段2的索引/字段2的值


        //获取URL关键字：$key
        $key=$url;

        if(($position=strpos($url,"&"))!=false){
            $key=substr($url, 0,$position);
            $key=rtrim($key,$config['default']['RESOURCE_SEPERATOR']);
        }

        $keys=explode($config['default']['RESOURCE_SEPERATOR'],$key);


        /*
         *根据关键字来获取模块名和控制器名
         */	
        if(!isModule($config,$keys[0])){//不含模块名的URL关键字，这个关键字就表示控制器名

            $module="Common";
            $controller=$keys[0];
            foreach ($config["default"]["MODULE"] as  $val) {

                    if(in_array($keys[0],$config[$val]["CONTROLLER_MAP"])){
                            $module=$val;
                            break;
                    }
            }
            $controller=getRealController($config,$module,$keys[0]);

        }else{
            //$keys[0]、$keys[1]分别表示keys中的模块部分和控制器部分,必须始终保证这一点
            $module=getRealModule($config,$keys[0]);
            if(!isset($keys[1])){
                    $controller="Index";
            }else{

                    $controller=getRealController($config,$module,$keys[1]);
            }

        }


            //var_dump($module,$controller);


    }

    $method=ucfirst($_SERVER['REQUEST_METHOD']);//访问页面使用的请求方法

}


//不管上面如何处理，只要最终返回真实的模块名、控制器名、方法名即可，分别是$module、$controller、$method

define('MODULE',$module);//真实的模块名
define('CONTROLLER',$controller);//真实的控制器名称和模型名称
define('METHOD',$method);
$classname=APP_NAME.'\\'.$module.'\controllers\\'.$controller.'Controller';


//调用对应控制器的方法
if(class_exists($classname)){

        $ref=new \ReflectionClass($classname);
        $controller=$ref->newInstance();
    //前置的拦截器方法    
    if(method_exists($classname,"Before".$method)) {
        call_user_func(array($classname,"Before".$method)); 
    }

    $controller->$method($query); //执行对应的控制器方法
    
    //后置的拦截器方法
    if(method_exists($classname,"After".$method)) {
        call_user_func(array($classname,"After".$method)); 	    	
    }

}else{
    exit('<div><h1  style="color:#f00" >404</h1></div>');
    //Class '.$classname.' is Not found !
}

/*
 *自动加载类的函数
 */
function autoLoad($classname) {  //参数$classname就是要调用但可能尚未加载的类名
    $cname=str_replace('\\', '/', $classname);
    $classpath=dirname(__DIR__).'/'.$cname.'.class.php';
    $filepath=dirname(__DIR__).'/'.$cname.'.php';
    if(file_exists($classpath)){
		require_once($classpath);
    }elseif(file_exists($filepath)){
		require_once($filepath);
    }

    //require_once(realpath(APP_PATH).'/'.$name . '.class.php');//This is wrong ,why?
}

function checkRouters($uri,$conf){
    if(isset($conf['ROUTERS'])){
            foreach ($conf['ROUTERS'] as $mode => $action) {
                    $pattern="/".str_replace("/", "\/", $mode)."/";
                    if(preg_match($pattern, $uri)){
                            $query=preg_replace($pattern, "", $uri);
                            $action=str_replace(APP_NAME."\\","", $action);
                            list($module,$nil,$controller,$method)=explode("\\",$action);

                            return array($module,$controller,$method,$query);
                    }
            }
    }
    return false;

}

function isModule($config,$arg){

    if(array_key_exists($arg, $config["default"]["MODULE"])||in_array($arg,$config["default"]["MODULE"])){
            return true;
    }

    return false;
}


//根据模块映射中定义的模块名来获取真实的模块名
function getRealModule($config,$module){
    if (isset($config['default']['MODULE_MAP'])) {

            if(array_key_exists($module,$config['default']['MODULE'])){
                    $module=$config['default']['MODULE_MAP'][$module];

            }
    }			
    return $module;
}

//根据模块映射中定义的控制器名来获取真实的控制器名
function getRealController($config,$module,$controller){
    if (isset($config[$module]['CONTROLLER_MAP'])) {
            if (array_key_exists($controller,$config[$module]['CONTROLLER_MAP'])){

                    $controller=$config[$module]['CONTROLLER_MAP'][$controller];//真实的模型名，对应着控制器

            }
    }	
    return $controller;
}


function U($s){
    return $config['default']['DEFAULT_INDEX'].$s;
}

?>
