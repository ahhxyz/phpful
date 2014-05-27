<?php
namespace phpful\Core;


/*
 *只针对关系型数据库，且只使用PDO
 *构造函数的参数$args是个数组，表示动态的数据库配置信息
 */
class Model{
    //public    $conn=array();//所有的数据库连接，即所有的PDO对象
	protected static   $PDO;//当前的数据库连接
	protected static   $Conn;
	public    $prefix;//数据表前缀
	protected $model; //完整的模型类的名称，即包含模型类的包的信息
	protected $_table;//不含前缀的数据表名称
	protected $table;//完整的的数据表名称
    private   $where;//where子句,使用此变量的每个方法必须先初始化在使用，因为这是成员变量而不是局部变量，下同;
    private   $_limit;//limit子句;
    private   $_order;//order子句;
    protected $_view;//视图模型;
    protected $_rel;//关联模型;
    public    $_fields=array();//这是一个数字数组，它的值是数据库的数据表字段;
    protected $fields;//pai chu
	protected $data=array();//从表单接受过来的数据；
	protected $_data=array();//$data处理后的数据,用来插入数据库,这个数组的键名对应数据表的字段名;
	protected $_map;
	protected $_validate;//字段验证;
	protected $_auto=array();
	public function __construct($args=""){ 
		$config=\phpful\Common\C();
		$this->model=get_class($this);

		$this->_table=isset($args[0])?$args[0]:strtolower(CONTROLLER);
		$this->prefix=$config[MODULE]['DB']['PREFIX'];
        $this->table=$this->prefix.$this->_table;

        if(!isset(self::$PDO)){
        	if(isset($args[1])){
        		if(is_numeric($num)){
        			$Conn=self::getConn($config,$args[1]);
        		}else{
        			$Conn=$args[1];
        		}
        	}else{
        		$Conn=self::getConn($config,0);
        	}
        	self::$Conn=explode(",", $Conn);
        	self::$PDO= new \PDO(self::$Conn[0],self::$Conn[1],self::$Conn[2],array(\PDO::ATTR_PERSISTENT => true));

        }



        $this->_fields();
		

	}


	private static function getConn($config,$num){
		$module=$config["Common"]["MODULE"][$num];
		$Db=$config[$module]["DB"];
		return $Db["TYPE"].':host='.$Db["HOST"].';dbname='.$Db["DBNAME"].','.$Db["USER"].",".$Db["PWD"];
	}



	public function __set($name,$value){
		if(array_key_exists($name,$this->_fields)){
		  	$this->_data[$name]=filter($value);			
		}
		
	}



	public function __get($name){
		if(isset($this->_data[$name])){			
			return $this->_data[$name];
		}
		
	}



	public function __call($name,$args){	
		if(substr_count($name,'getBy')==1){			
			return $this->getBy(strtolower(substr($name,5)),$args['0']);			
		}elseif(substr_count($name,'getFieldBy')==1){
		    return $this->getFieldBy(strtolower(substr($name,10)),$args);			
		}
		
		
	}



	/*
	取得当前数据的所有字段;
	*/
	private function _fields(){
		    $statement=self::$PDO->prepare('select * from '.$this->table);
		    $statement->execute();
		    $res=$statement->fetch(\PDO::FETCH_ASSOC);
		    if(!empty($res)){
		        foreach($res as $key=>$val){
			     $this->_fields[]=$key;			
		        }
		    }
		

	}




	public function Get(){
		if(isset($this->_view)){
			$tables=array_keys($this->_view);
		    foreach($this->_view as $model=>$fields){
			    foreach($fields as $key=>$val){
					if($key=='_on'){
						$val=str_replace('=', '='.$this->_prefix, $val);
						$_joins[]=' JOIN '.$model.' ON '.$val;		
						//$on=$val;
					}else{
					    $_fields[]=' '.is_int($key)?$this->_prefix.$model.$val:$this->_prefix.$model.$key.' as '.$val;
					}				
			    }
		    }
			$_fields=implode(',',$_fields);
			$_joins =implode(' ',$_joins);
			$sql='SELECT '.$_fields.' '.$_joins.' '.$this->where.' '.$this->_limit;
			
		}elseif(isset($this->_rel)){
					foreach($this->_rel as $key=>$val){
						$tables[]=$key;
						foreach($val as $field=>$value){
							$type[$key]=$field['mapping_type'];
							$name[$key]=$field['mapping_name'];
							$foreign_key[$key]=$field['foreign_key'];						
						}
						
					}  


				$sql='';
			
		}else{
			echo $sql='SELECT * FROM '.$this->table.' '.$this->where.' '.$this->_limit;

		}
		//echo $this->where;
		$statement=self::$PDO->prepare($sql);
		$statement->execute();		
		$res=$statement->fetchAll();
		//self::$PDO=null;
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



	public function Post($data=''){
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
		self::$PDO->exec('INSERT INTO '.$this->_table.' ('.$fields.')'.' VALUES('.$values.') ');

		$_pk=self::$PDO->lastInsertId();
		//self::$PDO=null;
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
	
		$res= self::$PDO->exec('UPDATE  '.$this->_table.' SET '.$set.' '.$this->where);
		//self::$PDO=null;
		return $res;

	}
	






	public function Delete(){
	    $res=self::$PDO->exec('DELETE FROM '.$this->_table.$this->where);
		//self::$PDO=null;
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
	$res=self::$PDO->query('SELECT '.$select.' FROM '.$this->_table.' WHERE '.$field.'="'.$val.'"');
	//self::$PDO=null;
	return $res;
}



public function getFieldBy($field,array $args){		
	$statement=self::$PDO->prepare('SELECT '.$args['1'].' FROM '.$this->_table.' WHERE '.$field.'="'.$args['0'].'"');
	$statement->execute();
	$res=$statement->fetchColumn();
	//self::$PDO=null;
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