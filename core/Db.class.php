<?php
namespace phpful\core;
use phpful\core;

/*
 *只针对关系型数据库，且只使用PDO
 *构造函数的参数$args是个数组，表示动态的数据库配置信息
 */
class Db{
    private static   $instance;
    private $_pdo;
    private function __construct($args=""){ 
        $configs=Config::getInstance()->getConfig();
        $this->model=get_class($this);

        $this->_table=isset($args[0])?$args[0]:strtolower(CONTROLLER);
        $this->prefix=$config[MODULE]['DB']['PREFIX'];
        $this->table=$this->prefix.$this->_table;
        $module=$config["Common"]["MODULE"][$num];
        $Db=$config[$module]["DB"];
        return $Db["TYPE"].':host='.$Db["HOST"].';dbname='.$Db["DBNAME"].','.$Db["USER"].",".$Db["PWD"];    
        $this->_pdo= new \PDO(self::$Conn[0],self::$Conn[1],self::$Conn[2],array(\PDO::ATTR_PERSISTENT => true));
        
		

    }


    public static function getInstance($config,$num){
        if(!isset(self::$instance)){

            self::$instance=new Db();

        }
        return self::$instance;
    }


    public function Get(){
		
	
        $statement=  $this->_pdo->prepare($sql);
        $statement->execute();		
        $res=$statement->fetchAll();
        //self::$instance=null;
        return $res;	
				
		/*
	SELECT 
	article.aid,article.title,
	user.username,
	type.typename 
	FROM article 
	JOIN user ON article.uid=user.uid 
	JOIN type ON article.tid=type.tid


		*/	

    }



	public function insert($data=''){
		$this->create();
		
		if(empty($data)){
			$data=$this->_data;
		}
		$data=filter($data);
		foreach($data as $key=>$val){
			$field[]=$key;
			$value[]='"'.$val.'"';		
		}

		$fields=implode(',',$field);
		$values=implode(',',$value);
		self::$instance->exec('INSERT INTO '.$this->_table.' ('.$fields.')'.' VALUES('.$values.') ');

		$_pk=self::$instance->lastInsertId();
		//self::$instance=null;
		return $_pk;

	}




	public function Put( $data=''){
        

	    if(!isset($this->where)){
	       echo json_encode(array('errorMsg'=>'必须设置where条件！'));
	       return ;
	    }

        foreach($data as $key=>$val){
		   $sets[]='  '.$key.'="'.$val.'"';		   
		}
		 
		$set=implode(',',$sets);
	
		$res= self::$instance->exec('UPDATE  '.$this->_table.' SET '.$set.' '.$this->where);
		//self::$instance=null;
		return $res;

	}
	






	public function Delete(){
	    $res=self::$instance->exec('DELETE FROM '.$this->_table.$this->where);
		//self::$instance=null;
		return $res;
	}








	public function where($args){
		$this->where=' where '.$args;			
				
		return $this;
		
	}


