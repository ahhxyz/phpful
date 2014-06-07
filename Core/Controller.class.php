<?php
namespace phpful\Core;
class Controller{
	public $model;
	private $args;
	private $file;
	private $_vars;
        public $query;//URI中的参数
	public function __construct($file=''){
		$this->file=$file;
                
                if(method_exists($this, "init")){
		$this->init();//子类中定义的控制器公共方法
                }
	}




	public function assign($name,&$val=''){
		if(is_array($name)){
			$this->_vars=array_merge($this->_vars,$name);
		}
		else{
			$this->_vars[$name]=$val;
			
		}
		
		
	}



	public function display(){
		$view=new view($this->file);
		  $page=$view->parse();
		  $file=APP_PATH.'/Common/Runtime/Cache/'.md5(time()).'.php';//$_COOKIE['PHPSESSID']
		  if(file_exists($file)){
			  unlink($file);
		  }
		  file_put_contents($file,$page);
		  require($file);
	}

	/*
	 *Restful的方法
	 *restful的url示例：
	 GET /user  :获取资源user的所有数据
	 GET /user/1  :获取资源user的field=1的数据
	 GET /user/1-2-12-108  :多字段查询
	 所以，restful的url里面出现的只有资源，当然就不能出现动词喽。
	 **************************************************************************************************************************************
	 *Get()方法：用来获取资源
	 *@解析URI来获取资源参数，其实就是模型的某个字段的值。为空则表示获取所有数据；
	 *对参数进行解析，根据解析结果来查询相应字段，然后返回数据；比如：1-2-12-108，这个参数中，每部分的数值在不同的模型中对应不同的字段；
	 */

	public function get(){
		echo '<h1 style="color:#49c;width:100%;text-align:center;">Welcome to use phpful!</h1>';//首次安装时会执行该方法。
	}

	/*
	 *Post()方法：用来新建资源
	 */


	public function post(){

	}

	/*
	 *Put()方法：用来更新资源
	 */

	public function put(){

	}

	/*
	 *Delete()方法：用来删除资源
	 */


	public function delete(){

	}

public function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
         echo($output);
         return null;
    }else
         return $output;
}


}


?>