	public function fields($str){
	 	$this->fields[]=$str;
		return $this;
	}








public function create($data=''){
	if($_POST['token']==$_SESSION['token']){
		echo json_encode(array('errorMsg'=> 'Token wrong'));
		return ;
	}
	if(!empty($data)){
		$this->_data=$data;				

	}else{
		$this->data=$_POST['data'];
	 //先检查是否是基本模型，如果是则进行字段映射;
		if(isset($this->_map)){

			//检查字段映射中的数据库字段是否正确
			foreach($this->_map as $val){
				if(!array_key_exists($val,$this->_fields)){
					
					echo json_encode(array('errorMsg'=> '字段映射错误！'));
					return false;			
				}
			}
		}

		if(isset($this->_validate)){
		//进行字段验证
			foreach($this->_validate as $val){
			    switch($val['1']){
			        case 'require':
			            if(empty($this->data[$val['0']])){
				            echo json_encode(array('errorMsg'=>$val['2']));
				            return false;
				        }
			        break;

			        case 'email':			        
				        $reg='/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/';
				        if(!preg_match($reg,str_replace('%40','@',$this->data[$val['0']]))){
				            echo json_encode(array('errorMsg'=>$val['2']));
				            return false;
					 	}			   
			        break;
			        
			        case 'number':			   
			        break;


			        case 'unique':
			            $column=isset($this->_map[$val['0']])?$this->_map[$val['0']]:$val['0'];
				        $column_val=$this->data[$val['0']];//这个字段的值;
				        $fun='getBy'.$column;
			            if($this->$fun($column_val)){
				            echo json_encode(array('errorMsg'=>$val['2']));
				        }
					        return false;
				    break;

					case 'function':
					    $commonFile=APP_PATH.'/'.MODULE.'/'.'/Common/common.php';
					    if (file_exists($commonFile)) {
					    	
					    	require_once($commonFile);
					    	
					    	if(!$val['1']()){
				            	echo json_encode(array('errorMsg'=>$val['2']));
					        	return false;
					        }	
					    }
					break;
					

					case 'callback':
						if(!$this->$val['1']()){
				            	echo json_encode(array('errorMsg'=>$val['2']));
					        	return false;
						}

					break;


					case 'in':
						if($val['1']<$condition2['0']&&$val['1']>$condition2['1']){
						    $rlt[]=true;	
						}else{
					        return false;
					        $this->valError[]=$val['2'];							
						}
					break;


					case 'confirm':
						$to=array_key_exists($val['0'],$this->data)?$this->data[$val['0']]:$val['0'];

						if($to!=$this->data[$val['1']]){
					        $rlt[]= false;
					        $this->valError[]=$val['2'];
						}else{
						    $rlt[]=true;	
						}
					break;
				}

			}

		}
		
		//根据字段映射创建数据;							
		//$this->_data=isset($this->no_repeat)?$this->no_repeat():$this->data;
		foreach($this->_data as $key=>$val){
			if(array_key_exists($key,$this->_map)){
				$value=$this->_data[$key];
				$this->_data[$this->_map[$key]]=$value;
				unset($this->_data[$key]);
			}
		}
		
		if(isset($this->_auto)){
		//数据自动完成
			foreach($this->_auto as $key=>$val){
				$this->_data[$val['0']]=$val['1'];			   
			}	
		}

	}

	$this->_data=filter($this->_data);
}









public function getBy($field,$val){
	$select=isset($this->fields)?$this->fields:' * ';
	$res=self::$instance->query('SELECT '.$select.' FROM '.$this->_table.' WHERE '.$field.'="'.$val.'"');
	//self::$instance=null;
	return $res;
}



public function getFieldBy($field,array $args){		
	$statement=self::$instance->prepare('SELECT '.$args['1'].' FROM '.$this->_table.' WHERE '.$field.'="'.$args['0'].'"');
	$statement->execute();
	$res=$statement->fetchColumn();
	//self::$instance=null;
	return $res;
}



	/*
	过滤函数
	$val;要过滤的参数值;
	
	
	
	*/
	public function filter($data){
		if(is_array($data)){

		}else{

		}
		return $data;
		
	}



	/*
	取得当前用户数据表的总记录数;
	*/
	public function Count(){

	}


	public function Max($field){
	    
	}

	public function Min($field){

	}
	public function Avg($field){

	}

	public function Sum($field){

	}

		/*
		如果接收的数据中有字段不需插入数据库，那么就在字段验证后剔除这些字段；
		
		*/
	public function no_repeat(){
	 	foreach($this->no_repeat as $val){
			unset($this->data[$val]);			
		}
	
		return $this->data;
	}






	public function addPrefix($val){
		preg_match_all('/(on(|\s)).*?(\.)/is',$val,$on); 
	    $sOn=$on['0']['0'];
		$rOn=trim(str_replace('.','',str_replace('on','',$on['0']['0'])))!='admin'?str_replace('on','on '.$this->prefix,str_replace(' ','',$sOn)):$sOn;
		$val=str_ireplace($sOn,$rOn,$val);
		preg_match_all('/(=(|\s)).*?(\.)/is',$val,$eq); 
	    $sEq=$eq['0']['0'];
		$rEq=trim(str_replace('.','',str_replace('=','',$eq['0']['0'])))!='admin'?str_replace('=','='.$this->prefix,str_replace(' ','',$sEq)):$sEq;
		$val=str_ireplace($sEq,$rEq,$val);
		return $val;
						
	}

}


	
	

?>